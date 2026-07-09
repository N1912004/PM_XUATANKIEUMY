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

class IngredientsExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected const COLS = 7;

    public function title(): string
    {
        return 'Danh mục nguyên liệu';
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rows = [];
        $rows[] = ['DANH MỤC NGUYÊN LIỆU', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', ''];
        $rows[] = [
            'Mã nguyên liệu',
            'Tên nguyên liệu',
            'Loại nguyên liệu',
            'Đơn vị tính',
            'Đơn giá tham chiếu gốc',
            'Nhà cung cấp',
            'Trạng thái',
        ];

        $this->ingredients()->each(function (Ingredient $ingredient) use (&$rows): void {
            $suppliersText = $ingredient->suppliers->pluck('name')->implode(', ');

            $rows[] = [
                $ingredient->code,
                $ingredient->name,
                $ingredient->type,
                $ingredient->unit,
                (float) $ingredient->reference_price,
                $suppliersText ?: '--',
                $ingredient->status ? 'Đang hoạt động' : 'Ngừng hoạt động',
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
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A3:{$lastCol}3")->getFont()->setBold(true);
                $sheet->getStyle("E4:E{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            },
        ];
    }

    /**
     * @return Collection<int, Ingredient>
     */
    private function ingredients(): Collection
    {
        return Ingredient::query()
            ->with('suppliers')
            ->orderBy('id')
            ->get();
    }
}
