<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Exports\PurchaseOrdersListExport;
use App\Exports\PurchaseOrderTemplateExport;
use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListPurchaseOrders extends Page
{
    use WithPagination;

    protected static string $resource = PurchaseOrderResource::class;

    protected static string $view = 'filament.resources.purchase-orders.pages.list-purchase-orders';

    public string $search = '';

    public string $monthFilter = '';

    public string $typeFilter = '';

    public string $statusFilter = '';

    public int $perPage = 10;

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'monthFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        // Default to current month if not set
        if (empty($this->monthFilter)) {
            $this->monthFilter = now()->format('Y-m');
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMonthFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->monthFilter = now()->format('Y-m');
        $this->typeFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function deleteOrder(int $orderId): void
    {
        $order = PurchaseOrder::query()->find($orderId);

        if (! $order) {
            return;
        }

        abort_unless(PurchaseOrderResource::canDelete($order), 403);

        $order->delete();

        Notification::make()
            ->title(__('purchase_order.notifications.deleted'))
            ->success()
            ->send();

        $this->resetPage();
    }

    public function formatFriendly(float $value): string
    {
        if ($value >= 1000000) {
            $m = $value / 1000000;

            return (floor($m) == $m ? number_format($m, 0) : number_format($m, 1, '.', '')).' tr';
        }
        if ($value >= 1000) {
            return __('purchase_order.currency.thousand', ['value' => number_format($value / 1000, 0, '.', '.')]);
        }

        return __('purchase_order.currency.amount', ['value' => number_format($value)]);
    }

    public function monthOptions(): array
    {
        // Distinct months from purchase orders.
        // substr(created_at, 1, 7) = 'YYYY-MM' — chạy được trên CẢ MySQL lẫn SQLite;
        // DATE_FORMAT là hàm riêng của MySQL nên làm vỡ suite test (chạy trên SQLite).
        $months = PurchaseOrder::query()
            ->selectRaw('substr(created_at, 1, 7) as month_val')
            ->distinct()
            ->orderBy('month_val', 'desc')
            ->pluck('month_val')
            ->all();

        $options = [];

        // Add current month if not present
        $currentMonth = now()->format('Y-m');
        if (! in_array($currentMonth, $months)) {
            $months[] = $currentMonth;
            rsort($months);
        }

        foreach ($months as $m) {
            $carbon = Carbon::parse($m.'-01');
            $options[$m] = __('purchase_order.filters.month', ['month' => $carbon->format('m/Y')]);
        }

        return $options;
    }

    public function stats(): array
    {
        [$startOfMonth, $endOfMonth] = $this->monthRange();

        $statusCounts = PurchaseOrder::query()
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        // Total value of all orders created in this filtered month (aggregated in SQL)
        $totalValue = (float) PurchaseOrderItem::query()
            ->whereHas('purchaseOrder', fn ($q) => $q->whereBetween('created_at', [$startOfMonth, $endOfMonth]))
            ->selectRaw('COALESCE(SUM(quantity_ordered * unit_price), 0) as aggregate')
            ->value('aggregate');

        return [
            'total_orders' => $statusCounts->sum(),
            'pending_orders' => $statusCounts->get('checking', 0),
            'done_orders' => $statusCounts->get('done', 0),
            'total_value' => $totalValue,
        ];
    }

    public function orders(): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->with(['supplier', 'items'])
            ->withCount('items')
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);
    }

    public function exportExcel(): BinaryFileResponse
    {
        abort_unless(PurchaseOrderResource::canViewAny(), 403);

        // Xuất .xlsx thật qua Laravel Excel (trước đây là CSV) — dùng chung baseQuery() nên
        // file xuất tôn trọng đúng bộ lọc và phạm vi theo vai trò của bảng đang xem.
        $orders = $this->baseQuery()
            ->with(['supplier', 'kitchen', 'items'])
            ->orderByDesc('id')
            ->get();

        return Excel::download(
            new PurchaseOrdersListExport($orders),
            'don-dat-hang-'.now()->format('Ymd-His').'.xlsx',
        );
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function monthRange(): array
    {
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $this->monthFilter)) {
            $this->monthFilter = now()->format('Y-m');
        }

        $month = Carbon::parse($this->monthFilter.'-01');

        return [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()];
    }

    protected function baseQuery()
    {
        [$startOfMonth, $endOfMonth] = $this->monthRange();

        return PurchaseOrder::query()
            // Kitchen scoping theo convention Timekeeping: user thường chỉ thấy PO của bếp mình
            ->when(
                ($user = auth()->user()) && ! $user->hasRole(['super_admin', 'Quản trị viên']),
                fn ($query) => $query->where('kitchen_id', auth()->user()->currentKitchenId() ?? -1)
            )
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->when($this->search !== '', function ($query): void {
                $search = mb_strtolower($this->search);
                $query->where(function ($query) use ($search): void {
                    $query->whereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(note) LIKE ?', ["%{$search}%"])
                        ->orWhereHas('supplier', function ($q) use ($search) {
                            $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                        });
                });
            })
            ->when($this->typeFilter !== '', function ($query): void {
                // Ưu tiên cột type (PO mới); fallback LIKE trên note/code cho dữ liệu cũ chưa có type
                if ($this->typeFilter === 'week') {
                    $query->where(function ($q) {
                        $q->where('type', 'week')
                            ->orWhere(fn ($qq) => $qq->whereNull('type')->where(function ($w) {
                                $w->whereRaw('LOWER(note) LIKE ?', ['%tuần%'])
                                    ->orWhereRaw('LOWER(code) LIKE ?', ['%tuan%']);
                            }));
                    });
                } elseif ($this->typeFilter === 'day') {
                    $query->where(function ($q) {
                        $q->where('type', 'day')
                            ->orWhere(fn ($qq) => $qq->whereNull('type')->where(function ($w) {
                                $w->whereRaw('LOWER(note) LIKE ?', ['%ngày%'])
                                    ->orWhereRaw('LOWER(code) LIKE ?', ['%ngay%']);
                            }));
                    });
                }
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter));
    }

    /**
     * Xuất 1 đơn đặt hàng ra .xlsx theo đúng file mẫu "MẪU ĐƠN ĐẶT HÀNG.xlsx"
     * (dùng lại PurchaseOrderTemplateExport như nút xuất ở trang chi tiết — một mẫu duy nhất).
     */
    public function exportSingleOrder(int $orderId): BinaryFileResponse
    {
        $order = PurchaseOrder::with(['supplier', 'kitchen', 'items.ingredient'])->find($orderId);

        if (! $order) {
            abort(404);
        }

        abort_unless(PurchaseOrderResource::canView($order), 403);

        return Excel::download(
            new PurchaseOrderTemplateExport($order),
            'PO-'.$order->code.'-'.now()->format('Ymd').'.xlsx',
        );
    }
}
