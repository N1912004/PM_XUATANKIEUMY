<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'kitchen_id',
        'type',
        'voucher_code',
        'ingredient_id',
        'quantity',
        'after_quantity',
        'note',
        'attachment_url',
        'created_by',
    ];

    protected static function booted(): void
    {
        // Tự truy vết người thao tác cho mọi dòng thẻ kho tạo trong ngữ cảnh web
        static::creating(function (StockTransaction $transaction): void {
            $transaction->created_by ??= auth()->id();
        });
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
