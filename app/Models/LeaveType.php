<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Loại nghỉ phép hoặc loại tăng ca.
 */
class LeaveType extends Model
{
    protected $fillable = ['name', 'is_ot', 'sort', 'active'];

    protected function casts(): array
    {
        return [
            'is_ot' => 'boolean',
            'active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function leaveOvertimes(): HasMany
    {
        return $this->hasMany(LeaveOvertime::class);
    }

    /**
     * @param  bool|null  $isOt  null: mọi loại; true: chỉ loại tăng ca; false: chỉ loại nghỉ.
     * @return array<int, string> [id => name] các loại đang bật, theo thứ tự cấu hình.
     */
    public static function options(?bool $isOt = null): array
    {
        return static::query()
            ->where('active', true)
            ->when($isOt !== null, fn ($query) => $query->where('is_ot', $isOt))
            ->orderBy('sort')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
