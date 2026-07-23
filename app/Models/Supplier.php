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

        // 5. Khớp từ khóa tương đồng loại nguyên liệu & loại nhà cung cấp
        $ingTypeName = mb_strtolower($ingredient->typeRelation?->name ?? (is_string($ingredient->type) ? $ingredient->type : ''));
        if ($ingTypeName) {
            if (str_contains($supplierTypeLower, $ingTypeName) || str_contains($ingTypeName, $supplierTypeLower)) {
                return true;
            }

            // Nhóm thịt / động vật / hải sản
            if ((str_contains($supplierTypeLower, 'thịt') || str_contains($supplierTypeLower, 'động vật') || str_contains($supplierTypeLower, 'hải sản') || str_contains($supplierTypeLower, 'tươi')) &&
                (str_contains($ingTypeName, 'thịt') || str_contains($ingTypeName, 'động vật') || str_contains($ingTypeName, 'cá') || str_contains($ingTypeName, 'hải sản'))) {
                return true;
            }

            // Nhóm rau / củ / quả / thực vật
            if ((str_contains($supplierTypeLower, 'rau') || str_contains($supplierTypeLower, 'củ') || str_contains($supplierTypeLower, 'quả') || str_contains($supplierTypeLower, 'thực vật')) &&
                (str_contains($ingTypeName, 'rau') || str_contains($ingTypeName, 'củ') || str_contains($ingTypeName, 'quả') || str_contains($ingTypeName, 'thực vật'))) {
                return true;
            }

            // Nhóm khô / lương thực / gạo / gia vị
            if ((str_contains($supplierTypeLower, 'khô') || str_contains($supplierTypeLower, 'lương thực') || str_contains($supplierTypeLower, 'gạo') || str_contains($supplierTypeLower, 'chế biến')) &&
                (str_contains($ingTypeName, 'khô') || str_contains($ingTypeName, 'lương thực') || str_contains($ingTypeName, 'gạo') || str_contains($ingTypeName, 'gia vị') || str_contains($ingTypeName, 'chế biến'))) {
                return true;
            }
        }

        return false;
    }
}
