<?php

namespace App\Exports;

use App\Models\Menu;
use App\Models\Setting;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

/**
 * Xuất thực đơn ra .xlsx:
 * - Thực đơn ngày: theo mẫu CJ Catering / BlueFire 100% (Ảnh 2)
 * - Thực đơn tuần: theo mẫu ma trận tuần chuẩn 100% từ file LISTHANGMAU_THUCDON (1).xlsx (Sheet TD),
 *   ca làm việc động lấy theo Shift DB (CA 1, CA 2, CA 3...), tiêu đề A1 trình bày chuẩn đẹp bằng RichText.
 * Tự động đa ngôn ngữ VI / EN theo ngôn ngữ hệ thống đang chọn.
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

        return 'TD';
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
        if (file_exists(base_path('LISTHANGMAU_THUCDON (1).xlsx'))) {
            $rows = [];
            for ($r = 1; $r <= 35; $r++) {
                $rows[] = array_pad([], 10, '');
            }

            return $rows;
        }

        return $this->standardMultiDayArray();
    }

    protected function standardMultiDayArray(): array
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
        $templatePath = base_path('LISTHANGMAU_THUCDON (1).xlsx');
        if (! file_exists($templatePath)) {
            $this->formatStandardMultiDaySheet($sheet);

            return;
        }

        $tplSpreadsheet = IOFactory::load($templatePath);
        $source = $tplSpreadsheet->getSheetByName('TD') ?? $tplSpreadsheet->getSheet(0);

        // 1. Clear sample dishes in D3:J34
        for ($r = 3; $r <= 34; $r++) {
            for ($c = 4; $c <= 10; $c++) {
                $colStr = Coordinate::stringFromColumnIndex($c);
                $source->setCellValue($colStr.$r, '');
            }
        }

        // 2. Set A1 Title & Date Range via RichText (Merged A1:I1)
        $isEn = app()->getLocale() === 'en';
        $dates = $this->menus->pluck('date')
            ->filter()
            ->map(fn ($d) => $d instanceof Carbon ? $d : Carbon::parse($d));

        if ($dates->isEmpty() && preg_match('/(\d{2}\/\d{2}\/\d{4})/', $this->subtitle, $m)) {
            $start = Carbon::createFromFormat('d/m/Y', $m[1])->startOfWeek();
        } else {
            $start = $dates->min() ? $dates->min()->copy()->startOfWeek() : now()->startOfWeek();
        }
        $end = $start->copy()->addDays(6);

        $titleLine1 = $isEn
            ? 'SUMMIT CANTEEN WEEKLY MENU '.$start->format('d.m.Y')."\n"
            : 'THỰC ĐƠN CANTEEN SUMMIT TUẦN '.$start->format('d.m.Y')."\n";
        $titleLine2 = $isEn
            ? '(From '.$start->format('d/m/Y').' to '.$end->format('d/m/Y').')'
            : '(Từ ngày '.$start->format('d/m/Y').' đến ngày '.$end->format('d/m/Y').')';

        $richText = new RichText;
        $run1 = $richText->createTextRun($titleLine1);
        $run1->getFont()->setName('Times New Roman')->setSize(16)->setBold(true)->setColor(new Color('FFFF0000'));

        $run2 = $richText->createTextRun($titleLine2);
        $run2->getFont()->setName('Times New Roman')->setSize(13)->setItalic(true)->setColor(new Color('FFFF0000'));

        $source->setCellValue('A1', $richText);

        // 3. Set clean Column A side label (Merged A2:A34)
        $source->setCellValue('A2', $isEn ? 'WEEKLY MENU' : 'THỰC ĐƠN TUẦN');

        // 4. Dynamic Shifts in Column B from DB Shifts
        $shifts = Shift::orderBy('sort_order')->orderBy('id')->get()->values();
        $shiftCells = [0 => 'B3', 1 => 'B15', 2 => 'B23'];
        foreach ($shiftCells as $idx => $cell) {
            if (isset($shifts[$idx])) {
                $sName = mb_strtoupper($shifts[$idx]->name);
                if (! str_starts_with($sName, 'THỰC ĐƠN') && ! str_starts_with($sName, 'MENU')) {
                    $sName = ($isEn ? 'MENU ' : 'THỰC ĐƠN ').$sName;
                }
                $source->setCellValue($cell, $sName);
            }
        }

        // 5. Map menus to D3:J34 matrix
        $shiftMap = [];
        foreach ($shifts as $idx => $s) {
            $shiftMap[$s->id] = $idx;
        }
        $shiftRowStart = [0 => 3, 1 => 15, 2 => 23];

        $byDayShift = $this->menus->groupBy(fn ($m) => ($m->date instanceof Carbon ? $m->date->toDateString() : Carbon::parse($m->date)->toDateString()).'|'.$m->shift_id);

        foreach ($byDayShift as $key => $items) {
            [$dateStr, $shiftId] = explode('|', $key);
            $cDate = Carbon::parse($dateStr);
            $dayIdx = (int) $start->diffInDays($cDate);
            $colStr = ['D', 'E', 'F', 'G', 'H', 'I', 'J'][$dayIdx] ?? null;
            if (! $colStr) {
                continue;
            }

            $sIdx = $shiftMap[$shiftId] ?? 0;
            $baseRow = $shiftRowStart[$sIdx] ?? 3;

            foreach ($items as $itemIdx => $m) {
                $targetRow = $baseRow + $itemIdx;
                if ($targetRow <= 34 && $m->recipe?->name) {
                    $source->setCellValue($colStr.$targetRow, $m->recipe->name);
                }
            }
        }

        // 6. Clone merged cells, dimensions, styles, values, and drawings onto target $sheet
        foreach ($source->getMergeCells() as $mergeRange) {
            $sheet->mergeCells($mergeRange);
        }

        foreach ($source->getColumnDimensions() as $col => $dimension) {
            $sheet->getColumnDimension($col)->setWidth($dimension->getWidth());
        }

        foreach ($source->getRowDimensions() as $row => $dimension) {
            $sheet->getRowDimension($row)->setRowHeight($dimension->getRowHeight());
        }

        $maxRow = min(35, $source->getHighestRow());
        $maxColIndex = Coordinate::columnIndexFromString('J');

        for ($r = 1; $r <= $maxRow; $r++) {
            for ($c = 1; $c <= $maxColIndex; $c++) {
                $colStr = Coordinate::stringFromColumnIndex($c);
                $cellAddr = $colStr.$r;
                $sourceCell = $source->getCell($cellAddr);
                $targetCell = $sheet->getCell($cellAddr);

                $targetCell->setValue($sourceCell->getValue());
                $sheet->duplicateStyle($source->getStyle($cellAddr), $cellAddr);
            }
        }

        foreach ($source->getDrawingCollection() as $drawing) {
            $newDrawing = clone $drawing;
            $newDrawing->setWorksheet($sheet);
        }

        // 7. Apply wrapText and center vertical alignment for D3:J34
        $sheet->getStyle('D3:J34')->getAlignment()->setWrapText(true);
        $sheet->getStyle('D3:J34')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    protected function formatStandardMultiDaySheet($sheet): void
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
