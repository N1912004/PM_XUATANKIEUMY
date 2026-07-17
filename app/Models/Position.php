<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Chức danh / chức vụ của nhân sự.
 */
class Position extends Model
{
    protected $fillable = ['name', 'sort', 'active'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * @return array<int, string> [id => name] các chức danh đang bật, theo thứ tự cấu hình.
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
