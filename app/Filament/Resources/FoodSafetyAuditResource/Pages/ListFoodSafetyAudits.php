<?php

namespace App\Filament\Resources\FoodSafetyAuditResource\Pages;

use App\Exports\FoodSafetyAuditReportExport;
use App\Filament\Resources\FoodSafetyAuditResource;
use App\Models\FoodSafetyAudit;
use App\Models\Menu;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListFoodSafetyAudits extends ListRecords
{
    protected static string $resource = FoodSafetyAuditResource::class;

    protected static string $view = 'filament.pages.food-safety-audit';

    public ?string $date = '2026-05-18'; // Default date from seeder for easy review

    public ?int $selectedShift = 1; // Default CA 1

    public string $activeStep = 'Bước 1';

    public string $canteen = 'Canteen Summit';

    public string $inspector = 'Nguyễn Văn An';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tạo dữ liệu'),
        ];
    }

    public function getStats(): array
    {
        if (! $this->date) {
            return [
                'ingredients' => 0,
                'portions' => 0,
                'dishes' => 0,
                'forms' => 5,
            ];
        }

        $query = Menu::with(['recipe.ingredients'])
            ->whereDate('date', $this->date);

        if ($this->selectedShift) {
            $query->where('shift_id', $this->selectedShift);
        }

        $menus = $query->get();

        $portions = $menus->sum('estimated_portions');
        $dishes = $menus->pluck('recipe_id')->unique()->count();

        $ingredientIds = [];
        foreach ($menus as $menu) {
            if ($menu->recipe) {
                foreach ($menu->recipe->ingredients as $ingredient) {
                    $ingredientIds[$ingredient->id] = true;
                }
            }
        }
        $ingredients = count($ingredientIds);

        return [
            'ingredients' => $ingredients,
            'portions' => $portions,
            'dishes' => $dishes,
            'forms' => 5,
        ];
    }

    public function getAuditItems(): array
    {
        if (! $this->date) {
            return [];
        }

        $query = Menu::with(['recipe.ingredients.supplier', 'shift'])
            ->whereDate('date', $this->date);

        if ($this->selectedShift) {
            $query->where('shift_id', $this->selectedShift);
        }

        $menus = $query->get();

        if ($this->activeStep === 'Bước 1') {
            // Step 1: Input raw ingredients check
            $seenIngredients = [];
            foreach ($menus as $menu) {
                $recipe = $menu->recipe;
                if (! $recipe) {
                    continue;
                }
                foreach ($recipe->ingredients as $ingredient) {
                    $qty = $menu->estimated_portions * $ingredient->pivot->quantity_per_portion;
                    if (isset($seenIngredients[$ingredient->id])) {
                        $seenIngredients[$ingredient->id]['quantity'] += $qty;

                        continue;
                    }

                    $seenIngredients[$ingredient->id] = [
                        'name' => $ingredient->name,
                        'type' => $ingredient->type,
                        'time' => '05:00',
                        'quantity' => $qty,
                        'unit' => $ingredient->unit,
                        'supplier' => $ingredient->supplier->name ?? 'Cơ sở tự do',
                        'invoice' => 'HĐ-'.($ingredient->supplier->code ?? 'NCC').'-'.str_replace('-', '', $this->date),
                        'vet_check' => ($ingredient->type === 'Động vật') ? 'Đạt' : '—',
                        'sensory' => 'Đạt',
                        'quick_test' => '—',
                        'notes' => 'Cảm quan tốt, sạch sẽ',
                    ];
                }
            }

            return array_values($seenIngredients);
        }

        // Steps 2 to 5: Dishes list check — lấy dữ liệu THẬT đã ghi nhận (nếu có)
        $records = $this->auditRecordsByRecipe();

        $dishes = [];
        foreach ($menus as $menu) {
            $recipe = $menu->recipe;
            if (! $recipe) {
                continue;
            }

            $dishName = $recipe->name;
            /** @var FoodSafetyAudit|null $audit */
            $audit = $records->get($recipe->id);

            if ($this->activeStep === 'Bước 2') {
                $dishes[] = [
                    'name' => $dishName,
                    'time' => $this->timeRange($audit?->cook_start_at, $audit?->cook_end_at),
                    'sensory' => $audit->status ?? '',
                    'temp' => $audit->temperature ?? '',
                    'cook' => $audit->inspected_by ?? '',
                    'kitchen' => $menu->shift->name ?? '',
                    'notes' => $audit->notes ?? '',
                ];
            } elseif ($this->activeStep === 'Bước 3') {
                $dishes[] = [
                    'name' => $dishName,
                    'time' => $audit?->sample_kept_at?->format('H:i') ?? '',
                    'sensory' => $audit->status ?? '',
                    'sample_kept' => $audit && $audit->sample_kept_by ? 'Có ('.$audit->sample_kept_by.')' : '',
                    'temp' => $audit->temperature ?? '',
                    'notes' => $audit->notes ?? '',
                ];
            } elseif ($this->activeStep === 'Lưu mẫu') {
                $dishes[] = [
                    'name' => $dishName,
                    'time' => $audit?->sample_kept_at?->format('H:i') ?? '',
                    'quantity' => '',
                    'sample_code' => $audit->sample_code ?? '',
                    'temp' => $audit->temperature ?? '',
                    'staff' => $audit->sample_kept_by ?? '',
                    'notes' => $audit->utensil ?? '',
                ];
            } elseif ($this->activeStep === 'Hủy mẫu') {
                $dishes[] = [
                    'name' => $dishName,
                    'time' => $audit?->sample_kept_at?->format('H:i') ?? '',
                    'retention' => '24 giờ',
                    'status' => $audit->status ?? '',
                    'staff' => $audit->sample_kept_by ?? '',
                    'notes' => $audit->notes ?? '',
                ];
            }
        }

        return $dishes;
    }

    /**
     * Lấy các bản ghi kiểm thực đã lưu cho ngày/ca/bước hiện tại, keyed theo recipe_id.
     *
     * @return Collection<int, FoodSafetyAudit>
     */
    protected function auditRecordsByRecipe(): Collection
    {
        $query = FoodSafetyAudit::whereDate('date', $this->date)
            ->where('stage', $this->activeStep)
            ->whereNotNull('recipe_id');

        if ($this->selectedShift) {
            $query->where('shift_id', $this->selectedShift);
        }

        return $query->get()->keyBy('recipe_id');
    }

    /**
     * Định dạng khoảng thời gian chế biến "HH:MM - HH:MM" từ 2 mốc giờ.
     */
    protected function timeRange(?string $start, ?string $end): string
    {
        $start = $start ? substr($start, 0, 5) : '';
        $end = $end ? substr($end, 0, 5) : '';

        if ($start && $end) {
            return $start.' - '.$end;
        }

        return $start ?: $end;
    }

    public function exportCSV(): StreamedResponse
    {
        $fileName = 'BaoCao_KiemThuc_3Buoc_'.str_replace('-', '', $this->date).'.csv';
        $items = $this->getAuditItems();

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($items) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel display support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Format depending on the current activeStep
            if ($this->activeStep === 'Bước 1') {
                // Header Step 1
                fputcsv($file, ['TT', 'Tên thực phẩm', 'Thời gian nhập', 'Khối lượng (Kg)', 'Nơi cung cấp', 'Hóa đơn chứng từ', 'ĐK Vệ sinh thú y', 'Cảm quan', 'Test nhanh', 'Ghi chú']);

                foreach ($items as $index => $item) {
                    fputcsv($file, [
                        $index + 1,
                        $item['name'] ?? '',
                        $item['time'] ?? '',
                        $item['quantity'] ?? 0,
                        $item['supplier'] ?? '',
                        $item['invoice'] ?? '',
                        $item['vet_check'] ?? '',
                        $item['sensory'] ?? '',
                        $item['quick_test'] ?? '',
                        $item['notes'] ?? '',
                    ]);
                }
            } elseif ($this->activeStep === 'Bước 2') {
                // Header Step 2
                fputcsv($file, ['TT', 'Tên món ăn', 'Thời gian chế biến', 'Cảm quan', 'Nhiệt độ chế biến', 'Người chế biến', 'Bếp thực hiện', 'Ghi chú']);

                foreach ($items as $index => $item) {
                    fputcsv($file, [
                        $index + 1,
                        $item['name'] ?? '',
                        $item['time'] ?? '',
                        $item['sensory'] ?? '',
                        $item['temp'] ?? '',
                        $item['cook'] ?? '',
                        $item['kitchen'] ?? '',
                        $item['notes'] ?? '',
                    ]);
                }
            } elseif ($this->activeStep === 'Bước 3') {
                // Header Step 3
                fputcsv($file, ['TT', 'Tên món ăn', 'Thời gian ăn', 'Cảm quan', 'Tủ lưu mẫu', 'Nhiệt độ khay', 'Ghi chú']);

                foreach ($items as $index => $item) {
                    fputcsv($file, [
                        $index + 1,
                        $item['name'] ?? '',
                        $item['time'] ?? '',
                        $item['sensory'] ?? '',
                        $item['sample_kept'] ?? '',
                        $item['temp'] ?? '',
                        $item['notes'] ?? '',
                    ]);
                }
            } elseif ($this->activeStep === 'Lưu mẫu') {
                // Header Step 4
                fputcsv($file, ['TT', 'Tên món ăn', 'Thời gian lưu', 'Khối lượng mẫu', 'Mã số mẫu', 'Nhiệt độ tủ lưu', 'Người lưu', 'Ghi chú']);

                foreach ($items as $index => $item) {
                    fputcsv($file, [
                        $index + 1,
                        $item['name'] ?? '',
                        $item['time'] ?? '',
                        $item['quantity'] ?? '',
                        $item['sample_code'] ?? '',
                        $item['temp'] ?? '',
                        $item['staff'] ?? '',
                        $item['notes'] ?? '',
                    ]);
                }
            } elseif ($this->activeStep === 'Hủy mẫu') {
                // Header Step 5
                fputcsv($file, ['TT', 'Tên món ăn', 'Thời gian hủy', 'Thời gian lưu giữ', 'Tình trạng mẫu', 'Người hủy', 'Ghi chú']);

                foreach ($items as $index => $item) {
                    fputcsv($file, [
                        $index + 1,
                        $item['name'] ?? '',
                        $item['time'] ?? '',
                        $item['retention'] ?? '',
                        $item['status'] ?? '',
                        $item['staff'] ?? '',
                        $item['notes'] ?? '',
                    ]);
                }
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    /**
     * Xuất báo cáo kiểm thực 3 bước theo biểu mẫu chuẩn Bộ Y tế (QĐ 1246/QĐ-BYT) — file .xlsx.
     * Mỗi bước là 1 sheet có tiêu đề gộp ô; dữ liệu B2/B3 lấy từ bản ghi kiểm thực đã lưu.
     */
    public function exportExcel(): BinaryFileResponse
    {
        $fileName = 'BaoCao_KiemThuc_3Buoc_'.str_replace('-', '', (string) $this->date).'.xlsx';

        $steps = ['Bước 1', 'Bước 2', 'Bước 3', 'Lưu mẫu', 'Hủy mẫu'];
        $itemsByStep = [];
        $previousStep = $this->activeStep;

        foreach ($steps as $step) {
            $this->activeStep = $step;
            $itemsByStep[$step] = $this->getAuditItems();
        }

        $this->activeStep = $previousStep;

        return Excel::download(
            new FoodSafetyAuditReportExport($itemsByStep, (string) $this->date, $this->canteen, $this->inspector),
            $fileName,
        );
    }
}
