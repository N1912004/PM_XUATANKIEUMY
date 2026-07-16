<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveOvertime extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'leave_type_id',
        'start_date',
        'end_date',
        'duration_text',
        'reason',
        'approver_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Tương thích ngược chiều ĐỌC: code cũ (seeder, export) đọc $leaveOvertime->type.
     */
    public function getTypeAttribute(): ?string
    {
        return $this->leaveType?->name;
    }

    /**
     * Tương thích ngược chiều GHI: `'type' => 'Nghỉ phép năm'` ánh xạ sang leave_type_id,
     * tự tạo loại nếu chưa có (đặt is_ot theo tên) — giống Kitchen/Ingredient/Recipe.
     */
    public function setTypeAttribute(?string $value): void
    {
        if (blank($value)) {
            $this->attributes['leave_type_id'] = null;

            return;
        }

        $name = trim($value);
        $isOt = str_contains($name, 'Tăng ca') || str_contains($name, 'tăng ca') || str_contains($name, 'OT') || str_contains($name, 'ot');

        $this->attributes['leave_type_id'] = LeaveType::firstOrCreate(
            ['name' => $name],
            ['is_ot' => $isOt],
        )->id;
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }
}
