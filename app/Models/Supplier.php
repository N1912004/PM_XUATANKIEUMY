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
        if ($ingredient->suppliers && $ingredient->suppliers->contains('id', $this->id)) {
            return true;
        }

        // 3. Nếu NCC có loại 'Tổng hợp' -> Cung cấp được tất cả
        if ($this->ingredientTypes && $this->ingredientTypes->contains(
            fn (IngredientType $type): bool => str_contains(mb_strtolower($type->name), 'tổng hợp')
        )) {
            return true;
        }

        return false;
    }
}
