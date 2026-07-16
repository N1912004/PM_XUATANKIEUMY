<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kitchen extends Model
{
    protected $fillable = [
        'area_id',
        'name',
        'type',
        'kitchen_type_id',
        'capacity',
        'manager_id',
        'status',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function kitchenType(): BelongsTo
    {
        return $this->belongsTo(KitchenType::class);
    }

    /**
     * Tương thích ngược chiều ĐỌC: code cũ (seeder, test, export) đọc $kitchen->type.
     */
    public function getTypeAttribute(): ?string
    {
        return $this->kitchenType?->name;
    }

    /**
     * Tương thích ngược chiều GHI: `'type' => 'Bếp trung tâm'` được ánh xạ sang
     * kitchen_type_id, tự tạo bản ghi loại nếu chưa có — giống Ingredient/Recipe.
     */
    public function setTypeAttribute(?string $value): void
    {
        $this->attributes['kitchen_type_id'] = filled($value)
            ? KitchenType::firstOrCreate(['name' => trim($value)])->id
            : null;
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
