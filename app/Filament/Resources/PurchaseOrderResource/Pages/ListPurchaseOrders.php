<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Exports\PurchaseOrdersListExport;
use App\Exports\PurchaseOrderTemplateExport;
use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
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

    public string $fromDate = '';

    public string $toDate = '';

    public string $statusFilter = '';

    public int $perPage = 10;

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'fromDate' => ['except' => ''],
        'toDate' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (empty($this->fromDate)) {
            $this->fromDate = now()->startOfMonth()->toDateString();
        }

        if (empty($this->toDate)) {
            $this->toDate = now()->endOfMonth()->toDateString();
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFromDate(): void
    {
        $this->resetPage();
    }

    public function updatedToDate(): void
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
        $this->fromDate = now()->startOfMonth()->toDateString();
        $this->toDate = now()->endOfMonth()->toDateString();
        $this->statusFilter = '';
        $this->resetPage();
    }

    /**
     * Tra PO theo id đã scope bếp — orderId là dữ liệu client, user thường
     * không được chạm PO của bếp khác (policy hiện chỉ check permission).
     */
    protected function findScopedOrder(int $orderId, array $with = []): ?PurchaseOrder
    {
        return PurchaseOrder::query()
            ->with($with)
            ->when(
                ($user = auth()->user()) && ! $user->hasRole([User::superAdminRole(), 'Quản trị viên']),
                fn ($query) => $query->where('kitchen_id', auth()->user()->currentKitchenId() ?? -1)
            )
            ->find($orderId);
    }

    public function deleteOrder(int $orderId): void
    {
        $order = $this->findScopedOrder($orderId);

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

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function dateRange(): array
    {
        $from = filled($this->fromDate) ? Carbon::parse($this->fromDate)->startOfDay() : now()->startOfMonth()->startOfDay();
        $to = filled($this->toDate) ? Carbon::parse($this->toDate)->endOfDay() : now()->endOfMonth()->endOfDay();

        if ($from > $to) {
            return [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    public function stats(): array
    {
        [$start, $end] = $this->dateRange();

        $statusCounts = PurchaseOrder::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        // Total value of all orders created in this filtered date range
        $totalValue = (float) PurchaseOrderItem::query()
            ->whereHas('purchaseOrder', fn ($q) => $q->whereBetween('created_at', [$start, $end]))
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

        $orders = $this->baseQuery()
            ->with(['supplier', 'kitchen', 'items'])
            ->orderByDesc('id')
            ->get();

        return Excel::download(
            new PurchaseOrdersListExport($orders),
            'don-dat-hang-'.now()->format('Ymd-His').'.xlsx',
        );
    }

    protected function baseQuery()
    {
        [$start, $end] = $this->dateRange();

        return PurchaseOrder::query()
            ->when(
                ($user = auth()->user()) && ! $user->hasRole(['super_admin', 'Quản trị viên']),
                fn ($query) => $query->where('kitchen_id', auth()->user()->currentKitchenId() ?? -1)
            )
            ->whereBetween('created_at', [$start, $end])
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
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter));
    }

    /**
     * Xuất 1 đơn đặt hàng ra .xlsx theo đúng file mẫu "MẪU ĐƠN ĐẶT HÀNG.xlsx"
     * (dùng lại PurchaseOrderTemplateExport như nút xuất ở trang chi tiết — một mẫu duy nhất).
     */
    public function exportSingleOrder(int $orderId): BinaryFileResponse
    {
        $order = $this->findScopedOrder($orderId, ['supplier', 'kitchen', 'items.ingredient']);

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
