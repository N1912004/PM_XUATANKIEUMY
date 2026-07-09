<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
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
     * Xuất Excel chi tiết cho riêng đơn hàng (nhà cung cấp) hiện tại
     */
    public function exportCurrentNcc(): StreamedResponse
    {
        $order = $this->record->load(['supplier', 'kitchen', 'items.ingredient']);
        $fileName = 'PO-'.$order->code.'-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($order): void {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, ['Mã đơn hàng', $order->code]);
            fputcsv($output, ['Nhà cung cấp', $order->supplier?->name ?? 'Chưa gán']);
            fputcsv($output, ['Ngày giao dự kiến', $order->estimated_delivery_date?->format('d/m/Y') ?? '']);
            fputcsv($output, []);
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
            fclose($output);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Xuất Excel gộp tất cả các đơn đặt hàng (nhà cung cấp) cùng đợt
     */
    public function exportAllNcc(): StreamedResponse
    {
        $relatedPOs = PurchaseOrder::with(['supplier', 'kitchen', 'items.ingredient'])
            ->where('kitchen_id', $this->record->kitchen_id)
            ->where('estimated_delivery_date', $this->record->estimated_delivery_date)
            ->get();

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
