<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ListHangExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected const COLS = 8;

    /**
     * @param  array<int, array<string, mixed>>  $grouped
     */
    public function __construct(
        protected array $grouped,
        protected string $date,
    ) {}

    public function title(): string
    {
        return __('list_hang.export.sheet_title');
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rows = [];
        $formattedDate = Carbon::parse($this->date)->format('d/m/Y');
        $rows[] = [__('list_hang.export.heading', ['date' => $formattedDate]), '', '', '', '', '', '', ''];
        $rows[] = [
            __('list_hang.export.cols.shift'),
            __('list_hang.export.cols.dish'),
            __('list_hang.export.cols.portions'),
            __('list_hang.export.cols.ing_code'),
            __('list_hang.export.cols.ing_name'),
            __('list_hang.export.cols.dl'),
            __('list_hang.export.cols.total_kg'),
            __('list_hang.export.cols.unit'),
        ];

        foreach ($this->grouped as $shift) {
            foreach ($shift['dishes'] as $dish) {
                foreach ($dish['ingredients'] as $ingredient) {
                    $rows[] = [
                        $shift['name'],
                        $dish['name'],
                        $dish['portions'],
                        $ingredient['code'],
                        $ingredient['name'],
                        $ingredient['quantity_per_portion'],
                        $ingredient['quantity'],
                        $ingredient['unit'],
                    ];
                }
            }
        }

        if (count($rows) === 2) {
            $rows[] = [__('list_hang.export.empty'), '', '', '', '', '', '', ''];
        }

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
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F4C81']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D3D3D3'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
