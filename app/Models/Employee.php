<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'department',
        'position',
        'area_id',
        'kitchen_id',
        'start_date',
        'status',
        'avatar_url',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function timekeepings()
    {
        return $this->hasMany(Timekeeping::class);
    }

    public function leaveOvertimes()
    {
        return $this->hasMany(LeaveOvertime::class);
    }
}
