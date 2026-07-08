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

class RecipeExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected const COLS = 10;

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
        $rows[] = ['NGÂN HÀNG THỰC ĐƠN', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', '', '', '', ''];
        $rows[] = [
            'Mã món',
            'Tên món ăn',
            'Nhóm món',
            'Mức giá suất ăn',
            'Đơn giá suất ăn',
            'Số nguyên liệu',
            'Tổng định lượng / phần',
            'Tổng cost nguyên liệu / phần',
            'Trạng thái',
            'Cập nhật',
        ];

        $this->recipes()->each(function (Recipe $recipe) use (&$rows): void {
            $rows[] = [
                $recipe->code,
                $recipe->name,
                $recipe->type,
                (float) $recipe->price_level,
                (float) $recipe->actual_price,
                $recipe->ingredients->count(),
                (float) $recipe->ingredients->sum('pivot.quantity_per_portion'),
                (float) $recipe->ingredients->sum(fn ($ingredient): float => (float) $ingredient->pivot->quantity_per_portion * (float) $ingredient->reference_price),
                match ($recipe->status) {
                    'active' => 'Đang hoạt động',
                    'pending' => 'Chờ rà soát',
                    'inactive' => 'Ngừng hoạt động',
                    default => $recipe->status,
                },
                $recipe->updated_at?->format('d/m/Y H:i'),
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
                $sheet->getStyle("D4:H{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            },
        ];
    }

    /**
     * @return Collection<int, Recipe>
     */
    private function recipes(): Collection
    {
        return Recipe::query()
            ->with('ingredients')
            ->latest('updated_at')
            ->get();
    }
}
