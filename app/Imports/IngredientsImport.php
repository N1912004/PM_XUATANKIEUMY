<?php

namespace App\Imports;

use App\Models\Ingredient;
use App\Models\IngredientType;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class IngredientsImport implements ToCollection
{
    public const CREATED = 'created';

    public const UPDATED = 'updated';

    public const SKIPPED = 'skipped';

    /**
     * Kết quả từng dòng, dùng chung cho màn xem trước và thông báo sau khi nhập.
     *
     * @var array<int, array{row: int, code: string, name: string, status: string, message: ?string}>
     */
    public array $results = [];

    public function collection(Collection $rows): void
    {
        // Bỏ qua 2 dòng đầu (Tiêu đề lớn ở dòng 1 và Tiêu đề cột ở dòng 2)
        foreach ($rows->slice(2) as $index => $row) {
            // $index đếm từ 0 nên số dòng thật trên Excel là $index + 1
            $this->importRow($row, $index + 1);
        }
    }

    /** Số dòng theo từng trạng thái. */
    public function countOf(string $status): int
    {
        return count(array_filter($this->results, fn (array $r): bool => $r['status'] === $status));
    }

    /** Tổng số dòng ghi vào CSDL thành công. */
    public function successCount(): int
    {
        return $this->countOf(self::CREATED) + $this->countOf(self::UPDATED);
    }

    /**
     * Lý do của các dòng bị bỏ qua, để liệt kê trong thông báo.
     *
     * @return array<int, string>
     */
    public function skippedMessages(): array
    {
        return array_values(array_map(
            fn (array $r): string => $r['message'],
            array_filter($this->results, fn (array $r): bool => $r['status'] === self::SKIPPED),
        ));
    }

    /**
     * Mỗi dòng chạy trong một transaction riêng: dòng lỗi không để lại
     * đơn vị / loại / nhà cung cấp tạo dở dang.
     */
    protected function importRow(mixed $row, int $excelRow): void
    {
        // Căn cứ theo cấu trúc: STT (0) | Mã (1) | Tên (2) | Loại (3) | Đơn vị (4) | Đơn giá (5) | NCC (6) | Trạng thái (7 - có thể thiếu)
        $code = isset($row[1]) ? trim((string) $row[1]) : '';
        $name = isset($row[2]) ? trim((string) $row[2]) : '';

        if ($code === '' && $name === '') {
            return; // Dòng trống hoàn toàn: bỏ qua im lặng, không tính là lỗi.
        }

        if ($code === '' || $name === '') {
            $this->record($excelRow, $code, $name, self::SKIPPED, __('ingredient.import.row_missing_fields', ['row' => $excelRow]));

            return;
        }

        // Bản ghi đã xoá mềm vẫn chiếm chỗ trên unique index `ingredients_code_unique`,
        // nên phải coi là đã tồn tại thay vì để INSERT nổ lỗi 1062.
        $existing = Ingredient::withTrashed()->where('code', $code)->first();

        if ($existing?->trashed()) {
            $this->record($excelRow, $code, $name, self::SKIPPED, __('ingredient.import.row_trashed', [
                'row' => $excelRow,
                'code' => $code,
            ]));

            return;
        }

        $type = isset($row[3]) ? trim((string) $row[3]) : null;
        $unit = isset($row[4]) ? trim((string) $row[4]) : null;

        // Xử lý đơn giá tham chiếu
        $refPrice = 0.00;
        if (isset($row[5])) {
            // Xóa ký tự phân cách hàng nghìn nếu có
            $priceStr = str_replace([',', '.'], '', (string) $row[5]);
            $refPrice = is_numeric($priceStr) ? (float) $priceStr : 0.00;
        }

        $supplierName = isset($row[6]) ? trim((string) $row[6]) : '';
        $statusText = isset($row[7]) ? trim((string) $row[7]) : '';

        // Mặc định trạng thái là hoạt động (true)
        $status = true;
        if (! empty($statusText)) {
            if (in_array(strtolower($statusText), ['ngừng hoạt động', 'tạm dừng', 'ngưng hoạt động', 'inactive', 'false', '0'])) {
                $status = false;
            }
        }

        try {
            DB::transaction(function () use ($code, $name, $type, $unit, $refPrice, $supplierName, $status) {
                // 1. Tìm hoặc tự động tạo mới Đơn vị tính
                $unitRecord = null;
                if (! empty($unit)) {
                    $unitRecord = Unit::query()->firstOrCreate(['name' => $unit]);
                }

                // 2. Tìm hoặc tự động tạo mới Loại nguyên liệu
                $typeRecord = null;
                if (! empty($type)) {
                    $typeRecord = IngredientType::query()->firstOrCreate(['name' => $type]);
                }

                // 3. Tạo mới hoặc cập nhật nguyên liệu
                $ingredient = Ingredient::query()->updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $name,
                        'unit_id' => $unitRecord?->id,
                        'ingredient_type_id' => $typeRecord?->id,
                        'reference_price' => $refPrice,
                        'status' => $status,
                        'old_unit' => $unit,
                        'old_type' => $type,
                    ]
                );

                // 4. Liên kết với Nhà cung cấp nếu có thông tin
                if (! empty($supplierName)) {
                    // Hỗ trợ trường hợp ghi nhiều NCC phân cách bằng dấu phẩy
                    $supplierNames = array_map('trim', explode(',', $supplierName));
                    $supplierIds = [];

                    foreach ($supplierNames as $sName) {
                        if (empty($sName)) {
                            continue;
                        }

                        // `suppliers.type` là NOT NULL không có default — NCC tạo tự động
                        // khi import mặc định là 'Tổng hợp' để không vỡ ràng buộc.
                        $supplier = Supplier::query()->firstOrCreate(
                            ['name' => $sName],
                            [
                                'code' => 'SUP_'.strtoupper(uniqid()),
                                'type' => 'Tổng hợp',
                            ]
                        );
                        $supplierIds[] = $supplier->id;
                    }

                    if (! empty($supplierIds)) {
                        $ingredient->suppliers()->syncWithoutDetaching($supplierIds);
                    }
                }
            });

            $this->record($excelRow, $code, $name, $existing ? self::UPDATED : self::CREATED);
        } catch (\Throwable $e) {
            $this->record($excelRow, $code, $name, self::SKIPPED, __('ingredient.import.row_failed', [
                'row' => $excelRow,
                'code' => $code,
                'message' => $e->getMessage(),
            ]));
        }
    }

    protected function record(int $row, string $code, string $name, string $status, ?string $message = null): void
    {
        $this->results[] = compact('row', 'code', 'name', 'status', 'message');
    }
}
