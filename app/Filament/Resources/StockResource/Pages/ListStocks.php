<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use App\Models\Ingredient;
use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Shift;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Livewire\WithFileUploads;

class ListStocks extends ListRecords
{
    use WithFileUploads;

    protected static string $resource = StockResource::class;

    protected static string $view = 'filament.pages.warehouse';

    public string $warehouseTab = 'stock';

    public string $search = '';

    public string $selectedType = '';

    // End day check properties
    public ?string $checkDate = null;

    public array $actualQuantities = [];

    public array $checkNotes = [];

    // Form flow properties
    public ?string $inMode = null;

    public ?string $outMode = null;

    public bool $showInModal = false;

    public bool $showOutModal = false;

    // PO Inbound properties
    public ?int $selectedPOId = null;

    public array $poItemsData = [];

    // Direct Inbound properties
    public array $directItemsData = [];

    public mixed $directInvoiceFile = null;

    // Production Outbound properties
    public ?string $prodDate = null;

    public ?int $prodShiftId = null;

    public array $prodItemsData = [];

    // Transfer Outbound properties
    public ?int $destKitchenId = null;

    public array $transferItemsData = [];

    public string $transferNote = '';

    // Ledger (Thẻ kho) properties
    public ?int $selectedLedgerIngId = null;

    public array $ledgerTransactions = [];

    // Single item fallback (from original code, to maintain compatibility)
    public ?int $inIngredientId = null;

    public ?string $inDate = null;

    public float $inQuantity = 0;

    public float $inPrice = 0;

    public string $inRef = '';

    public ?int $outIngredientId = null;

    public ?string $outDate = null;

    public float $outQuantity = 0;

    public string $outReason = 'Sản xuất theo list hàng';

    public string $outRef = '';

    public function mount(): void
    {
        parent::mount();
        $this->checkDate = now()->toDateString();
        $this->inDate = now()->toDateString();
        $this->outDate = now()->toDateString();
        $this->prodDate = now()->toDateString();

        $kitchenId = auth()->user()?->currentKitchenId();

        // Initialize actual quantities for end day checks
        $stocksQuery = Stock::query();
        if ($kitchenId) {
            $stocksQuery->where('kitchen_id', $kitchenId);
        }
        $stocks = $stocksQuery->get();
        foreach ($stocks as $stock) {
            $this->actualQuantities[$stock->id] = $stock->quantity;
            $this->checkNotes[$stock->id] = '';
        }

        // Set defaults
        $firstIng = Ingredient::first();
        if ($firstIng) {
            $this->inIngredientId = $firstIng->id;
            $this->outIngredientId = $firstIng->id;
        }

        $firstShift = Shift::first();
        if ($firstShift) {
            $this->prodShiftId = $firstShift->id;
        }

        $firstKitchen = Kitchen::where('id', '!=', $kitchenId)->first();
        if ($firstKitchen) {
            $this->destKitchenId = $firstKitchen->id;
        }

        // Tự động nhận tham số URL để chuyển đổi tab/PO nhanh từ màn hình Đặt hàng
        // (validate whitelist trước khi gán vào state để tránh giá trị bất thường từ query string)
        $poId = request()->query('po_id');
        $tab = request()->query('tab');
        $inMode = request()->query('inMode');

        if (is_string($tab) && in_array($tab, ['stock', 'in', 'out', 'check', 'log'], true)) {
            $this->warehouseTab = $tab;
        }
        if (is_string($inMode) && in_array($inMode, ['po', 'direct'], true)) {
            $this->inMode = $inMode;
        }
        if (is_scalar($poId) && (int) $poId > 0) {
            $this->selectedPOId = (int) $poId;
            $this->loadPOItems();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tạo tồn kho'),
        ];
    }

    public function setTab(string $tab): void
    {
        $this->warehouseTab = $tab;
    }

    public function openInTypeModal(): void
    {
        $this->showInModal = true;
    }

    public function openOutTypeModal(): void
    {
        $this->showOutModal = true;
    }

    public function closeModals(): void
    {
        $this->showInModal = false;
        $this->showOutModal = false;
    }

