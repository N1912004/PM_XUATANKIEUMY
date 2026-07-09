<?php

namespace App\Imports;

use App\Models\Ingredient;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class IngredientsImport implements ToCollection
{
    public function collection(Collection $rows): void
    {
        // Bỏ qua 3 dòng đầu (Tiêu đề chung, Dòng trống, Tiêu đề cột)
        $dataRows = $rows->slice(3);

        foreach ($dataRows as $row) {
            $code = isset($row[0]) ? trim((string) $row[0]) : null;
            $name = isset($row[1]) ? trim((string) $row[1]) : null;
            $type = isset($row[2]) ? trim((string) $row[2]) : null;
            $unit = isset($row[3]) ? trim((string) $row[3]) : null;
            $refPrice = isset($row[4]) ? (float) $row[4] : 0.00;
            $statusText = isset($row[6]) ? trim((string) $row[6]) : '';

            if (empty($code) || empty($name)) {
                continue;
            }

            $status = true;
            if ($statusText === 'Ngừng hoạt động' || $statusText === 'Tạm dừng') {
                $status = false;
            }

            // Update hoặc Create nguyên liệu dựa trên Mã (code)
            Ingredient::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'type' => $type,
                    'unit' => $unit,
                    'reference_price' => $refPrice,
                    'status' => $status,
                ]
            );
        }
    }
}
