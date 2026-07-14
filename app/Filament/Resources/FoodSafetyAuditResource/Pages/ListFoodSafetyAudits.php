<?php

namespace App\Filament\Resources\FoodSafetyAuditResource\Pages;

use App\Exports\FoodSafetyAuditReportExport;
use App\Filament\Resources\FoodSafetyAuditResource;
use App\Models\Employee;
use App\Models\FoodSafetyAudit;
use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\PurchaseOrderItem;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListFoodSafetyAudits extends ListRecords
{
    protected static string $resource = FoodSafetyAuditResource::class;

    protected static string $view = 'filament.pages.food-safety-audit';

    public ?string $date = '2026-05-18'; // Default date from seeder for easy review

    public ?int $selectedShift = 1; // Default CA 1

    public string $activeStep = 'Bước 1';

    public string $canteen = '';

    public string $inspector = '';

    public function mount(): void
    {
        parent::mount();

        // Địa điểm tự nhận theo bếp của tài khoản đang đăng nhập (BA: không nhập tay)
        $kitchenId = auth()->user()?->currentKitchenId();
        $this->canteen = ($kitchenId ? Kitchen::find($kitchenId)?->name : null)
            ?? Kitchen::first()?->name
            ?? 'Bếp ăn';

        // Người kiểm tra mặc định: nhân viên liên kết với tài khoản (chọn lại từ danh mục trên UI)
        $this->inspector = auth()->user()?->employee?->name ?? '';
    }

    /**
     * Danh mục nhân viên để chọn Người kiểm tra (BA: chọn từ danh mục, không gõ tay).
     *
     * @return array<int, string>
     */
    public function getInspectorOptions(): array
    {
        return Employee::query()
            ->where('status', 'Đang làm việc')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

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
            $ingredientIds = $menus
                ->flatMap(fn ($menu) => $menu->recipe?->ingredients->pluck('id') ?? collect())
                ->unique()
                ->values();

            // NCC + chứng từ lấy theo PO ĐÃ NHẬP KHO gần nhất của từng nguyên liệu
            // (BA: theo PO/ngày nhập thực tế, không dùng NCC cố định của nguyên liệu)
            $poInfoByIngredient = PurchaseOrderItem::query()
                ->whereIn('ingredient_id', $ingredientIds)
                ->whereHas('purchaseOrder', fn ($q) => $q->whereNotNull('stocked_at'))
                ->with(['purchaseOrder:id,code,supplier_id,stocked_at', 'purchaseOrder.supplier:id,name,phone,contact_name'])
                ->get(['id', 'purchase_order_id', 'ingredient_id'])
                ->sortByDesc(fn ($item) => $item->purchaseOrder?->stocked_at)
                ->unique('ingredient_id')
                ->keyBy('ingredient_id');

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

                    $poInfo = $poInfoByIngredient->get($ingredient->id);

                    $seenIngredients[$ingredient->id] = [
                        'name' => $ingredient->name,
                        'type' => $ingredient->type,
                        'time' => $poInfo?->purchaseOrder?->stocked_at?->format('H:i') ?? '',
                        'quantity' => $qty,
                        'unit' => $ingredient->unit,
                        'supplier' => $poInfo?->purchaseOrder?->supplier?->name
                            ?? $ingredient->supplier->name
                            ?? 'Cơ sở tự do',
                        'supplier_contact' => $poInfo?->purchaseOrder?->supplier?->phone ?? '',
                        'deliverer' => $poInfo?->purchaseOrder?->supplier?->contact_name ?? '',
                        // Chứng từ = mã PO thật đã nhập kho; chưa có PO thì để trống thay vì chuỗi tự chế
                        'invoice' => $poInfo?->purchaseOrder?->code ?? '—',
                        'vet_check' => ($ingredient->type === 'Động vật') ? 'Đạt' : '—',
                        'quarantine' => ($ingredient->type === 'Động vật') ? 'Có' : '—',
                        'sensory' => 'Đạt',
                        'quick_test' => '—',
                        'action' => '',
                        'notes' => '',
                    ];
                }
            }

            // Gom theo PHÂN LOẠI nguyên liệu (Động vật / Thực vật / Gia vị...) theo mẫu QĐ 1246
            uasort($seenIngredients, fn (array $a, array $b): int => [$a['type'], $a['name']] <=> [$b['type'], $b['name']]);

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

            // Các key bổ sung (shift/portions/main_ingredients/...) phục vụ Excel theo
            // biểu mẫu B1–B5; các key cũ giữ nguyên cho bảng hiển thị trên trang.
            $shiftLabel = $menu->shift->name ?? '';
            $portions = (int) $menu->estimated_portions;

            if ($this->activeStep === 'Bước 2') {
                $mainIngredients = $recipe->ingredients
                    ->map(fn ($ing) => $ing->name.' '.rtrim(rtrim(number_format($ing->pivot->quantity_per_portion * $portions, 1, ',', '.'), '0'), ',').'kg')
                    ->take(6)
                    ->implode(', ');

                $dishes[] = [
                    'name' => $dishName,
                    'shift' => $shiftLabel,
                    'main_ingredients' => $mainIngredients,
                    'portions' => $portions,
                    // cook_start_at là chuỗi TIME (không cast datetime) — cắt HH:MM như timeRange()
                    'prep_time' => $audit?->cook_start_at ? substr((string) $audit->cook_start_at, 0, 5) : '',
                    'time' => $this->timeRange($audit?->cook_start_at, $audit?->cook_end_at),
                    'staff_check' => 'Đạt',
                    'equipment_check' => 'Đạt',
                    'area_check' => 'Đạt',
                    'sensory' => $audit->status ?? '',
                    'temp' => $audit->temperature ?? '',
                    'cook' => $audit->inspected_by ?? '',
                    'kitchen' => $shiftLabel,
                    'action' => '',
                    'notes' => $audit->notes ?? '',
                ];
            } elseif ($this->activeStep === 'Bước 3') {
                $dishes[] = [
                    'name' => $dishName,
                    'shift' => $shiftLabel,
                    'portions' => $portions,
                    'time' => $audit?->sample_kept_at?->format('H:i') ?? '',
                    'eat_time' => $audit?->sample_kept_at?->copy()->addMinutes(30)?->format('H:i') ?? '',
                    'utensil' => $audit->utensil ?? '',
                    'sensory' => $audit->status ?? '',
                    'sample_kept' => $audit && $audit->sample_kept_by ? 'Có ('.$audit->sample_kept_by.')' : '',
                    'temp' => $audit->temperature ?? '',
                    'action' => '',
                    'notes' => $audit->notes ?? '',
                ];
            } elseif ($this->activeStep === 'Lưu mẫu') {
                $dishes[] = [
                    'name' => $dishName,
                    'shift' => $shiftLabel,
                    'portions' => $portions,
                    'sample_amount' => '≥100g',
                    'container' => $audit->utensil ?? '',
                    'time' => $audit?->sample_kept_at?->format('H:i') ?? '',
                    'destroy_at' => $audit?->sample_kept_at?->copy()->addDay()?->format('H:i (d/m)') ?? '',
                    'quantity' => '',
                    'sample_code' => $audit->sample_code ?? '',
                    'temp' => $audit->temperature ?? '',
                    'staff' => $audit->sample_kept_by ?? '',
                    'destroyer' => '',
                    'notes' => $audit->notes ?? '',
                ];
            } elseif ($this->activeStep === 'Hủy mẫu') {
                $dishes[] = [
                    'name' => $dishName,
                    'shift' => $shiftLabel,
                    'portions' => $portions,
                    'sample_amount' => '≥100g',
                    'container' => $audit->utensil ?? '',
                    'temp' => $audit->temperature ?? '',
                    'kept_at' => $audit?->sample_kept_at?->format('H:i') ?? '',
                    'time' => $audit?->sample_kept_at?->copy()->addDay()?->format('H:i (d/m)') ?? '',
                    'retention' => '24 giờ',
                    'status' => $audit->status ?? '',
                    'keeper' => $audit->sample_kept_by ?? '',
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
