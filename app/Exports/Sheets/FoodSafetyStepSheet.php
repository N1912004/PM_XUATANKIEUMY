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
    protected const STEP_CONFIG = [
        'Bước 1' => [
            'title' => 'BƯỚC 1 – KIỂM TRA TRƯỚC KHI CHẾ BIẾN (NGUYÊN LIỆU NHẬP)',
            'headings' => ['TT', 'Tên thực phẩm', 'Thời gian nhập', 'Khối lượng (Kg)', 'Nơi cung cấp', 'Hóa đơn chứng từ', 'ĐK Vệ sinh thú y', 'Cảm quan', 'Test nhanh', 'Ghi chú'],
            'keys' => ['time', 'quantity', 'supplier', 'invoice', 'vet_check', 'sensory', 'quick_test', 'notes'],
        ],
        'Bước 2' => [
            'title' => 'BƯỚC 2 – KIỂM TRA TRONG QUÁ TRÌNH CHẾ BIẾN',
            'headings' => ['TT', 'Tên món ăn', 'Thời gian chế biến', 'Cảm quan', 'Nhiệt độ chế biến', 'Người chế biến', 'Ca/Bếp thực hiện', 'Ghi chú'],
            'keys' => ['time', 'sensory', 'temp', 'cook', 'kitchen', 'notes'],
        ],
        'Bước 3' => [
            'title' => 'BƯỚC 3 – KIỂM TRA TRƯỚC KHI ĂN',
            'headings' => ['TT', 'Tên món ăn', 'Thời gian ăn', 'Cảm quan', 'Lưu mẫu', 'Nhiệt độ', 'Ghi chú'],
            'keys' => ['time', 'sensory', 'sample_kept', 'temp', 'notes'],
        ],
        'Lưu mẫu' => [
            'title' => 'THEO DÕI LƯU MẪU THỨC ĂN',
            'headings' => ['TT', 'Tên món ăn', 'Thời gian lưu', 'Khối lượng mẫu', 'Mã số mẫu', 'Nhiệt độ tủ lưu', 'Người lưu', 'Ghi chú (dụng cụ)'],
            'keys' => ['time', 'quantity', 'sample_code', 'temp', 'staff', 'notes'],
        ],
        'Hủy mẫu' => [
            'title' => 'THEO DÕI HỦY MẪU THỨC ĂN',
            'headings' => ['TT', 'Tên món ăn', 'Thời gian hủy', 'Thời gian lưu giữ', 'Tình trạng mẫu', 'Người hủy', 'Ghi chú'],
            'keys' => ['time', 'retention', 'status', 'staff', 'notes'],
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

        // Block tiêu đề (sẽ được gộp ô trong AfterSheet)
        $rows[] = $this->pad(['CƠ SỞ: '.$this->canteen], $cols);
        $rows[] = $this->pad(['BÁO CÁO KIỂM THỰC BA BƯỚC – '.$config['title'].' (QĐ 1246/QĐ-BYT)'], $cols);
        $rows[] = $this->pad(['Ngày kiểm tra: '.$dateFormatted.'   |   Người kiểm tra: '.$this->inspector], $cols);
        $rows[] = $this->pad([], $cols); // dòng trống

        // Dòng heading của bảng
        $rows[] = $config['headings'];

        // Dữ liệu
        foreach ($this->items as $index => $item) {
            $row = [$index + 1, $item['name'] ?? ''];
            foreach ($config['keys'] as $key) {
                $row[] = $item[$key] ?? '';
            }
            $rows[] = $row;
        }

        if (empty($this->items)) {
            $rows[] = $this->pad(['(Chưa có dữ liệu ghi nhận)'], $cols);
        }

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
            },
        ];
    }
}
