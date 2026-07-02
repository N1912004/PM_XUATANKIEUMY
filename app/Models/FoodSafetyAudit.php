<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodSafetyAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'shift_id',
        'stage',
        'status',
        'inspected_by',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
