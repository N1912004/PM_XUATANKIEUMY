<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Báo cáo tài chính chi phí bếp ăn: nhóm Ngày → Ca → Món, kèm số suất và TỔNG GIÁ VỐN (Cost).
 */
class FinancialReportExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected const COLS = 6;

    /**
     * @param  array<int, array<string, mixed>>  $grouped
     * @param  array<string, mixed>  $stats
     */
    public function __construct(
        protected array $grouped,
        protected array $stats,
    ) {}

    public function title(): string
    {
        return 'Báo cáo tài chính';
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rows = [];
        $rows[] = ['BÁO CÁO TÀI CHÍNH CHI PHÍ BẾP ĂN', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];
        $rows[] = ['Ngày', 'Thứ', 'Ca', 'Món ăn', 'Số suất', 'Giá vốn (đ)'];

        foreach ($this->grouped as $day) {
            foreach ($day['shifts'] as $shift) {
                foreach ($shift['dishes'] as $dish) {
                    $rows[] = [
                        $day['date_formatted'],
                        $day['day_of_week'],
                        $shift['name'],
                        $dish['name'],
                        $dish['suat'],
                        round($dish['dish_cost'] ?? 0),
                    ];
                }
            }
        }

        $rows[] = ['', '', '', '', '', ''];
        $rows[] = [
            'TỔNG CỘNG',
            '',
            '',
            ($this->stats['dishes'] ?? 0).' món',
            $this->stats['suat'] ?? 0,
            round($this->stats['cost'] ?? 0),
        ];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(self::COLS);
                $lastRow = $sheet->getHighestRow();

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A3:{$lastCol}3")->getFont()->setBold(true);
                $sheet->getStyle("A{$lastRow}:{$lastCol}{$lastRow}")->getFont()->setBold(true);

                // Định dạng số cho cột giá vốn
                $sheet->getStyle("F4:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            },
        ];
    }
}
