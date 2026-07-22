<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class DayMenu extends Model
{
    use HasFactory;

    protected $fillable = ['kitchen_id', 'date', 'status', 'audit_reason'];

    protected $casts = ['date' => 'date'];

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function isPastLocked(): bool
    {
        return $this->status === 'locked' && Carbon::parse($this->date)->isBefore(today());
    }

    public function editBlockReason(string $targetStatus, ?string $reason): ?string
    {
        if ($this->isPastLocked()) {
            return 'past';
        }

        $currentRank = Menu::STATUS_ORDER[$this->status] ?? 0;
        $targetRank = Menu::STATUS_ORDER[$targetStatus] ?? 0;

        if ($targetRank < $currentRank) {
            return 'downgrade';
        }

        return $this->status === 'locked' && trim((string) $reason) === '' ? 'need_reason' : null;
    }
}
