<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Throwable;

class FoodSafetyExcelTemplateRenderer
{
    private const TEMPLATE = 'MẪU KIỂM THỰC 3 BƯỚC_SHOW.xlsx';

    private const RANGES = [
        'Bước 1' => ['sheet' => 'B1', 'lastColumn' => 'M'],
        'Bước 2' => ['sheet' => 'B2', 'lastColumn' => 'L'],
        'Bước 3' => ['sheet' => 'B3', 'lastColumn' => 'I'],
        'Lưu mẫu' => ['sheet' => 'B4', 'lastColumn' => 'L'],
        'Hủy mẫu' => ['sheet' => 'B5', 'lastColumn' => 'L'],
    ];

    /**
     * @param  array{dateText: string, canteen: string, inspector: string, companyName: string, companyAddress: string, items?: array<int, array<string, mixed>>}  $context
     */
    public function render(string $step, array $context): string
    {
        $config = self::RANGES[$step] ?? self::RANGES['Bước 1'];
        $template = base_path(self::TEMPLATE);

        if (!is_file($template)) {
            return '';
        }

        try {
            $cacheKey = 'food-safety-template-html:v26:' . md5($step . '|' . filemtime($template) . '|' . json_encode($context));

            return Cache::remember($cacheKey, now()->addMinutes(10), fn(): string => $this->renderTemplate($template, $config, $context));
        } catch (Throwable $e) {
            Log::warning('Food safety Excel template render failed', [
                'step' => $step,
                'message' => $e->getMessage(),
            ]);

            return '';
        }
    }

    /**
     * @param  array{sheet: string, lastColumn: string}  $config
     * @param  array{dateText: string, canteen: string, inspector: string, companyName: string, companyAddress: string, items?: array<int, array<string, mixed>>}  $context
     */
    private function renderTemplate(string $template, array $config, array $context): string
    {
        $previousReporting = error_reporting();
        error_reporting($previousReporting & ~E_DEPRECATED);

        try {
            $spreadsheet = $this->prepareSheet($template, $config, $context);

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
     * Dựng sheet hoàn chỉnh (đã đổ dữ liệu thật + đồng bộ format) cho 1 bước — dùng CHUNG cho
     * render HTML trên trang và xuất file Excel, đảm bảo 2 đầu ra khớp nhau tuyệt đối.
     *
     * @param  array{sheet: string, lastColumn: string}  $config
     * @param  array{dateText: string, canteen: string, inspector: string, companyName: string, companyAddress: string, items?: array<int, array<string, mixed>>}  $context
     */
    private function prepareSheet(string $template, array $config, array $context): Spreadsheet
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setLoadSheetsOnly([$config['sheet']]);
        $spreadsheet = $reader->load($this->trimmedTemplatePath($template, $config));
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setComments([]);
        if ($config['sheet'] === 'B1') {
            $this->normalizeB1Header($sheet);
        } else {
            $this->normalizeMetaBlockLikeB1($sheet, $config['sheet']);
        }
        $this->applyHeader($sheet, $config['sheet'], $context);
        // Thay chữ ký mẫu TRƯỚC khi đổ dữ liệu nghiệp vụ: chạy sau sẽ ghi đè cả ô
        // "Người lưu mẫu" chứa tên THẬT từ bản ghi kiểm thực (trùng tên người trong file mẫu).
        $this->replaceSampleSignatures($sheet, $config['lastColumn'], (string) ($context['inspector'] ?? ''));
        $this->applyBusinessData($sheet, $config['sheet'], $context['items'] ?? []);
        $this->applyUniformTableFont($sheet, $config['sheet'], count($context['items'] ?? []));
        $this->trimSheet($sheet, $config['lastColumn'], $this->lastUsedRow($sheet, $config['lastColumn']));

        return $spreadsheet;
    }

    /**

     * @param  array<string, array{dateText: string, canteen: string, inspector: string, companyName: string, companyAddress: string, items?: array<int, array<string, mixed>>}>  $contextsByStep  key = 'Bước 1'…'Hủy mẫu'
     */
    public function exportBytes(array $contextsByStep): ?string
    {
        $template = base_path(self::TEMPLATE);

        if (!is_file($template)) {
            return null;
        }

        $cacheKey = 'food-safety-export-xlsx:v1:' . md5(filemtime($template) . '|' . json_encode($contextsByStep));

        // Cache base64 thay vì bytes thô: driver cache mặc định là DATABASE (bảng `cache`,
        // cột text utf8) — chuỗi nhị phân xlsx làm INSERT nổ "Incorrect string value".
        $encoded = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($contextsByStep): string {
            $workbook = $this->exportWorkbook($contextsByStep);

            if ($workbook === null) {
                // Không cache kết quả rỗng — thiếu file mẫu là lỗi môi trường, xử lý ở caller.
                throw new \RuntimeException('Không tìm thấy file Excel mẫu kiểm thực');
            }

            $tmpPath = tempnam(sys_get_temp_dir(), 'fsa-export-');

            try {
                $writer = new XlsxWriter($workbook);
                // Không có công thức trong workbook — tắt precalc để ghi nhanh.
                $writer->setPreCalculateFormulas(false);
                $writer->save($tmpPath);
                $workbook->disconnectWorksheets();

                return base64_encode((string) file_get_contents($tmpPath));
            } finally {
                @unlink($tmpPath);
            }
        });

        return base64_decode($encoded, true) ?: null;
    }

    /**
     * Xuất workbook 5 sheet B1–B5 bằng CHÍNH pipeline render trên trang — file Excel tải về
     * khớp 100% biểu mẫu đang hiển thị (format lẫn số liệu). Trả về null nếu thiếu file mẫu.
     *
     * @param  array<string, array{dateText: string, canteen: string, inspector: string, companyName: string, companyAddress: string, items?: array<int, array<string, mixed>>}>  $contextsByStep  key = 'Bước 1'…'Hủy mẫu'
     */
    public function exportWorkbook(array $contextsByStep): ?Spreadsheet
    {
        $template = base_path(self::TEMPLATE);

        if (!is_file($template)) {
            return null;
        }

        $previousReporting = error_reporting();
        error_reporting($previousReporting & ~E_DEPRECATED);

        try {
            $workbook = null;
            foreach (self::RANGES as $step => $config) {
                if (!isset($contextsByStep[$step])) {
                    continue;
                }

                $prepared = $this->prepareSheet($template, $config, $contextsByStep[$step]);
                // Định dạng chuẩn Excel CHỈ cho file xuất (không đụng HTML trên trang):
                // font 10–11pt, viền, banded, freeze, numFmt, độ rộng cột… theo yêu cầu BA.
                $this->applyExportStyling($prepared->getActiveSheet(), $config['sheet'], $config['lastColumn']);

                if ($workbook === null) {
                    $workbook = $prepared;

                    continue;
                }

                // addExternalSheet copy kèm style vào workbook đích (tên sheet B1–B5 không trùng)
                $workbook->addExternalSheet($prepared->getSheet(0));
            }

            // Chốt con trỏ về A1 cho MỌI sheet SAU khi gộp (addExternalSheet có thể reset selection
            // về ô cuối đã ghi) + active sheet đầu tiên → file mở ra ở đầu bảng, không cuộn xuống cuối.
            if ($workbook !== null) {
                foreach ($workbook->getAllSheets() as $s) {
                    $s->setSelectedCells('A1');
                }
                $workbook->setActiveSheetIndex(0);
            }

            return $workbook;
        } finally {
            error_reporting($previousReporting);
        }
    }

