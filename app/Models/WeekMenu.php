<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeekMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'kitchen_id',
        'date_from',
        'date_to',
        'status',
        'audit_reason',
    ];

    public const FINALIZED_STATUSES = ['sent', 'locked'];

    public const STATUS_ORDER = ['draft' => 0, 'sent' => 1, 'locked' => 2];

    public const STATUS_LABELS = [
        'draft' => 'Nháp',
        'sent' => 'Đã gửi khách hàng',
        'locked' => 'Đã chốt',
    ];

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class, 'week_menu_id');
    }

    public function isPastLocked(): bool
    {
        return false;
    }

    public function editBlockReason(string $targetStatus, ?string $reason): ?string
    {
        $currentRank = self::STATUS_ORDER[$this->status] ?? 0;
        $targetRank = self::STATUS_ORDER[$targetStatus] ?? 0;

        if ($targetRank < $currentRank) {
            return 'downgrade';
        }

        if ($this->status === 'locked' && empty(trim((string) $reason))) {
            return 'need_reason';
        }

        return null;
    }
}
