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
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

/**
 * Xuất thực đơn ra .xlsx:
 * - Thực đơn ngày: theo mẫu CJ Catering / BlueFire 100% (Ảnh 2)
 * - Thực đơn tuần: theo mẫu ma trận tuần chuẩn 100% (Ảnh 2), tự động lọc bỏ ca không có món,
 *   tất cả ô tiêu đề & phân loại món (B2=SHIFT/CA, C2=DISH/MÓN, C3=DISH 1/MÓN 1...) đều tự động đa ngôn ngữ VI / EN.
 *   màu sắc RGB y chang file gốc (B2/A2/B3 fill #BFBFBF, C2:J2 fill #D8D8D8, C3 fill #FBE4D5, font đỏ Times New Roman),
 *   viền xanh cyan #00B0F0 và tự động nhúng logo dự án từ System Settings.
 */
class MenuExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected bool $isSingleDay = false;

    protected string $singleDayDateStr = '';

    /** @param Collection<int, Menu> $menus */
    public function __construct(
        protected Collection $menus,
        protected string $subtitle = '',
        ?bool $isSingleDay = null,
        protected ?string $dateFrom = null,
        protected ?string $dateTo = null,
        protected array $customDishCategories = []
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
        $rows = [];
        for ($r = 1; $r <= 35; $r++) {
            $rows[] = array_pad([], 10, '');
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
        $isEn = app()->getLocale() === 'en';

        if ($this->dateFrom && $this->dateTo) {
            $start = Carbon::parse($this->dateFrom);
            $end = Carbon::parse($this->dateTo);
        } else {
            $dates = $this->menus->pluck('date')
                ->filter()
                ->map(fn ($d) => $d instanceof Carbon ? $d : Carbon::parse($d));

            if ($dates->isEmpty() && preg_match('/(\d{2}\/\d{2}\/\d{4})\s+đến\s+(\d{2}\/\d{2}\/\d{4})/', $this->subtitle, $m)) {
                $start = Carbon::createFromFormat('d/m/Y', $m[1]);
                $end = Carbon::createFromFormat('d/m/Y', $m[2]);
            } else {
                $start = $dates->min() ? $dates->min()->copy() : now()->startOfWeek();
                $end = $dates->max() ? $dates->max()->copy() : $start->copy()->addDays(6);
            }
        }

        $daysCount = max(1, (int) $start->diffInDays($end) + 1);
        $colLetters = ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'];
        $lastHeaderCol = $colLetters[min($daysCount - 1, count($colLetters) - 1)] ?? 'J';

        // 1. Header Banner Row 1 (Merged A1:{$lastHeaderCol}1)
        $sheet->mergeCells("A1:{$lastHeaderCol}1");
        $sheet->getRowDimension(1)->setRowHeight(55);

        $titleLine1 = $isEn
            ? 'SUMMIT CANTEEN WEEKLY MENU '.$start->format('d.m.Y')."\n"
            : 'THỰC ĐƠN CANTEEN SUMMIT TUẦN '.$start->format('d.m.Y')."\n";
        $titleLine2 = $isEn
            ? '(From '.$start->format('d/m/Y').' to '.$end->format('d/m/Y').')'
            : '(Từ ngày '.$start->format('d/m/Y').' đến ngày '.$end->format('d/m/Y').')';

        $richText = new RichText;
        $run1 = $richText->createTextRun($titleLine1);
        $run1->getFont()->setName('Times New Roman')->setSize(15)->setBold(true)->setColor(new Color('FFFF0000'));

        $run2 = $richText->createTextRun($titleLine2);
        $run2->getFont()->setName('Times New Roman')->setSize(12)->setBold(true)->setItalic(true)->setColor(new Color('FFFF0000'));

        $sheet->setCellValue('A1', $richText);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

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
            $drawing->setHeight(50);
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(4);
            $drawing->setWorksheet($sheet);
        }

        // 2. Row 2 Header: B2=SHIFT/CA (BFBFBF fill, Red font), C2=DISH/MÓN (D8D8D8 fill, Red font), D2... (D8D8D8 fill, Red font)
        $sheet->getRowDimension(2)->setRowHeight(32);

        $dayNamesVi = [
            0 => 'Chủ nhật',
            1 => 'THỨ 2',
            2 => 'THỨ 3',
            3 => 'THỨ 4',
            4 => 'THỨ 5',
            5 => 'THỨ 6',
            6 => 'Thứ 7',
        ];

        $dayNamesEn = [
            0 => 'SUN',
            1 => 'MON',
            2 => 'TUE',
            3 => 'WED',
            4 => 'THU',
            5 => 'FRI',
            6 => 'SAT',
        ];

        $headers = [
            'A' => '',
            'B' => $isEn ? 'SHIFT' : 'CA',
            'C' => $isEn ? 'DISH' : 'MÓN',
        ];

        for ($d = 0; $d < $daysCount; $d++) {
            $cDate = $start->copy()->addDays($d);
            $colLetter = $colLetters[$d] ?? null;
            if (! $colLetter) {
                break;
            }
            $wDay = $cDate->dayOfWeek;
            $headers[$colLetter] = $isEn ? ($dayNamesEn[$wDay] ?? '') : ($dayNamesVi[$wDay] ?? '');
        }

        foreach ($headers as $col => $hText) {
            if ($col !== 'A') {
                $sheet->setCellValue($col.'2', $hText);
            }
        }

        // Style B2
        $sheet->getStyle('B2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FF0000'], 'name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BFBFBF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Style C2:{$lastHeaderCol}2
        $sheet->getStyle("C2:{$lastHeaderCol}2")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FF0000'], 'name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D8D8D8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // 3. Dynamic Shift filtering & Category localization
        $allShifts = Shift::orderBy('sort_order')->orderBy('id')->get()->values();
        $activeShiftIds = $this->menus->pluck('shift_id')->unique()->filter()->toArray();

        $defaultCategoriesVi = [
            0 => ['MÓN 1', 'MÓN 2', 'RAU XÀO/LUỘC', 'CANH', 'CƠM', 'MÓN CHAY 1', 'MÓN CHAY 2', 'CANH CHAY', 'MÓN CHAY 3', 'CƠM CHAY', 'COMBO', 'TRÁNG MIỆNG'],
            1 => ['MÓN MẶN 1', 'MÓN MẶN 2', 'MÓN RAU XÀO/LUỘC', 'MÓN CANH', 'MÓN CHAY 1', 'MÓN CHAY 2', 'RAU XÀO CHAY', 'TRÁNG MIỆNG'],
            2 => ['MÓN MẶN 1', 'MÓN MẶN 2', 'MÓN RAU XÀO/LUỘC', 'MÓN CANH', 'CƠM', 'MÓN CHAY 1', 'MÓN CHAY 2', 'MÓN RAU XÀO/LUỘC', 'MÓN CHAY 3', 'CƠM', 'COMBO', 'TRÁNG MIỆNG'],
        ];

        $defaultCategoriesEn = [
            0 => ['DISH 1', 'DISH 2', 'STIR-FRIED / BOILED VEG', 'SOUP', 'RICE', 'VEGETARIAN 1', 'VEGETARIAN 2', 'VEGETARIAN SOUP', 'VEGETARIAN 3', 'VEGETARIAN RICE', 'COMBO', 'DESSERT'],
            1 => ['MAIN DISH 1', 'MAIN DISH 2', 'STIR-FRIED / BOILED VEG', 'SOUP', 'VEGETARIAN 1', 'VEGETARIAN 2', 'VEGETARIAN STIR-FRY', 'DESSERT'],
            2 => ['MAIN DISH 1', 'MAIN DISH 2', 'STIR-FRIED / BOILED VEG', 'SOUP', 'RICE', 'VEGETARIAN 1', 'VEGETARIAN 2', 'STIR-FRIED VEG', 'VEGETARIAN 3', 'RICE', 'COMBO', 'DESSERT'],
        ];

        $currentRow = 3;
        $shiftRowMap = [];

        foreach ($allShifts as $sIdx => $sObj) {
            $hasItems = in_array($sObj->id, $activeShiftIds, true);

            // Hide shifts that have NO menu items (unless activeShiftIds is empty, then render first shift)
            if (! $hasItems && $activeShiftIds !== []) {
                continue;
            }

            $shiftMenus = $this->menus->where('shift_id', $sObj->id);
            $maxDishesCount = $shiftMenus
                ->groupBy(fn ($m) => $m->date instanceof Carbon ? $m->date->toDateString() : Carbon::parse($m->date)->toDateString())
                ->map(fn ($g) => $g->count())
                ->max() ?: 1;

            $shiftCustomCats = $this->customDishCategories[$sObj->id] ?? null;
            if (! is_array($shiftCustomCats) && is_array($this->customDishCategories)) {
                $firstVal = reset($this->customDishCategories);
                if (is_string($firstVal)) {
                    $shiftCustomCats = $this->customDishCategories;
                }
            }

            $defaults = $defaultCategoriesVi[$sIdx] ?? [];
            $categories = [];
            for ($i = 0; $i < $maxDishesCount; $i++) {
                $categories[] = $shiftCustomCats[$i] ?? ($defaults[$i] ?? ('MÓN '.($i + 1)));
            }

            $catCount = count($categories);
            $startRow = $currentRow;
            $endRow = $startRow + $catCount - 1;

            $sName = mb_strtoupper($sObj->name);
            if (! str_starts_with($sName, 'THỰC ĐƠN') && ! str_starts_with($sName, 'MENU')) {
                $sName = ($isEn ? 'MENU ' : 'THỰC ĐƠN ').$sName;
            }

            // Merge B{startRow}:B{endRow}
            $sheet->mergeCells("B{$startRow}:B{$endRow}");
            $sheet->setCellValue("B{$startRow}", $sName);

            // Style Shift B (BFBFBF fill, Red font)
            $sheet->getStyle("B{$startRow}:B{$endRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FF0000'], 'name' => 'Times New Roman'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BFBFBF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);

            // Populate categories in Column C (FBE4D5 fill, 0070C0 font)
            foreach ($categories as $catIdx => $catName) {
                $r = $startRow + $catIdx;
                $sheet->getRowDimension($r)->setRowHeight(30);
                $sheet->setCellValue("C{$r}", $catName);
            }

            // Style Category C
            $sheet->getStyle("C{$startRow}:C{$endRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '0070C0'], 'name' => 'Times New Roman'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FBE4D5']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            $shiftRowMap[$sObj->id] = ['startRow' => $startRow, 'endRow' => $endRow];
            $currentRow = $endRow + 1;
        }

        $lastRow = $currentRow - 1;

        // 4. Merge Side Header Column A (A2:A{lastRow}) (BFBFBF fill, Red font)
        $sheet->mergeCells("A2:A{$lastRow}");
        $sheet->setCellValue('A2', $isEn ? "W\nE\nE\nK\nL\nY\n\nM\nE\nN\nU" : "T\nH\nỰ\nC\n\nĐ\nƠ\nN\n\nT\nU\nẦ\nN");
        $sheet->getStyle("A2:A{$lastRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FF0000'], 'name' => 'Times New Roman'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BFBFBF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        // 5. Populate dish names into D..{$lastHeaderCol}
        $byDayShift = $this->menus->groupBy(fn ($m) => ($m->date instanceof Carbon ? $m->date->toDateString() : Carbon::parse($m->date)->toDateString()).'|'.$m->shift_id);

        foreach ($byDayShift as $key => $items) {
            [$dateStr, $shiftId] = explode('|', $key);
            $cDate = Carbon::parse($dateStr);
            if ($cDate->isBefore($start) || $cDate->isAfter($end)) {
                continue;
            }

            $dayIdx = (int) $start->diffInDays($cDate);
            $colStr = $colLetters[$dayIdx] ?? null;
            if (! $colStr) {
                continue;
            }

            $baseRow = $shiftRowMap[$shiftId]['startRow'] ?? 3;

            foreach ($items as $itemIdx => $m) {
                $targetRow = $baseRow + $itemIdx;
                if ($targetRow <= $lastRow && $m->recipe?->name) {
                    $sourceCell = $sheet->getCell($colStr.$targetRow);
                    // Append if dish already present on slot, or set
                    $existing = $sourceCell->getValue();
                    if ($existing) {
                        $sourceCell->setValue($existing."\n".$m->recipe->name);
                    } else {
                        $sourceCell->setValue($m->recipe->name);
                    }
                }
            }
        }

        // 6. Style Dish Data Cells D3:{$lastHeaderCol}{lastRow}
        $sheet->getStyle("D3:{$lastHeaderCol}{$lastRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '0070C0'], 'name' => 'Times New Roman'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        // 7. Column Widths
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(18);
        for ($d = 0; $d < $daysCount; $d++) {
            if (isset($colLetters[$d])) {
                $sheet->getColumnDimension($colLetters[$d])->setWidth(24);
            }
        }

        // 8. Cyan Borders (#00B0F0) on the entire matrix (A1:{$lastHeaderCol}{lastRow})
        $sheet->getStyle("A1:{$lastHeaderCol}{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '00B0F0']]],
        ]);
    }
}
