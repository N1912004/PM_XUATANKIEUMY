<?php

namespace App\Imports;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Import Ngân hàng thực đơn theo file mẫu "ĐỊNH LƯỢNG MÓN ĂN.xlsx".
 *
 * Cấu trúc cột (positional, dữ liệu từ dòng 3):
 * STT (0) | ĐƠN GIÁ SUẤT ĂN (1) | NHÓM NL (2, carry-forward) | TÊN MÓN ĂN (3)
 * | NGUYÊN LIỆU (4) | ĐỊNH LƯỢNG GR (5) | ĐƠN GIÁ NL (6) | THÀNH TIỀN (7) | THÀNH TIỀN MÓN (8)
 *
 * Dòng có TÊN MÓN mở một món mới; các dòng kế tiếp (trống cột 0-3) là nguyên liệu của món đó.
 * Định lượng file mẫu tính bằng GRAM — hệ thống lưu chuẩn KG (gr / 1000).
 */
class RecipesImport implements ToCollection
{
    public const CREATED = 'created';

    public const UPDATED = 'updated';

    public const SKIPPED = 'skipped';

    /** @var array<int, array{row: int, name: string, status: string, message: ?string}> */
    public array $results = [];

    public function collection(Collection $rows): void
    {
        $current = null;      // ['row' => int, 'name' => ..., 'price' => ..., 'group' => ..., 'ingredients' => [...]]
        $carriedGroup = null; // Nhóm NL (cột C) chỉ ghi ở dòng đầu nhóm — carry xuống các món sau

        // Bỏ 2 dòng tiêu đề (dòng 1 trống/tựa lớn, dòng 2 là header cột)
        foreach ($rows->slice(2) as $index => $row) {
            $excelRow = $index + 1;
            $dishName = isset($row[3]) ? trim((string) $row[3]) : '';
            $groupName = isset($row[2]) ? trim((string) $row[2]) : '';
            $ingName = isset($row[4]) ? trim((string) $row[4]) : '';

            if ($groupName !== '') {
                $carriedGroup = $groupName;
            }

            if ($dishName !== '') {
                // Món mới bắt đầu → chốt món trước đó
                $this->persist($current);

                $current = [
                    'row' => $excelRow,
                    'name' => $dishName,
                    'price' => (float) preg_replace('/[^0-9.]/', '', (string) ($row[1] ?? 0)),
                    'group' => $carriedGroup ?: 'Món mặn',
                    'ingredients' => [],
                ];
            }

            if ($ingName !== '' && $current !== null) {
                $gram = (float) ($row[5] ?? 0);
                if ($gram > 0) {
                    $current['ingredients'][] = [
                        'name' => $ingName,
                        'quantity_kg' => round($gram / 1000, 4),
                        'price' => (float) preg_replace('/[^0-9.]/', '', (string) ($row[6] ?? 0)),
                    ];
                }
            }
        }

        $this->persist($current);
    }

    /**
     * Ghi 1 món + định mức trong 1 transaction riêng — món lỗi không ảnh hưởng món khác.
     *
     * @param  array<string, mixed>|null  $dish
     */
    protected function persist(?array $dish): void
    {
        if ($dish === null || $dish['name'] === '') {
            return;
        }

        if ($dish['ingredients'] === []) {
            $this->record($dish['row'], $dish['name'], self::SKIPPED, "Dòng {$dish['row']} — món \"{$dish['name']}\" không có nguyên liệu nào có định lượng > 0.");

            return;
        }

        // Recipe giờ dùng soft delete: tra cả bản đã xóa mềm — nếu không, import sẽ tạo
        // bản MỚI trùng tên với bản đã xóa (cùng bẫy ingredients_code_unique trong CLAUDE.md).
        // Món đã xóa mềm → BỎ QUA kèm hướng dẫn, đồng bộ hành vi với IngredientsImport.
        $trashed = Recipe::onlyTrashed()->where('name', $dish['name'])->first();
        if ($trashed) {
            $this->record($dish['row'], $dish['name'], self::SKIPPED, "Dòng {$dish['row']} — món \"{$dish['name']}\" đang bị XÓA MỀM trong hệ thống. Khôi phục món (bộ lọc Đã xóa → Khôi phục) rồi import lại nếu muốn cập nhật.");

            return;
        }

        try {
            $wasNew = false;

            DB::transaction(function () use ($dish, &$wasNew): void {
                $recipe = Recipe::query()->firstOrNew(['name' => $dish['name']]);
                $isNew = ! $recipe->exists;
                $wasNew = $isNew;

                if ($isNew) {
                    $recipe->code = 'MON'.strtoupper(uniqid());
                }

                $recipe->fill([
                    'type' => $dish['group'],
                    'actual_price' => $dish['price'],
                    'price_level' => $dish['price'],
                    // Món import về trạng thái Chờ rà soát để bếp trưởng duyệt định lượng/giá
                    'status' => 'pending',
                ])->save();

                $sync = [];
                foreach ($dish['ingredients'] as $item) {
                    // Nguyên liệu tra theo tên (kể cả bản đã xóa mềm để không vỡ unique code)
                    $ingredient = Ingredient::withTrashed()->where('name', $item['name'])->first();

                    if ($ingredient?->trashed()) {
                        $ingredient->restore();
                    }

                    if (! $ingredient) {
                        $ingredient = Ingredient::create([
                            'code' => 'NL'.strtoupper(uniqid()),
                            'name' => $item['name'],
                            'type' => $dish['group'],
                            'unit' => 'Kg',
                            'reference_price' => $item['price'],
                            'status' => true,
                        ]);
                    }

                    $sync[$ingredient->id] = ['quantity_per_portion' => $item['quantity_kg']];
                }

                // Sync toàn bộ định mức: re-import file là cập nhật lại bảng định mức của món
                $recipe->ingredients()->sync($sync);
            });

            $this->record($dish['row'], $dish['name'], $wasNew ? self::CREATED : self::UPDATED);
        } catch (\Throwable $e) {
            $this->record($dish['row'], $dish['name'], self::SKIPPED, "Dòng {$dish['row']} — không lưu được món \"{$dish['name']}\": {$e->getMessage()}");
        }
    }

    protected function record(int $row, string $name, string $status, ?string $message = null): void
    {
        $this->results[] = ['row' => $row, 'name' => $name, 'status' => $status, 'message' => $message];
    }

    public function countOf(string $status): int
    {
        return count(array_filter($this->results, fn (array $r): bool => $r['status'] === $status));
    }

    /** @return array<int, string> */
    public function skippedMessages(): array
    {
        return array_values(array_map(
            fn (array $r): string => (string) $r['message'],
            array_filter($this->results, fn (array $r): bool => $r['status'] === self::SKIPPED),
        ));
    }
}
