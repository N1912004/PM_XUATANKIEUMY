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
        'recipe_id',
        'stage',
        'status',
        'inspected_by',
        'cook_start_at',
        'cook_end_at',
        'temperature',
        'sample_kept_by',
        'sample_kept_at',
        'sample_code',
        'utensil',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'sample_kept_at' => 'datetime',
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
