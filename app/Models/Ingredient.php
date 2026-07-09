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
        'supplier_id',
        'reference_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
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
