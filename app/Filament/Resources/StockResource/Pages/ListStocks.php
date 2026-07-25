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
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getSubheading(): ?string
    {
        return __('warehouse.subheading');
    }

    public string $warehouseTab = 'stock';

    public string $search = '';

    public string $selectedType = '';

    public string $selectedSort = 'latest';

    public int $perPage = 10;

    public int|string|null $selectedKitchenId = null;

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

        $this->selectedKitchenId = session('active_kitchen_id') ?? auth()->user()?->currentKitchenId() ?? 'all';
        $kitchenId = is_numeric($this->selectedKitchenId) ? (int) $this->selectedKitchenId : null;

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

    public function formatQty(float|int|string|null $value): string
    {
        $val = (float) ($value ?? 0);
        $formatted = number_format($val, 2, ',', '.');

        return str_ends_with($formatted, ',00') ? substr($formatted, 0, -3) : rtrim($formatted, '0');
    }

    public function updatedSelectedKitchenId($value): void
    {
        if ($value === 'all' || empty($value)) {
            session(['active_kitchen_id' => 'all']);
            $this->selectedKitchenId = 'all';
        } else {
            session(['active_kitchen_id' => (int) $value]);
            $this->selectedKitchenId = (int) $value;
        }

        // Đổi bếp KHÔNG reload cả trang (trước dùng redirect) — mọi getter đọc currentKitchenId()
        // qua session nên Livewire tự render lại đúng bếp. Chỉ cần dọn cache/ state theo bếp cũ:
        $kitchenId = is_numeric($this->selectedKitchenId) ? (int) $this->selectedKitchenId : null;

        // Cache dữ liệu theo bếp — phải xóa để nạp lại theo bếp mới
        $this->checkStocksCache = null;
        $this->systemQtyCache = [];

        // Bếp nhận mặc định (điều chuyển) phải khác bếp nguồn mới
        $firstKitchen = Kitchen::where('id', '!=', $kitchenId)->first();
        $this->destKitchenId = $firstKitchen?->id;

        // Đóng mọi luồng nhập/xuất/kiểm kê đang mở dở của bếp cũ + xóa dữ liệu form theo bếp
        $this->inMode = null;
        $this->outMode = null;
        $this->selectedPOId = null;
        $this->poItemsData = [];
        $this->directItemsData = [];
        $this->prodItemsData = [];
        $this->transferItemsData = [];
        $this->actualQuantities = [];
        $this->checkNotes = [];
        $this->selectedLedgerIngId = null;
        $this->ledgerTransactions = [];

        // Nếu đang ở tab kiểm kê thì nạp lại tồn hệ thống của bếp mới
        if ($this->warehouseTab === 'check') {
            $this->initEndDayCheck();
        }

        $this->resetPage();
        $this->resetPage('logPage');
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
                ->color('primary')
                ->icon('heroicon-o-document-arrow-up')
                ->action(fn () => $this->openOutTypeModal()),
            Actions\Action::make('check_end_day')
                ->label(__('warehouse.actions.end_day_check'))
                ->color('gray')
                ->outlined()
                ->icon('heroicon-o-clipboard-document-check')
                ->action(fn () => $this->setTab('check')),
        ];
    }

    /**
     * Bếp mà tài khoản đang thao tác. Trước đây fallback ngầm về Kitchen::first() — tức là
     * người chưa gắn bếp lại vô tình xuất/nhập kho của một bếp bất kỳ. Nay trả null và các
     * luồng gọi phải báo rõ "chưa gắn bếp" (super_admin cũng phải gắn nhân viên để vận hành kho).
     */
    public function operatingKitchenId(): ?int
    {
        return auth()->user()?->currentKitchenId();
    }

    public function setTab(string $tab): void
    {
        $this->warehouseTab = $tab;
        $this->selectedType = '';
        $this->search = '';
        $this->checkStocksCache = null;
        $this->resetPage();
        $this->resetPage('logPage');

        if ($tab === 'check' && $this->actualQuantities === []) {
            $this->initEndDayCheck();
        }

    }

    /** Nạp tồn HỆ THỐNG CỦA NGÀY KIỂM KÊ vào form (chỉ khi mở tab / đổi ngày). */
    protected function initEndDayCheck(): void
    {
        $system = $this->getSystemQuantities($this->checkDate);

        foreach ($this->getCheckStocks() as $stock) {
            $this->actualQuantities[$stock->id] = $system[$stock->id] ?? 0.0;
            $this->checkNotes[$stock->id] = '';
        }
    }

    /** Đổi ngày kiểm kê thì nạp lại số liệu của đúng ngày đó. */
    public function updatedCheckDate(): void
    {
        $this->checkStocksCache = null;
        $this->systemQtyCache = [];
        $this->actualQuantities = [];
        $this->checkNotes = [];
        $this->initEndDayCheck();
    }

    /** @var array<string, array<int, float>> Memo tồn hệ thống theo ngày trong 1 render */
    protected array $systemQtyCache = [];

    /**
     * Tồn HỆ THỐNG cuối ngày `$date` cho từng dòng kho (BA R18).
     *
     * `stocks.quantity` là tồn HIỆN TẠI, nên tồn cuối ngày D = tồn hiện tại trừ đi
     * toàn bộ biến động phát sinh SAU ngày D (nhật ký ghi dấu: nhập +, xuất −).
     * Nhờ vậy chọn ngày quá khứ ra đúng số của ngày đó, không phải số hôm nay.
     *
     * @return array<int, float> stock_id => tồn cuối ngày
     */
    public function getSystemQuantities(?string $date): array
    {
        $date ??= now()->toDateString();

        if (isset($this->systemQtyCache[$date])) {
            return $this->systemQtyCache[$date];
        }

        $stocks = $this->getCheckStocks();

        // Điều chỉnh kiểm kê CỦA CHÍNH ngày này (voucher KK-<ngày>) là phần chốt của ngày đó,
        // KHÔNG phải biến động "sau ngày" — nếu tính nó vào, lưu lại cùng số đếm cho ngày quá khứ
        // sẽ trừ tồn thêm mỗi lần (không idempotent). Loại ra để diff hội tụ về 0.
        $selfCheckVoucher = 'KK-'.Carbon::parse($date)->format('Ymd');

        // Gộp theo CẢ (ingredient_id, kitchen_id): một nguyên liệu có thể tồn ở nhiều bếp
        // (admin xem "tất cả bếp"), gộp chỉ theo ingredient_id sẽ trừ chồng biến động của bếp khác.
        $after = StockTransaction::query()
            ->whereIn('ingredient_id', $stocks->pluck('ingredient_id'))
            ->whereIn('kitchen_id', $stocks->pluck('kitchen_id')->unique())
            ->where('created_at', '>=', Carbon::parse($date)->addDay()->startOfDay())
            // Chỉ loại điều chỉnh KK của CHÍNH ngày này; giữ mọi biến động khác (kể cả
            // voucher NULL — SQL 'NULL != x' ra NULL nên phải OR whereNull, không thì mất dòng).
            ->where(function ($q) use ($selfCheckVoucher): void {
                $q->whereNull('voucher_code')
                    ->orWhere('voucher_code', '!=', $selfCheckVoucher);
            })
            ->groupBy('ingredient_id', 'kitchen_id')
            ->selectRaw('ingredient_id, kitchen_id, SUM(quantity) as delta')
            ->get();

        $deltaByStock = [];
        foreach ($after as $row) {
            $deltaByStock[$row->ingredient_id.'-'.$row->kitchen_id] = (float) $row->delta;
        }

        $result = [];
        foreach ($stocks as $stock) {
            $delta = $deltaByStock[$stock->ingredient_id.'-'.$stock->kitchen_id] ?? 0;
            $result[$stock->id] = (float) $stock->quantity - $delta;
        }

        return $this->systemQtyCache[$date] = $result;
    }

    /**
     * Tồn ĐẦU KỲ của ngày kiểm kê = tồn cuối ngày hôm trước (BA R18: số chốt hôm nay
     * chính là tồn đầu kỳ hôm sau — hệ quả trực tiếp của cách tính theo nhật ký).
     *
     * @return array<int, float>
     */
    public function getOpeningQuantities(): array
    {
        return $this->getSystemQuantities(
            Carbon::parse($this->checkDate ?? now())->subDay()->toDateString()
        );
    }

    /** @var Collection|null Memo 1 render cho tab kiểm kê */
    protected $checkStocksCache = null;

    public function getCheckStocks()
    {
        if ($this->checkStocksCache !== null) {
            return $this->checkStocksCache;
        }

        $kitchenId = auth()->user()?->currentKitchenId();

        return $this->checkStocksCache = Stock::with(['ingredient.typeRelation'])
            ->whereHas('ingredient')
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->when(! empty($this->search), function ($query) {
                $searchLower = '%'.strtolower($this->search).'%';
                $query->whereHas('ingredient', function ($q) use ($searchLower) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$searchLower])
                        ->orWhereRaw('LOWER(code) LIKE ?', [$searchLower]);
                });
            })
            ->when(! empty($this->selectedType), function ($query) {
                $query->whereHas('ingredient.typeRelation', function ($q) {
                    $q->where('name', $this->selectedType);
                });
            })
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

    public function getIngredientTypeOptions(): array
    {
        if ($this->warehouseTab === 'log') {
            $types = [
                __('warehouse.transaction_types.inbound'),
                __('warehouse.transaction_types.external_inbound'),
                __('warehouse.transaction_types.outbound'),
                __('warehouse.transaction_types.transfer_out'),
                __('warehouse.transaction_types.transfer_in'),
                __('warehouse.transaction_types.stock_check'),
            ];

            return array_values(array_unique($types));
        }

        return IngredientType::orderBy('name')->pluck('name')->all();
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
        if (! $this->operatingKitchenId()) {
            Notification::make()
                ->title(__('warehouse.notifications.select_specific_kitchen'))
                ->warning()
                ->send();

            return;
        }

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
        if (! $this->operatingKitchenId()) {
            Notification::make()
                ->title(__('warehouse.notifications.select_specific_kitchen'))
                ->warning()
                ->send();

            return;
        }

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
        if (! $kitchenId) {
            Notification::make()
                ->title(__('warehouse.notifications.select_specific_kitchen'))
                ->warning()
                ->send();

            return;
        }

        // Đối chiếu với tồn HỆ THỐNG CỦA NGÀY KIỂM KÊ (tính lại từ DB, không tin payload client)
        $systemQty = collect($this->getSystemQuantities($this->checkDate));

        // Kho thực tế không thể ÂM — số đếm âm là nhập sai, chặn ngay thay vì ghi vào sổ
        foreach ($this->actualQuantities as $stockId => $actualQty) {
            if ((float) $actualQty < 0) {
                Notification::make()
                    ->title(__('warehouse.notifications.negative_stock_title'))
                    ->body(__('warehouse.notifications.negative_stock_body'))
                    ->danger()
                    ->send();

                return;
            }
        }

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

        $checkDate = $this->checkDate ?? now()->toDateString();
        $voucherCode = 'KK-'.Carbon::parse($checkDate)->format('Ymd');

        try {
            DB::transaction(function () use ($kitchenId, $systemQty, $voucherCode): void {
                foreach ($this->actualQuantities as $stockId => $actualQty) {
                    // Khóa dòng tồn và chỉ chấp nhận stock thuộc bếp của user (chống sửa payload chéo bếp)
                    $stock = Stock::query()
                        ->with('ingredient')
                        ->whereKey($stockId)
                        ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
                        ->lockForUpdate()
                        ->first();

                    if (! $stock || ! $systemQty->has($stockId)) {
                        continue;
                    }

                    // Lệch so với tồn của NGÀY KIỂM KÊ; áp bằng ĐIỀU CHỈNH (+/−) chứ không ghi đè,
                    // để kiểm kê ngày quá khứ không xóa mất các biến động phát sinh sau đó.
                    $diff = (float) $actualQty - (float) $systemQty[$stockId];

                    if ($diff == 0) {
                        continue;
                    }

                    $newQty = (float) $stock->quantity + $diff;

                    // Điều chỉnh của ngày quá khứ có thể kéo tồn HIỆN TẠI xuống âm — không cho phép
                    if ($newQty < 0) {
                        throw new \RuntimeException(__('warehouse.notifications.negative_after_check', [
                            'name' => $stock->ingredient?->name ?? '',
                        ]));
                    }

                    // Loại giao dịch 'Kiểm kê' riêng (không trộn với Nhập/Xuất kho thường)
                    // để nhật ký đối soát phân biệt được điều chỉnh kiểm kê; quantity giữ DẤU
                    // (+ thừa / − thiếu) — chiều nằm ngay trong số liệu.
                    StockTransaction::create([
                        'kitchen_id' => $stock->kitchen_id,
                        'ingredient_id' => $stock->ingredient_id,
                        'type' => __('warehouse.transaction_types.stock_check'),
                        'voucher_code' => $voucherCode,
                        'quantity' => $diff,
                        'after_quantity' => $newQty,
                        'note' => __('warehouse.notes.end_day_check').': '.(($this->checkNotes[$stockId] ?? '') ?: __('warehouse.notes.actual_difference_adjustment')),
                    ]);

                    $stock->update(['quantity' => $newQty]);
                }
            });
        } catch (\RuntimeException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();

            return;
        }

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

        $kitchenId = auth()->user()?->currentKitchenId() ?? $po->kitchen_id;
        $stocks = Stock::where('kitchen_id', $kitchenId)->pluck('quantity', 'ingredient_id');

        $this->poItemsData = [];
        foreach ($po->items as $item) {
            $this->poItemsData[] = [
                'id' => $item->id,
                'ingredient_id' => $item->ingredient_id,
                'name' => $item->ingredient->name,
                'unit' => $item->ingredient->unit,
                'current_stock' => (float) ($stocks[$item->ingredient_id] ?? 0),
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
            // Lệch là phải có lý do — kể cả nhận 0 (thiếu TOÀN BỘ), trường hợp nghiêm trọng nhất
            if ($qtyReceived !== $qtyOrdered && trim((string) ($itemData['receive_note'] ?? '')) === '') {
                Notification::make()
                    ->title(__('warehouse.notifications.missing_difference_reason_title'))
                    ->body(__('warehouse.notifications.po_difference_body', ['name' => e($itemData['name'] ?? '')]))
                    ->danger()
                    ->send();

                return;
            }
        }

        $userKitchenId = auth()->user()?->currentKitchenId();

        foreach ($this->poItemsData as $itemData) {
            if ((float) ($itemData['quantity_received'] ?? 0) < 0 || (float) ($itemData['unit_price'] ?? 0) < 0) {
                Notification::make()
                    ->title(__('warehouse.notifications.negative_stock_title'))
                    ->body(__('Số lượng và đơn giá không được là số âm'))
                    ->danger()
                    ->send();

                return;
            }
        }

        $done = DB::transaction(function () use ($userKitchenId): bool {
            // Khóa PO và kiểm tra lại trạng thái NGAY TRONG transaction:
            // bấm đúp / 2 người cùng xác nhận thì người sau thấy stocked_at đã có và dừng.
            // Chỉ nhận PO thuộc ĐÚNG bếp của user (Shield policy không xét kitchen — payload
            // client có thể trỏ sang PO của bếp khác); admin (kitchen null) không giới hạn.
            $po = PurchaseOrder::query()
                ->whereKey($this->selectedPOId)
                ->when($userKitchenId, fn ($q) => $q->where('kitchen_id', $userKitchenId))
                ->lockForUpdate()
                ->first();

            if (! $po) {
                Notification::make()->title(__('warehouse.notifications.po_not_found'))->danger()->send();

                return false;
            }

            // Chỉ PO đang chờ nhập mới được nhập kho — chặn draft/cancelled bị flip thẳng sang done,
            // và done/stocked (bấm đúp, 2 người) thì dừng.
            if (! in_array($po->status, ['sent', 'checking'], true) || $po->stocked_at !== null) {
                Notification::make()->title(__('warehouse.notifications.po_already_stocked'))->warning()->send();

                return false;
            }

            $kitchenId = $userKitchenId ?? $po->kitchen_id;

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

        foreach ($this->directItemsData as $item) {
            if ((float) ($item['quantity'] ?? 0) < 0 || (float) ($item['unit_price'] ?? 0) < 0) {
                Notification::make()
                    ->title(__('warehouse.notifications.negative_stock_title'))
                    ->body(__('Số lượng và đơn giá không được là số âm'))
                    ->danger()
                    ->send();

                return;
            }
        }

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

        // Fail-closed: nhập kho trực tiếp phải gắn ĐÚNG một bếp. Admin xem "tất cả bếp" (kitchen null)
        // hay thủ kho chưa gắn bếp đều không được ghi tồn vào kitchen_id null (tồn "vô chủ").
        $kitchenId = auth()->user()?->currentKitchenId();
        if (! $kitchenId) {
            Notification::make()
                ->title(__('warehouse.notifications.select_specific_kitchen'))
                ->warning()
                ->send();

            return;
        }

        // Lọc dòng hợp lệ TRƯỚC khi lưu file — tránh lưu hóa đơn mồ côi + báo thành công giả khi
        // mọi dòng đều qty ≤ 0.
        $validItems = array_filter($this->directItemsData, function ($item): bool {
            return ! empty($item['ingredient_id']) && (float) ($item['quantity'] ?? 0) > 0;
        });

        if ($validItems === []) {
            Notification::make()->title(__('warehouse.notifications.add_at_least_one_item'))->danger()->send();

            return;
        }

        $path = $this->directInvoiceFile->store('stock-vouchers', 'public');

        foreach ($validItems as $itemData) {
            Stock::recordExternalInbound(
                kitchenId: $kitchenId,
                ingredientId: $itemData['ingredient_id'],
                quantity: (float) $itemData['quantity'],
                unitPrice: (float) ($itemData['unit_price'] ?? 0),
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
        $kitchenId = $this->operatingKitchenId();

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

        if (! empty($this->search)) {
            $searchLower = strtolower($this->search);
            $ingredients = array_filter($ingredients, function ($item) use ($searchLower) {
                return str_contains(strtolower($item['name']), $searchLower);
            });
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

        $kitchenId = $this->operatingKitchenId();

        if (! $kitchenId) {
            Notification::make()->title(__('warehouse.notifications.no_source_kitchen'))->danger()->send();

            return;
        }

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

        // Chặn tự-điều-chuyển: bếp nhận trùng bếp xuất là vô nghĩa (đóng băng rồi cộng lại chính
        // tồn của mình) — UI ẩn bếp mình nhưng payload có thể giả, phải chặn server-side.
        if ((int) $this->destKitchenId === (int) $sourceKitchenId) {
            Notification::make()->title(__('warehouse.notifications.select_destination_kitchen'))->danger()->send();

            return;
        }

        // Chặn server-side: bếp nhận phải CÙNG KHU VỰC với bếp xuất (không tin select đã lọc ở UI)
        $sourceAreaId = Kitchen::whereKey($sourceKitchenId)->value('area_id');
        $destAreaId = Kitchen::whereKey($this->destKitchenId)->value('area_id');
        if ($destAreaId !== $sourceAreaId) {
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

        // Chỉ BẾP NHẬN mới được xác nhận nhận hàng (blade chỉ ẩn nút — không đủ, phải chặn server-side).
        // Fail-closed: kitchen null hợp lệ CHỈ với admin (xem "tất cả bếp"); thủ kho chưa gắn bếp
        // (kitchen null) KHÔNG được xác nhận thay bếp khác.
        $user = auth()->user();
        $isAdmin = (bool) $user?->hasRole([User::superAdminRole(), 'Quản trị viên']);
        $kitchenId = $user?->currentKitchenId();

        $allowed = $isAdmin || ($kitchenId !== null && (int) $transfer->dest_kitchen_id === (int) $kitchenId);
        if (! $allowed) {
            Notification::make()->title(__('warehouse.notifications.only_destination_can_receive'))->danger()->send();

            return;
        }

        // confirmReceived() no-op nếu phiếu không còn ở trạng thái chờ nhận — báo đúng kết quả thật
        $received = $transfer->confirmReceived(auth()->id());

        if ($received === false) {
            Notification::make()->title(__('warehouse.notifications.transfer_not_found'))->warning()->send();

            return;
        }

        Notification::make()->title(__('warehouse.notifications.transfer_received'))->success()->send();
    }

    /**
     * Hủy phiếu điều chuyển đang trên đường: nhả lượng đóng băng về bếp xuất (không đổi tồn thật).
     * Chỉ BẾP XUẤT (hoặc admin) được hủy — bếp nhận chỉ có quyền xác nhận nhận.
     */
    public function cancelTransfer(int $transferId): void
    {
        abort_unless(StockResource::canEdit(new Stock), 403);

        $transfer = StockTransfer::find($transferId);
        if (! $transfer) {
            Notification::make()->title(__('warehouse.notifications.transfer_not_found'))->danger()->send();

            return;
        }

        // Fail-closed: kitchen null chỉ hợp lệ với admin; thủ kho phải đúng BẾP XUẤT của phiếu.
        $user = auth()->user();
        $isAdmin = (bool) $user?->hasRole([User::superAdminRole(), 'Quản trị viên']);
        $kitchenId = $user?->currentKitchenId();

        $allowed = $isAdmin || ($kitchenId !== null && (int) $transfer->source_kitchen_id === (int) $kitchenId);
        if (! $allowed) {
            Notification::make()->title(__('warehouse.notifications.only_source_can_cancel'))->danger()->send();

            return;
        }

        $cancelled = $transfer->cancel();

        if ($cancelled === false) {
            Notification::make()->title(__('warehouse.notifications.transfer_not_found'))->warning()->send();

            return;
        }

        Notification::make()->title(__('warehouse.notifications.transfer_cancelled'))->success()->send();
    }

    // Helper Lists
    public function getPendingPOs(): array
    {
        $kitchenId = auth()->user()?->currentKitchenId();
        // Bỏ các PO không có dòng nguyên liệu nào — chọn vào chỉ hiện bảng kiểm hàng TRỐNG
        $query = PurchaseOrder::query()
            ->whereIn('status', ['sent', 'checking'])
            ->whereNull('stocked_at')
            ->whereHas('items');
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
        $this->checkStocksCache = null;
        if ($this->warehouseTab === 'out' && $this->outMode === 'production') {
            $this->loadProductionItems();
        }
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
        $query = Stock::with(['ingredient.supplier'])
            ->whereHas('ingredient');

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

        if ($this->selectedSort === 'oldest') {
            $query->orderBy('stocks.updated_at', 'asc')->orderBy('stocks.id', 'asc');
        } elseif ($this->selectedSort === 'name_asc') {
            $query->join('ingredients', 'stocks.ingredient_id', '=', 'ingredients.id')
                ->select('stocks.*')
                ->orderBy('ingredients.name', 'asc');
        } elseif ($this->selectedSort === 'name_desc') {
            $query->join('ingredients', 'stocks.ingredient_id', '=', 'ingredients.id')
                ->select('stocks.*')
                ->orderBy('ingredients.name', 'desc');
        } else {
            $query->orderBy('stocks.updated_at', 'desc')->orderBy('stocks.id', 'desc');
        }

        return $query->paginate($this->perPage);
    }

    /** Bộ lọc tab Nhật ký kho: theo loại giao dịch, nguyên liệu và KHOẢNG THỜI GIAN (đối soát) */
    public string $logTypeFilter = '';

    public string $logIngredientFilter = '';

    public string $logFromDate = '';

    public string $logToDate = '';

    public int $logPerPage = 15;

    public function updatedLogPerPage(): void
    {
        $this->resetPage('logPage');
    }

    public function updatedLogTypeFilter(): void
    {
        $this->resetPage('logPage');
    }

    public function updatedLogIngredientFilter(): void
    {
        $this->resetPage('logPage');
    }

    public function updatedLogFromDate(): void
    {
        $this->resetPage('logPage');
    }

    public function updatedLogToDate(): void
    {
        $this->resetPage('logPage');
    }

    public function getLogTotal(): int
    {
        return $this->logQuery()->count();
    }

    public function getLogPaginator()
    {
        return $this->logQuery()->paginate($this->logPerPage, ['*'], 'logPage');
    }

    public function getLogData(): array
    {
        return $this->logQuery()->take($this->logPerPage)->get()->toArray();
    }

    protected function logQuery(): Builder
    {
        $kitchenId = auth()->user()?->currentKitchenId();

        return StockTransaction::with(['ingredient'])
            ->latest()
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->when(! empty($this->search), function ($query) {
                $searchLower = '%'.strtolower($this->search).'%';
                $query->whereHas('ingredient', function ($q) use ($searchLower) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$searchLower])
                        ->orWhereRaw('LOWER(code) LIKE ?', [$searchLower]);
                });
            })
            ->when(! empty($this->selectedType), function ($query) {
                $query->where(function ($q) {
                    $q->where('type', $this->selectedType)
                        ->orWhereHas('ingredient.typeRelation', function ($sub) {
                            $sub->where('name', $this->selectedType);
                        });
                });
            })
            ->when($this->logTypeFilter !== '', fn ($q) => $q->where('type', $this->logTypeFilter))
            ->when($this->logIngredientFilter !== '', fn ($q) => $q->where('ingredient_id', (int) $this->logIngredientFilter))
            ->when($this->logFromDate !== '', fn ($q) => $q->where('created_at', '>=', Carbon::parse($this->logFromDate)->startOfDay()))
            ->when($this->logToDate !== '', fn ($q) => $q->where('created_at', '<', Carbon::parse($this->logToDate)->addDay()->startOfDay()));
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
