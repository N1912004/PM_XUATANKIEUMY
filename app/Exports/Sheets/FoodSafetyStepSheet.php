<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Một sheet tương ứng 1 bước kiểm thực, dựng theo biểu mẫu BYT: tiêu đề gộp ô
 * (cơ sở, tên báo cáo, ngày, người kiểm tra) + bảng dữ liệu của bước đó.
 */
class FoodSafetyStepSheet implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    /**
     * Cấu hình cột theo từng bước: nhãn tiêu đề + khóa dữ liệu tương ứng.
     *
     * @var array<string, array{title: string, headings: array<int, string>, keys: array<int, string>}>
     */
    /**
     * Cột dựng theo file mẫu "MẪU KIỂM THỰC 3 BƯỚC_.xlsx" (sheet B1–B5, QĐ 1246/QĐ-BYT).
     */
    protected const STEP_CONFIG = [
        'Bước 1' => [
            'title' => 'BƯỚC 1: KIỂM TRA TRƯỚC KHI CHẾ BIẾN THỨC ĂN',
            'headings' => ['TT', 'Tên thực phẩm', 'Thời gian nhập (giờ, ngày)', 'Khối lượng (kg/lít)', 'Tên cơ sở cung cấp', 'Địa chỉ, điện thoại', 'Tên người giao', 'Chứng từ, hóa đơn', 'Giấy ĐK VS thú y', 'Giấy kiểm dịch', 'Kiểm tra cảm quan', 'Xét nghiệm nhanh', 'Biện pháp xử lý'],
            'keys' => ['name', 'time', 'quantity', 'supplier', 'supplier_contact', 'deliverer', 'invoice', 'vet_check', 'quarantine', 'sensory', 'quick_test', 'action'],
        ],
        'Bước 2' => [
            'title' => 'BƯỚC 2: KIỂM TRA KHI CHẾ BIẾN THỨC ĂN',
            'headings' => ['TT', 'Ca/bữa ăn', 'Tên món ăn', 'Nguyên liệu chính', 'Số lượng/số suất ăn', 'Thời gian sơ chế', 'Thời gian chế biến', 'ĐK vệ sinh: Người tham gia', 'Trang thiết bị dụng cụ', 'Khu vực chế biến', 'Kiểm tra cảm quan', 'Biện pháp xử lý'],
            'keys' => ['shift', 'name', 'main_ingredients', 'portions', 'prep_time', 'time', 'staff_check', 'equipment_check', 'area_check', 'sensory', 'action'],
        ],
        'Bước 3' => [
            'title' => 'BƯỚC 3: KIỂM TRA TRƯỚC KHI ĂN',
            'headings' => ['TT', 'Ca/bữa ăn', 'Tên món ăn', 'Số lượng suất ăn', 'Thời gian chia món ăn', 'Thời gian bắt đầu ăn', 'Dụng cụ (chia, chứa đựng)', 'Kiểm tra cảm quan', 'Biện pháp xử lý'],
            'keys' => ['shift', 'name', 'portions', 'time', 'eat_time', 'utensil', 'sensory', 'action'],
        ],
        'Lưu mẫu' => [
            'title' => 'BIỂU MẪU THEO DÕI LƯU MẪU THỨC ĂN',
            'headings' => ['TT', 'Bữa ăn (giờ ăn)', 'Tên món ăn', 'Số lượng suất ăn', 'Khối lượng/thể tích mẫu', 'Dụng cụ chứa mẫu', 'Nhiệt độ bảo quản', 'Thời gian lấy mẫu', 'Thời gian hủy mẫu', 'Ghi chú', 'Người lưu mẫu', 'Người hủy mẫu'],
            'keys' => ['shift', 'name', 'portions', 'sample_amount', 'container', 'temp', 'time', 'destroy_at', 'notes', 'staff', 'destroyer'],
        ],
        'Hủy mẫu' => [
            'title' => 'BIỂU MẪU THEO DÕI HỦY MẪU THỨC ĂN',
            'headings' => ['TT', 'Bữa ăn (giờ ăn)', 'Tên món ăn', 'Số lượng suất ăn', 'Khối lượng/thể tích mẫu', 'Dụng cụ chứa mẫu', 'Nhiệt độ bảo quản', 'Thời gian lấy mẫu', 'Thời gian hủy mẫu', 'Ghi chú', 'Người lưu mẫu', 'Người hủy mẫu'],
            'keys' => ['shift', 'name', 'portions', 'sample_amount', 'container', 'temp', 'kept_at', 'time', 'notes', 'keeper', 'staff'],
        ],
    ];

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(
        protected string $step,
        protected array $items,
        protected string $date,
        protected string $canteen,
        protected string $inspector,
    ) {}

    public function title(): string
    {
        return $this->step;
    }

    protected function config(): array
    {
        return self::STEP_CONFIG[$this->step] ?? self::STEP_CONFIG['Bước 1'];
    }

    public function columnCount(): int
    {
        return count($this->config()['headings']);
    }

    /**
     * Số dòng tiêu đề (block header gộp ô) trước dòng heading của bảng.
     */
    public const HEADER_ROWS = 4;

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $config = $this->config();
        $cols = $this->columnCount();
        $dateFormatted = Carbon::parse($this->date)->format('d/m/Y');

        $rows = [];

        // Block tiêu đề theo file mẫu B1–B5 (gộp ô trong AfterSheet)
        $rows[] = $this->pad(['Tên cơ sở: '.$this->canteen], $cols);
        $rows[] = $this->pad([$config['title'].'  (Ban hành: QĐ 1246/QĐ-BYT ngày 31/3/2017)'], $cols);
        $rows[] = $this->pad(['Thời gian kiểm tra: '.$dateFormatted.'   |   Địa điểm kiểm tra: '.$this->canteen.'   |   Người kiểm tra: '.$this->inspector], $cols);
        $rows[] = $this->pad([], $cols); // dòng trống

        // Dòng heading của bảng
        $rows[] = $config['headings'];

        // Dữ liệu
        foreach ($this->items as $index => $item) {
            $row = [$index + 1];
            foreach ($config['keys'] as $key) {
                $row[] = $item[$key] ?? '';
            }
            $rows[] = $row;
        }

        if (empty($this->items)) {
            $rows[] = $this->pad(['(Chưa có dữ liệu ghi nhận)'], $cols);
        }

        // Khối chữ ký ký bản cứng theo biểu mẫu BYT (được canh/gộp ô trong AfterSheet)
        $rows[] = $this->pad([], $cols);
        $rows[] = $this->pad(['ĐẠI DIỆN NHÀ ĂN', '', '', 'NGƯỜI KIỂM TRA'], $cols);
        $rows[] = $this->pad(['(Ký, ghi rõ họ tên)', '', '', '(Ký, ghi rõ họ tên)'], $cols);

        return $rows;
    }

    /**
     * @param  array<int, mixed>  $row
     * @return array<int, mixed>
     */
    protected function pad(array $row, int $length): array
    {
        return array_pad($row, $length, '');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex($this->columnCount());

                // Gộp ô 3 dòng tiêu đề đầu
                foreach ([1, 2, 3] as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                $sheet->getStyle('A1:A3')->getFont()->setBold(true);
                $sheet->getStyle('A2')->getFont()->setSize(14);

                // In đậm dòng heading của bảng
                $headingRow = self::HEADER_ROWS + 1;
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Khối chữ ký: 2 dòng cuối — gộp nửa trái (Đại diện nhà ăn) / nửa phải (Người kiểm tra)
                $dataRows = max(count($this->items), 1);
                $signLabelRow = self::HEADER_ROWS + 1 + $dataRows + 2; // +1 blank, dòng nhãn
                $signNoteRow = $signLabelRow + 1;
                $midCol = Coordinate::stringFromColumnIndex(3);
                $rightStart = Coordinate::stringFromColumnIndex(4);
                foreach ([$signLabelRow, $signNoteRow] as $r) {
                    $sheet->mergeCells("A{$r}:{$midCol}{$r}");
                    $sheet->mergeCells("{$rightStart}{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                $sheet->getStyle("A{$signLabelRow}:{$lastCol}{$signLabelRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$signNoteRow}:{$lastCol}{$signNoteRow}")->getFont()->setItalic(true);
            },
        ];
    }
}
