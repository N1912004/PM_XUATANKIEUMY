<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'ingredient_id',
        'quantity_ordered',
        'quantity_received',
        'unit_price',
        'receive_note',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class)->withTrashed();
    }

    public function setQuantityOrderedAttribute($value): void
    {
        $this->attributes['quantity_ordered'] = max(0, (float) $value);
    }

    public function setQuantityReceivedAttribute($value): void
    {
        $this->attributes['quantity_received'] = max(0, (float) $value);
    }
}
