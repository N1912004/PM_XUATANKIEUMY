<?php

namespace App\Exports;

use App\Models\Supplier;
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

class SuppliersExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected const COLS = 8;

    protected const TITLE = 'DANH SÁCH NHÀ CUNG CẤP';

    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    public function title(): string
    {
        return self::TITLE;
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rows = [];
        $rows[] = [self::TITLE, '', '', '', '', '', '', ''];
        $rows[] = ['STT', 'Mã NCC', 'Tên NCC', 'Số điện thoại', 'Email', 'Loại TP cung cấp', 'Số nguyên liệu', 'Trạng thái'];

        $index = 1;
        $this->suppliers()->each(function (Supplier $supplier) use (&$rows, &$index): void {
            $rows[] = [
                $index++,
                $supplier->code,
                $supplier->name,
                $supplier->phone,
                $supplier->email,
                $supplier->ingredientTypes->pluck('name')->join(', '),
                $supplier->ingredients_count,
                $supplier->status ? 'Đang hoạt động' : 'Tạm khóa',
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

                $sheet->getStyle("A3:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G3:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
     * @return Collection<int, Supplier>
     */
    private function suppliers(): Collection
    {
        // Clone để không ảnh hưởng truy vấn gốc; giữ nguyên bộ lọc đã truyền vào.
        $query = $this->query ? clone $this->query : Supplier::query()->withCount('ingredients')->orderByDesc('id');

        return $query->with('ingredientTypes')->get();
    }
}
