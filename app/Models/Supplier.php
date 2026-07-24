<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'contact_name',
        'phone',
        'email',
        'status',
        'notes',
        'documents',
    ];

    protected $casts = [
        'status' => 'boolean',
        'documents' => 'array',
    ];

    protected static function booted(): void
    {
        // `type` là chuỗi loại (ghép dấu phẩy) do form/import ghi vào; giữ pivot
        // `ingredient_type_supplier` luôn khớp để lọc theo quan hệ chuẩn Filament.
        static::saved(function (Supplier $supplier): void {
            if ($supplier->wasChanged('type') || $supplier->wasRecentlyCreated) {
                $supplier->syncIngredientTypesFromString();
            }
        });
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_supplier')
            ->withPivot('reference_price')
            ->withTimestamps();
    }

    public function priceLogs(): HasMany
    {
        return $this->hasMany(SupplierPriceLog::class);
    }

    /**
     * Loại thực phẩm cung cấp, chuẩn hoá qua bảng nối (dùng chung danh mục
     * `ingredient_types` với Nguyên liệu). Đây là nguồn để lọc.
     */
    public function ingredientTypes(): BelongsToMany
    {
        return $this->belongsToMany(IngredientType::class, 'ingredient_type_supplier');
    }

    /**
     * Đồng bộ pivot từ cột `type`: tách dấu phẩy, tự tạo loại còn thiếu trong
     * danh mục để không mất dữ liệu, rồi sync id.
     */
    public function syncIngredientTypesFromString(): void
    {
        $names = collect(explode(',', (string) $this->type))
            ->map(fn (string $name): string => trim($name))
            ->filter()
            ->unique();

        $ids = $names->map(
            fn (string $name): int => IngredientType::query()->firstOrCreate(['name' => $name])->getKey()
        )->all();

        $this->ingredientTypes()->sync($ids);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * NCC này có cung cấp được nguyên liệu không — dùng cho gán nhanh NCC ở các trang
     * tạo PO (CreatePurchaseOrder, ListHang): chỉ auto-fill khi cung cấp được, không thì
     * để trống để user tự chọn. Khớp theo 5 tầng: NCC chính → pivot NCC phụ → loại
     * "Tổng hợp" → pivot loại thực phẩm → từ khóa tương đồng tên loại.
     *
     * Gọi với supplier đã eager-load ['ingredientTypes'] và ingredient đã load
     * ['suppliers', 'typeRelation'] để tránh N+1 khi lặp nhiều nguyên liệu.
     */
    public function canProvideIngredient(Ingredient $ingredient): bool
    {
        // 1. Khớp trực tiếp NCC chính của nguyên liệu (cột supplier_id)
        if ($ingredient->supplier_id && (int) $ingredient->supplier_id === (int) $this->id) {
            return true;
        }

        // 2. Khớp trong danh sách NCC phụ / liên kết (bảng pivot ingredient_supplier)
        if ($ingredient->suppliers->contains('id', $this->id)) {
            return true;
        }

        // 3. Nếu loại của NCC là 'Tổng hợp' -> Cung cấp được tất cả
        $supplierTypeLower = mb_strtolower((string) $this->type);
        if (str_contains($supplierTypeLower, 'tổng hợp')) {
            return true;
        }

        // 4. Khớp theo danh mục loại thực phẩm (bảng pivot ingredient_type_supplier)
        if ($ingredient->ingredient_type_id && $this->ingredientTypes->contains('id', $ingredient->ingredient_type_id)) {
            return true;
        }

        return false;
    }
}
