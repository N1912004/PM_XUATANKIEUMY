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
        'gender',
        'dob',
        'id_card',
        'id_card_date',
        'id_card_place',
        'marital_status',
        'nationality',
        'ethnic',
        'religion',
        'permanent_address',
        'temporary_address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'sub_department',
        'level',
        'work_type',
        'manager_id',
        'documents',
    ];

    protected $casts = [
        'dob' => 'date',
        'id_card_date' => 'date',
        'start_date' => 'date',
        'documents' => 'array',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
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
