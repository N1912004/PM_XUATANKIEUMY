<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Menu extends Model
{
    use HasFactory;

    /**
     * Các trường được ghi vết khi thực đơn đã chốt/đã gửi bị chỉnh sửa.
     *
     * @var array<int, string>
     */
    protected const AUDITED_FIELDS = ['kitchen_id', 'week_menu_id', 'day_menu_id', 'date', 'shift_id', 'recipe_id', 'estimated_portions', 'status'];

    protected $fillable = [
        'kitchen_id',
        'week_menu_id',
        'day_menu_id',
        'date',
        'shift_id',
        'recipe_id',
        'estimated_portions',
        'status',
    ];

    /**
     * Các trạng thái "đã hoàn tất" mà mọi thay đổi sau đó đều bắt buộc ghi vết.
     *
     * @var array<int, string>
     */
    public const FINALIZED_STATUSES = ['sent', 'locked'];

    /**
     * Thứ bậc vòng đời trạng thái (BA 07/07/2026): không được hạ cấp trạng thái
     * khi lưu lại — chỉ đi tiến Nháp → Đã gửi khách hàng → Đã chốt.
     *
     * @var array<string, int>
     */
    public const STATUS_ORDER = ['draft' => 0, 'sent' => 1, 'locked' => 2];

    /**
     * Nhãn hiển thị tiếng Việt của từng trạng thái.
     *
     * @var array<string, string>
     */
    public const STATUS_LABELS = [
        'draft' => 'Nháp',
        'sent' => 'Đã gửi khách hàng',
        'locked' => 'Đã chốt',
    ];

    /**
     * Lý do sửa (transient — không lưu vào bảng menus) do trang lập thực đơn gán trước khi
     * update; hook audit đọc và ghi vào menu_audit_logs.reason.
     */
    public ?string $auditReason = null;

    /**
     * Thực đơn quá khứ đã hoàn thành (đã chốt + ngày cũ) bị khóa cứng, không cho sửa/xóa.
     */
    public function isPastLocked(): bool
    {
        return false;
    }

    /**
     * Bộ guard vòng đời dùng chung cho mọi màn sửa thực đơn: trả về lý do bị chặn
     * ('downgrade' | 'need_reason') hoặc null nếu được phép ghi.
     * Dùng một chỗ duy nhất để 3 trang lập thực đơn không lệch quy tắc.
     */
    public function editBlockReason(string $newStatus, ?string $reason): ?string
    {
        if (! array_key_exists($newStatus, self::STATUS_ORDER)) {
            return 'invalid_status';
        }

        $currentStatus = $this->exists
            ? (string) $this->getRawOriginal('status')
            : (string) $this->status;

        if (self::STATUS_ORDER[$newStatus] < (self::STATUS_ORDER[$currentStatus] ?? PHP_INT_MAX)) {
            return 'downgrade';
        }

        if ($currentStatus === 'locked' && trim((string) $reason) === '') {
            return 'need_reason';
        }

        return null;
    }

    protected $casts = [
        'date' => 'date',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function dayMenu(): BelongsTo
    {
        return $this->belongsTo(DayMenu::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function weekMenu(): BelongsTo
    {
        return $this->belongsTo(WeekMenu::class, 'week_menu_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(MenuAuditLog::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Menu $menu): void {
            $blocked = $menu->exists
                ? $menu->editBlockReason((string) $menu->status, $menu->auditReason)
                : (array_key_exists((string) $menu->status, self::STATUS_ORDER) ? null : 'invalid_status');

            if ($blocked === null) {
                return;
            }

            throw ValidationException::withMessages([
                $blocked === 'need_reason' ? 'edit_reason' : 'status' => match ($blocked) {
                    'past' => __('menu.errors.past_locked_edit'),
                    'downgrade' => __('menu.errors.status_downgrade'),
                    'need_reason' => __('menu.errors.audit_reason_required'),
                    default => __('menu.errors.invalid_status'),
                },
            ]);
        });

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
                    'reason' => $menu->auditReason,
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
                'reason' => $menu->auditReason,
                'edited_at' => now(),
            ]);
        });
    }
}
