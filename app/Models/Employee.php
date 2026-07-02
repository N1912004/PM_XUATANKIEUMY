<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'department',
        'position',
        'area',
        'start_date',
        'status',
        'avatar_url',
    ];

    public function timekeepings()
    {
        return $this->hasMany(Timekeeping::class);
    }

    public function leaveOvertimes()
    {
        return $this->hasMany(LeaveOvertime::class);
    }
}
