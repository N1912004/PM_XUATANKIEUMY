<?php

namespace App\Exports;

use App\Exports\Sheets\FoodSafetyStepSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Báo cáo kiểm thực 3 bước theo biểu mẫu Bộ Y tế (QĐ 1246/QĐ-BYT).
 * Gộp 5 bước (Bước 1/2/3 + Lưu mẫu + Hủy mẫu) thành 1 workbook, mỗi bước 1 sheet.
 */
class FoodSafetyAuditReportExport implements WithMultipleSheets
{
    /**
     * @param  array<string, array<int, array<string, mixed>>>  $itemsByStep
     */
    public function __construct(
        protected array $itemsByStep,
        protected string $date,
        protected string $canteen,
        protected string $inspector,
    ) {}

    /**
     * @return array<int, FoodSafetyStepSheet>
     */
    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->itemsByStep as $step => $items) {
            $sheets[] = new FoodSafetyStepSheet($step, $items, $this->date, $this->canteen, $this->inspector);
        }

        return $sheets;
    }
}