    private const SECTION_FILL = 'F1CEEE';

    private const BAND_FILL = 'F2F2F2';

    /**
     * Căn ngang từng cột theo ý nghĩa (STT/trạng thái/giờ = center, tên/mô tả = left, số lượng = right).
     *
     * @var array<string, array<string, string>>
     */
    private const COL_ALIGN = [
        'B1' => ['A' => 'center', 'B' => 'left', 'C' => 'center', 'D' => 'right', 'E' => 'left', 'F' => 'left', 'G' => 'left', 'H' => 'center', 'I' => 'center', 'J' => 'center', 'K' => 'center', 'L' => 'center', 'M' => 'left'],
        'B2' => ['A' => 'center', 'B' => 'center', 'C' => 'left', 'D' => 'left', 'E' => 'right', 'F' => 'center', 'G' => 'center', 'H' => 'center', 'I' => 'center', 'J' => 'center', 'K' => 'center', 'L' => 'left'],
        'B3' => ['A' => 'center', 'B' => 'center', 'C' => 'left', 'D' => 'right', 'E' => 'center', 'F' => 'center', 'G' => 'left', 'H' => 'center', 'I' => 'left'],
        'B4' => ['A' => 'center', 'B' => 'center', 'C' => 'left', 'D' => 'right', 'E' => 'center', 'F' => 'left', 'G' => 'center', 'H' => 'center', 'I' => 'center', 'J' => 'left', 'K' => 'left', 'L' => 'left'],
        'B5' => ['A' => 'center', 'B' => 'center', 'C' => 'left', 'D' => 'right', 'E' => 'center', 'F' => 'left', 'G' => 'center', 'H' => 'center', 'I' => 'center', 'J' => 'left', 'K' => 'left', 'L' => 'left'],
    ];

    /**
     * Độ rộng cột (đơn vị "ký tự" của Excel) theo ý nghĩa cột từng sheet.
     *
     * @var array<string, array<string, int>>
     */
    private const COL_WIDTH = [
        'B1' => ['A' => 5, 'B' => 28, 'C' => 10, 'D' => 12, 'E' => 24, 'F' => 16, 'G' => 16, 'H' => 16, 'I' => 10, 'J' => 10, 'K' => 12, 'L' => 12, 'M' => 20],
        'B2' => ['A' => 5, 'B' => 8, 'C' => 28, 'D' => 35, 'E' => 10, 'F' => 10, 'G' => 10, 'H' => 10, 'I' => 10, 'J' => 10, 'K' => 12, 'L' => 20],
        'B3' => ['A' => 5, 'B' => 8, 'C' => 28, 'D' => 10, 'E' => 12, 'F' => 12, 'G' => 28, 'H' => 12, 'I' => 20],
        'B4' => ['A' => 5, 'B' => 8, 'C' => 28, 'D' => 10, 'E' => 16, 'F' => 14, 'G' => 12, 'H' => 14, 'I' => 14, 'J' => 14, 'K' => 18, 'L' => 18],
        'B5' => ['A' => 5, 'B' => 8, 'C' => 28, 'D' => 10, 'E' => 16, 'F' => 14, 'G' => 12, 'H' => 14, 'I' => 14, 'J' => 14, 'K' => 18, 'L' => 18],
    ];