    public function startInbound(string $mode): void
    {
        $this->inMode = $mode;
        $this->showInModal = false;
        $this->warehouseTab = 'in';

        if ($mode === 'po') {
            $pending = $this->getPendingPOs();
            if (! empty($pending)) {
                $this->selectedPOId = $pending[0]->id;
                $this->loadPOItems();
            } else {
                $this->selectedPOId = null;
                $this->poItemsData = [];
            }
        } elseif ($mode === 'direct') {
            $this->directItemsData = [];
            $this->addDirectRow();
        }
    }

    public function startOutbound(string $mode): void
    {
        $this->outMode = $mode;
        $this->showOutModal = false;
        $this->warehouseTab = 'out';

        if ($mode === 'production') {
            $this->loadProductionItems();
        } elseif ($mode === 'transfer') {
            $this->transferItemsData = [];
            $this->addTransferRow();
        }
    }

    // End Day Check Save
    public function saveEndDay(): void
    {
        $kitchenId = auth()->user()?->currentKitchenId();

        foreach ($this->actualQuantities as $stockId => $actualQty) {
            $stock = Stock::find($stockId);
            if (! $stock) {
                continue;
            }

            $diff = (float) $actualQty - (float) $stock->quantity;
            if ($diff != 0) {
                $type = $diff > 0 ? 'Nhập kho' : 'Xuất kho';

                StockTransaction::create([
                    'kitchen_id' => $kitchenId,
                    'ingredient_id' => $stock->ingredient_id,
                    'type' => $type,
                    'quantity' => abs($diff),
                    'after_quantity' => $actualQty,
                    'note' => 'Kiểm kê cuối ngày: '.($this->checkNotes[$stockId] ?: 'Điều chỉnh chênh lệch thực tế'),
                ]);

                $stock->update(['quantity' => $actualQty]);
            }
        }

        Notification::make()
            ->title('Đã lưu tồn cuối ngày thành công!')
            ->success()
            ->send();
    }

    // PO Inbound Handlers
    public function loadPOItems(): void
    {
        if (! $this->selectedPOId) {
            $this->poItemsData = [];

            return;
        }

        $po = PurchaseOrder::with('items.ingredient')->find($this->selectedPOId);
        if (! $po) {
            $this->poItemsData = [];

            return;
        }

        $this->poItemsData = [];
        foreach ($po->items as $item) {
            $this->poItemsData[] = [
                'id' => $item->id,
                'ingredient_id' => $item->ingredient_id,
                'name' => $item->ingredient->name,
                'unit' => $item->ingredient->unit,
                'quantity_ordered' => (float) $item->quantity_ordered,
                'quantity_received' => (float) $item->quantity_ordered, // Pre-filled default
                'unit_price' => (float) $item->unit_price,
            ];
        }
    }

    public function updatedSelectedPOId(): void
    {
        $this->loadPOItems();
    }

    public function confirmInboundPO(): void
    {
        if (! $this->selectedPOId) {
            Notification::make()->title('Vui lòng chọn đơn đặt hàng hợp lệ!')->danger()->send();

            return;
        }

        $po = PurchaseOrder::find($this->selectedPOId);
        if (! $po) {
            Notification::make()->title('Đơn đặt hàng không tồn tại!')->danger()->send();

            return;
        }

        $kitchenId = auth()->user()?->currentKitchenId() ?? $po->kitchen_id;

        foreach ($this->poItemsData as $itemData) {
            $qtyReceived = (float) ($itemData['quantity_received'] ?? 0);
            $unitPrice = (float) ($itemData['unit_price'] ?? 0);

            if ($qtyReceived <= 0) {
                continue;
            }

            $poItem = PurchaseOrderItem::find($itemData['id']);
            if ($poItem) {
                $poItem->update([
                    'quantity_received' => $qtyReceived,
                    'unit_price' => $unitPrice,
                ]);
            }

            $stock = Stock::firstOrCreate(
                [
                    'kitchen_id' => $kitchenId,
                    'ingredient_id' => $itemData['ingredient_id'],
                ],
                [
                    'quantity' => 0,
                    'min_quantity' => 10,
                    'unit_price' => $unitPrice,
                ]
            );

            $oldQty = $stock->quantity;
            $newQty = $oldQty + $qtyReceived;

            $stock->update([
                'quantity' => $newQty,
                'unit_price' => $unitPrice > 0 ? $unitPrice : $stock->unit_price,
            ]);

            StockTransaction::create([
                'kitchen_id' => $kitchenId,
                'ingredient_id' => $itemData['ingredient_id'],
                'type' => 'Nhập kho',
                'voucher_code' => $po->code,
                'quantity' => $qtyReceived,
                'after_quantity' => $newQty,
                'note' => "Nhập kho thực tế từ đơn đặt hàng: {$po->code}",
            ]);
        }

        $po->update([
            'status' => 'done',
            'stocked_at' => now(),
        ]);

        Notification::make()->title('Đã xác nhận nhập theo PO và cập nhật Thẻ kho!')->success()->send();

        $this->inMode = null;
        $this->warehouseTab = 'stock';
    }

