<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'time_from',
        'time_to',
        'time_range',
    ];

    protected static function booted(): void
    {
        static::saving(function (Shift $shift): void {
            $timeFrom = static::normalizeTime($shift->time_from);
            $timeTo = static::normalizeTime($shift->time_to);

            if ($timeFrom === null || $timeTo === null) {
                throw ValidationException::withMessages([
                    'time_from' => __('catalog.shift.validation.time_required'),
                    'time_to' => __('catalog.shift.validation.time_required'),
                ]);
            }

            if ($timeFrom === $timeTo) {
                throw ValidationException::withMessages([
                    'time_to' => __('catalog.shift.validation.different_times'),
                ]);
            }

            if (static::durationMinutes($timeFrom, $timeTo) > 12 * 60) {
                throw ValidationException::withMessages([
                    'time_to' => __('catalog.shift.validation.max_duration'),
                ]);
            }

            $shift->time_from = $timeFrom;
            $shift->time_to = $timeTo;
        });
    }

    public function setTimeRangeAttribute(?string $value): void
    {
        if (is_string($value) && preg_match('/^\s*([01]\d|2[0-3]):([0-5]\d)\s*-\s*([01]\d|2[0-3]):([0-5]\d)\s*$/', $value, $matches)) {
            $this->attributes['time_from'] = "{$matches[1]}:{$matches[2]}:00";
            $this->attributes['time_to'] = "{$matches[3]}:{$matches[4]}:00";

            return;
        }

        $this->attributes['time_from'] = null;
        $this->attributes['time_to'] = null;
    }

    public function getTimeRangeAttribute(): string
    {
        if ($this->time_from === null || $this->time_to === null) {
            return '';
        }

        return substr($this->time_from, 0, 5).' - '.substr($this->time_to, 0, 5);
    }

    private static function normalizeTime(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        if (! preg_match('/^([01]\d|2[0-3]):([0-5]\d)(?::[0-5]\d)?$/', trim($value), $matches)) {
            return null;
        }

        return "{$matches[1]}:{$matches[2]}:00";
    }

    private static function durationMinutes(string $timeFrom, string $timeTo): int
    {
        $from = static::timeToMinutes($timeFrom);
        $to = static::timeToMinutes($timeTo);

        return ($to - $from + (24 * 60)) % (24 * 60);
    }

    private static function timeToMinutes(string $time): int
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));

        return ($hour * 60) + $minute;
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }
}
