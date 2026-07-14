<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuAuditLog extends Model
{
    protected $fillable = [
        'menu_id',
        'user_id',
        'action',
        'field',
        'old_value',
        'new_value',
        'reason',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