    // Direct Inbound Handlers
    public function addDirectRow(): void
    {
        $firstIng = Ingredient::first();
        $this->directItemsData[] = [
            'ingredient_id' => $firstIng ? $firstIng->id : '',
            'quantity' => 0,
            'unit_price' => $firstIng ? $firstIng->reference_price : 0,
        ];
    }

    public function removeDirectRow(int $index): void
    {
        unset($this->directItemsData[$index]);
        $this->directItemsData = array_values($this->directItemsData);
    }

    public function confirmDirectInbound(): void
    {
        if (empty($this->directItemsData)) {
            Notification::make()->title('Vui lòng thêm ít nhất một mặt hàng!')->danger()->send();

            return;
        }

        if (! $this->directInvoiceFile) {
            Notification::make()->title('Hóa đơn chứng từ đính kèm là bắt buộc khi nhập kho ngoài!')->danger()->send();

            return;
        }

        $kitchenId = auth()->user()?->currentKitchenId();
        $path = $this->directInvoiceFile->store('stock-vouchers', 'public');

        foreach ($this->directItemsData as $itemData) {
            $ingId = $itemData['ingredient_id'];
            $qty = (float) ($itemData['quantity'] ?? 0);
            $price = (float) ($itemData['unit_price'] ?? 0);

            if (! $ingId || $qty <= 0) {
                continue;
            }

            Stock::recordExternalInbound(
                kitchenId: $kitchenId,
                ingredientId: $ingId,
                quantity: $qty,
                unitPrice: $price,
                attachmentUrl: $path,
                note: 'Nhập mua ngoài trực tiếp'
            );
        }

        Notification::make()->title('Đã lưu phiếu nhập mua ngoài và cập nhật Thẻ kho!')->success()->send();

        $this->inMode = null;
        $this->directItemsData = [];
        $this->directInvoiceFile = null;
        $this->warehouseTab = 'stock';
    }

    // Production Outbound Handlers
    public function loadProductionItems(): void
    {
        $kitchenId = auth()->user()?->currentKitchenId() ?? Kitchen::first()?->id;

        if (! $this->prodDate || ! $this->prodShiftId || ! $kitchenId) {
            $this->prodItemsData = [];

            return;
        }

        $menus = Menu::with(['recipe.ingredients'])
            ->where('kitchen_id', $kitchenId)
            ->where('date', $this->prodDate)
            ->where('shift_id', $this->prodShiftId)
            ->get();

        // Nạp tồn kho 1 lần cho toàn bộ nguyên liệu liên quan (tránh N+1 trong vòng lặp)
        $ingredientIds = $menus->flatMap(fn ($menu) => $menu->recipe->ingredients->pluck('id'))->unique();
        $stocksByIngredient = Stock::where('kitchen_id', $kitchenId)
            ->whereIn('ingredient_id', $ingredientIds)
            ->get()
            ->keyBy('ingredient_id');

        $ingredients = [];
        foreach ($menus as $menu) {
            $portions = (float) $menu->estimated_portions;
            foreach ($menu->recipe->ingredients as $ing) {
                $qtyPerPortion = (float) $ing->pivot->quantity_per_portion;
                $needed = $portions * $qtyPerPortion;

                if (isset($ingredients[$ing->id])) {
                    $ingredients[$ing->id]['quantity_expected'] += $needed;
                } else {
                    $stock = $stocksByIngredient->get($ing->id);
                    $ingredients[$ing->id] = [
                        'ingredient_id' => $ing->id,
                        'name' => $ing->name,
                        'unit' => $ing->unit,
                        'quantity_expected' => $needed,
                        'quantity_actual' => $needed,
                        'available_qty' => $stock ? $stock->available_quantity : 0,
                    ];
                }
            }
        }

        $this->prodItemsData = array_values($ingredients);
    }

    public function updatedProdDate(): void
    {
        $this->loadProductionItems();
    }

