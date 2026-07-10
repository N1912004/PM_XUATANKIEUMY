<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Menu extends Model
{
    use HasFactory;

    /**
     * Các trường được ghi vết khi thực đơn đã chốt/đã gửi bị chỉnh sửa.
     *
     * @var array<int, string>
     */
    protected const AUDITED_FIELDS = ['date', 'shift_id', 'recipe_id', 'estimated_portions', 'status'];

    /**
     * Các trạng thái "đã hoàn tất" mà mọi thay đổi sau đó đều bắt buộc ghi vết.
     *
     * @var array<int, string>
     */
    protected const FINALIZED_STATUSES = ['sent', 'locked'];

    protected $fillable = [
        'kitchen_id',
        'date',
        'shift_id',
        'recipe_id',
        'estimated_portions',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(MenuAuditLog::class);
    }

    protected static function booted(): void
    {
        // Ghi vết mọi thay đổi trên thực đơn ĐÃ CHỐT / ĐÃ GỬI (ai sửa, sửa gì, lúc nào)
        static::updated(function (Menu $menu): void {
            $originalStatus = $menu->getOriginal('status');
            if (! in_array($originalStatus, self::FINALIZED_STATUSES, true)) {
                return;
            }

            // Gom các field thay đổi thành 1 lệnh insert duy nhất thay vì N insert nhỏ
            $now = now();
            $rows = [];
            foreach (self::AUDITED_FIELDS as $field) {
                if (! $menu->wasChanged($field)) {
                    continue;
                }

                $rows[] = [
                    'menu_id' => $menu->id,
                    'user_id' => Auth::id(),
                    'action' => 'updated',
                    'field' => $field,
                    'old_value' => (string) $menu->getOriginal($field),
                    'new_value' => (string) $menu->getAttribute($field),
                    'edited_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows !== []) {
                MenuAuditLog::insert($rows);
            }
        });

        static::deleted(function (Menu $menu): void {
            if (! in_array($menu->getOriginal('status'), self::FINALIZED_STATUSES, true)) {
                return;
            }

            // menu_id để null vì bản ghi menu đã bị xóa (FK), lưu id vào old_value để tra cứu
            MenuAuditLog::create([
                'menu_id' => null,
                'user_id' => Auth::id(),
                'action' => 'deleted',
                'field' => null,
                'old_value' => 'Thực đơn #'.$menu->id,
                'new_value' => null,
                'edited_at' => now(),
            ]);
        });
    }
}
