<?php

namespace App\Filament\Resources\SupplierResource\Concerns;

use App\Models\Ingredient;
use Illuminate\Support\Collection;

/**
 * Gán nguyên liệu cho NCC theo cơ chế SEARCH-SELECT từng nguyên liệu (BA R12):
 * gõ tìm → chọn thêm từng dòng → nhập đơn giá; KHÔNG dùng checkbox trên toàn danh sách
 * (danh sách nguyên liệu có thể hàng nghìn dòng, checkbox + limit 100 làm sót dữ liệu).
 *
 * Dùng chung cho CreateSupplier và EditSupplier — hai trang share cùng blade form-supplier.
 *
 * @property string $ingredientSearch
 * @property array<int, bool> $selectedIngredients
 * @property array<int, float|null> $ingredientCosts
 */
trait ManagesSupplierIngredients
{
    /** Số kết quả gợi ý tối đa cho mỗi lần gõ tìm kiếm. */
    protected const SEARCH_LIMIT = 15;

    /**
     * Kết quả gợi ý cho ô tìm kiếm — đã loại các nguyên liệu ĐÃ chọn.
     *
     * @return Collection<int, Ingredient>
     */
    public function ingredientSearchResults(): Collection
    {
        if (trim($this->ingredientSearch) === '') {
            return collect();
        }

        $search = mb_strtolower(trim($this->ingredientSearch));
        $chosen = $this->chosenIngredientIds();

        return Ingredient::query()
            ->whereNotIn('id', $chosen === [] ? [0] : $chosen)
            ->where(function ($query) use ($search): void {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
            })
            ->orderBy('name')
            ->limit(self::SEARCH_LIMIT)
            ->get();
    }

    /** Thêm 1 nguyên liệu vào danh sách cung cấp; đơn giá gợi ý = giá tham chiếu hiện có. */
    public function addSupplierIngredient(int $ingredientId): void
    {
        $ingredient = Ingredient::find($ingredientId);

        if (! $ingredient) {
            return;
        }

        $this->selectedIngredients[$ingredientId] = true;
        $this->ingredientCosts[$ingredientId] ??= (float) $ingredient->reference_price;
        $this->ingredientSearch = '';
    }

    public function removeSupplierIngredient(int $ingredientId): void
    {
        unset($this->selectedIngredients[$ingredientId], $this->ingredientCosts[$ingredientId]);
    }

    /**
     * Các nguyên liệu ĐÃ chọn (hiển thị ở bảng dưới ô tìm kiếm).
     *
     * @return Collection<int, Ingredient>
     */
    public function chosenIngredients(): Collection
    {
        $ids = $this->chosenIngredientIds();

        if ($ids === []) {
            return collect();
        }

        return Ingredient::whereIn('id', $ids)->orderBy('name')->get();
    }

    /** @return array<int, int> */
    protected function chosenIngredientIds(): array
    {
        return collect($this->selectedIngredients)
            ->filter()
            ->keys()
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
}
