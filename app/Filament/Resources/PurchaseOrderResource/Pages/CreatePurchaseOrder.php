<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Shift;
use App\Models\Supplier;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class CreatePurchaseOrder extends Page
{
    protected static string $resource = PurchaseOrderResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('purchase_order.breadcrumb.home'),
            PurchaseOrderResource::getUrl('index') => __('purchase_order.breadcrumb.list'),
            __('purchase_order.breadcrumb.create'),
        ];
    }

    protected static string $view = 'filament.resources.purchase-orders.pages.create-purchase-order';

    public function getHeading(): string
    {
        return '';
    }

    public function getHeader(): ?View
    {
        return null;
    }

    public string $orderDate = '';

    public string $sourceFrom = '';

    public string $sourceTo = '';

    public array $selectedShifts = [];

    public array $groupSuppliers = [];

    public array $itemSuppliers = [];

    public array $itemQuantities = [];

    public array $itemSelected = [];

    /** @var array<int, string> Phiếu (P1/P2/P3) theo ingredient_id — tách 1 NCC thành nhiều đơn */
    public array $itemSplits = [];

    public const SPLIT_OPTIONS = ['P1', 'P2', 'P3'];

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

    public function assignGroupSupplier($value, string $groupKey): void
    {
        $supplier = filled($value) ? Supplier::with(['ingredientTypes'])->find($value) : null;
        $this->groupSuppliers[$groupKey] = filled($value) ? (int) $value : null;

        $groups = $this->getAggregatedGroupsProperty();

        if (isset($groups[$groupKey])) {
            foreach ($groups[$groupKey]['items'] as $item) {
                $ingId = $item['ingredient_id'];
                if (! $supplier) {
                    $this->itemSuppliers[$ingId] = null;

                    continue;
                }

                $ingredient = Ingredient::with(['suppliers', 'typeRelation'])->find($ingId);
                if ($ingredient && $this->canSupplierProvideIngredient($supplier, $ingredient)) {
                    $this->itemSuppliers[$ingId] = (int) $value;
                } else {
                    $this->itemSuppliers[$ingId] = null;
                }
            }
        }
    }

    public function updatedGroupSuppliers($value, $groupKey): void
    {
        $this->assignGroupSupplier($value, $groupKey);
    }

    protected function canSupplierProvideIngredient(?Supplier $supplier, Ingredient $ingredient): bool
    {
        return $supplier !== null && $supplier->canProvideIngredient($ingredient);
    }

    public function updatedItemQuantities($value, $key): void
    {
        if (is_numeric($value) && (float) $value < 0) {
            $this->itemQuantities[$key] = 0;
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

        $menus = Menu::with(['recipe.ingredients.typeRelation', 'recipe.ingredients.suppliers', 'recipe.ingredients.unitRelation', 'recipe.ingredients.supplier'])
            // Khoảng nửa mở [from, to+1) — cột date có thể lưu kèm giờ (SQLite trong test),
            // so sánh '<= Y-m-d' sẽ trượt "Y-m-d 00:00:00"; cùng pattern với ListHang.
            ->where('date', '>=', $this->sourceFrom)
            ->where('date', '<', Carbon::parse($this->sourceTo)->addDay()->toDateString())
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
                    $typeModel = $ing->typeRelation;
                    $typeId = $typeModel?->id ?? (int) $ing->ingredient_type_id;
                    $typeName = $typeModel?->name ?? (is_string($ing->type) ? $ing->type : null) ?? 'Khác';

                    $groupKey = 'type_'.($typeId ?: md5($typeName));
                    $typeLower = mb_strtolower($typeName);

                    if (str_contains($typeLower, 'động vật') || str_contains($typeLower, 'thịt') || str_contains($typeLower, 'cá')) {
                        $groupClass = 'ot-thit';
                    } elseif (str_contains($typeLower, 'thực vật') || str_contains($typeLower, 'rau') || str_contains($typeLower, 'củ')) {
                        $groupClass = 'ot-uot';
                    } else {
                        $groupClass = 'ot-kho';
                    }

                    $displayTypeName = __('purchase_order.group_names.'.$typeName);
                    if ($displayTypeName === 'purchase_order.group_names.'.$typeName) {
                        $displayTypeName = $typeName;
                    }

                    $groupLabel = $displayTypeName;

                    $aggregated[$ingId] = [
                        'ingredient_id' => $ingId,
                        'default_supplier_id' => $ing->supplier_id,
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

        $allSuppliers = $this->suppliers->keyBy('id');

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
            $manualQty = max(0, (float) ($this->itemQuantities[$ingId] ?? round($item['total_kg'], 2)));

            if (array_key_exists($ingId, $this->itemSuppliers)) {
                $selectedSupplierId = $this->itemSuppliers[$ingId] ? (int) $this->itemSuppliers[$ingId] : null;
            } else {
                // Mặc định: NCC chính của nguyên liệu, hoặc NCC khớp đúng loại nhóm.
                // KHÔNG fallback "NCC đầu tiên trong DB" — gán bừa NCC không cung cấp
                // được (NCC gạo cho rau) tệ hơn để trống; trống thì ô viền đỏ và
                // createAndSendOrders đã validate bắt buộc chọn trước khi lưu.
                $rawTypeName = str_replace(['🥩 ', '🥬 ', '📦 '], '', $item['group_label']);
                $matchedSupplier = $allSuppliers->first(function ($s) use ($rawTypeName) {
                    $sType = mb_strtolower($s->type ?? '');
                    $tName = mb_strtolower($rawTypeName);

                    return str_contains($sType, $tName) || str_contains($tName, $sType);
                });

                $selectedSupplierId = $item['default_supplier_id'] ?: $matchedSupplier?->id;
            }

            $isSupplierValid = false;
            if ($selectedSupplierId) {
                $supplier = $allSuppliers->get($selectedSupplierId);
                $ingredientObj = Ingredient::with(['suppliers', 'typeRelation'])->find($ingId);
                $isSupplierValid = ($supplier && $ingredientObj && $supplier->canProvideIngredient($ingredientObj));
            }

            // Nếu NCC đã chọn không cung cấp được nguyên liệu này → để TRỐNG (null) để giao diện báo lỗi đỏ
            if (! $isSupplierValid) {
                $selectedSupplierId = null;
            }

            $existingOrders = isset($existingPOItems[$ingId])
                ? array_values(array_unique(array_filter($existingPOItems[$ingId]->pluck('code')->toArray())))
                : [];

            if (! isset($this->itemSelected[$ingId])) {
                $this->itemSelected[$ingId] = empty($existingOrders);
            }
            $isSelected = (bool) $this->itemSelected[$ingId];

            $split = $this->itemSplits[$ingId] ?? 'P1';
            if (! in_array($split, self::SPLIT_OPTIONS, true)) {
                $split = 'P1';
            }

            $item['already_ordered_pos'] = $existingOrders;
            $item['selected'] = $isSelected;
            $item['quantity_manual'] = (float) $manualQty;
            $item['supplier_id'] = $selectedSupplierId;
            $item['supplier_valid'] = $isSupplierValid;
            $item['split'] = $split;
            $item['line_total'] = ($isSelected && $selectedSupplierId) ? ((float) $manualQty * $item['reference_price']) : 0;
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
                ->title(__('purchase_order.validation.no_ingredient_selected_title'))
                ->body(__('purchase_order.validation.no_ingredient_selected_body'))
                ->warning()
                ->send();

            return;
        }

        // Validate: Mọi nguyên liệu được tích chọn BẮT BUỘC phải chọn Nhà cung cấp hợp lệ
        $missingSupplierItems = [];
        foreach ($allItems as $it) {
            if (empty($it['supplier_id']) || empty($it['supplier_valid'])) {
                $missingSupplierItems[] = $it['name'];
            }
        }

        if (! empty($missingSupplierItems)) {
            $count = count($missingSupplierItems);
            $namesList = implode(', ', array_slice($missingSupplierItems, 0, 3)).($count > 3 ? '...' : '');
            Notification::make()
                ->title(__('purchase_order.validation.supplier_required_title'))
                ->body(__('purchase_order.validation.supplier_required_body', ['count' => $count, 'names' => $namesList]))
                ->danger()
                ->send();

            return;
        }

        $kitchenId = auth()->user()?->currentKitchenId();
        // Tách đơn theo NCC + Phiếu (P1/P2/P3) — mỗi cặp thành 1 PO riêng, giống ListHang
        $itemsGrouped = collect($allItems)->groupBy(fn ($it) => $it['supplier_id'].'|'.$it['split']);

        $createdCount = 0;

        DB::transaction(function () use ($itemsGrouped, $kitchenId, &$createdCount) {
            foreach ($itemsGrouped as $groupKey => $items) {
                [$supplierId, $split] = explode('|', (string) $groupKey);
                $supplier = Supplier::find((int) $supplierId);
                if (! $supplier) {
                    continue;
                }

                // Code format ngắn gọn theo ID: PO-{id} (ví dụ PO-1, PO-2...)
                $po = PurchaseOrder::create([
                    'code' => 'PO-TEMP',
                    'kitchen_id' => $kitchenId,
                    'supplier_id' => (int) $supplierId,
                    'status' => 'sent',
                    'type' => 'day',
                    'estimated_delivery_date' => $this->orderDate,
                    'note' => __('purchase_order.create.auto_note', ['date' => Carbon::parse($this->orderDate)->format('d/m/Y')]),
                ]);

                $po->updateQuietly(['code' => 'PO-'.$po->id]);

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
            ->title(__('purchase_order.notifications.created_success_title'))
            ->body(__('purchase_order.notifications.created_success_body', ['count' => $createdCount]))
            ->success()
            ->send();

        return redirect()->to(PurchaseOrderResource::getUrl('index'));
    }
}
