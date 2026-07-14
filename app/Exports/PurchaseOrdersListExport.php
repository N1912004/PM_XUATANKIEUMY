<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Danh sách đơn đặt hàng (.xlsx) — thay bản CSV cũ. Cùng style header 0F4C81 với các export khác.
 *
 * @see SuppliersExport style tham chiếu
 */
class PurchaseOrdersListExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected const COLS = 8;

    protected const TITLE = 'DANH SÁCH ĐƠN ĐẶT HÀNG';

    /** @param Collection<int, PurchaseOrder> $orders */
    public function __construct(protected Collection $orders) {}

    public function title(): string
    {
        return 'Đơn đặt hàng';
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rows = [];
        $rows[] = array_pad([self::TITLE], self::COLS, '');
        $rows[] = ['STT', 'Mã đơn', 'Nhà cung cấp', 'Bếp ăn', 'Ngày giao dự kiến', 'Tổng giá trị', 'Trạng thái', 'Ghi chú'];

        $index = 1;
        $grandTotal = 0.0;

        foreach ($this->orders as $order) {
            $total = (float) $order->items->sum(fn ($item) => (float) $item->quantity_ordered * (float) $item->unit_price);
            $grandTotal += $total;

            $rows[] = [
                $index++,
                $order->code,
                $order->supplier?->name ?? 'Chưa gán',
                $order->kitchen?->name ?? 'Chung',
                $order->estimated_delivery_date?->format('d/m/Y') ?? '',
                $total,
                PurchaseOrder::STATUS_LABELS[$order->status] ?? $order->status,
                $order->notes ?? '',
            ];
        }

        $rows[] = ['', 'TỔNG CỘNG', '', '', '', $grandTotal, '', ''];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(self::COLS);
                $lastRow = $sheet->getHighestRow();

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getRowDimension(1)->setRowHeight(35);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F4C81']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getStyle("F3:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("A{$lastRow}:{$lastCol}{$lastRow}")->getFont()->setBold(true);

                $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D3D3D3']]],
                ]);
            },
        ];
    }
}
