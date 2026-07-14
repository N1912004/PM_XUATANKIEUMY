<?php

namespace App\Exports;

use App\Models\Menu;
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
 * Xuất thực đơn ra .xlsx (thay bản CSV cũ) — dùng cho cả nút xuất trên card tuần/ngày
 * và nút "Gửi xác nhận" (file gửi khách duyệt).
 */
class MenuExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected const COLS = 8;

    protected const TITLE = 'THỰC ĐƠN';

    /** @param Collection<int, Menu> $menus */
    public function __construct(protected Collection $menus, protected string $subtitle = '') {}

    public function title(): string
    {
        return 'Thực đơn';
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rows = [];
        $rows[] = array_pad([self::TITLE], self::COLS, '');
        $rows[] = array_pad([$this->subtitle], self::COLS, '');
        $rows[] = ['STT', 'Bếp ăn', 'Ngày', 'Thứ', 'Ca', 'Món ăn', 'Số suất', 'Trạng thái'];

        $dayNames = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];

        foreach ($this->menus as $i => $menu) {
            $rows[] = [
                $i + 1,
                $menu->kitchen?->name ?? '',
                $menu->date->format('d/m/Y'),
                $dayNames[$menu->date->dayOfWeek],
                $menu->shift?->name ?? '',
                $menu->recipe?->name ?? '',
                (int) $menu->estimated_portions,
                Menu::STATUS_LABELS[$menu->status] ?? $menu->status,
            ];
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
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->getRowDimension(1)->setRowHeight(32);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F4C81']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getStyle("G4:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A3:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D3D3D3']]],
                ]);
            },
        ];
    }
}
