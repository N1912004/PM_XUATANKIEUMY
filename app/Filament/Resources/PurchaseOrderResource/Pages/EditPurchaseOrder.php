<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Exports\PurchaseOrdersBatchExport;
use App\Exports\PurchaseOrderTemplateExport;
use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected static string $view = 'filament.resources.purchase-orders.pages.edit-purchase-order';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
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

    /**
     * Xuất đơn đặt hàng theo BIỂU MẪU NCC (MẪU ĐƠN ĐẶT HÀNG.xlsx): file .xlsx thật,
     * section theo nhóm nguyên liệu, dòng tổng cộng + khối chữ ký.
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
     * Xuất .xlsx gộp TẤT CẢ đơn đặt hàng cùng đợt — mỗi nhà cung cấp một sheet theo biểu mẫu
     * (trước đây là CSV dồn nhiều đơn vào một khối văn bản, không gửi được cho NCC).
     */
    public function exportAllNcc(): BinaryFileResponse
    {
        abort_unless(PurchaseOrderResource::canView($this->record), 403);

        // Chỉ xuất các PO cùng đợt mà user thực sự có quyền xem (không vượt phạm vi record đã authorize)
        $relatedPOs = PurchaseOrder::with(['supplier', 'kitchen', 'items.ingredient'])
            ->where('kitchen_id', $this->record->kitchen_id)
            ->where('estimated_delivery_date', $this->record->estimated_delivery_date)
            ->get()
            ->filter(fn (PurchaseOrder $po): bool => PurchaseOrderResource::canView($po))
            ->values();

        // Null-safe: PO có thể chưa có ngày giao dự kiến
        $fileName = 'PO-DOT-'.($this->record->estimated_delivery_date?->format('Ymd') ?? now()->format('Ymd')).'.xlsx';

        return Excel::download(new PurchaseOrdersBatchExport($relatedPOs), $fileName);
    }
}