    public function updatedProdShiftId(): void
    {
        $this->loadProductionItems();
    }

    public function confirmProductionOut(): void
    {
        if (empty($this->prodItemsData)) {
            Notification::make()->title('Không có nguyên liệu sản xuất cần xuất!')->danger()->send();

            return;
        }

        $kitchenId = auth()->user()?->currentKitchenId() ?? Kitchen::first()?->id;

        foreach ($this->prodItemsData as $itemData) {
            $actual = (float) ($itemData['quantity_actual'] ?? 0);
            $available = (float) ($itemData['available_qty'] ?? 0);

            if ($actual > $available) {
                Notification::make()->title("Số lượng xuất của {$itemData['name']} vượt quá lượng tồn khả dụng ($available)! Vui lòng điều chỉnh lại.")
                    ->danger()
                    ->send();

                return;
            }
        }

        $shift = Shift::find($this->prodShiftId);
        $shiftName = $shift ? $shift->name : "Ca #{$this->prodShiftId}";
        $voucherCode = 'PX-SX-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);

        foreach ($this->prodItemsData as $itemData) {
            $qty = (float) ($itemData['quantity_actual'] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $stock = Stock::where('kitchen_id', $kitchenId)->where('ingredient_id', $itemData['ingredient_id'])->first();
            if ($stock) {
                $newQty = $stock->quantity - $qty;
                $stock->update(['quantity' => $newQty]);

                StockTransaction::create([
                    'kitchen_id' => $kitchenId,
                    'ingredient_id' => $itemData['ingredient_id'],
                    'type' => 'Xuất kho',
                    'voucher_code' => $voucherCode,
                    'quantity' => -$qty,
                    'after_quantity' => $newQty,
                    'note' => "Xuất kho sản xuất ca {$shiftName} ngày {$this->prodDate}",
                ]);
            }
        }

        Notification::make()->title('Đã xác nhận xuất kho sản xuất và ghi Thẻ kho!')->success()->send();

        $this->outMode = null;
        $this->prodItemsData = [];
        $this->warehouseTab = 'stock';
    }

    // Transfer Outbound Handlers
    public function addTransferRow(): void
    {
        $firstIng = Ingredient::first();
        $kitchenId = auth()->user()?->currentKitchenId();
        $stock = $firstIng ? Stock::where('kitchen_id', $kitchenId)->where('ingredient_id', $firstIng->id)->first() : null;

        $this->transferItemsData[] = [
            'ingredient_id' => $firstIng ? $firstIng->id : '',
            'quantity' => 0,
            'available_qty' => $stock ? $stock->available_quantity : 0,
        ];
    }

    public function removeTransferRow(int $index): void
    {
        unset($this->transferItemsData[$index]);
        $this->transferItemsData = array_values($this->transferItemsData);
    }

    public function updatedTransferItemsData($value, $key): void
    {
        if (str_ends_with($key, '.ingredient_id')) {
            $parts = explode('.', $key);
            $index = (int) $parts[0];
            $ingId = $value;
            $kitchenId = auth()->user()?->currentKitchenId();
            $stock = Stock::where('kitchen_id', $kitchenId)->where('ingredient_id', $ingId)->first();
            $this->transferItemsData[$index]['available_qty'] = $stock ? $stock->available_quantity : 0;
        }
    }

    public function confirmTransferOut(): void
    {
        if (! $this->destKitchenId) {
            Notification::make()->title('Vui lòng chọn bếp nhận!')->danger()->send();

            return;
        }

        if (empty($this->transferItemsData)) {
            Notification::make()->title('Vui lòng thêm ít nhất một nguyên liệu cần điều chuyển!')->danger()->send();

            return;
        }

        $sourceKitchenId = auth()->user()?->currentKitchenId();
        if (! $sourceKitchenId) {
            Notification::make()->title('Tài khoản của bạn không được gán bếp để xuất điều chuyển!')->danger()->send();

            return;
        }

        foreach ($this->transferItemsData as $itemData) {
            $ingId = $itemData['ingredient_id'];
            $qty = (float) ($itemData['quantity'] ?? 0);
            $available = (float) ($itemData['available_qty'] ?? 0);

            if (! $ingId || $qty <= 0) {
                continue;
            }

            if ($qty > $available) {
                $ingName = Ingredient::find($ingId)?->name ?? 'nguyên liệu';
                Notification::make()->title("Số lượng điều chuyển của {$ingName} vượt quá lượng tồn khả dụng ($available)!")
                    ->danger()
                    ->send();

                return;
            }
        }

        $transfer = StockTransfer::create([
            'code' => 'CK-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'source_kitchen_id' => $sourceKitchenId,
            'dest_kitchen_id' => $this->destKitchenId,
            'status' => StockTransfer::STATUS_IN_TRANSIT,
            'created_by' => auth()->id(),
            'note' => $this->transferNote,
        ]);

        foreach ($this->transferItemsData as $itemData) {
            $ingId = $itemData['ingredient_id'];
            $qty = (float) ($itemData['quantity'] ?? 0);

            if (! $ingId || $qty <= 0) {
                continue;
            }

            StockTransferItem::create([
                'stock_transfer_id' => $transfer->id,
                'ingredient_id' => $ingId,
                'quantity' => $qty,
            ]);
        }

        $transfer->freezeSourceStock();

        Notification::make()->title('Đã tạo phiếu điều chuyển thành công! Trạng thái: Đang chuyển.')->success()->send();

        $this->outMode = null;
        $this->transferItemsData = [];
        $this->transferNote = '';
        $this->warehouseTab = 'out';
    }

    public function confirmTransferReceive(int $transferId): void
    {
        $transfer = StockTransfer::find($transferId);
        if (! $transfer) {
            Notification::make()->title('Không tìm thấy phiếu điều chuyển!')->danger()->send();

            return;
        }

        $transfer->confirmReceived(auth()->id());

        Notification::make()->title('Đã xác nhận nhận hàng thành công và cập nhật tồn kho!')->success()->send();
    }

    // Helper Lists
    public function getPendingPOs(): array
    {
        $kitchenId = auth()->user()?->currentKitchenId();
        $query = PurchaseOrder::query()->whereIn('status', ['sent', 'checking'])->whereNull('stocked_at');
        if ($kitchenId) {
            $query->where('kitchen_id', $kitchenId);
        }

        return $query->with('supplier')->get()->all();
    }

    public function getTransferKitchens(): array
    {
        $kitchenId = auth()->user()?->currentKitchenId();
        $query = Kitchen::query();
        if ($kitchenId) {
            $query->where('id', '!=', $kitchenId);
        }

        return $query->get()->all();
    }

    /** @var array<int, Ingredient>|null Cache trong 1 lần render — blade gọi hàm này trong vòng lặp dòng */
    protected ?array $ingredientsListCache = null;

    public function getIngredientsList(): array
    {
        return $this->ingredientsListCache ??= Ingredient::where('status', true)->get()->all();
    }

    public function getRecentTransfers(): array
    {
        $kitchenId = auth()->user()?->currentKitchenId();
        if (! $kitchenId) {
            return StockTransfer::with(['destKitchen', 'sourceKitchen'])->latest()->take(10)->get()->all();
        }

        return StockTransfer::with(['destKitchen', 'sourceKitchen'])
            ->where(function ($q) use ($kitchenId) {
                $q->where('source_kitchen_id', $kitchenId)
                    ->orWhere('dest_kitchen_id', $kitchenId);
            })
            ->latest()
            ->take(10)
            ->get()
            ->all();
    }

    // Original Single Item Movement Fallbacks (for compatibility if called)
    public function saveMovement(string $type): void
    {
        if ($type === 'in') {
            if (! $this->inIngredientId || $this->inQuantity <= 0) {
                Notification::make()->title('Vui lòng chọn nguyên liệu và số lượng hợp lệ!')->danger()->send();

                return;
            }

            $kitchenId = auth()->user()?->currentKitchenId();
            $stock = Stock::where('kitchen_id', $kitchenId)->where('ingredient_id', $this->inIngredientId)->first();
            if (! $stock) {
                $stock = Stock::create([
                    'kitchen_id' => $kitchenId,
                    'ingredient_id' => $this->inIngredientId,
                    'quantity' => 0,
                    'min_quantity' => 10,
                    'unit_price' => $this->inPrice,
                ]);
            }

            $newQty = $stock->quantity + $this->inQuantity;
            StockTransaction::create([
                'kitchen_id' => $kitchenId,
                'ingredient_id' => $stock->ingredient_id,
                'type' => 'Nhập kho',
                'quantity' => $this->inQuantity,
                'after_quantity' => $newQty,
                'note' => $this->inRef ?: 'Nhập kho trực tiếp',
            ]);

            $stock->update([
                'quantity' => $newQty,
                'unit_price' => $this->inPrice > 0 ? $this->inPrice : $stock->unit_price,
            ]);

            Notification::make()->title('Đã lưu nhập kho thành công!')->success()->send();

            $this->inQuantity = 0;
            $this->inRef = '';
        } else {
            if (! $this->outIngredientId || $this->outQuantity <= 0) {
                Notification::make()->title('Vui lòng chọn nguyên liệu và số lượng hợp lệ!')->danger()->send();

                return;
            }

            $kitchenId = auth()->user()?->currentKitchenId();
            $stock = Stock::where('kitchen_id', $kitchenId)->where('ingredient_id', $this->outIngredientId)->first();
            if (! $stock || $stock->quantity < $this->outQuantity) {
                Notification::make()->title('Số lượng tồn kho không đủ để xuất!')->danger()->send();

                return;
            }

            $newQty = $stock->quantity - $this->outQuantity;
            StockTransaction::create([
                'kitchen_id' => $kitchenId,
                'ingredient_id' => $stock->ingredient_id,
                'type' => 'Xuất kho',
                'quantity' => $this->outQuantity,
                'after_quantity' => $newQty,
                'note' => $this->outReason.($this->outRef ? ' ('.$this->outRef.')' : ''),
            ]);

            $stock->update(['quantity' => $newQty]);

            Notification::make()->title('Đã lưu xuất kho thành công!')->success()->send();

            $this->outQuantity = 0;
            $this->outRef = '';
        }
    }

    public function getWarehouseData(): array
    {
        $kitchenId = auth()->user()?->currentKitchenId();
        $query = Stock::with(['ingredient.supplier']);

        if ($kitchenId) {
            $query->where('kitchen_id', $kitchenId);
        }

        if (! empty($this->search)) {
            $searchLower = '%'.strtolower($this->search).'%';
            $query->whereHas('ingredient', function ($q) use ($searchLower) {
                $q->whereRaw('LOWER(name) LIKE ?', [$searchLower])
                    ->orWhereRaw('LOWER(code) LIKE ?', [$searchLower]);
            });
        }

        if (! empty($this->selectedType)) {
            $query->whereHas('ingredient', function ($q) {
                $q->where('type', $this->selectedType);
            });
        }

        return $query->get()->toArray();
    }

    public function getLogData(): array
    {
        $kitchenId = auth()->user()?->currentKitchenId();
        $query = StockTransaction::with(['ingredient'])->latest();

        if ($kitchenId) {
            $query->where('kitchen_id', $kitchenId);
        }

        return $query->take(50)->get()->toArray();
    }

    public function getStats(): array
    {
        $kitchenId = auth()->user()?->currentKitchenId();
        $query = Stock::query();

        if ($kitchenId) {
            $query->where('kitchen_id', $kitchenId);
        }

        // Tính KPI bằng SQL aggregate thay vì nạp toàn bộ bảng tồn kho vào RAM
        $stats = $query
            ->selectRaw('COUNT(*) AS total_items')
            ->selectRaw('COALESCE(SUM(quantity * unit_price), 0) AS total_value')
            ->selectRaw('COALESCE(SUM(CASE WHEN quantity <= min_quantity THEN 1 ELSE 0 END), 0) AS low_stock')
            ->selectRaw('COALESCE(SUM(CASE WHEN DATE(updated_at) = ? THEN 1 ELSE 0 END), 0) AS checked_today', [now()->toDateString()])
            ->first();

        $totalItems = (int) $stats->total_items;

        return [
            'items' => $totalItems,
            'value' => (float) $stats->total_value,
            'low' => (int) $stats->low_stock,
            'check' => max(0, $totalItems - (int) $stats->checked_today),
        ];
    }

    public function openLedger(int $ingId): void
    {
        $this->selectedLedgerIngId = $ingId;
        $kitchenId = auth()->user()?->currentKitchenId();

        $this->ledgerTransactions = StockTransaction::where('ingredient_id', $ingId)
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->latest()
            ->get()
            ->toArray();
    }

    public function closeLedger(): void
    {
        $this->selectedLedgerIngId = null;
        $this->ledgerTransactions = [];
    }
}
