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
 * Báo cáo tổng hợp xuất ăn & nguyên liệu tiêu thụ theo mẫu chuẩn bluefire_demo.html:
 * Ngày → Thứ → Ca → Món ăn → Loại món → Mã NL → Nguyên liệu → Định lượng → Số suất → Số phần → Giá vốn → Tổng KG.
 */
class FinancialReportExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected const COLS = 11;

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
        return __('report.export_title');
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rows = [];
        $rows[] = [__('report.export_heading'), '', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = [
            __('report.export_cols.date'),
            __('report.export_cols.day'),
            __('report.export_cols.shift'),
            __('report.export_cols.dish'),
            __('report.export_cols.dish_type'),
            __('report.export_cols.ing_code'),
            __('report.export_cols.ing_name'),
            __('report.export_cols.dl'),
            __('report.export_cols.portions'),
            __('report.export_cols.cost'),
            __('report.export_cols.total_kg'),
        ];

        foreach ($this->grouped as $day) {
            foreach ($day['shifts'] as $shift) {
                foreach ($shift['dishes'] as $dish) {
                    $dishCost = round($dish['dish_cost'] ?? 0);
                    if (empty($dish['ingredients'])) {
                        $rows[] = [
                            $day['date_formatted'],
                            $day['day_of_week'],
                            $shift['name'],
                            $dish['name'],
                            $dish['type'] ?? '',
                            '',
                            '',
                            '',
                            $dish['suat'],
                            $dishCost,
                            '',
                        ];
                    } else {
                        foreach ($dish['ingredients'] as $isFirst => $ing) {
                            $rows[] = [
                                $day['date_formatted'],
                                $day['day_of_week'],
                                $shift['name'],
                                $isFirst === 0 ? $dish['name'] : '',
                                $isFirst === 0 ? ($dish['type'] ?? '') : '',
                                $ing['code'],
                                $ing['name'],
                                round(($ing['dl_g'] ?? 0) * 1000, 2),
                                $isFirst === 0 ? $dish['suat'] : '',
                                round((float) ($ing['line_cost'] ?? 0)),
                                round((float) ($ing['quantity'] ?? 0), 3),
                            ];
                        }
                    }
                }
            }
        }

        $rows[] = ['', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = [
            __('report.export_summary.total'),
            '',
            '',
            __('report.counts.dishes', ['count' => $this->stats['dishes'] ?? 0]),
            '',
            '',
            __('report.counts.ingredients', ['count' => $this->stats['ingredients'] ?? 0]),
            '',
            $this->stats['suat'] ?? 0,
            round($this->stats['cost'] ?? 0),
            '',
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

                // Định dạng căn lề và định dạng số
                $sheet->getStyle("H4:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("J4:J{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("K4:K{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            },
        ];
    }
}
