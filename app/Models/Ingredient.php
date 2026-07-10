<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'unit',
        'unit_id',
        'ingredient_type_id',
        'supplier_id',
        'reference_price',
        'status',
    ];

    protected $with = [
        'unitRelation',
        'typeRelation',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function unitRelation(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function typeRelation(): BelongsTo
    {
        return $this->belongsTo(IngredientType::class, 'ingredient_type_id');
    }

    public function getUnitAttribute(): ?string
    {
        return $this->unitRelation?->name ?? $this->attributes['old_unit'] ?? null;
    }

    public function getTypeAttribute(): ?string
    {
        return $this->typeRelation?->name ?? $this->attributes['old_type'] ?? null;
    }

    /**
     * Tương thích ngược chiều GHI: code cũ (import Excel, seeder, test) vẫn tạo
     * nguyên liệu bằng chuỗi 'unit' => 'Kg' — mutator ánh xạ sang unit_id
     * (tự tạo bản ghi danh mục nếu chưa có), không ghi vào cột đã đổi tên.
     */
    public function setUnitAttribute(?string $value): void
    {
        $this->attributes['unit_id'] = filled($value)
            ? Unit::firstOrCreate(['name' => trim($value)])->id
            : null;
    }

    public function setTypeAttribute(?string $value): void
    {
        $this->attributes['ingredient_type_id'] = filled($value)
            ? IngredientType::firstOrCreate(['name' => trim($value)])->id
            : null;
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'ingredient_supplier')
            ->withPivot('reference_price')
            ->withTimestamps();
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_ingredients')
            ->withPivot('quantity_per_portion')
            ->withTimestamps();
    }

    protected static function booted(): void
    {
        // Khi ĐƠN GIÁ THAM CHIẾU của nguyên liệu thay đổi → mọi món ăn đang hoạt động
        // dùng nguyên liệu này tự động chuyển về 'pending' (Chờ rà soát) để kiểm soát giá vốn.
        static::updated(function (Ingredient $ingredient): void {
            if ($ingredient->wasChanged('reference_price')) {
                $ingredient->recipes()
                    ->where('recipes.status', 'active')
                    ->update(['recipes.status' => 'pending']);
            }
        });
    }
}
