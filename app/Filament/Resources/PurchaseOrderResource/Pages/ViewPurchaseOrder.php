<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Exports\PurchaseOrdersBatchExport;
use App\Exports\PurchaseOrderTemplateExport;
use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Resources\Pages\ViewRecord;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected static string $view = 'filament.resources.purchase-orders.pages.view-purchase-order';

    public function getTitle(): string
    {
        return __('purchase_order.detail.order_title', ['code' => $this->record->code]);
    }

    public function getHeading(): string
    {
        return '';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * Lấy danh sách các PO cùng đợt đặt hàng (cùng ngày giao dự kiến và cùng bếp)
     */
    public function getRelatedPOs()
    {
        return PurchaseOrder::with(['supplier', 'items'])
            ->withCount('items')
            ->where('kitchen_id', $this->record->kitchen_id)
            ->where('estimated_delivery_date', $this->record->estimated_delivery_date)
            ->get();
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
     * Xuất đơn đặt hàng theo BIỂU MẪU NCC
     */
    public function exportCurrentNcc(): BinaryFileResponse
    {
        abort_unless(PurchaseOrderResource::canView($this->record), 403);

        $fileName = 'PO-'.$this->record->code.'-'.now()->format('Ymd').'.xlsx';

        return Excel::download(
            new PurchaseOrderTemplateExport($this->record),
            $fileName,
        );
    }

    /**
     * Xuất .xlsx gộp TẤT CẢ đơn đặt hàng cùng đợt
     */
    public function exportAllNcc(): BinaryFileResponse
    {
        abort_unless(PurchaseOrderResource::canView($this->record), 403);

        $relatedPOs = PurchaseOrder::with(['supplier', 'kitchen', 'items.ingredient'])
            ->where('kitchen_id', $this->record->kitchen_id)
            ->where('estimated_delivery_date', $this->record->estimated_delivery_date)
            ->get()
            ->filter(fn (PurchaseOrder $po): bool => PurchaseOrderResource::canView($po))
            ->values();

        $fileName = 'PO-DOT-'.($this->record->estimated_delivery_date?->format('Ymd') ?? now()->format('Ymd')).'.xlsx';

        return Excel::download(new PurchaseOrdersBatchExport($relatedPOs), $fileName);
    }
}
