<?php

namespace App\Exports;

use App\Models\Ingredient;
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

class IngredientsExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected const COLS = 8;

    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    public function title(): string
    {
        return __('ingredient.excel.title');
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rows = [];
        $rows[] = [__('ingredient.excel.title'), '', '', '', '', '', '', ''];
        $rows[] = [
            __('ingredient.excel.stt'),
            __('ingredient.excel.code'),
            __('ingredient.excel.name'),
            __('ingredient.excel.type'),
            __('ingredient.excel.unit'),
            __('ingredient.excel.price'),
            __('ingredient.excel.supplier'),
            __('ingredient.excel.status'),
        ];

        $index = 1;
        $this->ingredients()->each(function (Ingredient $ingredient) use (&$rows, &$index): void {
            $suppliersText = $ingredient->suppliers->pluck('name')->implode(', ');

            $rows[] = [
                $index++,
                $ingredient->code,
                $ingredient->name,
                $ingredient->type,
                $ingredient->unit,
                (float) $ingredient->reference_price,
                $suppliersText ?: '',
                $ingredient->status ? __('ingredient.status.active') : __('ingredient.status.inactive'),
            ];
        });

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
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getRowDimension(2)->setRowHeight(25);
                $headerStyle = [
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '0F4C81'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ];
                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray($headerStyle);

                $sheet->getStyle("F3:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("F3:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A3:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("H3:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $borderStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D3D3D3'],
                        ],
                    ],
                ];
                $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray($borderStyle);
            },
        ];
    }

    /**
     * @return Collection<int, Ingredient>
     */
    private function ingredients(): Collection
    {
        // Clone query để tránh ảnh hưởng đến truy vấn gốc của Filament
        $query = $this->query ? clone $this->query : Ingredient::query()->orderBy('id');

        return $query
            ->with(['suppliers', 'unitRelation', 'typeRelation'])
            ->get();
    }
}
