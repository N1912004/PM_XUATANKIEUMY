<?php

namespace App\Filament\Pages;

use App\Exports\ListHangExport;
use App\Filament\Resources\MenuResource;
use App\Filament\Resources\PurchaseOrderResource;
use App\Filament\Resources\StockResource;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Shift;
use App\Models\Supplier;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    /**
     * Xuất Excel danh sách hàng của ngày/ca đang xem: Ca → Món → Nguyên liệu.
     */
    public function exportList(): BinaryFileResponse
    {
        abort_unless(StockResource::canViewAny() || MenuResource::canViewAny(), 403);

        return Excel::download(
            new ListHangExport($this->getGroupedData(), (string) $this->date),
            'list_hang_'.$this->date.'.xlsx',
        );
    }

    public function loadPOIngredients(): void
    {
        $kitchenId = auth()->user()?->currentKitchenId();

        if (empty($this->poSelectedShifts) || ! $this->poSourceFrom || ! $this->poSourceTo) {
            $this->poItems = [];

            return;
        }

        // Khoảng nửa mở [from, to+1) — dùng index, đúng trên cả MySQL lẫn SQLite (test)
        // Chỉ tổng hợp từ thực đơn ĐÃ CHỐT — thực đơn nháp/đang gửi chưa phải căn cứ mua hàng
        $menus = Menu::with(['recipe.ingredients.supplier'])
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->where('status', 'locked')
            ->where('date', '>=', $this->poSourceFrom)
            ->where('date', '<', Carbon::parse($this->poSourceTo)->addDay()->toDateString())
            ->whereIn('shift_id', $this->poSelectedShifts)
            ->get();

        // Prefetch existing PO codes per ingredient for the order date in one query
        // (avoids one query per ingredient inside the loop below).
        $poDateEnd = Carbon::parse($this->poDate)->addDay()->toDateString();
        $existingPoCodesByIngredient = PurchaseOrderItem::query()
            ->whereHas('purchaseOrder', fn ($q) => $q->where('estimated_delivery_date', '>=', $this->poDate)
                ->where('estimated_delivery_date', '<', $poDateEnd))
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

    /** @var Collection|null Memo 1 render — blade gọi trong vòng lặp */
    protected $shiftsCache = null;

    public function getShiftsList()
    {
        return $this->shiftsCache ??= Shift::all();
    }

    /** @var Collection|null Memo 1 render, keyBy id để tra cứu O(1) */
    protected $activeSuppliersCache = null;

    public function getActiveSuppliers()
    {
        return $this->activeSuppliersCache ??= Supplier::where('status', true)->get()->keyBy('id');
    }

    /** @var Collection|null Memo 1 render — tra cứu tên NCC theo id (kể cả NCC ngừng hoạt động) */
    protected $allSuppliersCache = null;

    public function getAllSuppliers()
    {
        return $this->allSuppliersCache ??= Supplier::all()->keyBy('id');
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
        abort_unless(PurchaseOrderResource::canCreate(), 403);

        // Quy định nghiệp vụ: ngày đặt hàng chỉ trong vòng 2 ngày kế tiếp từ hôm nay
        $orderDate = Carbon::parse($this->poDate)->startOfDay();
        if ($orderDate->lt(today()) || $orderDate->gt(today()->addDays(2))) {
            Notification::make()
                ->title('Ngày đặt hàng không hợp lệ')
                ->body('Chỉ được đặt hàng cho hôm nay hoặc tối đa 2 ngày kế tiếp.')
                ->danger()
                ->send();

            return;
        }

        $selectedItems = collect($this->poItems)->filter(fn ($item) => $item['checked'] && (float) ($item['quantity_manual'] ?? 0) > 0);

        if ($selectedItems->isEmpty()) {
            Notification::make()->title('Không có nguyên liệu nào được chọn để tạo PO!')->warning()->send();

            return;
        }

        $kitchenId = auth()->user()?->currentKitchenId();

        // Group by Supplier and Split (Phiếu 1, Phiếu 2, Phiếu 3)
        $grouped = $selectedItems->groupBy(fn ($item) => $item['supplier_id'].'-'.$item['split']);

        // Đơn giá tra lại từ DB theo ingredient_id — KHÔNG tin reference_price trong payload Livewire
        // (client có thể sửa để tạo PO giá 0đ)
        $ingredientIds = $selectedItems->pluck('ingredient_id')->unique();
        $priceByIngredient = Ingredient::whereIn('id', $ingredientIds)
            ->pluck('reference_price', 'id');

        // Bảng báo giá theo TỪNG NCC (pivot ingredient_supplier) — ưu tiên hơn giá tham chiếu chung
        // khi nguyên liệu có giá riêng với NCC được chọn. Key: "supplier_id-ingredient_id".
        $pivotPrices = DB::table('ingredient_supplier')
            ->whereIn('ingredient_id', $ingredientIds)
            ->get(['supplier_id', 'ingredient_id', 'reference_price'])
            ->keyBy(fn ($row) => $row->supplier_id.'-'.$row->ingredient_id);

        $poCount = 0;
        $poCodes = [];
        $skippedNames = [];

        DB::transaction(function () use ($grouped, $kitchenId, $priceByIngredient, $pivotPrices, &$poCount, &$poCodes, &$skippedNames): void {
            foreach ($grouped as $key => $items) {
                $parts = explode('-', $key);
                $supplierId = (int) $parts[0];
                $split = $parts[1];

                $supplier = Supplier::find($supplierId);
                if (! $supplier) {
                    // Không bỏ qua lặng lẽ — gom lại để báo cho user biết mặt hàng nào chưa được đặt
                    $skippedNames = array_merge($skippedNames, $items->pluck('name')->all());

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
                    'type' => 'day',
                    'estimated_delivery_date' => $this->poDate,
                    'note' => 'Đơn đặt hàng tự động tạo từ List hàng ngày '.Carbon::parse($this->poDate)->format('d/m/Y')." ({$split})",
                ]);

                foreach ($items as $item) {
                    // Giá theo NCC (pivot) nếu có báo giá > 0, ngược lại dùng giá tham chiếu chung
                    $pivotPrice = (float) ($pivotPrices->get($supplierId.'-'.$item['ingredient_id'])->reference_price ?? 0);
                    $unitPrice = $pivotPrice > 0
                        ? $pivotPrice
                        : (float) ($priceByIngredient[$item['ingredient_id']] ?? 0);

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'ingredient_id' => $item['ingredient_id'],
                        'quantity_ordered' => (float) $item['quantity_manual'],
                        'quantity_received' => 0.0,
                        'unit_price' => $unitPrice,
                    ]);
                }

                $poCount++;
                $poCodes[] = $poCode;
            }
        });

        $notification = Notification::make()
            ->title('Tạo PO thành công!')
            ->body("Đã tạo tự động {$poCount} đơn đặt hàng nháp: ".implode(', ', $poCodes))
            ->success()
            ->persistent();

        if ($skippedNames !== []) {
            $notification->body(
                "Đã tạo {$poCount} đơn đặt hàng nháp: ".implode(', ', $poCodes)
                ."\n⚠️ BỎ QUA (chưa gán nhà cung cấp): ".implode(', ', array_unique($skippedNames))
            )->warning();
        }

        $notification->send();

        $this->mode = 'list';
    }

    /**
     * Request-scope memo: getGroupedData() được gọi từ cả getStats() lẫn blade,
     * chỉ tính 1 lần mỗi render. (Protected nên không bị Livewire serialize.)
     *
     * @var array<int, array<string, mixed>>|null
     */
    protected ?array $groupedDataMemo = null;

    public function getGroupedData(): array
    {
        if ($this->groupedDataMemo !== null) {
            return $this->groupedDataMemo;
        }

        if (empty($this->selectedShifts) || ! $this->date) {
            return $this->groupedDataMemo = [];
        }

        $kitchenId = auth()->user()?->currentKitchenId();

        $shifts = Shift::whereIn('id', $this->selectedShifts)->get();

        // 1 query cho tất cả ca (thay vì 1 query/ca); khoảng nửa mở [ngày, ngày+1)
        // để dùng được index và đúng trên cả MySQL lẫn SQLite (test)
        $menusByShift = Menu::with(['recipe.ingredients'])
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->where('status', 'locked')
            ->where('date', '>=', $this->date)
            ->where('date', '<', Carbon::parse($this->date)->addDay()->toDateString())
            ->whereIn('shift_id', $this->selectedShifts)
            ->get()
            ->groupBy('shift_id');

        $data = [];

        foreach ($shifts as $shift) {
            $menus = $menusByShift->get($shift->id, collect());

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

        return $this->groupedDataMemo = $data;
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

        // Khoảng nửa mở [ngày, ngày+1) để dùng index (date, shift_id); gộp 2 aggregate vào 1 query
        $totals = Menu::where('status', 'locked')
            ->where('date', '>=', $this->date)
            ->where('date', '<', Carbon::parse($this->date)->addDay()->toDateString())
            ->whereIn('shift_id', $this->selectedShifts)
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->selectRaw('COALESCE(SUM(estimated_portions), 0) as portions, COUNT(DISTINCT recipe_id) as dishes')
            ->first();

        $portions = (int) $totals->portions;
        $dishes = (int) $totals->dishes;

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
