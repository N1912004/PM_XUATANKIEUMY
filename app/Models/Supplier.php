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
}
