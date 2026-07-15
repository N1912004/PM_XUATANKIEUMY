<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use Throwable;

class FoodSafetyExcelTemplateRenderer
{
    private const TEMPLATE = 'MẪU KIỂM THỰC 3 BƯỚC_SHOW.xlsx';

    private const RANGES = [
        'Bước 1' => ['sheet' => 'B1', 'lastColumn' => 'M', 'lastRow' => 64],
        'Bước 2' => ['sheet' => 'B2', 'lastColumn' => 'L', 'lastRow' => 31],
        'Bước 3' => ['sheet' => 'B3', 'lastColumn' => 'I', 'lastRow' => 31],
        'Lưu mẫu' => ['sheet' => 'B4', 'lastColumn' => 'L', 'lastRow' => 19],
        'Hủy mẫu' => ['sheet' => 'B5', 'lastColumn' => 'L', 'lastRow' => 17],
    ];

    /**
     * @param  array{dateText: string, canteen: string, inspector: string, companyName: string, companyAddress: string, items?: array<int, array<string, mixed>>}  $context
     */
    public function render(string $step, array $context): string
    {
        $config = self::RANGES[$step] ?? self::RANGES['Bước 1'];
        $template = base_path(self::TEMPLATE);

        if (! is_file($template)) {
            return '';
        }

        try {
            $cacheKey = 'food-safety-template-html:v6:'.md5($step.'|'.filemtime($template).'|'.json_encode($context));

            return Cache::remember($cacheKey, now()->addMinutes(10), fn (): string => $this->renderTemplate($template, $config, $context));
        } catch (Throwable) {
            return '';
        }
    }

    /**
     * @param  array{sheet: string, lastColumn: string, lastRow: int}  $config
     * @param  array{dateText: string, canteen: string, inspector: string, companyName: string, companyAddress: string, items?: array<int, array<string, mixed>>}  $context
     */
    private function renderTemplate(string $template, array $config, array $context): string
    {
        $previousReporting = error_reporting();
        error_reporting($previousReporting & ~E_DEPRECATED);

        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setLoadSheetsOnly([$config['sheet']]);
            $spreadsheet = $reader->load($template);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setComments([]);
            if ($config['sheet'] === 'B1') {
                $this->normalizeB1Header($sheet);
            }
            $this->applyHeader($sheet, $config['sheet'], $context);
            $this->applyBusinessData($sheet, $config['sheet'], $context['items'] ?? []);
            $this->trimSheet($sheet, $config['lastColumn'], $config['lastRow']);

            $writer = new Html($spreadsheet);
            $writer->setSheetIndex(0);
            $writer->setPreCalculateFormulas(false);

            ob_start();
            $writer->save('php://output');
            $html = (string) ob_get_clean();
        } finally {
            error_reporting($previousReporting);
        }

        $spreadsheet->disconnectWorksheets();

