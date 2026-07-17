<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'department',
        'position',
        'department_id',
        'position_id',
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Tương thích ngược chiều GHI: code cũ (seeder, test, import) tạo nhân viên bằng chuỗi
     * `'department' => 'Kho'` — mutator ánh xạ sang department_id (tự tạo bản ghi danh mục
     * nếu chưa có). Không thêm accessor đọc vì tên trùng relation department().
     */
    public function setDepartmentAttribute(?string $value): void
    {
        $this->attributes['department_id'] = filled($value)
            ? Department::firstOrCreate(['name' => trim($value)])->id
            : null;
    }

    public function setPositionAttribute(?string $value): void
    {
        $this->attributes['position_id'] = filled($value)
            ? Position::firstOrCreate(['name' => trim($value)])->id
            : null;
    }

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }

    /** Tài khoản đăng nhập gắn với hồ sơ nhân viên này (1-1, có thể chưa có). */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function timekeepings(): HasMany
    {
        return $this->hasMany(Timekeeping::class);
    }

    public function leaveOvertimes(): HasMany
    {
        return $this->hasMany(LeaveOvertime::class);
    }
}