    /**
     * Định dạng chuẩn Excel cho MỘT sheet đã đổ dữ liệu — áp riêng cho file xuất, không ảnh hưởng
     * bản HTML render trên trang. Nhận diện các mốc dòng động (item đổi → dòng dịch) bằng cách quét
     * nội dung, không hard-code số dòng.
     */
    private function applyExportStyling($sheet, string $sheetName, string $lastColumn): void
    {
        $lastColIdx = Coordinate::columnIndexFromString($lastColumn);
        $lastRow = $sheet->getHighestRow();
        $full = "A1:{$lastColumn}{$lastRow}";

        // --- Mốc dòng động ---
        $landmarks = $this->exportLandmarks($sheet, $sheetName, $lastColIdx, $lastRow);
        $headerRows = $landmarks['headerRows'];
        $headerTop = $headerRows[0];
        $headerBottom = end($headerRows);
        $dataFirst = $landmarks['dataFirst'];
        $dataLast = $landmarks['dataLast'];
        $groupRows = $landmarks['groupRows'];
        $footerRows = $landmarks['footerRows'];

        $this->applyExportFooterLayout($sheet, $sheetName, $lastColumn, $dataLast, (string) $sheet->getCell('A3')->getValue());
        $lastRow = $sheet->getHighestRow();
        $footerRows = $this->footerRowsForExport($sheet, $lastColIdx, $dataLast, $lastRow);

        // (#1) Làm phẳng RichText về text thường trước (chỉ duyệt qua các ô tồn tại dữ liệu thực tế)
        foreach ($sheet->getCoordinates(false) as $coord) {
            [$col, $row] = Coordinate::indexesFromString($coord);
            if ($col > $lastColIdx || $row > $lastRow) {
                continue;
            }
            $cell = $sheet->getCell($coord);
            $value = $cell->getValue();
            if ($value instanceof RichText) {
                $cell->setValueExplicit($value->getPlainText(), DataType::TYPE_STRING);
            }
        }

        // Áp font Times New Roman 11pt đen cho toàn bảng bằng MỘT applyFromArray duy nhất.
        // PhpSpreadsheet 1.x: MỖI setter lẻ (setName/setSize/setARGB) = 1 lượt quét cả range,
        // mỗi style biến thể phải linear-scan + md5 toàn bộ cellXf collection — gộp 3 lệnh
        // thành 1 giảm 2/3 chi phí ngay trên range lớn nhất (xem thêm ghi chú cuối method).
        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
            'font' => ['name' => 'Times New Roman', 'size' => 11, 'color' => ['argb' => 'FF000000']],
        ]);

        // (#2) Gỡ mọi vùng gộp 1×1 vô nghĩa.
        foreach ($sheet->getMergeCells() as $range) {
            [$l, $t, $r, $b] = $this->rangeBounds($range);
            if ($l === $r && $t === $b) {
                $sheet->unmergeCells($range);
            }
        }

        // (#1) Company title 16pt bold italic center; sub-header "BƯỚC/BIỂU MẪU" 13pt bold center;
        // "Ban hành" 10pt italic; các dòng meta còn lại 11pt thường, căn trái.
        if ($landmarks['companyCell']) {
            $this->styleCell($sheet, $landmarks['companyCell'], 16, true, true, Alignment::HORIZONTAL_CENTER);
        }
        if ($landmarks['titleCell']) {
            $this->styleCell($sheet, $landmarks['titleCell'], 13, true, false, Alignment::HORIZONTAL_CENTER);
        }
        if ($landmarks['issuedCell']) {
            $this->styleCell($sheet, $landmarks['issuedCell'], 10, false, true, Alignment::HORIZONTAL_CENTER);
        }
        for ($row = 1; $row < $headerTop; $row++) {
            if ($this->rowIsMeta($sheet, $row, $landmarks)) {
                $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                    'font' => ['size' => 11, 'bold' => false],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
            }
        }

        // (#1) Header bảng: 11pt bold center wrap — đặt từng ô (ô gộp dọc C5:C6… range không áp được),
        // nhưng gộp font + alignment vào MỘT applyFromArray/ô (trước đây 5 setter lẻ = 5 lượt style/ô).
        foreach ($headerRows as $row) {
            for ($col = 1; $col <= $lastColIdx; $col++) {
                $coord = Coordinate::stringFromColumnIndex($col) . $row;
                $sheet->getStyle($coord)->applyFromArray([
                    'font' => ['size' => 11, 'bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);
            }
        }

        // (#3) B1: cột chứng từ (H) ép text @; cột khối lượng (D) tách đơn vị kg → số + numFmt.
        if ($sheetName === 'B1') {
            $sheet->setCellValue("D{$headerTop}", 'Khối lượng (kg)');
        }

        // (#7) Body: vertical center + wrapText; 11pt thường.
        // Áp dụng hàng loạt cho cả vùng body để tăng tốc độ xử lý lên gấp hàng trăm lần
        $bodyRange = "A{$dataFirst}:{$lastColumn}{$dataLast}";
        $sheet->getStyle($bodyRange)->applyFromArray([
            'font' => ['size' => 11, 'bold' => false],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        // Căn ngang hàng loạt theo cột (nhanh hơn so với chạy vòng lặp cho từng ô)
        $align = self::COL_ALIGN[$sheetName] ?? [];
        foreach ($align as $letter => $alignType) {
            $h = match ($alignType) {
                'center' => Alignment::HORIZONTAL_CENTER,
                'right' => Alignment::HORIZONTAL_RIGHT,
                default => Alignment::HORIZONTAL_LEFT,
            };
            $sheet->getStyle("{$letter}{$dataFirst}:{$letter}{$dataLast}")->getAlignment()->setHorizontal($h);
        }

        // (#4) Dòng nhóm (B1/B3...) giữ nền hồng, chữ đậm, căn trái lề
        foreach ($groupRows as $row) {
            $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . self::SECTION_FILL]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ]);
        }

        // Định dạng cột cụ thể theo từng sheet
        if ($sheetName === 'B1') {
            $sheet->getStyle("H{$dataFirst}:H{$dataLast}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            for ($row = $dataFirst; $row <= $dataLast; $row++) {
                if (!in_array($row, $groupRows, true)) {
                    $this->numericKgCell($sheet, "D{$row}");
                }
            }
        }
        if ($sheetName === 'B3') {
            $sheet->getStyle("E{$dataFirst}:E{$dataLast}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        }

        // (#6) Table-style trực quan: banded rows xen kẽ (bỏ qua dòng nhóm) — KHÔNG dùng ListObject
        // để không phá merged cell / dòng nhóm full-width của biểu mẫu QĐ 1246.
        $bandIndex = 0;
        for ($row = $dataFirst; $row <= $dataLast; $row++) {
            if (in_array($row, $groupRows, true)) {
                continue;
            }
            if ($bandIndex % 2 === 1) {
                $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . self::BAND_FILL]],
                ]);
            }
            $bandIndex++;
        }

        // (#8) Viền thin đen toàn vùng header + body.
        $sheet->getStyle("A{$headerTop}:{$lastColumn}{$dataLast}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']],
            ],
        ]);

        // (#9) Footer/chữ ký 10pt italic + bỏ khoảng trắng thừa trước "- K : Không Đạt".
        foreach ($footerRows as $row) {
            $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                'font' => ['size' => 10, 'italic' => true],
            ]);
            for ($col = 1; $col <= $lastColIdx; $col++) {
                $cell = $sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row);
                $value = (string) $cell->getValue();
                if (str_contains($value, 'Không Đạt')) {
                    $cell->setValue(preg_replace('/\s{2,}(- K)/u', '   $1', $value));
                }
            }
        }

        // Cắt sạch dòng trống thừa phía dưới footer (file mẫu để lại tới ~51 dòng ⇒ bảng loãng).
        $contentLast = max($dataLast, $footerRows === [] ? $dataLast : max($footerRows));
        $highest = $sheet->getHighestRow();
        if ($highest > $contentLast) {
            $sheet->removeRow($contentLast + 1, $highest - $contentLast);
        }

        // (#10) Độ rộng cột thủ công + chiều cao dòng cố định, gọn gàng (không để auto quá sát).
        foreach (self::COL_WIDTH[$sheetName] ?? [] as $letter => $width) {
            $sheet->getColumnDimension($letter)->setWidth($width);
        }
        for ($row = 1; $row <= $contentLast; $row++) {
            $height = match (true) {
                $sheetName === 'B2' && $row === $headerTop => 71,
                $sheetName === 'B2' && $row === $headerBottom => 67,
                $sheetName === 'B5' && in_array($row, $headerRows, true) => 76,
                in_array($row, $headerRows, true) => 34,   // header cột có wrap 2 dòng
                $row < $headerTop => 22,                    // meta + company + tiêu đề
                in_array($row, $groupRows, true) => 22,     // dòng phân nhóm
                $row > $dataLast => 20,                     // footer / chữ ký
                default => 20,                              // dòng dữ liệu
            };
            $sheet->getRowDimension($row)->setRowHeight($height);
        }

        // (#5) Freeze ngay sau dải header cột: giữ meta + header đứng yên, phần dữ liệu cuộn.
        $sheet->freezePane('A' . ($headerBottom + 1));

        // Mở file ở cỡ vừa mắt: ép zoom 100% (file mẫu lưu 17–49% khiến bảng tí xíu khi mở)
        // và đặt con trỏ về A1 thay vì dòng cuối.
        $sheet->getSheetView()->setZoomScale(100)->setZoomScaleNormal(100);
        $sheet->setSelectedCells('A1');
    }

    /**
     * Nhận diện các mốc dòng của sheet đã đổ dữ liệu (không phụ thuộc số item).
     *
     * @return array{headerRows: array<int, int>, dataFirst: int, dataLast: int, groupRows: array<int, int>, footerRows: array<int, int>, companyCell: ?string, titleCell: ?string, issuedCell: ?string}
     */
    private function exportLandmarks($sheet, string $sheetName, int $lastColIdx, int $lastRow): array
    {
        $headerTopRows = [];
        $companyCell = null;
        $titleCell = null;
        $issuedCell = null;
        $groupRows = [];

        $coords = $sheet->getCoordinates(false);
        foreach ($coords as $coord) {
            [$col, $row] = Coordinate::indexesFromString($coord);
            if ($col > $lastColIdx || $row > $lastRow) {
                continue;
            }
            $value = trim((string) $sheet->getCell($coord)->getValue());
            if ($value === '') {
                continue;
            }
            if ($col === 1 && $value === 'TT') {
                $headerTopRows[] = $row;
            }
            if ($col === 1 && preg_match('/^(I|II|III|IV|V|VI)\.\s/u', $value)) {
                $groupRows[] = $row;
            }
            if ($companyCell === null && str_contains($value, 'Địa chỉ:') && str_contains($value, "\n")) {
                $companyCell = $coord;
            }
            if ($titleCell === null && (str_starts_with($value, 'BƯỚC') || str_starts_with($value, 'BIỂU MẪU'))) {
                $titleCell = $coord;
            }
            if ($issuedCell === null && str_starts_with($value, 'Ban hành')) {
                $issuedCell = $coord;
            }
        }

        $headerTop = count($headerTopRows) > 0 ? min($headerTopRows) : 1;
        sort($groupRows);

        // Header 2 dòng cho B1/B2 (dòng phụ "Tên cơ sở cung cấp…" / "Người tham gia chế biến…").
        $headerRows = in_array($sheetName, ['B1', 'B2'], true) ? [$headerTop, $headerTop + 1] : [$headerTop];

        // Dòng dữ liệu đầu tiên: STT dạng số ngay sau header.
        $dataFirst = end($headerRows) + 1;
        for ($row = end($headerRows) + 1; $row <= $lastRow; $row++) {
            if (ctype_digit(trim((string) $sheet->getCell("A{$row}")->getValue()))) {
                $dataFirst = $row;
                break;
            }
        }

        // Dòng dữ liệu cuối: STT số hoặc dòng nhóm, dừng trước footer (GHI CHÚ/Nhân viên/Đại diện).
        $dataLast = $dataFirst;
        for ($row = $dataFirst; $row <= $lastRow; $row++) {
            $a = trim((string) $sheet->getCell("A{$row}")->getValue());
            if (ctype_digit($a) || in_array($row, $groupRows, true)) {
                $dataLast = $row;

                continue;
            }
            if ($a !== '' && preg_match('/(GHI CHÚ|Ghi chú|Nhân viên|Đại diện)/u', $a)) {
                break;
            }
        }

        // Footer = dòng có nội dung nằm sau dataLast.
        $footerRows = [];
        for ($row = $dataLast + 1; $row <= $lastRow; $row++) {
            for ($col = 1; $col <= $lastColIdx; $col++) {
                if (trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue()) !== '') {
                    $footerRows[] = $row;
                    break;
                }
            }
        }

        return compact('headerRows', 'dataFirst', 'dataLast', 'groupRows', 'footerRows', 'companyCell', 'titleCell', 'issuedCell');
    }

    private function applyExportFooterLayout($sheet, string $sheetName, string $lastColumn, int $dataLast, string $inspectorMeta): void
    {
        $inspector = trim(preg_replace('/^Người kiểm tra:\s*/u', '', $inspectorMeta) ?? '');
        $inspector = $inspector !== '' ? mb_strtoupper($inspector) : '';
        $highest = $sheet->getHighestRow();
        $footerStart = $dataLast + 1;
        $footerEnd = max($highest, $footerStart + 4);
        $area = "A{$footerStart}:{$lastColumn}{$footerEnd}";

        $this->unmergeArea($sheet, $area);
        for ($row = $dataLast + 1; $row <= $footerEnd; $row++) {
            $this->clearRow($sheet, $row, 'A', $lastColumn);
        }

        $merge = function (string $range) use ($sheet): void {
            if (!isset($sheet->getMergeCells()[$range])) {
                $sheet->mergeCells($range);
            }
        };
        $applyFooterGrid = function () use ($sheet, $lastColumn, $footerStart, $footerEnd): void {
            $sheet->getStyle("A{$footerStart}:{$lastColumn}{$footerEnd}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFD9D9D9'],
                    ],
                ],
            ]);
        };

        if ($sheetName === 'B1') {
            $sheet->setCellValue("B{$footerStart}", 'GHI CHÚ:');
            $sheet->setCellValue('B' . ($footerStart + 1), 'Đ: Đạt');
            $sheet->setCellValue('B' . ($footerStart + 2), 'K: Không');
            $sheet->setCellValue("E{$footerStart}", 'Đại diện nhà ăn');
            $sheet->setCellValue('E' . ($footerStart + 1), $inspector);
            $merge('E' . ($footerStart + 1) . ':E' . ($footerStart + 2));
            $applyFooterGrid();

            return;
        }

        if ($sheetName === 'B2') {
            $sheet->setCellValue("B{$footerStart}", 'GHI CHÚ:');
            $sheet->setCellValue('B' . ($footerStart + 1), 'Đ: Đạt');
            $sheet->setCellValue('B' . ($footerStart + 2), 'K: Không');
            $sheet->setCellValue("D{$footerStart}", 'Nhân viên kiểm soát');
            $sheet->setCellValue('D' . ($footerStart + 1), $inspector);
            $sheet->setCellValue("K{$footerStart}", 'Nhân viên giám sát');
            $applyFooterGrid();

            return;
        }

        if ($sheetName === 'B3') {
            $sheet->setCellValue("C{$footerStart}", 'Nhân viên kiểm tra');
            $sheet->setCellValue('C' . ($footerStart + 1), $inspector);
            $sheet->setCellValue("G{$footerStart}", 'Nhân viên giám sát');
            $merge("G{$footerStart}:I{$footerStart}");
            $merge('C' . ($footerStart + 1) . ':C' . ($footerStart + 2));
            $applyFooterGrid();

            return;
        }

        if (in_array($sheetName, ['B4', 'B5'], true)) {
            $sheet->setCellValue("B{$footerStart}", 'GHI CHÚ:');
            $sheet->setCellValue('B' . ($footerStart + 1), 'Đ: Đạt');
            $sheet->setCellValue('B' . ($footerStart + 2), 'K: Không');
            $sheet->setCellValue("D{$footerStart}", 'Nhân viên kiểm tra');
            $sheet->setCellValue('D' . ($footerStart + 1), $inspector);
            $sheet->setCellValue("I{$footerStart}", 'Đại diện công ty');
            $merge("D{$footerStart}:E{$footerStart}");
            $merge('D' . ($footerStart + 1) . ':E' . ($footerStart + 2));
            $merge("I{$footerStart}:L{$footerStart}");
            $merge('I' . ($footerStart + 1) . ':L' . ($footerStart + 1));
            $applyFooterGrid();
        }
    }

    private function footerRowsForExport($sheet, int $lastColIdx, int $dataLast, int $lastRow): array
    {
        $footerRows = [];
        for ($row = $dataLast + 1; $row <= $lastRow; $row++) {
            for ($col = 1; $col <= $lastColIdx; $col++) {
                if (trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue()) !== '') {
                    $footerRows[] = $row;
                    break;
                }
            }
        }

        return $footerRows;
    }

    /**
     * @param  array{companyCell: ?string, titleCell: ?string, issuedCell: ?string}  $landmarks
     */
    private function rowIsMeta($sheet, int $row, array $landmarks): bool
    {
        $a = trim((string) $sheet->getCell("A{$row}")->getValue());

        return preg_match('/^(Tên cơ sở|Thời gian kiểm tra|Địa điểm kiểm tra|Người kiểm tra)/u', $a) === 1;
    }

    private function styleCell($sheet, string $cell, int $size, bool $bold, bool $italic, string $horizontal): void
    {
        // Một applyFromArray thay cho 7 setter lẻ (mỗi setter = 1 lượt style riêng — xem applyExportStyling).
        $sheet->getStyle($cell)->applyFromArray([
            'font' => ['name' => 'Times New Roman', 'size' => $size, 'bold' => $bold, 'italic' => $italic],
            'alignment' => [
                'horizontal' => $horizontal,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
    }

    /**
     * Ô khối lượng B1 đang là chuỗi "14,00 kg": bóc " kg", chuyển số VN (dấu . nghìn, , thập phân)
     * thành số thực + numFmt #,##0.00. Ô đơn vị khác (Quả/Trái/Cái) giữ nguyên text.
     */
    private function numericKgCell($sheet, string $cell): void
    {
        $raw = trim((string) $sheet->getCell($cell)->getValue());
        if (!preg_match('/^([\d.,]+)\s*kg$/iu', $raw, $m)) {
            return;
        }

        $number = (float) str_replace(',', '.', str_replace('.', '', $m[1]));
        $sheet->getCell($cell)->setValueExplicit($number, DataType::TYPE_NUMERIC);
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00');
    }

    /**
     * File mẫu khai báo 1000 dòng × 33 cột nhưng nội dung thật chỉ ~65 dòng × 13 cột. Load thẳng
     * bản gốc rồi cắt bằng removeRow tốn ~6-18 GIÂY mỗi lần cache miss (ReferenceHelper quét toàn bộ
     * ~30k ô cho MỖI thao tác xóa/chèn dòng). Vì vậy: cắt phần thừa MỘT LẦN, lưu bản mẫu nhỏ vào
     * storage theo mtime của file gốc — các render sau chỉ load bản nhỏ (giữ nguyên style/độ rộng
     * cột/chiều cao dòng vì đi qua Xlsx writer thật). Lỗi ở bước này thì trả về file gốc (chậm nhưng đúng).
     *
     * @param  array{sheet: string, lastColumn: string}  $config
     */
    private function trimmedTemplatePath(string $template, array $config): string
    {
        $path = storage_path('framework/cache/fsa-template-' . $config['sheet'] . '-' . filemtime($template) . '.xlsx');

        if (is_file($path)) {
            return $path;
        }

        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setLoadSheetsOnly([$config['sheet']]);
            $spreadsheet = $reader->load($template);
            $sheet = $spreadsheet->getActiveSheet();

            $lastColumnIndex = Coordinate::columnIndexFromString($config['lastColumn']);
            // Cắt sát dòng nội dung thật (B1 ~65, B2/B3 ~31, B4/B5 ~19) — giữ dư dòng rỗng có style
            // thì sau round-trip Xlsx writer chúng thành dòng thật và lộ thành dòng trắng cuối bảng.
            $contentLastRow = $this->lastUsedRow($sheet, $config['lastColumn']);

            // Gỡ merge ngoài vùng mẫu trước, rồi xóa thẳng các ô thừa khỏi cell collection
            // (hash delete — không đi qua ReferenceHelper).
            foreach ($sheet->getMergeCells() as $range) {
                [, , $right, $bottom] = $this->rangeBounds($range);
                if ($bottom > $contentLastRow || $right > $lastColumnIndex) {
                    $sheet->unmergeCells($range);
                }
            }

            $collection = $sheet->getCellCollection();
            foreach ($collection->getCoordinates() as $coordinate) {
                [$columnIndex, $row] = Coordinate::indexesFromString($coordinate);
                if ($row > $contentLastRow || $columnIndex > $lastColumnIndex) {
                    $collection->delete($coordinate);
                }
            }

            // removeRow/removeColumn lúc này rẻ (collection đã nhỏ) — dọn nốt row/column
            // dimension thừa và đưa cached bounds về đúng.
            $highestRow = $sheet->getHighestRow();
            if ($highestRow > $contentLastRow) {
                $sheet->removeRow($contentLastRow + 1, $highestRow - $contentLastRow);
            }
            $sheet->removeColumn(Coordinate::stringFromColumnIndex($lastColumnIndex + 1), 64);
            $sheet->garbageCollect();

            // Dọn bản trim của mtime cũ rồi ghi atomic (tmp + rename) để 2 request song song không đè nhau.
            foreach (glob(storage_path('framework/cache/fsa-template-' . $config['sheet'] . '-*.xlsx')) ?: [] as $stale) {
                @unlink($stale);
            }
            $tmpPath = $path . '.' . getmypid() . '.tmp';
            (new XlsxWriter($spreadsheet))->save($tmpPath);
            rename($tmpPath, $path);
            $spreadsheet->disconnectWorksheets();

            return $path;
        } catch (Throwable $e) {
            Log::warning('Food safety template trim failed, dùng file gốc', [
                'sheet' => $config['sheet'],
                'message' => $e->getMessage(),
            ]);

            return $template;
        }
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

        // Nhóm lạ (type không khớp nhóm nào) dồn vào nhóm III thay vì bị rớt khỏi biểu mẫu —
        // B1 là hồ sơ pháp lý, mất dòng nguyên liệu là sai hồ sơ.
        $byGroup = collect($items)->groupBy(function (array $item) use ($groups): string {
            $title = $this->stepOneGroupTitle($item);

            return isset($groups[$title]) ? $title : 'III. Bún, đậu hủ,...';
        });
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
                // SĐT phải là TEXT: ô mẫu format General làm HTML writer ép chuỗi số → mất số 0 đầu.
                $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
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
        // Bỏ dòng 19 (kẽ hở giữa 2 khối ca trưa/ca chiều của file mẫu) để dải dữ liệu liên tục —
        // resizeDataRows xóa theo khối liên tục, kẽ hở làm sót lại 1 dòng sample của Excel mẫu.
        $sheet->removeRow(19, 1);
        $rows = range(8, 25);
        $this->resizeDataRows($sheet, $rows, count($items), 'A', 'L');
        $rows = range(8, 8 + max(1, count($items)) - 1);
        // Gỡ merge dọc cột Ca/bữa ăn của file mẫu để giá trị ca hiển thị đủ trên TỪNG dòng
        $this->unmergeArea($sheet, 'A' . min($rows) . ':L' . max($rows));
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
            ], null, 'A' . $rows[$index]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function fillStepThree($sheet, array $items): void
    {
        // Bỏ dòng 19 như fillStepTwo — cùng bố cục 2 khối trong file mẫu.
        $sheet->removeRow(19, 1);
        $rows = range(7, 24);
        $this->resizeDataRows($sheet, $rows, count($items), 'A', 'I');
        $rows = range(7, 7 + max(1, count($items)) - 1);
        $this->unmergeArea($sheet, 'A' . min($rows) . ':I' . max($rows));
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
            ], null, 'A' . $rows[$index]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function fillSampleSheet($sheet, array $items, string $sheetName): void
    {
        $rows = $sheetName === 'B4' ? range(7, 16) : range(7, 14);
        $this->resizeDataRows($sheet, $rows, count($items), 'A', 'L');
        $rows = range(7, 7 + max(1, count($items)) - 1);
        $this->unmergeArea($sheet, 'A' . min($rows) . ':L' . max($rows));
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
            ], null, 'A' . $rows[$index]);
        }
    }

    private function clearRow($sheet, int $row, string $firstColumn, string $lastColumn): void
    {
        $start = Coordinate::columnIndexFromString($firstColumn);
        $end = Coordinate::columnIndexFromString($lastColumn);

        for ($column = $start; $column <= $end; $column++) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($column) . $row, null);
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
        $normalized = mb_strtolower($type . ' ' . $name);

        return match (true) {
            // 'trứng' phải xét TRƯỚC nhóm I: "trứng gà" chứa 'gà' nên nếu xét sau sẽ bị xếp nhầm nhóm I.
            str_contains($normalized, 'trứng') => 'VI. Trứng các loại,...',
            str_contains($normalized, 'động vật'), str_contains($normalized, 'thịt'), str_contains($normalized, 'cá'), str_contains($normalized, 'bò'), str_contains($normalized, 'gà') => 'I. Thực phẩm tươi sống, đông lạnh: thịt, cá, gà,...',
            str_contains($normalized, 'thực vật'), str_contains($normalized, 'rau'), str_contains($normalized, 'củ'), str_contains($normalized, 'quả'), str_contains($normalized, 'trái cây'), str_contains($normalized, 'gia vị'), str_contains($normalized, 'sả'), str_contains($normalized, 'hành') => 'II. Rau củ, quả, trái cây, các loại,...',
            str_contains($normalized, 'thực phẩm khô'), str_contains($normalized, 'thực phẩm chế biến'), str_contains($normalized, 'lương thực'), str_contains($normalized, 'bún'), str_contains($normalized, 'đậu'), str_contains($normalized, 'gạo'), str_contains($normalized, 'mỳ') => 'III. Bún, đậu hủ,...',
            default => $type,
        };
    }

    /**
     * @param  array{dateText: string, canteen: string, inspector: string, companyName: string, companyAddress: string}  $context
     */
    private function applyHeader($sheet, string $sheetName, array $context): void
    {
        $company = mb_strtoupper($context['canteen'] . '-' . $context['companyName']);
        $address = 'Địa chỉ: ' . $context['companyAddress'];

        // Cả B1 đến B5 đều đồng nhất 3 dòng meta: Thời gian / Địa điểm / Người kiểm tra
        $sheet->setCellValue('A1', 'Thời gian kiểm tra: ' . $context['dateText']);
        $sheet->setCellValue('A2', 'Địa điểm kiểm tra: ' . $context['canteen']);
        $sheet->setCellValue('A3', 'Người kiểm tra: ' . $context['inspector']);
        $sheet->setCellValue('A4', ''); // Dòng 4 trống để giữ khoảng cách giãn dòng giống B1

        // E2 là ô gốc của B1, còn B2-B5 do đẩy lùi dòng 1 lên nên ghi vào E1 (ô đầu của khối gộp)
        $companyCell = ($sheetName === 'B1') ? 'E2' : 'E1';
        $sheet->setCellValue($companyCell, $company . "\n" . $address);
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

    /**
     * Dựng lại khối meta của B2→B5 theo ĐÚNG khung B1 (mẫu chuẩn): 4 dòng meta trái trên cùng
     * (Tên cơ sở / Thời gian / Địa điểm / Người kiểm tra, merge A:D), company góc phải (E2),
     * tiêu đề biểu mẫu + "Ban hành" nằm một dải riêng ngay trên header bảng. Mọi text cũ trong
     * vùng meta (company tĩnh, tiêu đề, Ban hành, meta đặt lộn xộn theo từng sheet) bị xóa sạch
     * rồi ghi lại vào vị trí thống nhất — hết cảnh mỗi tab một kiểu.
     */
    private function normalizeMetaBlockLikeB1($sheet, string $sheetName): void
    {
        $config = match ($sheetName) {
            // titleSource: ô đang giữ tiêu đề biểu mẫu trong file mẫu gốc của từng sheet.
            // Dịch toàn bộ khối header của B2->B5 sát lên trên (bắt đầu từ dòng 1 thay vì dòng 2)
            // để loại bỏ dòng trắng thừa thãi phía trên cùng.
            // Cỡ chữ đồng bộ theo chuẩn B1: company 36 (đậm nghiêng), tiêu đề 38 (đậm), Ban hành 36 (nghiêng).
            // Title không được lấn vào cột A–D (đã merge cho meta trái) — B3 từng đặt D4:G5 đè lên A4:D4
            // làm merge xung đột và tiêu đề biến mất.
            'B2' => ['lastColumn' => 'L', 'metaRows' => 5, 'titleSource' => 'D2', 'company' => 'E1:J3', 'blank' => 'K1:L3', 'title' => 'E4:J5', 'issued' => 'K4:L5', 'companySize' => 36, 'titleSize' => 38],
            'B3' => ['lastColumn' => 'I', 'metaRows' => 5, 'titleSource' => 'C2', 'company' => 'E1:I3', 'blank' => null, 'title' => 'E4:G5', 'issued' => 'H4:I5', 'companySize' => 36, 'titleSize' => 38],
            'B4', 'B5' => ['lastColumn' => 'L', 'metaRows' => 5, 'titleSource' => 'E4', 'company' => 'E1:J3', 'blank' => 'K1:L3', 'title' => 'E4:J5', 'issued' => 'K4:L5', 'companySize' => 36, 'titleSize' => 38],
            default => null,
        };

        if ($config === null) {
            return;
        }

        // Giữ lại tiêu đề biểu mẫu trước khi dọn trắng cả vùng meta.
        $title = trim((string) $sheet->getCell($config['titleSource'])->getValue());
        if ($sheetName === 'B3') {
            // Đè tiêu đề B3 vì file Excel mẫu của B3 bị copy nhầm tiêu đề B2
            $title = 'BƯỚC 3: KIỂM TRA TRƯỚC KHI ĂN';
        }

        $metaArea = 'A1:' . $config['lastColumn'] . $config['metaRows'];
        foreach ($sheet->getMergeCells() as $range) {
            if ($this->rangesTouch($range, $metaArea)) {
                $sheet->unmergeCells($range);
            }
        }

        for ($row = 1; $row <= $config['metaRows']; $row++) {
            $this->clearRow($sheet, $row, 'A', $config['lastColumn']);
            $sheet->getRowDimension($row)->setRowHeight(38);
        }

        // Danh sách các ô cần merge chính
        $mergeTargets = array_filter([
            $config['company'],
            $config['blank'],
            $config['title'],
            $config['issued'],
        ]);

        // Merge 3 dòng meta bên lề trái. Các dòng trống dưới meta để nguyên như file user đã chỉnh:
        // B2/B4/B5 không gộp A4:D5; B3 vẫn được xử lý qua blankRanges bên dưới nếu cần.
        for ($r = 1; $r <= 3; $r++) {
            $mergeTargets[] = "A{$r}:D{$r}";
        }

        // Tự động tìm các dòng trống lề phải (không bị chiếm bởi công ty và tiêu đề) để merge
        $occupiedRows = [];
        foreach ([$config['company'], $config['title']] as $range) {
            if ($range) {
                [$startCol, $startRow, $endCol, $endRow] = $this->rangeBounds($range);
                for ($r = $startRow; $r <= $endRow; $r++) {
                    $occupiedRows[$r] = true;
                }
            }
        }

        $blankRanges = [];
        for ($r = 1; $r <= $config['metaRows']; $r++) {
            if (!isset($occupiedRows[$r])) {
                $blankRange = 'E' . $r . ':' . $config['lastColumn'] . $r;
                $mergeTargets[] = $blankRange;
                $blankRanges[] = $blankRange;
            }
        }

        // B3 trong file user vẫn giữ A4:D5 là vùng trống gộp, các sheet còn lại để rời.
        if ($sheetName === 'B3') {
            for ($r = 4; $r <= $config['metaRows']; $r++) {
                $mergeTargets[] = "A{$r}:D{$r}";
                $blankRanges[] = "A{$r}:D{$r}";
            }
        }

        foreach ($mergeTargets as $target) {
            $sheet->mergeCells($target);
        }

        $titleCell = explode(':', $config['title'])[0];
        $issuedCell = explode(':', $config['issued'])[0];
        $sheet->setCellValue($titleCell, $title);
        $sheet->setCellValue($issuedCell, 'Ban hành: QĐ 1246/2017-BYT');

        // Định dạng font cho lề trái A1->A3
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

        $centerBold = fn(int $size, bool $bold = true, bool $italic = false): array => [
            'font' => [
                'name' => 'Times New Roman',
                'size' => $size,
                'bold' => $bold,
                'italic' => $italic,
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
                'wrapText' => true,
            ],
        ];

        // Công ty in đậm + in nghiêng và xóa viền dọc trong vùng gộp để đồng nhất B1
        $sheet->getStyle($config['company'])->applyFromArray(
            $centerBold($config['companySize'], true, true) + [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_NONE,
                    ],
                ],
            ]
        );

        if ($config['blank']) {
            $sheet->getStyle($config['blank'])->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_NONE,
                    ],
                ],
            ]);
        }

        // Loại bỏ toàn bộ viền dọc của các vùng trống (A4->D6, E3->L3, E6->L6)
        foreach ($blankRanges as $range) {
            $sheet->getStyle($range)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_NONE,
                    ],
                ],
            ]);
        }

        $sheet->getStyle($config['title'])->applyFromArray($centerBold($config['titleSize']));
        // "Ban hành" theo chuẩn B1: 36, thường, nghiêng
        $sheet->getStyle($config['issued'])->applyFromArray($centerBold(36, false, true));

        // B2/B3 trong file user đã xóa dòng trống nằm giữa block meta và header bảng:
        // header bắt đầu ở dòng 6, dữ liệu bắt đầu ở dòng 8.
        if (in_array($sheetName, ['B2', 'B3'], true)) {
            $sheet->removeRow(6, 1);
        }
    }

    /**
     * Đồng bộ cỡ chữ BẢNG dữ liệu của B2→B5 theo chuẩn B1: header bảng 36 đậm, dòng dữ liệu 38 —
     * file mẫu gốc mỗi sheet một cỡ (B3 30, B4/B5 26) nên 5 tab nhìn lệch nhau.
     */
    private function applyUniformTableFont($sheet, string $sheetName, int $itemCount): void
    {
        $config = match ($sheetName) {
            'B2' => ['headerRows' => [6, 7], 'dataStart' => 8, 'lastColumn' => 'L'],
            'B3' => ['headerRows' => [6], 'dataStart' => 7, 'lastColumn' => 'I'],
            'B4', 'B5' => ['headerRows' => [6], 'dataStart' => 7, 'lastColumn' => 'L'],
            default => null,
        };

        if ($config === null) {
            return;
        }

        foreach ($config['headerRows'] as $row) {
            $sheet->getStyle("A{$row}:{$config['lastColumn']}{$row}")->applyFromArray([
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
        }

        $dataEnd = $config['dataStart'] + max(1, $itemCount) - 1;
        $sheet->getStyle("A{$config['dataStart']}:{$config['lastColumn']}{$dataEnd}")->applyFromArray([
            'font' => [
                'name' => 'Times New Roman',
                'size' => 38,
                'bold' => false,
            ],
        ]);
    }

    /**
     * Gỡ mọi vùng gộp ô giao với $area (dùng cho vùng dữ liệu trước khi đổ số liệu từng dòng).
     */
    private function unmergeArea($sheet, string $area): void
    {
        foreach ($sheet->getMergeCells() as $range) {
            if ($this->rangesTouch($range, $area)) {
                $sheet->unmergeCells($range);
            }
        }
    }

    private function rangesTouch(string $a, string $b): bool
    {
        [$aLeft, $aRow1, $aRight, $aRow2] = $this->rangeBounds($a);
        [$bLeft, $bRow1, $bRight, $bRow2] = $this->rangeBounds($b);

        return $aLeft <= $bRight && $aRight >= $bLeft && $aRow1 <= $bRow2 && $aRow2 >= $bRow1;
    }

    /**
     * @return array{0: int, 1: int, 2: int, 3: int}
     */
    private function rangeBounds(string $range): array
    {
        $range = str_replace('$', '', $range);
        $parts = explode(':', $range);
        $start = $parts[0];
        $end = $parts[1] ?? $start;

        preg_match('/([A-Z]+)(\d+)/iu', $start, $startMatch);
        preg_match('/([A-Z]+)(\d+)/iu', $end, $endMatch);

        $left = Coordinate::columnIndexFromString($startMatch[1]);
        $right = Coordinate::columnIndexFromString($endMatch[1]);
        $top = (int) $startMatch[2];
        $bottom = (int) $endMatch[2];

        return [
            min($left, $right),
            min($top, $bottom),
            max($left, $right),
            max($top, $bottom),
        ];
    }

    private function normalizeB1GroupRows($sheet): void
    {
        $lastRow = $sheet->getHighestRow();
        for ($row = 1; $row <= $lastRow; $row++) {
            $text = trim((string) $sheet->getCell("A{$row}")->getValue());
            if (!preg_match('/^(I|II|III|VI)\./u', $text)) {
                continue;
            }

            foreach ($sheet->getMergeCells() as $range) {
                if (preg_match('/^[A-M]' . $row . ':/u', $range)) {
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

    /**
     * File mẫu chứa tên người thật ở khối chữ ký (sample data). Người kiểm tra thay bằng
     * inspector động; người giám sát/đại diện công ty để trống chờ ký tay.
     */
    private function replaceSampleSignatures($sheet, string $lastColumn, string $inspector): void
    {
        foreach ($sheet->getCoordinates(false) as $coordinate) {
            $cell = $sheet->getCell($coordinate);
            $value = mb_strtolower(trim((string) $cell->getValue()));

            if ($value === 'nguyễn thị ánh ngọc') {
                $cell->setValue($inspector !== '' ? mb_strtoupper($inspector) : '');
            } elseif ($value === 'nguyễn thị xuân tiên') {
                $cell->setValue(null);
            }
        }
    }

    private function trimSheet($sheet, string $lastColumn, int $lastRow): void
    {
        // Không dùng getHighestRow(): giá trị cached chỉ tăng không giảm và có thể NHỎ hơn
        // row dimension thật — removeRow không phủ hết thì dimension phía dưới shift lên
        // thành dòng trắng ma dưới bảng. Tính biên thật từ cells + row dimensions.
        $maxRow = $sheet->getHighestDataRow();
        foreach ($sheet->getRowDimensions() as $dimension) {
            $maxRow = max($maxRow, $dimension->getRowIndex());
        }
        if ($maxRow > $lastRow) {
            $sheet->removeRow($lastRow + 1, $maxRow - $lastRow);
        }

        // Chỉ xóa cột khi THẬT SỰ còn cột thừa: removeColumn trên vùng không có cột nào
        // vẫn chạy ReferenceHelper và tạo ra dải ô rỗng ở cột cuối (M) tới cachedHighestRow,
        // làm lộ hàng chục dòng trắng dưới bảng.
        $lastColumnIndex = Coordinate::columnIndexFromString($lastColumn);
        if (Coordinate::columnIndexFromString($sheet->getHighestColumn()) > $lastColumnIndex) {
            $sheet->removeColumn(Coordinate::stringFromColumnIndex($lastColumnIndex + 1), 64);
        }
    }

    private function lastUsedRow($sheet, string $lastColumn): int
    {
        $lastColumnIndex = Coordinate::columnIndexFromString($lastColumn);
        $lastRow = 1;

        // Duyệt các ô ĐANG TỒN TẠI thay vì getCell() từng tọa độ — getCell tạo cell rỗng
        // cho mọi ô chưa có, quét lưới đầy đủ sinh hàng nghìn object thừa.
        foreach ($sheet->getCoordinates(false) as $coordinate) {
            [$columnIndex, $row] = Coordinate::indexesFromString($coordinate);
            if ($row <= $lastRow || $columnIndex > $lastColumnIndex) {
                continue;
            }

            $value = $sheet->getCell($coordinate)->getValue();
            if ($value !== null && trim((string) $value) !== '') {
                $lastRow = $row;
            }
        }

        return $lastRow;
    }

    private function extractUsableHtml(string $html): string
    {
        preg_match_all('/<style\b[^>]*>(.*?)<\/style>/is', $html, $styles);
        preg_match('/<body\b[^>]*>(.*?)<\/body>/is', $html, $body);

        $styleHtml = collect($styles[0] ?? [])->implode("\n");
        $bodyHtml = $body[1] ?? $html;

        return '<div class="fsa-template-html">' . $styleHtml . $bodyHtml . '</div>';
    }
}
