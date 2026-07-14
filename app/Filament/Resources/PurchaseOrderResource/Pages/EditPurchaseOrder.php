<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Exports\PurchaseOrderTemplateExport;
use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Xuất Excel gộp tất cả các đơn đặt hàng (nhà cung cấp) cùng đợt
     */
    public function exportAllNcc(): StreamedResponse
    {
        abort_unless(PurchaseOrderResource::canView($this->record), 403);

        // Chỉ xuất các PO cùng đợt mà user thực sự có quyền xem (không vượt phạm vi record đã authorize)
        $relatedPOs = PurchaseOrder::with(['supplier', 'kitchen', 'items.ingredient'])
            ->where('kitchen_id', $this->record->kitchen_id)
            ->where('estimated_delivery_date', $this->record->estimated_delivery_date)
            ->get()
            ->filter(fn ($po) => PurchaseOrderResource::canView($po))
            ->values();

        // Null-safe: PO có thể chưa có ngày giao dự kiến
        $fileName = 'PO-DOT-'.($this->record->estimated_delivery_date?->format('Ymd') ?? now()->format('Ymd')).'.csv';

        return response()->streamDownload(function () use ($relatedPOs): void {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            foreach ($relatedPOs as $order) {
                fputcsv($output, ['Mã đơn hàng', $order->code]);
                fputcsv($output, ['Nhà cung cấp', $order->supplier?->name ?? 'Chưa gán']);
                fputcsv($output, ['Ngày giao dự kiến', $order->estimated_delivery_date?->format('d/m/Y') ?? '']);
                fputcsv($output, ['STT', 'Mã nguyên liệu', 'Tên nguyên liệu', 'Đơn vị', 'SL đặt', 'Đơn giá', 'Thành tiền']);

                $i = 1;
                foreach ($order->items as $item) {
                    $total = $item->quantity_ordered * $item->unit_price;
                    fputcsv($output, [
                        $i++,
                        $item->ingredient?->code ?? '',
                        $item->ingredient?->name ?? '',
                        $item->ingredient?->unit ?? '',
                        $item->quantity_ordered,
                        $item->unit_price,
                        $total,
                    ]);
                }
                fputcsv($output, []); // dòng trống phân cách
            }
            fclose($output);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
