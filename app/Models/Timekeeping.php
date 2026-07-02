<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timekeeping extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'shift_id',
        'check_in',
        'check_out',
        'overtime_hours',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
