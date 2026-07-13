<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use App\Models\Ingredient;
use App\Models\IngredientType;
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
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

class ListStocks extends ListRecords
{
    use WithFileUploads;

    protected static string $resource = StockResource::class;

    protected static string $view = 'filament.pages.warehouse';

    public function getTitle(): string
    {
        return __('warehouse.title');
    }

    public function getSubheading(): ?string
    {
        return __('warehouse.subheading');
    }

    public string $warehouseTab = 'stock';

    public string $search = '';

    public string $selectedType = '';

    public int $perPage = 10;

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

    public string $outReason = '';

    public string $outRef = '';

    public function mount(): void
    {
        parent::mount();
        $this->checkDate = now()->toDateString();
        $this->inDate = now()->toDateString();
        $this->outDate = now()->toDateString();
        $this->prodDate = now()->toDateString();
        $this->outReason = __('warehouse.notes.default_out_reason');

        $kitchenId = auth()->user()?->currentKitchenId();

        // actualQuantities khởi tạo LAZY khi user mở tab kiểm kê (setTab('check'))
        // — không nạp toàn bộ bảng Stock vào payload Livewire cho mọi lần vào trang

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

            if ($tab === 'check') {
                $this->initEndDayCheck();
            }
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
            Actions\Action::make('create_in')
                ->label(__('warehouse.actions.create_in'))
                ->color('gray')
                ->outlined()
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn () => $this->openInTypeModal()),
            Actions\Action::make('create_out')
                ->label(__('warehouse.actions.create_out'))
                ->color('gray')
                ->outlined()
                ->icon('heroicon-o-document-arrow-up')
                ->action(fn () => $this->openOutTypeModal()),
            Actions\Action::make('check_end_day')
                ->label(__('warehouse.actions.end_day_check'))
                ->color('gray')
                ->outlined()
                ->icon('heroicon-o-clipboard-document-check')
                ->action(fn () => $this->setTab('check')),
            Actions\CreateAction::make()
                ->label(__('warehouse.actions.create_stock'))
                ->color('success'),
        ];
    }

    public function setTab(string $tab): void
    {
        $this->warehouseTab = $tab;

        if ($tab === 'check' && $this->actualQuantities === []) {
            $this->initEndDayCheck();
        }
    }

    /** Nạp tồn hiện tại của bếp vào form kiểm kê (chỉ khi mở tab). */
    protected function initEndDayCheck(): void
    {
        foreach ($this->getCheckStocks() as $stock) {
            $this->actualQuantities[$stock->id] = $stock->quantity;
            $this->checkNotes[$stock->id] = '';
        }
    }

    /** @var Collection|null Memo 1 render cho tab kiểm kê */
    protected $checkStocksCache = null;

    public function getCheckStocks()
    {
        if ($this->checkStocksCache !== null) {
            return $this->checkStocksCache;
        }

        $kitchenId = auth()->user()?->currentKitchenId();

        return $this->checkStocksCache = Stock::with('ingredient')
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->get();
    }

    /** @var Collection|null Memo 1 render */
    protected $shiftsCache = null;

    public function getShiftsList()
    {
        return $this->shiftsCache ??= Shift::all();
    }

    public function getLedgerIngredient(): ?Ingredient
    {
        return $this->selectedLedgerIngId ? Ingredient::find($this->selectedLedgerIngId) : null;
    }

    /** @var \Illuminate\Support\Collection|null Memo 1 render — options bộ lọc loại NL từ bảng danh mục */
    protected $ingredientTypesCache = null;

    public function getIngredientTypeOptions()
    {
        return $this->ingredientTypesCache ??= IngredientType::orderBy('name')->pluck('name');
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
        abort_unless(StockResource::canEdit(new Stock), 403);

        $kitchenId = auth()->user()?->currentKitchenId();

        // Dòng nào lệch tồn (thực tế ≠ hệ thống) BẮT BUỘC ghi lý do — đối chiếu tồn từ DB
        $systemQty = Stock::query()
            ->whereIn('id', array_keys($this->actualQuantities))
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->pluck('quantity', 'id');

        foreach ($this->actualQuantities as $stockId => $actualQty) {
            if (! $systemQty->has($stockId)) {
                continue;
            }
            $diff = (float) $actualQty - (float) $systemQty[$stockId];
            if ($diff != 0 && trim((string) ($this->checkNotes[$stockId] ?? '')) === '') {
                Notification::make()
                    ->title(__('warehouse.notifications.missing_check_reason_title'))
                    ->body(__('warehouse.notifications.missing_check_reason_body'))
                    ->danger()
                    ->send();

                return;
            }
        }

        DB::transaction(function () use ($kitchenId): void {
            foreach ($this->actualQuantities as $stockId => $actualQty) {
                // Khóa dòng tồn và chỉ chấp nhận stock thuộc bếp của user (chống sửa payload chéo bếp)
                $stock = Stock::query()
                    ->whereKey($stockId)
                    ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    continue;
                }

                $diff = (float) $actualQty - (float) $stock->quantity;
                if ($diff != 0) {
                    // Loại giao dịch 'Kiểm kê' riêng (không trộn với Nhập/Xuất kho thường)
                    // để nhật ký đối soát phân biệt được điều chỉnh kiểm kê; quantity giữ DẤU
                    // (+ thừa / − thiếu) — chiều nằm ngay trong số liệu.
                    StockTransaction::create([
                        'kitchen_id' => $stock->kitchen_id,
                        'ingredient_id' => $stock->ingredient_id,
                        'type' => __('warehouse.transaction_types.stock_check'),
                        'quantity' => $diff,
                        'after_quantity' => $actualQty,
                        'note' => __('warehouse.notes.end_day_check').': '.(($this->checkNotes[$stockId] ?? '') ?: __('warehouse.notes.actual_difference_adjustment')),
                    ]);

                    $stock->update(['quantity' => $actualQty]);
                }
            }
        });

        Notification::make()
            ->title(__('warehouse.notifications.end_day_saved'))
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
                'receive_note' => '',
            ];
        }
    }

    public function updatedSelectedPOId(): void
    {
        $this->loadPOItems();
    }

    public function confirmInboundPO(): void
    {
        abort_unless(StockResource::canCreate(), 403);

        if (! $this->selectedPOId) {
            Notification::make()->title(__('warehouse.notifications.select_valid_po'))->danger()->send();

            return;
        }

        // Kiểm hàng: dòng nào lệch số lượng (thực nhận ≠ đặt) bắt buộc ghi lý do.
        // Số ĐẶT đối chiếu từ DB — payload Livewire có thể bị sửa để né việc ghi lý do.
        $orderedByItemId = PurchaseOrderItem::query()
            ->where('purchase_order_id', $this->selectedPOId)
            ->pluck('quantity_ordered', 'id');

        foreach ($this->poItemsData as $itemData) {
            $qtyReceived = (float) ($itemData['quantity_received'] ?? 0);
            $qtyOrdered = (float) ($orderedByItemId[$itemData['id'] ?? 0] ?? 0);
            if ($qtyReceived > 0 && $qtyReceived !== $qtyOrdered && trim((string) ($itemData['receive_note'] ?? '')) === '') {
                Notification::make()
                    ->title(__('warehouse.notifications.missing_difference_reason_title'))
                    ->body(__('warehouse.notifications.po_difference_body', ['name' => e($itemData['name'] ?? '')]))
                    ->danger()
                    ->send();

                return;
            }
        }

        $done = DB::transaction(function (): bool {
            // Khóa PO và kiểm tra lại trạng thái NGAY TRONG transaction:
            // bấm đúp / 2 người cùng xác nhận thì người sau thấy stocked_at đã có và dừng.
            $po = PurchaseOrder::query()->whereKey($this->selectedPOId)->lockForUpdate()->first();

            if (! $po) {
                Notification::make()->title(__('warehouse.notifications.po_not_found'))->danger()->send();

                return false;
            }

            if ($po->status === 'done' || $po->stocked_at !== null) {
                Notification::make()->title(__('warehouse.notifications.po_already_stocked'))->warning()->send();

                return false;
            }

            $kitchenId = auth()->user()?->currentKitchenId() ?? $po->kitchen_id;

            foreach ($this->poItemsData as $itemData) {
                $qtyReceived = (float) ($itemData['quantity_received'] ?? 0);

                if ($qtyReceived <= 0) {
                    continue;
                }

                // Chỉ nhận item thuộc đúng PO đang xác nhận (chống sửa payload trỏ sang PO khác)
                $poItem = PurchaseOrderItem::query()
                    ->whereKey($itemData['id'])
                    ->where('purchase_order_id', $po->id)
                    ->first();

                if (! $poItem) {
                    continue;
                }

                // Đơn giá KHÓA theo giá đã chốt trên PO — không nhận giá từ payload client
                // (quy định nghiệp vụ: giá nhập kho = giá NCC đã chốt lúc đặt hàng)
                $unitPrice = (float) $poItem->unit_price;

                $receiveNote = trim((string) ($itemData['receive_note'] ?? '')) ?: null;

                $poItem->update([
                    'quantity_received' => $qtyReceived,
                    'receive_note' => $receiveNote,
                ]);

                $stock = Stock::query()
                    ->where('kitchen_id', $kitchenId)
                    ->where('ingredient_id', $poItem->ingredient_id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    $stock = Stock::create([
                        'kitchen_id' => $kitchenId,
                        'ingredient_id' => $poItem->ingredient_id,
                        'quantity' => 0,
                        'min_quantity' => 10,
                        'unit_price' => $unitPrice,
                    ]);
                }

                $newQty = (float) $stock->quantity + $qtyReceived;

                $stock->update([
                    'quantity' => $newQty,
                    'unit_price' => $unitPrice > 0 ? $unitPrice : $stock->unit_price,
                ]);

                StockTransaction::create([
                    'kitchen_id' => $kitchenId,
                    'ingredient_id' => $poItem->ingredient_id,
                    'type' => __('warehouse.transaction_types.inbound'),
                    'voucher_code' => $po->code,
                    'quantity' => $qtyReceived,
                    'after_quantity' => $newQty,
                    'note' => __('warehouse.notes.po_inbound', ['code' => $po->code]).($receiveNote ? ' - '.__('warehouse.notes.difference', ['note' => $receiveNote]) : ''),
                ]);
            }

            // updateQuietly: đã nhập kho tại đây rồi, không để hook booted của PurchaseOrder nhập lần 2
            $po->updateQuietly([
                'status' => 'done',
                'stocked_at' => now(),
            ]);

            return true;
        });

        if (! $done) {
            return;
        }

        Notification::make()->title(__('warehouse.notifications.po_inbound_confirmed'))->success()->send();

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
        abort_unless(StockResource::canCreate(), 403);

        if (empty($this->directItemsData)) {
            Notification::make()->title(__('warehouse.notifications.add_at_least_one_item'))->danger()->send();

            return;
        }

        if (! $this->directInvoiceFile) {
            Notification::make()->title(__('warehouse.notifications.invoice_required'))->danger()->send();

            return;
        }

        // Chỉ nhận ảnh/PDF tối đa 5MB — chặn upload file thực thi/script vào disk public
        $this->validate(
            ['directInvoiceFile' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120']],
            [
                'directInvoiceFile.mimes' => __('warehouse.validation.invoice_mimes'),
                'directInvoiceFile.max' => __('warehouse.validation.invoice_max'),
            ],
        );

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
                note: __('warehouse.notes.direct_inbound')
            );
        }

        Notification::make()->title(__('warehouse.notifications.direct_inbound_saved'))->success()->send();

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

        // Chỉ gom nguyên liệu từ thực đơn ĐÃ CHỐT — nháp/đang gửi chưa được phép xuất kho sản xuất
        $menus = Menu::with(['recipe.ingredients'])
            ->where('kitchen_id', $kitchenId)
            ->where('status', 'locked')
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
        abort_unless(StockResource::canEdit(new Stock), 403);

        if (empty($this->prodItemsData)) {
            Notification::make()->title(__('warehouse.notifications.no_production_items'))->danger()->send();

            return;
        }

        $kitchenId = auth()->user()?->currentKitchenId() ?? Kitchen::first()?->id;

        $shift = Shift::find($this->prodShiftId);
        $shiftName = $shift ? $shift->name : "Ca #{$this->prodShiftId}";

        try {
            DB::transaction(function () use ($kitchenId, $shiftName): void {
                $voucherCode = $this->nextVoucherCode('PX-SX');

                foreach ($this->prodItemsData as $itemData) {
                    $qty = (float) ($itemData['quantity_actual'] ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }

                    // Khóa dòng tồn và kiểm tra tồn khả dụng THỰC TẾ tại thời điểm xuất
                    // (không tin available_qty do client giữ từ lúc mở form)
                    $stock = Stock::query()
                        ->where('kitchen_id', $kitchenId)
                        ->where('ingredient_id', $itemData['ingredient_id'])
                        ->lockForUpdate()
                        ->first();

                    $available = $stock ? ((float) $stock->quantity - (float) $stock->frozen_quantity) : 0.0;
                    if (! $stock || $qty > $available) {
                        throw new \RuntimeException(__('warehouse.notifications.production_over_available', [
                            'name' => $itemData['name'],
                            'available' => $available,
                        ]));
                    }

                    $newQty = (float) $stock->quantity - $qty;
                    $stock->update(['quantity' => $newQty]);

                    StockTransaction::create([
                        'kitchen_id' => $kitchenId,
                        'ingredient_id' => $itemData['ingredient_id'],
                        'type' => __('warehouse.transaction_types.outbound'),
                        'voucher_code' => $voucherCode,
                        'quantity' => -$qty,
                        'after_quantity' => $newQty,
                        'note' => __('warehouse.notes.production_outbound', ['shift' => $shiftName, 'date' => $this->prodDate]),
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();

            return;
        }

        Notification::make()->title(__('warehouse.notifications.production_out_confirmed'))->success()->send();

        $this->outMode = null;
        $this->prodItemsData = [];
        $this->warehouseTab = 'stock';
    }

    /**
     * Sinh mã phiếu tuần tự theo ngày (max hiện có + 1) — thay cho random_int(1,999) dễ trùng.
     * Gọi bên trong DB::transaction để thu hẹp tối đa cửa sổ race giữa 2 phiếu đồng thời.
     */
    protected function nextVoucherCode(string $type): string
    {
        $prefix = $type.'-'.now()->format('Ymd').'-';
        $last = StockTransaction::where('voucher_code', 'like', $prefix.'%')
            ->orderByDesc('voucher_code')
            ->value('voucher_code');
        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
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
            'unit' => $firstIng ? $firstIng->unit : '',
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

            $ing = Ingredient::find($ingId);
            $this->transferItemsData[$index]['unit'] = $ing ? $ing->unit : '';
        }
    }

    public function confirmTransferOut(): void
    {
        abort_unless(StockResource::canEdit(new Stock), 403);

        if (! $this->destKitchenId) {
            Notification::make()->title(__('warehouse.notifications.select_destination_kitchen'))->danger()->send();

            return;
        }

        if (empty($this->transferItemsData)) {
            Notification::make()->title(__('warehouse.notifications.add_transfer_item'))->danger()->send();

            return;
        }

        $sourceKitchenId = auth()->user()?->currentKitchenId();
        if (! $sourceKitchenId) {
            Notification::make()->title(__('warehouse.notifications.no_source_kitchen'))->danger()->send();

            return;
        }

        // Chặn server-side: bếp nhận phải CÙNG KHU VỰC với bếp xuất (không tin select đã lọc ở UI)
        $sourceAreaId = Kitchen::whereKey($sourceKitchenId)->value('area_id');
        $destAreaId = Kitchen::whereKey($this->destKitchenId)->value('area_id');
        if ($sourceAreaId && $destAreaId !== $sourceAreaId) {
            Notification::make()
                ->title(__('warehouse.notifications.cross_area_transfer_title'))
                ->body(__('warehouse.notifications.cross_area_transfer_body'))
                ->danger()
                ->send();

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
                $ingName = Ingredient::find($ingId)?->name ?? __('warehouse.common.ingredient');
                Notification::make()->title(__('warehouse.notifications.transfer_over_available', [
                    'name' => $ingName,
                    'available' => $available,
                ]))
                    ->danger()
                    ->send();

                return;
            }
        }

        try {
            // Tạo phiếu + items + đóng băng tồn trong MỘT transaction: lỗi bước nào rollback toàn bộ,
            // không để lại phiếu "Đang chuyển" mà chưa đóng băng tồn (hoặc ngược lại).
            DB::transaction(function () use ($sourceKitchenId): void {
                $lastCode = StockTransfer::where('code', 'like', 'CK-'.now()->format('Ymd').'-%')
                    ->orderByDesc('code')
                    ->value('code');
                $sequence = $lastCode ? ((int) substr($lastCode, -4)) + 1 : 1;

                $transfer = StockTransfer::create([
                    'code' => 'CK-'.now()->format('Ymd').'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
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

                // Kiểm tra tồn khả dụng thực tế + đóng băng (ném RuntimeException nếu thiếu)
                $transfer->freezeSourceStock();
            });
        } catch (\RuntimeException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();

            return;
        }

        Notification::make()->title(__('warehouse.notifications.transfer_created'))->success()->send();

        $this->outMode = null;
        $this->transferItemsData = [];
        $this->transferNote = '';
        $this->warehouseTab = 'out';
    }

    public function confirmTransferReceive(int $transferId): void
    {
        abort_unless(StockResource::canEdit(new Stock), 403);

        $transfer = StockTransfer::find($transferId);
        if (! $transfer) {
            Notification::make()->title(__('warehouse.notifications.transfer_not_found'))->danger()->send();

            return;
        }

        // Chỉ BẾP NHẬN mới được xác nhận nhận hàng (blade chỉ ẩn nút — không đủ, phải chặn server-side)
        $kitchenId = auth()->user()?->currentKitchenId();
        if ($kitchenId !== null && (int) $transfer->dest_kitchen_id !== (int) $kitchenId) {
            Notification::make()->title(__('warehouse.notifications.only_destination_can_receive'))->danger()->send();

            return;
        }

        $transfer->confirmReceived(auth()->id());

        Notification::make()->title(__('warehouse.notifications.transfer_received'))->success()->send();
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

            // Quy định nghiệp vụ: chỉ điều chuyển giữa các bếp CÙNG KHU VỰC quản lý
            $areaId = Kitchen::whereKey($kitchenId)->value('area_id');
            if ($areaId) {
                $query->where('area_id', $areaId);
            }
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
        abort_unless(StockResource::canEdit(new Stock), 403);

        if ($type === 'in') {
            if (! $this->inIngredientId || $this->inQuantity <= 0) {
                Notification::make()->title(__('warehouse.notifications.select_valid_ingredient_quantity'))->danger()->send();

                return;
            }

            $kitchenId = auth()->user()?->currentKitchenId();

            DB::transaction(function () use ($kitchenId): void {
                $stock = Stock::where('kitchen_id', $kitchenId)
                    ->where('ingredient_id', $this->inIngredientId)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    $stock = Stock::create([
                        'kitchen_id' => $kitchenId,
                        'ingredient_id' => $this->inIngredientId,
                        'quantity' => 0,
                        'min_quantity' => 10,
                        'unit_price' => $this->inPrice,
                    ]);
                }

                $newQty = (float) $stock->quantity + $this->inQuantity;

                $stock->update([
                    'quantity' => $newQty,
                    'unit_price' => $this->inPrice > 0 ? $this->inPrice : $stock->unit_price,
                ]);

                StockTransaction::create([
                    'kitchen_id' => $kitchenId,
                    'ingredient_id' => $stock->ingredient_id,
                    'type' => __('warehouse.transaction_types.inbound'),
                    'quantity' => $this->inQuantity,
                    'after_quantity' => $newQty,
                    'note' => $this->inRef ?: __('warehouse.notes.direct_stock_in'),
                ]);
            });

            Notification::make()->title(__('warehouse.notifications.stock_in_saved'))->success()->send();

            $this->inQuantity = 0;
            $this->inRef = '';
        } else {
            if (! $this->outIngredientId || $this->outQuantity <= 0) {
                Notification::make()->title(__('warehouse.notifications.select_valid_ingredient_quantity'))->danger()->send();

                return;
            }

            $kitchenId = auth()->user()?->currentKitchenId();

            try {
                DB::transaction(function () use ($kitchenId): void {
                    $stock = Stock::where('kitchen_id', $kitchenId)
                        ->where('ingredient_id', $this->outIngredientId)
                        ->lockForUpdate()
                        ->first();

                    // So sánh với tồn KHẢ DỤNG (trừ phần đang đóng băng cho điều chuyển), không phải tồn thô
                    $available = $stock ? ((float) $stock->quantity - (float) $stock->frozen_quantity) : 0.0;
                    if (! $stock || $available < $this->outQuantity) {
                        throw new \RuntimeException(__('warehouse.notifications.available_not_enough'));
                    }

                    $newQty = (float) $stock->quantity - $this->outQuantity;

                    $stock->update(['quantity' => $newQty]);

                    StockTransaction::create([
                        'kitchen_id' => $kitchenId,
                        'ingredient_id' => $stock->ingredient_id,
                        'type' => __('warehouse.transaction_types.outbound'),
                        'quantity' => $this->outQuantity,
                        'after_quantity' => $newQty,
                        'note' => $this->outReason.($this->outRef ? ' ('.$this->outRef.')' : ''),
                    ]);
                });
            } catch (\RuntimeException $e) {
                Notification::make()->title($e->getMessage())->danger()->send();

                return;
            }

            Notification::make()->title(__('warehouse.notifications.stock_out_saved'))->success()->send();

            $this->outQuantity = 0;
            $this->outRef = '';
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedType(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function getWarehouseData(): LengthAwarePaginator
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
            // Cột ingredients.type đã đổi thành quan hệ ingredient_type_id → lọc qua bảng danh mục
            $query->whereHas('ingredient.typeRelation', function ($q) {
                $q->where('name', $this->selectedType);
            });
        }

        return $query->orderBy('id')->paginate($this->perPage);
    }

    /** Bộ lọc tab Nhật ký kho: theo loại giao dịch và nguyên liệu (phục vụ đối soát) */
    public string $logTypeFilter = '';

    public string $logIngredientFilter = '';

    public function getLogData(): array
    {
        $kitchenId = auth()->user()?->currentKitchenId();
        $query = StockTransaction::with(['ingredient'])->latest();

        if ($kitchenId) {
            $query->where('kitchen_id', $kitchenId);
        }

        if ($this->logTypeFilter !== '') {
            $query->where('type', $this->logTypeFilter);
        }

        if ($this->logIngredientFilter !== '') {
            $query->where('ingredient_id', (int) $this->logIngredientFilter);
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

    public function transactionTypeLabel(?string $type): string
    {
        return match ($type) {
            __('warehouse.transaction_types.inbound') => __('warehouse.transaction_type_labels.inbound'),
            __('warehouse.transaction_types.external_inbound') => __('warehouse.transaction_type_labels.external_inbound'),
            __('warehouse.transaction_types.outbound') => __('warehouse.transaction_type_labels.outbound'),
            __('warehouse.transaction_types.transfer_out') => __('warehouse.transaction_type_labels.transfer_out'),
            __('warehouse.transaction_types.transfer_in') => __('warehouse.transaction_type_labels.transfer_in'),
            __('warehouse.transaction_types.stock_check') => __('warehouse.transaction_type_labels.stock_check'),
            default => $type ?? '—',
        };
    }

    public function transferStatusLabel(?string $status): string
    {
        return match ($status) {
            StockTransfer::STATUS_DONE => __('warehouse.status.received'),
            StockTransfer::STATUS_CANCELLED => __('warehouse.status.cancelled'),
            StockTransfer::STATUS_IN_TRANSIT => __('warehouse.status.in_transit'),
            default => $status ?? '—',
        };
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
