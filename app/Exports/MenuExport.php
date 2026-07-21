<?php

namespace App\Exports;

use App\Models\Menu;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

/**
 * Xuất thực đơn ra .xlsx (thực đơn ngày theo mẫu CJ Catering / BlueFire 100%, thực đơn tuần theo bảng tổng hợp),
 * tự động đa ngôn ngữ VI / EN theo ngôn ngữ hệ thống đang chọn.
 */
class MenuExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected bool $isSingleDay = false;

    protected string $singleDayDateStr = '';

    /** @param Collection<int, Menu> $menus */
    public function __construct(
        protected Collection $menus,
        protected string $subtitle = '',
        ?bool $isSingleDay = null
    ) {
        if ($isSingleDay !== null) {
            $this->isSingleDay = $isSingleDay;
        } else {
            $uniqueDates = $this->menus->pluck('date')
                ->filter()
                ->map(fn ($d) => $d instanceof Carbon ? $d->toDateString() : Carbon::parse($d)->toDateString())
                ->unique();
            $this->isSingleDay = $uniqueDates->count() <= 1;
        }

        if ($this->isSingleDay) {
            $firstDate = $this->menus->pluck('date')->filter()->first();
            if ($firstDate) {
                $cDate = $firstDate instanceof Carbon ? $firstDate : Carbon::parse($firstDate);
                $this->singleDayDateStr = $cDate->format('d/m/Y');
            }
        }
    }

    public function title(): string
    {
        $isEn = app()->getLocale() === 'en';
        if ($this->isSingleDay) {
            return $isEn ? 'Day Menu' : 'Thực đơn ngày';
        }

        return $isEn ? 'Week Menu' : 'Thực đơn tuần';
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        if ($this->isSingleDay) {
            return $this->singleDayArray();
        }

        return $this->multiDayArray();
    }

    protected function singleDayArray(): array
    {
        $isEn = app()->getLocale() === 'en';
        $rows = [];

        // Row 1: Header logo cell (left) & Company Name (right)
        $companyName = $isEn
            ? 'CJ CATERING VIETNAM SERVICE CO., LTD'
            : 'CÔNG TY TNHH DỊCH VỤ CJ CATERING VIỆT NAM';
        $rows[] = ['', $companyName];
        // Row 2 & 3: Empty rows merged with Row 1
        $rows[] = ['', ''];
        $rows[] = ['', ''];

        // Row 4: Title Banner
        $titleText = $isEn ? 'SAVORY MENU 46' : 'MENU MẶN 46';
        $rows[] = [$titleText, ''];

        // Row 5: Table Header
        $colAHeader = $isEn ? 'STRUCTURE' : 'CƠ CẤU';
        $colBHeader = $isEn ? 'DISH NAME' : 'TÊN MÓN ĂN';
        $rows[] = [$colAHeader, $colBHeader];

        // Row 6+: Dishes (k phân biệt ca — gom toàn bộ món trong ngày. Xuất ĐÚNG số món thực tế)
        $dishNames = $this->menus->pluck('recipe.name')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($dishNames === []) {
            $dishNames = [''];
        }

        $dishPrefix = $isEn ? 'DISH ' : 'MÓN ';

        foreach ($dishNames as $i => $dishName) {
            $label = $dishPrefix.($i + 1);
            $rows[] = [$label, $dishName];
        }

        return $rows;
    }

    protected function multiDayArray(): array
    {
        $isEn = app()->getLocale() === 'en';
        $rows = [];

        $bannerTitle = $isEn ? 'WEEKLY MENU' : 'THỰC ĐƠN TUẦN';
        $rows[] = array_pad([$bannerTitle], 8, '');
        $rows[] = array_pad([$this->subtitle], 8, '');

        if ($isEn) {
            $rows[] = ['#', 'Kitchen', 'Date', 'Day', 'Shift', 'Dish Name', 'Portions', 'Status'];
            $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        } else {
            $rows[] = ['STT', 'Bếp ăn', 'Ngày', 'Thứ', 'Ca', 'Món ăn', 'Số suất', 'Trạng thái'];
            $dayNames = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
        }

        foreach ($this->menus as $i => $menu) {
            $cDate = $menu->date instanceof Carbon ? $menu->date : Carbon::parse($menu->date);
            $statusLabel = __('menu.status.'.$menu->status);
            if ($statusLabel === 'menu.status.'.$menu->status) {
                $statusLabel = Menu::STATUS_LABELS[$menu->status] ?? $menu->status;
            }

            $rows[] = [
                $i + 1,
                $menu->kitchen?->name ?? '',
                $cDate->format('d/m/Y'),
                $dayNames[$cDate->dayOfWeek],
                $menu->shift?->name ?? '',
                $menu->recipe?->name ?? '',
                (int) $menu->estimated_portions,
                $statusLabel,
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                if ($this->isSingleDay) {
                    $this->formatSingleDaySheet($sheet);
                } else {
                    $this->formatMultiDaySheet($sheet);
                }
            },
        ];
    }

    protected function formatSingleDaySheet($sheet): void
    {
        $lastRow = $sheet->getHighestRow();

        // 1. Set column widths (Column A widened to 30 for spacious logo presentation)
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(50);

        // 2. Merge cells for Header (A1:A3 & B1:B3)
        $sheet->mergeCells('A1:A3');
        $sheet->mergeCells('B1:B3');

        // Resolve System Settings Logo from http://127.0.0.1:8001/admin/system-settings?tab=-thuong-hieu-tab
        $systemLogoRel = Setting::get('site_logo');
        $resolvedLogoPath = null;

        if ($systemLogoRel && Storage::disk('public')->exists($systemLogoRel)) {
            $resolvedLogoPath = Storage::disk('public')->path($systemLogoRel);
        } elseif (file_exists(public_path('images/bluefire-logo.png'))) {
            $resolvedLogoPath = public_path('images/bluefire-logo.png');
        }

        if ($resolvedLogoPath && file_exists($resolvedLogoPath)) {
            $drawing = new Drawing;
            $drawing->setName('System Brand Logo');
            $drawing->setDescription('System Brand Logo');
            $drawing->setPath($resolvedLogoPath);
            $drawing->setCoordinates('A1');
            $drawing->setHeight(54);
            $drawing->setOffsetX(15);
            $drawing->setOffsetY(6);
            $drawing->setWorksheet($sheet);
        } else {
            $sheet->setCellValue('A1', "BlueFire\nTASTE & BEAUTY");
            $sheet->getStyle('A1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 13,
                    'color' => ['rgb' => '002060'],
                    'name' => 'Calibri',
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);
        }

        // Style B1:B3 (CJ Catering Company Name)
        $sheet->getStyle('B1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'color' => ['rgb' => 'FF0000'],
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // 3. Row 4: Merge A4:B4 (Banner title "MENU MẶN 46")
        $sheet->mergeCells('A4:B4');
        $sheet->getRowDimension(4)->setRowHeight(30);
        $sheet->getStyle('A4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'FF0000'],
                'name' => 'Calibri',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFC000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 4. Row 5: Table Header (CƠ CẤU | TÊN MÓN ĂN)
        $sheet->getRowDimension(5)->setRowHeight(28);
        $sheet->getStyle('A5:B5')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '002060'],
                'name' => 'Calibri',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 5. Data Rows (A6:B{lastRow})
        for ($r = 6; $r <= $lastRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(26);
        }
        $sheet->getStyle("A6:B{$lastRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '0070C0'],
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 6. Cyan Borders (#00B0F0) on the entire table (A1:B{lastRow})
        $cyanBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '00B0F0'],
                ],
            ],
        ];
        $sheet->getStyle("A1:B{$lastRow}")->applyFromArray($cyanBorder);
    }

    protected function formatMultiDaySheet($sheet): void
    {
        $lastCol = Coordinate::stringFromColumnIndex(8);
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
    }
}