        return $this->extractUsableHtml($html);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function applyBusinessData($sheet, string $sheetName, array $items): void
    {
        match ($sheetName) {
            'B1' => $this->fillStepOne($sheet, $items),
            'B2' => $this->fillStepTwo($sheet, $items),
            'B3' => $this->fillStepThree($sheet, $items),
            'B4', 'B5' => $this->fillSampleSheet($sheet, $items, $sheetName),
            default => null,
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function fillStepOne($sheet, array $items): void
    {
        $groups = [
            'I. Thực phẩm tươi sống, đông lạnh: thịt, cá, gà,...' => ['header' => 7, 'rows' => range(8, 13)],
            'II. Rau củ, quả, trái cây, các loại,...' => ['header' => 14, 'rows' => range(15, 54)],
            'III. Bún, đậu hủ,...' => ['header' => 55, 'rows' => range(56, 58)],
            'VI. Trứng các loại,...' => ['header' => 59, 'rows' => range(60, 60)],
        ];

        $byGroup = collect($items)->groupBy(fn (array $item): string => $this->stepOneGroupTitle($item));
        $orderedGroups = array_keys($groups);

        foreach (array_reverse($groups, true) as $group => $definition) {
            $rows = $definition['rows'];
            $groupItems = $byGroup->get($group, collect())->values();
            $usedRows = $groupItems->count();
            $serialStart = 1;
            foreach ($orderedGroups as $orderedGroup) {
                if ($orderedGroup === $group) {
                    break;
                }
                $serialStart += $byGroup->get($orderedGroup, collect())->count();
            }

            if ($usedRows === 0) {
                $sheet->removeRow($definition['header'], count($rows) + 1);

                continue;
            }

            if ($usedRows > count($rows)) {
                $extraRows = $usedRows - count($rows);
                $insertAt = max($rows) + 1;
                $sheet->insertNewRowBefore($insertAt, $extraRows);
                for ($offset = 0; $offset < $extraRows; $offset++) {
                    $this->copyRowStyle($sheet, max($rows), $insertAt + $offset, 'A', 'M');
                }
                $rows = range(min($rows), max($rows) + $extraRows);
            }

            foreach ($rows as $row) {
                $this->clearRow($sheet, $row, 'A', 'M');
            }

            foreach ($groupItems as $index => $item) {
                $row = $rows[$index];
                $sheet->fromArray([
                    $serialStart + $index,
                    $item['name'] ?? '',
                    $item['time'] ?? '',
                    $item['quantity_display'] ?? '',
                    $item['supplier'] ?? '',
                    $item['supplier_contact'] ?? '',
                    $item['deliverer'] ?? '',
                    $item['invoice'] ?? '',
                    $item['vet_check'] ?? '',
                    $item['quarantine'] ?? '',
                    $item['sensory'] ?? '',
                    $item['quick_test'] ?? '',
                    $item['action'] ?? ($item['notes'] ?? ''),
                ], null, "A{$row}");
            }

            if ($usedRows < count($rows)) {
                $sheet->removeRow($rows[$usedRows], count($rows) - $usedRows);
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function fillStepTwo($sheet, array $items): void
    {
        $rows = array_merge(range(9, 18), range(20, 27));
        $this->resizeDataRows($sheet, $rows, count($items), 'A', 'L');
        $rows = range(9, 9 + min(count($items), max(1, count($items))) - 1);
        foreach ($rows as $row) {
            $this->clearRow($sheet, $row, 'A', 'L');
        }

        foreach (array_slice($items, 0, count($rows)) as $index => $item) {
            $sheet->fromArray([
                $index + 1,
                $item['shift'] ?? '',
                $item['name'] ?? '',
                $item['main_ingredients'] ?? '',
                $item['portions'] ?? '',
                $item['prep_time'] ?? '',
                $item['finish_time'] ?? '',
                $item['staff_check'] ?? '',
                $item['equipment_check'] ?? '',
                $item['area_check'] ?? '',
                $item['sensory'] ?? '',
                $item['action'] ?? ($item['notes'] ?? ''),
            ], null, 'A'.$rows[$index]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function fillStepThree($sheet, array $items): void
    {
        $rows = array_merge(range(9, 18), range(20, 27));
        $this->resizeDataRows($sheet, $rows, count($items), 'A', 'I');
        $rows = range(9, 9 + min(count($items), max(1, count($items))) - 1);
        foreach ($rows as $row) {
            $this->clearRow($sheet, $row, 'A', 'I');
        }

        foreach (array_slice($items, 0, count($rows)) as $index => $item) {
            $sheet->fromArray([
                $index + 1,
                $item['shift'] ?? '',
                $item['name'] ?? '',
                $item['portions'] ?? '',
                $item['time'] ?? '',
                $item['eat_time'] ?? '',
                $item['utensil'] ?? '',
                $item['sensory'] ?? '',
                $item['action'] ?? ($item['notes'] ?? ''),
            ], null, 'A'.$rows[$index]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function fillSampleSheet($sheet, array $items, string $sheetName): void
    {
        $rows = $sheetName === 'B4' ? range(7, 16) : range(7, 14);
        $this->resizeDataRows($sheet, $rows, count($items), 'A', 'L');
        $rows = range(7, 7 + min(count($items), max(1, count($items))) - 1);
        foreach ($rows as $row) {
            $this->clearRow($sheet, $row, 'A', 'L');
        }

        foreach (array_slice($items, 0, count($rows)) as $index => $item) {
            $sheet->fromArray([
                $index + 1,
                $item['shift'] ?? '',
                $item['name'] ?? '',
                $item['portions'] ?? '',
                $item['sample_amount'] ?? '',
                $item['container'] ?? '',
                $item['temp'] ?? '',
                $sheetName === 'B4' ? ($item['time'] ?? '') : ($item['kept_at'] ?? ''),
                $sheetName === 'B4' ? ($item['destroy_at'] ?? '') : ($item['time'] ?? ''),
                $item['notes'] ?? '',
                $sheetName === 'B4' ? ($item['staff'] ?? '') : ($item['keeper'] ?? ''),
                $sheetName === 'B4' ? ($item['destroyer'] ?? '') : ($item['staff'] ?? ''),
            ], null, 'A'.$rows[$index]);
        }
    }

    private function clearRow($sheet, int $row, string $firstColumn, string $lastColumn): void
    {
        $start = Coordinate::columnIndexFromString($firstColumn);
        $end = Coordinate::columnIndexFromString($lastColumn);

        for ($column = $start; $column <= $end; $column++) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($column).$row, null);
        }
    }

    /**
     * @param  array<int, int>  $rows
     */
    private function resizeDataRows($sheet, array $rows, int $itemCount, string $firstColumn, string $lastColumn): void
    {
        $capacity = count($rows);
        $itemCount = max(1, $itemCount);

        if ($itemCount > $capacity) {
            $extraRows = $itemCount - $capacity;
            $insertAt = max($rows) + 1;
            $sheet->insertNewRowBefore($insertAt, $extraRows);
            for ($offset = 0; $offset < $extraRows; $offset++) {
                $this->copyRowStyle($sheet, max($rows), $insertAt + $offset, $firstColumn, $lastColumn);
            }

            return;
        }

        if ($itemCount < $capacity) {
            $sheet->removeRow($rows[$itemCount], $capacity - $itemCount);
        }
    }

    private function copyRowStyle($sheet, int $sourceRow, int $targetRow, string $firstColumn, string $lastColumn): void
    {
        $sheet->duplicateStyle(
            $sheet->getStyle("{$firstColumn}{$sourceRow}:{$lastColumn}{$sourceRow}"),
            "{$firstColumn}{$targetRow}:{$lastColumn}{$targetRow}",
        );
        $sheet->getRowDimension($targetRow)->setRowHeight($sheet->getRowDimension($sourceRow)->getRowHeight());
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function stepOneGroupTitle(array $item): string
    {
        $type = (string) ($item['type'] ?? '');
        $name = (string) ($item['name'] ?? '');
        $normalized = mb_strtolower($type.' '.$name);

        return match (true) {
            str_contains($normalized, 'động vật'), str_contains($normalized, 'thịt'), str_contains($normalized, 'cá'), str_contains($normalized, 'bò'), str_contains($normalized, 'gà') => 'I. Thực phẩm tươi sống, đông lạnh: thịt, cá, gà,...',
            str_contains($normalized, 'thực vật'), str_contains($normalized, 'rau'), str_contains($normalized, 'củ'), str_contains($normalized, 'quả'), str_contains($normalized, 'trái cây'), str_contains($normalized, 'gia vị'), str_contains($normalized, 'sả'), str_contains($normalized, 'hành') => 'II. Rau củ, quả, trái cây, các loại,...',
            str_contains($normalized, 'thực phẩm khô'), str_contains($normalized, 'thực phẩm chế biến'), str_contains($normalized, 'lương thực'), str_contains($normalized, 'bún'), str_contains($normalized, 'đậu'), str_contains($normalized, 'gạo'), str_contains($normalized, 'mỳ') => 'III. Bún, đậu hủ,...',
            str_contains($normalized, 'trứng') => 'VI. Trứng các loại,...',
            default => $type,
        };
    }

    /**
     * @param  array{dateText: string, canteen: string, inspector: string, companyName: string, companyAddress: string}  $context
     */
    private function applyHeader($sheet, string $sheetName, array $context): void
    {
        $company = mb_strtoupper($context['canteen'].'-'.$context['companyName']);
        $address = 'Địa chỉ: '.$context['companyAddress'];

        if ($sheetName === 'B1') {
            $sheet->setCellValue('A1', 'Thời gian kiểm tra: '.$context['dateText']);
            $sheet->setCellValue('A2', 'Địa điểm kiểm tra: '.$context['canteen']);
            $sheet->setCellValue('A3', 'Người kiểm tra: '.$context['inspector']);
            $sheet->setCellValue('E2', $company."\n".$address);

            return;
        }

        if (in_array($sheetName, ['B2', 'B3'], true)) {
            $leftCol = $sheetName === 'B2' ? 'D' : 'C';
            $sheet->setCellValue($leftCol.'1', $company."\n".$address);
            $sheet->setCellValue($sheetName === 'B2' ? 'B3' : 'A3', 'Tên cơ sở:  '.$context['canteen']);
            $sheet->setCellValue($sheetName === 'B2' ? 'B4' : 'A4', 'Thời gian kiểm tra: Ngày '.$context['dateText']);
            $sheet->setCellValue($sheetName === 'B2' ? 'B5' : 'A5', 'Địa điểm kiểm tra:     '.$context['canteen']);
            $sheet->setCellValue($sheetName === 'B2' ? 'B6' : 'A6', 'Người kiểm tra: '.$context['inspector']);

            return;
        }

        $sheet->setCellValue('A1', 'Tên cơ sở:  '.$context['canteen']);
        $sheet->setCellValue('A2', 'Thời gian kiểm tra: Ngày '.$context['dateText']);
        $sheet->setCellValue('A3', 'Địa điểm kiểm tra:     '.$context['canteen']);
        $sheet->setCellValue('A4', 'Người kiểm tra: '.$context['inspector']);
        $sheet->setCellValue('E2', $company."\n".$address);
    }

    private function normalizeB1Header($sheet): void
    {
        $sheet->removeRow(1, 1);

        foreach (['A1:D2', 'A3:D3', 'A4:D4'] as $range) {
            if ($sheet->getMergeCells()[$range] ?? false) {
                $sheet->unmergeCells($range);
            }
        }

        foreach (['A1:D1', 'A2:D2', 'A3:D3', 'A4:D4'] as $range) {
            $sheet->mergeCells($range);
        }

        $sheet->setCellValue('A4', '');

        $sheet->getRowDimension(1)->setRowHeight(38);
        $sheet->getRowDimension(2)->setRowHeight(38);
        $sheet->getRowDimension(3)->setRowHeight(38);
        $sheet->getRowDimension(4)->setRowHeight(82.5);

        $sheet->getStyle('A1:D3')->applyFromArray([
            'font' => [
                'name' => 'Times New Roman',
                'size' => 36,
                'bold' => false,
                'italic' => false,
            ],
            'alignment' => [
                'horizontal' => 'left',
                'vertical' => 'center',
                'wrapText' => false,
            ],
        ]);

        $sheet->getStyle('A5:M6')->applyFromArray([
            'font' => [
                'name' => 'Times New Roman',
                'size' => 36,
                'bold' => true,
                'italic' => false,
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
                'wrapText' => true,
            ],
        ]);

        $this->normalizeB1GroupRows($sheet);
    }

    private function normalizeB1GroupRows($sheet): void
    {
        $lastRow = $sheet->getHighestRow();
        for ($row = 1; $row <= $lastRow; $row++) {
            $text = trim((string) $sheet->getCell("A{$row}")->getValue());
            if (! preg_match('/^(I|II|III|VI)\./u', $text)) {
                continue;
            }

            foreach ($sheet->getMergeCells() as $range) {
                if (preg_match('/^[A-M]'.$row.':/u', $range)) {
                    $sheet->unmergeCells($range);
                }
            }

            $sheet->mergeCells("A{$row}:M{$row}");
            $sheet->getRowDimension($row)->setRowHeight(91.5);
            $sheet->getStyle("A{$row}:M{$row}")->applyFromArray([
                'font' => [
                    'name' => 'Times New Roman',
                    'size' => 38,
                    'bold' => true,
                    'italic' => false,
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'color' => ['rgb' => 'F1CEEE'],
                ],
                'alignment' => [
                    'horizontal' => 'left',
                    'vertical' => 'center',
                    'wrapText' => true,
                ],
            ]);
        }
    }

    private function trimSheet($sheet, string $lastColumn, int $lastRow): void
    {
        $maxRow = $sheet->getHighestRow();
        if ($maxRow > $lastRow) {
            $sheet->removeRow($lastRow + 1, $maxRow - $lastRow);
        }

        $startColumn = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($lastColumn) + 1);
        $sheet->removeColumn($startColumn, 64);
    }

    private function extractUsableHtml(string $html): string
    {
        preg_match_all('/<style\b[^>]*>(.*?)<\/style>/is', $html, $styles);
        preg_match('/<body\b[^>]*>(.*?)<\/body>/is', $html, $body);

        $styleHtml = collect($styles[0] ?? [])->implode("\n");
        $bodyHtml = $body[1] ?? $html;

        return '<div class="fsa-template-html">'.$styleHtml.$bodyHtml.'</div>';
    }
}
