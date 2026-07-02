<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveOvertime extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'start_date',
        'end_date',
        'duration_text',
        'reason',
        'approver_id',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }
}
