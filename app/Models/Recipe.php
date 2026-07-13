<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'recipe_type_id',
        'price_level',
        'actual_price',
        'cost_override',
        'price_option',
        'status',
        'description',
    ];

    /**
     * Lý do điều chỉnh cost, được truyền tạm từ form của Filament.
     */
    public ?string $cost_override_reason = null;

    protected $casts = [
        'cost_override' => 'float',
    ];

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
            ->using(RecipeIngredient::class)
            ->withPivot(['quantity_per_portion', 'note'])
            ->withTimestamps();
    }

    public function recipeType(): BelongsTo
    {
        return $this->belongsTo(RecipeType::class, 'recipe_type_id');
    }

    public function recipeCostLogs(): HasMany
    {
        return $this->hasMany(RecipeCostLog::class)->orderBy('id', 'desc');
    }

    /**
     * Tương thích ngược: tự động đồng bộ nhóm món dạng chuỗi với bảng danh mục động.
     */
    public function getTypeAttribute(): ?string
    {
        return $this->recipeType?->name ?? $this->attributes['old_type'] ?? null;
    }

    public function setTypeAttribute(?string $value): void
    {
        if (filled($value)) {
            $type = RecipeType::firstOrCreate(['name' => trim($value)]);
            $this->attributes['recipe_type_id'] = $type->id;
        } else {
            $this->attributes['recipe_type_id'] = null;
        }
    }

    /**
     * Giá cost thực tế: nếu có cost override ➔ dùng cost override, ngược lại ➔ tự tính từ định mức nguyên liệu.
     */
    public function effectiveCostPerPortion(): float
    {
        if ($this->cost_override !== null && $this->cost_override !== '') {
            return (float) $this->cost_override;
        }

        // Load ingredients nếu chưa load
        if (! $this->relationLoaded('ingredients')) {
            $this->load('ingredients');
        }

        return (float) $this->ingredients->sum(function ($ingredient) {
            return (float) ($ingredient->pivot->quantity_per_portion ?? 0) * (float) ($ingredient->reference_price ?? 0);
        });
    }

    protected static function booted(): void
    {
        // Tự động ghi nhật ký biến động giá cost override khi được lưu
        static::saving(function (Recipe $recipe): void {
            if ($recipe->isDirty('cost_override')) {
                $oldValue = $recipe->getOriginal('cost_override');
                $newValue = $recipe->cost_override;

                // Lấy lý do thay đổi truyền qua model property
                $reason = $recipe->cost_override_reason ?: 'Điều chỉnh giá cost món ăn';

                // Chỉ ghi log nếu đã lưu bản ghi (có ID)
                if ($recipe->exists) {
                    $recipe->recipeCostLogs()->create([
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                        'reason' => $reason,
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        });
    }
}
