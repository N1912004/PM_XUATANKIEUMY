<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Loại nhà ăn / bếp — bảng riêng thay thế nhóm kitchen_type trong catalogs.
 */
class KitchenType extends Model
{
    protected $fillable = ['name', 'sort', 'active'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function kitchens(): HasMany
    {
        return $this->hasMany(Kitchen::class);
    }

    /**
     * Options cho Select/filter: chỉ lấy mục đang bật, theo thứ tự cấu hình.
     *
     * @return array<int, string> [id => name]
     */
    public static function options(): array
    {
        return static::query()
            ->where('active', true)
            ->orderBy('sort')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
