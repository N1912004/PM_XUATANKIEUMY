<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Danh mục cấu hình động dùng chung (BA R37) — một bảng, nhiều nhóm.
 *
 * Thêm một danh mục mới: khai báo hằng số nhóm + nhãn trong GROUP_LABELS, rồi gọi
 * `Catalog::options(Catalog::TÊN_NHÓM)` ở form/filter. Không hard-code mảng options nữa.
 */
class Catalog extends Model
{
    public const DEPARTMENT = 'department';

    public const POSITION = 'position';

    public const LEAVE_TYPE = 'leave_type';

    /** @var array<string, string> */
    public const GROUP_LABELS = [
        self::DEPARTMENT => 'Phòng ban',
        self::POSITION => 'Chức danh / chức vụ',
        self::LEAVE_TYPE => 'Loại nghỉ phép / tăng ca',
    ];

    protected $fillable = ['group', 'name', 'sort', 'active'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * Options cho Select/filter: chỉ lấy mục đang bật, theo thứ tự cấu hình.
     *
     * @return array<string, string>
     */
    public static function options(string $group): array
    {
        return static::query()
            ->group($group)
            ->where('active', true)
            ->orderBy('sort')
            ->orderBy('name')
            ->pluck('name', 'name')
            ->all();
    }
}
