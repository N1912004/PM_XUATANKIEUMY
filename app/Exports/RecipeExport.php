<?php

namespace App\Exports;

use App\Models\Recipe;
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
 * Xuất Ngân hàng thực đơn — cùng format với IngredientsExport (Danh mục nguyên liệu):
 * tiêu đề gộp ô cỡ lớn + dòng header nền xanh đậm chữ trắng + viền toàn bảng.
 */
class RecipeExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected const COLS = 11;

    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    public function title(): string
    {
        return 'Ngân hàng thực đơn';
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rows = [];
        $rows[] = array_pad(['NGÂN HÀNG THỰC ĐƠN'], self::COLS, '');
        $rows[] = [
            'STT',
            'Mã món',
            'Tên món ăn',
            'Nhóm món',
            'Mức giá suất ăn',
            'Đơn giá suất ăn',
            'Số nguyên liệu',
            'Tổng định lượng / phần (kg)',
            'Tổng cost nguyên liệu / phần',
            'Trạng thái',
            'Ngày tạo',
        ];

        $index = 1;
        $this->recipes()->each(function (Recipe $recipe) use (&$rows, &$index): void {
            $rows[] = [
                $index++,
                $recipe->code,
                $recipe->name,
                $recipe->type,
                (float) $recipe->selling_price_per_portion,
                (float) $recipe->cost_per_portion,
                $recipe->ingredients->count(),
                (float) $recipe->ingredients->sum('pivot.quantity_per_portion'),
                // Cost hiệu lực: ưu tiên cost override — khớp với bảng danh sách và Báo cáo
                $recipe->effectiveCostPerPortion(),
                match ($recipe->status) {
                    'active' => 'Đang hoạt động',
                    'pending' => 'Chờ rà soát',
                    'inactive' => 'Ngừng hoạt động',
                    default => $recipe->status,
                },
                $recipe->created_at?->format('d/m/Y H:i'),
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

                // Tiêu đề lớn gộp ô — cùng cỡ/độ cao với export Danh mục nguyên liệu
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getRowDimension(1)->setRowHeight(35);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // Header nền xanh đậm, chữ trắng đậm, canh giữa
                $sheet->getRowDimension(2)->setRowHeight(25);
                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
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
                ]);

                // Format số: tiền (E, F, I) và định lượng (H) canh phải; STT/SL NL/Trạng thái canh giữa
                $sheet->getStyle("E3:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("H3:I{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("E3:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("H3:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A3:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G3:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("J3:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Viền mảnh toàn bảng (từ header đến dòng cuối)
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

    /**
     * @return Collection<int, Recipe>
     */
    private function recipes(): Collection
    {
        if ($this->query) {
            return $this->query->with('ingredients')->latest('created_at')->get();
        }

        return Recipe::query()
            ->with('ingredients')
            ->latest('created_at')
            ->get();
    }
}
