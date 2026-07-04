<?php

namespace App\Filament\Resources\FoodSafetyAuditResource\Pages;

use App\Filament\Resources\FoodSafetyAuditResource;
use App\Models\Menu;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
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
            ->where('date', $this->date);

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
            ->where('date', $this->date);

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
                        'invoice' => 'HĐ-'.($ingredient->supplier->code ?? 'NCC').'-'.mt_rand(100, 999),
                        'vet_check' => ($ingredient->type === 'Động vật') ? 'Đạt' : '—',
                        'sensory' => 'Đạt',
                        'quick_test' => '—',
                        'notes' => 'Cảm quan tốt, sạch sẽ',
                    ];
                }
            }

            return array_values($seenIngredients);
        }

        // Steps 2 to 5: Dishes list check
        $dishes = [];
        foreach ($menus as $index => $menu) {
            $recipe = $menu->recipe;
            if (! $recipe) {
                continue;
            }

            $dishName = $recipe->name;

            if ($this->activeStep === 'Bước 2') {
                $dishes[] = [
                    'name' => $dishName,
                    'time' => '09:30',
                    'sensory' => 'Đạt',
                    'temp' => '85°C',
                    'cook' => $this->inspector,
                    'kitchen' => 'Bếp số '.(($index % 3) + 1),
                    'notes' => 'Chín đều, màu sắc tốt',
                ];
            } elseif ($this->activeStep === 'Bước 3') {
                $dishes[] = [
                    'name' => $dishName,
                    'time' => '11:00',
                    'sensory' => 'Đạt',
                    'sample_kept' => 'Có (Tủ lưu mẫu)',
                    'temp' => '72°C',
                    'notes' => 'Khay chia thức ăn sạch',
                ];
            } elseif ($this->activeStep === 'Lưu mẫu') {
                $dishes[] = [
                    'name' => $dishName,
                    'time' => '10:45',
                    'quantity' => '150g',
                    'sample_code' => 'M-'.str_replace('-', '', $this->date).'-'.$recipe->code,
                    'temp' => '4°C',
                    'staff' => $this->inspector,
                    'notes' => 'Hộp vô trùng, niêm phong',
                ];
            } elseif ($this->activeStep === 'Hủy mẫu') {
                $dishes[] = [
                    'name' => $dishName,
                    'time' => '11:00',
                    'retention' => '24 giờ',
                    'status' => 'Bình thường',
                    'staff' => $this->inspector,
                    'notes' => 'Hủy mẫu theo quy định',
                ];
            }
        }

        return $dishes;
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
}
