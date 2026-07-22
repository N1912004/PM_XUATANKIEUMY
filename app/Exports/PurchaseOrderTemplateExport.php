<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Xuất đơn đặt hàng gửi NCC theo file mẫu "MẪU ĐƠN ĐẶT HÀNG.xlsx":
 * tiêu đề gộp ô + các SECTION theo nhóm nguyên liệu (Động vật / Thực vật / Thực phẩm khô /
 * Gia vị / ...), mỗi section có header cột riêng:
 * STT | NGUYÊN LIỆU | Đơn vị tính | Số Lượng (KG) | Giá tiền | NCC | Tổng | Ghi chú.
 */
class PurchaseOrderTemplateExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected const COLS = 8;

    protected const SECTION_HEADINGS = ['STT', 'NGUYÊN LIỆU', 'Đơn vị tính', 'Số Lượng (KG)', 'Giá tiền', 'NCC', 'Tổng', 'Ghi chú'];

    /** @var array<int, int> Dòng tiêu đề section (1-indexed) để style */
    protected array $sectionTitleRows = [];

    /** @var array<int, int> Dòng heading cột của từng section */
    protected array $headingRows = [];

    protected int $grandTotalRow = 0;

    public function __construct(protected PurchaseOrder $order, protected ?string $sheetTitle = null) {}

    public function title(): string
    {
        return $this->sheetTitle ?: 'Đơn đặt hàng';
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $order = $this->order->loadMissing(['supplier', 'kitchen', 'items.ingredient']);

        $rows = [];
        $rows[] = $this->pad(['ĐẶT HÀNG NHÀ CUNG CẤP']);
        $rows[] = $this->pad([
            'Ngày: '.($order->estimated_delivery_date?->format('d/m/Y') ?? now()->format('d/m/Y'))
            .'   |   Mã đơn: '.$order->code
            .'   |   NCC: '.($order->supplier?->name ?? 'Chưa gán')
            .'   |   Bếp: '.($order->kitchen?->name ?? ''),
        ]);
        $rows[] = $this->pad([]);

        // Section theo nhóm nguyên liệu, đúng bố cục file MẪU ĐƠN ĐẶT HÀNG.xlsx
        $grouped = $order->items->groupBy(fn ($item) => $item->ingredient?->typeRelation?->name ?? $item->ingredient?->type ?: 'Khác');
        $grandTotal = 0.0;

        foreach ($grouped as $groupName => $items) {
            $this->sectionTitleRows[] = count($rows) + 1;
            $rows[] = $this->pad([$this->getSectionTitle((string) $groupName)]);

            $this->headingRows[] = count($rows) + 1;
            $rows[] = self::SECTION_HEADINGS;

            $i = 1;
            foreach ($items as $item) {
                $lineTotal = (float) $item->quantity_ordered * (float) $item->unit_price;
                $grandTotal += $lineTotal;

                $rows[] = [
                    $i++,
                    $item->ingredient?->name ?? '',
                    $item->ingredient?->unit ?? 'Kg',
                    (float) $item->quantity_ordered,
                    (float) $item->unit_price,
                    $order->supplier?->name ?? '',
                    $lineTotal,
                    $item->receive_note ?? '',
                ];
            }

            $rows[] = $this->pad([]);
        }

        $this->grandTotalRow = count($rows) + 1;
        $rows[] = ['', 'TỔNG CỘNG', '', '', '', '', $grandTotal, ''];

        // Khối chữ ký
        $rows[] = $this->pad([]);
        $rows[] = ['NGƯỜI ĐẶT HÀNG', '', '', '', 'NHÀ CUNG CẤP XÁC NHẬN', '', '', ''];
        $rows[] = ['(Ký, ghi rõ họ tên)', '', '', '', '(Ký, ghi rõ họ tên)', '', '', ''];

        return $rows;
    }

    protected function getSectionTitle(string $groupName): string
    {
        $lower = mb_strtolower($groupName);
        if (str_contains($lower, 'động vật') || str_contains($lower, 'thịt') || str_contains($lower, 'cá')) {
            return 'Động Vật: ( Thịt, cá, tôm, ....... )';
        }
        if (str_contains($lower, 'thực vật') || str_contains($lower, 'rau') || str_contains($lower, 'củ')) {
            return 'Thực vật: ( Rau, củ quả,.... )';
        }
        if (str_contains($lower, 'khô')) {
            return 'Thực phẩm khô: ( Hàng khô, đồ đóng hộp,.... )';
        }
        if (str_contains($lower, 'gia vị')) {
            return 'Gia vị: ( Mắm, muối, đường,.... )';
        }

        return $groupName.':';
    }

    /**
     * @param  array<int, mixed>  $row
     * @return array<int, mixed>
     */
    protected function pad(array $row): array
    {
        return array_pad($row, self::COLS, '');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'H';
                $lastRow = $sheet->getHighestRow();

                // Tiêu đề lớn gộp ô + dòng thông tin đơn
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15);
                $sheet->getStyle("A1:{$lastCol}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                foreach ($this->sectionTitleRows as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setBold(true);
                }

                foreach ($this->headingRows as $r) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                if ($this->grandTotalRow > 0) {
                    $sheet->getStyle("A{$this->grandTotalRow}:{$lastCol}{$this->grandTotalRow}")->getFont()->setBold(true);
                }

                // Chữ ký: 2 dòng cuối — trái (A:D) người đặt, phải (E:H) NCC
                foreach ([$lastRow - 1, $lastRow] as $r) {
                    $sheet->mergeCells("A{$r}:D{$r}");
                    $sheet->mergeCells("E{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                $sheet->getStyle('A'.($lastRow - 1).":{$lastCol}".($lastRow - 1))->getFont()->setBold(true);

                // Format tiền tệ cột Giá tiền + Tổng
                $sheet->getStyle("E1:E{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("G1:G{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            },
        ];
    }
}
