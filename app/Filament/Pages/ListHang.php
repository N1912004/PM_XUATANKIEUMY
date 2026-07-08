<?php

namespace App\Filament\Pages;

use App\Models\Menu;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Shift;
use App\Models\Supplier;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ListHang extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.list-hang';

    public string $mode = 'list'; // 'list' or 'create_po'

    public ?string $date = null;

    public array $selectedShifts = [];

    public ?string $weekFrom = null;

    public ?string $weekTo = null;

    // PO Creation variables
    public ?string $poDate = null;

    public ?string $poSourceFrom = null;

    public ?string $poSourceTo = null;

    public array $poSelectedShifts = [];

    public array $poItems = [];

    public static function getNavigationGroup(): ?string
    {
        return __('CUNG ỨNG & KHO');
    }

    public static function getNavigationLabel(): string
    {
        return __('Danh sách hàng');
    }

    public function getTitle(): string
    {
        return __('Danh sách hàng');
    }

    public function mount(): void
    {
        $this->date = now()->toDateString();
        $dt = Carbon::parse($this->date);
        $this->weekFrom = $dt->copy()->startOfWeek()->toDateString();
        $this->weekTo = $dt->copy()->endOfWeek()->toDateString();
        $this->selectedShifts = Shift::pluck('id')->toArray();

        // PO Defaults
        $this->poDate = now()->toDateString();
        $this->poSourceFrom = now()->toDateString();
        $this->poSourceTo = now()->toDateString();
        $this->poSelectedShifts = Shift::pluck('id')->toArray();
    }

    public function updatedDate(): void
    {
        $dt = Carbon::parse($this->date);
        $this->weekFrom = $dt->copy()->startOfWeek()->toDateString();
        $this->weekTo = $dt->copy()->endOfWeek()->toDateString();
    }

    public function updatedPoSourceFrom(): void
    {
        $this->loadPOIngredients();
    }

    public function updatedPoSourceTo(): void
    {
        $this->loadPOIngredients();
    }

    public function updatedPoSelectedShifts(): void
    {
        $this->loadPOIngredients();
    }

    public function goToday(): void
    {
        $this->date = now()->toDateString();
        $this->updatedDate();
    }

    public function changeDay(int $delta): void
    {
        $this->date = Carbon::parse($this->date)->addDays($delta)->toDateString();
        $this->updatedDate();
    }

    public function goOrderCreate(): void
    {
        $this->mode = 'create_po';
        $this->poDate = $this->date;
        $this->poSourceFrom = $this->date;
        $this->poSourceTo = $this->date;
        $this->poSelectedShifts = $this->selectedShifts;
        $this->loadPOIngredients();
    }

    public function goBackToList(): void
    {
        $this->mode = 'list';
    }

    public function loadPOIngredients(): void
    {
        $kitchenId = auth()->user()?->currentKitchenId();

        if (empty($this->poSelectedShifts) || ! $this->poSourceFrom || ! $this->poSourceTo) {
            $this->poItems = [];

            return;
        }

        $menus = Menu::with(['recipe.ingredients.supplier'])
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->whereDate('date', '>=', $this->poSourceFrom)
            ->whereDate('date', '<=', $this->poSourceTo)
            ->whereIn('shift_id', $this->poSelectedShifts)
            ->get();

        // Prefetch existing PO codes per ingredient for the order date in one query
        // (avoids one query per ingredient inside the loop below).
        $existingPoCodesByIngredient = PurchaseOrderItem::query()
            ->whereHas('purchaseOrder', fn ($q) => $q->whereDate('estimated_delivery_date', $this->poDate))
            ->with('purchaseOrder:id,code')
            ->get(['id', 'purchase_order_id', 'ingredient_id'])
            ->groupBy('ingredient_id')
            ->map(fn ($items) => $items->pluck('purchaseOrder.code')->filter()->unique()->values());

        $agg = [];
        foreach ($menus as $menu) {
            $recipe = $menu->recipe;
            if (! $recipe) {
                continue;
            }

            foreach ($recipe->ingredients as $ing) {
                $qty = $menu->estimated_portions * $ing->pivot->quantity_per_portion;
                if ($qty <= 0) {
                    continue;
                }

                $ingId = $ing->id;
                if (isset($agg[$ingId])) {
                    $agg[$ingId]['total_suat'] += $menu->estimated_portions;
                    $agg[$ingId]['total_kg'] += $qty;
                    if (! in_array($recipe->name, $agg[$ingId]['dishes'])) {
                        $agg[$ingId]['dishes'][] = $recipe->name;
                    }
                } else {
                    // Already-ordered info: cap displayed codes to keep UI and Livewire payload small
                    $existingCodes = $existingPoCodesByIngredient->get($ingId, collect());
                    $orderedInfo = [
                        'total' => $existingCodes->count(),
                        'codes' => $existingCodes->take(3)->all(),
                    ];

                    $loai = 'kho';
                    if ($ing->type === 'Động vật') {
                        $loai = 'thit';
                    } elseif ($ing->type === 'Thực vật') {
                        $loai = 'uot';
                    }

                    $agg[$ingId] = [
                        'ingredient_id' => $ingId,
                        'name' => $ing->name,
                        'code' => $ing->code,
                        'unit' => $ing->unit,
                        'total_suat' => $menu->estimated_portions,
                        'total_kg' => $qty,
                        'quantity_manual' => round($qty, 3),
                        'reference_price' => (float) $ing->reference_price,
                        'supplier_id' => $ing->supplier_id,
                        'split' => 'P1',
                        'checked' => true,
                        'loai' => $loai,
                        'dishes' => [$recipe->name],
                        'ordered_info' => $orderedInfo,
                    ];
                }
            }
        }

        $this->poItems = array_values($agg);
    }

    public function bulkAssignSupplier(string $loai, int $supplierId): void
    {
        foreach ($this->poItems as $index => $item) {
            if ($item['loai'] === $loai) {
                $this->poItems[$index]['supplier_id'] = $supplierId;
            }
        }
    }

    public function createOrders(): void
    {
        $selectedItems = collect($this->poItems)->filter(fn ($item) => $item['checked'] && (float) ($item['quantity_manual'] ?? 0) > 0);

        if ($selectedItems->isEmpty()) {
            Notification::make()->title('Không có nguyên liệu nào được chọn để tạo PO!')->warning()->send();

            return;
        }

        $kitchenId = auth()->user()?->currentKitchenId();

        // Group by Supplier and Split (Phiếu 1, Phiếu 2, Phiếu 3)
        $grouped = $selectedItems->groupBy(fn ($item) => $item['supplier_id'].'-'.$item['split']);

        $poCount = 0;
        $poCodes = [];

        foreach ($grouped as $key => $items) {
            $parts = explode('-', $key);
            $supplierId = (int) $parts[0];
            $split = $parts[1];

            $supplier = Supplier::find($supplierId);
            if (! $supplier) {
                continue;
            }

            // Code format: PO-LH-YYYYMMDD-NCC-P1
            $nccCode = strtolower(str_replace(' ', '', $supplier->code ?: 'NCC'));
            $poCode = 'PO-LH-'.Carbon::parse($this->poDate)->format('Ymd').'-'.strtoupper($nccCode).'-'.$split;

            // Avoid duplicates
            $attempts = 0;
            while (PurchaseOrder::where('code', $poCode)->exists() && $attempts < 10) {
                $poCode = 'PO-LH-'.Carbon::parse($this->poDate)->format('Ymd').'-'.strtoupper($nccCode).'-'.$split.'-'.mt_rand(10, 99);
                $attempts++;
            }

            $po = PurchaseOrder::create([
                'code' => $poCode,
                'kitchen_id' => $kitchenId,
                'supplier_id' => $supplierId,
                'status' => 'draft',
                'estimated_delivery_date' => $this->poDate,
                'note' => 'Đơn đặt hàng tự động tạo từ List hàng ngày '.Carbon::parse($this->poDate)->format('d/m/Y')." ({$split})",
            ]);

            foreach ($items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'ingredient_id' => $item['ingredient_id'],
                    'quantity_ordered' => $item['quantity_manual'],
                    'quantity_received' => 0.0,
                    'unit_price' => $item['reference_price'],
                ]);
            }

            $poCount++;
            $poCodes[] = $poCode;
        }

        Notification::make()
            ->title('Tạo PO thành công!')
            ->body("Đã tạo tự động {$poCount} đơn đặt hàng nháp: ".implode(', ', $poCodes))
            ->success()
            ->persistent()
            ->send();

        $this->mode = 'list';
    }

    public function getGroupedData(): array
    {
        if (empty($this->selectedShifts) || ! $this->date) {
            return [];
        }

        $kitchenId = auth()->user()?->currentKitchenId();

        $shifts = Shift::whereIn('id', $this->selectedShifts)->get();
        $data = [];

        foreach ($shifts as $shift) {
            $menus = Menu::with(['recipe.ingredients'])
                ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
                ->whereDate('date', $this->date)
                ->where('shift_id', $shift->id)
                ->get();

            if ($menus->isEmpty()) {
                continue;
            }

            $dishes = [];
            $shiftPortions = 0;

            foreach ($menus as $menu) {
                $recipe = $menu->recipe;
                if (! $recipe) {
                    continue;
                }

                $shiftPortions += $menu->estimated_portions;
                $ingredients = [];

                foreach ($recipe->ingredients as $ingredient) {
                    $qty = $menu->estimated_portions * $ingredient->pivot->quantity_per_portion;
                    $ingredients[] = [
                        'code' => $ingredient->code,
                        'name' => $ingredient->name,
                        'quantity' => $qty,
                        'quantity_per_portion' => $ingredient->pivot->quantity_per_portion,
                        'unit' => $ingredient->unit,
                    ];
                }

                $dishes[] = [
                    'name' => $recipe->name,
                    'type' => $recipe->type,
                    'portions' => $menu->estimated_portions,
                    'ingredients' => $ingredients,
                ];
            }

            $data[] = [
                'id' => $shift->id,
                'name' => $shift->name,
                'time_range' => $shift->time_range,
                'total_portions' => $shiftPortions,
                'total_dishes' => count($dishes),
                'dishes' => $dishes,
            ];
        }

        return $data;
    }

    public function getStats(): array
    {
        if (empty($this->selectedShifts) || ! $this->date) {
            return [
                'shifts' => 0,
                'portions' => 0,
                'dishes' => 0,
                'ingredients' => 0,
            ];
        }

        $kitchenId = auth()->user()?->currentKitchenId();

        $portions = Menu::whereDate('date', $this->date)
            ->whereIn('shift_id', $this->selectedShifts)
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->sum('estimated_portions');

        $dishes = Menu::whereDate('date', $this->date)
            ->whereIn('shift_id', $this->selectedShifts)
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->distinct('recipe_id')
            ->count('recipe_id');

        $grouped = $this->getGroupedData();
        $ingCodes = [];
        foreach ($grouped as $s) {
            foreach ($s['dishes'] as $d) {
                foreach ($d['ingredients'] as $i) {
                    $ingCodes[$i['code']] = true;
                }
            }
        }

        return [
            'shifts' => count($grouped),
            'portions' => $portions,
            'dishes' => $dishes,
            'ingredients' => count($ingCodes),
        ];
    }

    public function generatePurchaseOrders(): void
    {
        $this->poDate = $this->date;
        $this->poSourceFrom = $this->date;
        $this->poSourceTo = $this->date;
        $this->poSelectedShifts = $this->selectedShifts;
        $this->loadPOIngredients();
        $this->createOrders();
    }
}
