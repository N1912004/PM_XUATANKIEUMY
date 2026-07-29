<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Illuminate\Support\Collection;
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

    /** @var Collection<int, PurchaseOrder> Các đơn của cùng một NCC trong đợt (phiếu tách P1/P2/P3) */
    protected Collection $orders;

    /**
     * @param  PurchaseOrder|Collection<int, PurchaseOrder>  $order  Một đơn, hoặc nhiều đơn cùng một NCC gộp vào một sheet.
     */
    public function __construct(PurchaseOrder|Collection $order, protected ?string $sheetTitle = null)
    {
        $this->orders = $order instanceof PurchaseOrder ? collect([$order]) : $order->values();
    }

    public function title(): string
    {
        return $this->sheetTitle ?: __('purchase_order.excel.sheet_title');
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $orders = $this->orders->each(fn (PurchaseOrder $po) => $po->loadMissing(['supplier', 'kitchen', 'items.ingredient']));
        $first = $orders->first();

        $rows = [];
        $rows[] = $this->pad([__('purchase_order.excel.header_title')]);
        $rows[] = $this->pad([
            __('purchase_order.excel.date', ['date' => $first?->estimated_delivery_date?->format('d/m/Y') ?? now()->format('d/m/Y')])
            .'   |   '.__('purchase_order.excel.order_code', ['code' => $orders->pluck('code')->implode(', ')])
            .'   |   '.__('purchase_order.excel.supplier', ['name' => $first?->supplier?->name ?? __('purchase_order.excel.unassigned')])
            .'   |   '.__('purchase_order.excel.kitchen', ['name' => $first?->kitchen?->name ?? '']),
        ]);
        $rows[] = $this->pad([]);

        // Section theo nhóm nguyên liệu, đúng bố cục file MẪU ĐƠN ĐẶT HÀNG.xlsx
        $grouped = $orders
            ->flatMap(fn (PurchaseOrder $po) => $po->items)
            ->groupBy(fn ($item) => $item->ingredient?->typeRelation?->name ?? $item->ingredient?->type ?: 'Khác');
        $grandTotal = 0.0;
        $codeByOrderId = $orders->pluck('code', 'id');

        $sectionHeadings = [
            __('purchase_order.excel.col_no'),
            __('purchase_order.excel.col_ingredient'),
            __('purchase_order.excel.col_unit'),
            __('purchase_order.excel.col_quantity'),
            __('purchase_order.excel.col_price'),
            __('purchase_order.excel.col_supplier'),
            __('purchase_order.excel.col_total'),
            __('purchase_order.excel.col_note'),
        ];

        foreach ($grouped as $groupName => $items) {
            $this->sectionTitleRows[] = count($rows) + 1;
            $rows[] = $this->pad([$this->getSectionTitle((string) $groupName)]);

            $this->headingRows[] = count($rows) + 1;
            $rows[] = $sectionHeadings;

            $i = 1;

            // Gộp các phiếu tách của cùng NCC: cùng nguyên liệu + cùng đơn giá thì cộng dồn số lượng.
            $merged = $items->groupBy(fn ($item) => $item->ingredient_id.'|'.(float) $item->unit_price);

            foreach ($merged as $lines) {
                $item = $lines->first();
                $quantity = (float) $lines->sum(fn ($line) => (float) $line->quantity_ordered);
                $lineTotal = $quantity * (float) $item->unit_price;
                $grandTotal += $lineTotal;

                $note = $lines
                    ->map(function ($line) use ($codeByOrderId): string {
                        $text = trim((string) $line->receive_note);
                        // Nhiều phiếu tách trong cùng sheet: ghi kèm mã đơn để không mất dấu P1/P2/P3
                        $code = $codeByOrderId->count() > 1
                            ? (string) $codeByOrderId->get($line->purchase_order_id, '')
                            : '';

                        return trim($code !== '' ? trim($code.' '.$text) : $text);
                    })
                    ->filter()
                    ->unique()
                    ->implode('; ');

                $rows[] = [
                    $i++,
                    $item->ingredient?->name ?? '',
                    $item->ingredient?->unitRelation?->name ?? $item->ingredient?->unit ?? 'Kg',
                    $quantity,
                    (float) $item->unit_price,
                    $first?->supplier?->name ?? '',
                    $lineTotal,
                    $note,
                ];
            }

            $rows[] = $this->pad([]);
        }

        $this->grandTotalRow = count($rows) + 1;
        $rows[] = ['', __('purchase_order.excel.grand_total'), '', '', '', '', $grandTotal, ''];

        // Khối chữ ký
        $rows[] = $this->pad([]);
        $rows[] = [__('purchase_order.excel.purchaser'), '', '', '', __('purchase_order.excel.supplier_sign'), '', '', ''];
        $rows[] = [__('purchase_order.excel.sign_note'), '', '', '', __('purchase_order.excel.sign_note'), '', '', ''];

        return $rows;
    }

    protected function getSectionTitle(string $groupName): string
    {
        $lower = mb_strtolower($groupName);
        if (str_contains($lower, 'động vật') || str_contains($lower, 'thịt') || str_contains($lower, 'cá')) {
            return __('purchase_order.excel.sec_animal');
        }
        if (str_contains($lower, 'thực vật') || str_contains($lower, 'rau') || str_contains($lower, 'củ')) {
            return __('purchase_order.excel.sec_plant');
        }
        if (str_contains($lower, 'khô')) {
            return __('purchase_order.excel.sec_dry');
        }
        if (str_contains($lower, 'gia vị')) {
            return __('purchase_order.excel.sec_spice');
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
