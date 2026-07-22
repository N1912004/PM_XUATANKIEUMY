<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Models\Menu;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Shift;
use App\Models\Supplier;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class CreatePurchaseOrder extends Page
{
    protected static string $resource = PurchaseOrderResource::class;

    protected static string $view = 'filament.resources.purchase-orders.pages.create-purchase-order';

    public string $orderDate = '';

    public string $sourceFrom = '';

    public string $sourceTo = '';

    public array $selectedShifts = [];

    public array $groupSuppliers = [];

    public array $itemSuppliers = [];

    public array $itemQuantities = [];

    public array $itemSelected = [];

    public function mount(): void
    {
        abort_unless(PurchaseOrderResource::canCreate(), 403);

        $this->orderDate = today()->toDateString();
        $this->sourceFrom = today()->toDateString();
        $this->sourceTo = today()->toDateString();
        $this->selectedShifts = Shift::pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    public function updatedSourceFrom(): void
    {
        if ($this->sourceFrom > $this->sourceTo) {
            $this->sourceTo = $this->sourceFrom;
        }
    }

    public function updatedSourceTo(): void
    {
        if ($this->sourceTo < $this->sourceFrom) {
            $this->sourceFrom = $this->sourceTo;
        }
    }

    public function updatedGroupSuppliers($value, $groupKey): void
    {
        $groups = $this->getAggregatedGroupsProperty();
        if (isset($groups[$groupKey])) {
            foreach ($groups[$groupKey]['items'] as $item) {
                $this->itemSuppliers[$item['ingredient_id']] = $value;
            }
        }
    }

    public function getShiftsProperty()
    {
        return Shift::all();
    }

    public function getSuppliersProperty()
    {
        return Supplier::orderBy('name')->get();
    }

    public function getAggregatedGroupsProperty(): array
    {
        $kitchenId = auth()->user()?->currentKitchenId();

        $menus = Menu::with(['recipe.ingredients'])
            ->where('date', '>=', $this->sourceFrom)
            ->where('date', '<=', $this->sourceTo)
            ->when(! empty($this->selectedShifts), fn ($q) => $q->whereIn('shift_id', $this->selectedShifts))
            ->when($kitchenId && ! auth()->user()?->hasRole(['super_admin', 'Quản trị viên']), fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->where('status', 'locked')
            ->get();

        // Fetch existing PO items for ingredients in this date range to mark as already ordered
        $existingPOItems = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->when($kitchenId && ! auth()->user()?->hasRole(['super_admin', 'Quản trị viên']), fn ($q) => $q->where('purchase_orders.kitchen_id', $kitchenId))
            ->where('purchase_orders.estimated_delivery_date', '>=', $this->sourceFrom)
            ->where('purchase_orders.estimated_delivery_date', '<=', $this->sourceTo)
            ->select('purchase_order_items.ingredient_id', 'purchase_orders.code')
            ->get()
            ->groupBy('ingredient_id');

        $aggregated = [];

        foreach ($menus as $menu) {
            $servings = (float) ($menu->estimated_portions ?? 1);
            $recipe = $menu->recipe;
            if (! $recipe) {
                continue;
            }

            $dishName = $recipe->name;

            foreach ($recipe->ingredients as $ing) {
                $ingId = $ing->id;
                $qtyPerPortion = (float) ($ing->pivot->quantity_per_portion ?? 0);
                $totalKg = $servings * $qtyPerPortion;

                if (! isset($aggregated[$ingId])) {
                    $ingType = $ing->typeRelation?->name ?? $ing->type ?? __('purchase_order.types.other');

                    $typeLower = mb_strtolower($ingType);
                    $nameLower = mb_strtolower($ing->name);

                    if (str_contains($typeLower, 'thịt') || str_contains($typeLower, 'cá') || str_contains($nameLower, 'thịt') || str_contains($nameLower, 'cá') || str_contains($nameLower, 'gà') || str_contains($nameLower, 'vịt') || str_contains($nameLower, 'trứng')) {
                        $groupKey = 'thit';
                        $groupLabel = '🥩 Thịt';
                        $groupClass = 'ot-thit';
                    } elseif (str_contains($typeLower, 'rau') || str_contains($typeLower, 'củ') || str_contains($nameLower, 'rau') || str_contains($nameLower, 'củ') || str_contains($nameLower, 'giá') || str_contains($nameLower, 'nấm')) {
                        $groupKey = 'uot';
                        $groupLabel = '🥬 Rau / Ướt';
                        $groupClass = 'ot-uot';
                    } elseif (str_contains($typeLower, 'khô') || str_contains($typeLower, 'gia vị') || str_contains($nameLower, 'khô') || str_contains($nameLower, 'gạo') || str_contains($nameLower, 'dầu')) {
                        $groupKey = 'kho';
                        $groupLabel = '📦 Hàng Khô';
                        $groupClass = 'ot-kho';
                    } else {
                        $groupKey = 'khac';
                        $groupLabel = '📦 Món Khác';
                        $groupClass = 'ot-kho';
                    }

                    $aggregated[$ingId] = [
                        'ingredient_id' => $ingId,
                        'name' => $ing->name,
                        'unit' => $ing->unitRelation?->name ?? $ing->unit ?? 'kg',
                        'qty_per_portion' => $qtyPerPortion,
                        'servings' => $servings,
                        'total_kg' => 0.0,
                        'reference_price' => (float) ($ing->reference_price ?? 0),
                        'group_key' => $groupKey,
                        'group_label' => $groupLabel,
                        'group_class' => $groupClass,
                        'dishes' => [$dishName],
                    ];
                } else {
                    $aggregated[$ingId]['servings'] += $servings;
                    if (! in_array($dishName, $aggregated[$ingId]['dishes'])) {
                        $aggregated[$ingId]['dishes'][] = $dishName;
                    }
                }

                $aggregated[$ingId]['total_kg'] += $totalKg;
            }
        }

        $allSuppliers = $this->suppliers;
        $firstSupplierId = $allSuppliers->first()?->id;

        $groups = [];
        foreach ($aggregated as $item) {
            $gKey = $item['group_key'];
            if (! isset($groups[$gKey])) {
                $groups[$gKey] = [
                    'key' => $gKey,
                    'label' => $item['group_label'],
                    'class' => $item['group_class'],
                    'items' => [],
                ];
            }

            $ingId = $item['ingredient_id'];
            $manualQty = $this->itemQuantities[$ingId] ?? round($item['total_kg'], 2);
            $selectedSupplierId = $this->itemSuppliers[$ingId] ?? ($this->groupSuppliers[$gKey] ?? $firstSupplierId);

            $existingOrders = isset($existingPOItems[$ingId])
                ? $existingPOItems[$ingId]->pluck('code')->unique()->values()->all()
                : [];

            if (! isset($this->itemSelected[$ingId])) {
                $isSelected = empty($existingOrders);
            } else {
                $isSelected = (bool) $this->itemSelected[$ingId];
            }

            $item['already_ordered_pos'] = $existingOrders;
            $item['selected'] = $isSelected;
            $item['quantity_manual'] = (float) $manualQty;
            $item['supplier_id'] = $selectedSupplierId;
            $item['line_total'] = $isSelected ? ((float) $manualQty * $item['reference_price']) : 0;
            $item['dish_string'] = implode(', ', array_slice($item['dishes'], 0, 3)).(count($item['dishes']) > 3 ? '...' : '');

            $groups[$gKey]['items'][] = $item;
        }

        return $groups;
    }

    public function createAndSendOrders()
    {
        abort_unless(PurchaseOrderResource::canCreate(), 403);

        $groups = $this->getAggregatedGroupsProperty();
        $allItems = [];
        foreach ($groups as $grp) {
            foreach ($grp['items'] as $it) {
                if (! empty($it['selected'])) {
                    $allItems[] = $it;
                }
            }
        }

        if (empty($allItems)) {
            Notification::make()
                ->title('Chưa có nguyên liệu nào được tích chọn!')
                ->body('Vui lòng chọn ít nhất 1 nguyên liệu để tạo đơn đặt hàng.')
                ->warning()
                ->send();

            return;
        }

        $kitchenId = auth()->user()?->currentKitchenId();
        $itemsGrouped = collect($allItems)->groupBy('supplier_id');

        $createdCount = 0;

        DB::transaction(function () use ($itemsGrouped, $kitchenId, &$createdCount) {
            foreach ($itemsGrouped as $supplierId => $items) {
                $supplier = Supplier::find($supplierId);
                if (! $supplier) {
                    continue;
                }

                $supplierCode = strtoupper($supplier->code ?: 'NCC');
                $poCode = 'PO-LH-'.Carbon::parse($this->orderDate)->format('Ymd').'-'.$supplierCode;

                $attempts = 0;
                while (PurchaseOrder::where('code', $poCode)->exists() && $attempts < 10) {
                    $poCode = 'PO-LH-'.Carbon::parse($this->orderDate)->format('Ymd').'-'.$supplierCode.'-'.mt_rand(10, 99);
                    $attempts++;
                }

                $po = PurchaseOrder::create([
                    'code' => $poCode,
                    'kitchen_id' => $kitchenId,
                    'supplier_id' => $supplierId,
                    'status' => 'sent',
                    'type' => 'day',
                    'estimated_delivery_date' => $this->orderDate,
                    'note' => 'Đơn đặt hàng tạo từ trang Tạo đơn đặt hàng ngày '.Carbon::parse($this->orderDate)->format('d/m/Y'),
                ]);

                foreach ($items as $it) {
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'ingredient_id' => $it['ingredient_id'],
                        'quantity_ordered' => (float) $it['quantity_manual'],
                        'quantity_received' => 0.0,
                        'unit_price' => (float) $it['reference_price'],
                    ]);
                }

                $createdCount++;
            }
        });

        Notification::make()
            ->title('Tạo đơn đặt hàng thành công!')
            ->body("Đã tạo và gửi {$createdCount} đơn đặt hàng theo Nhà cung cấp.")
            ->success()
            ->send();

        return redirect()->to(PurchaseOrderResource::getUrl('index'));
    }
}
