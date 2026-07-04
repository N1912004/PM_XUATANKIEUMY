<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'supplier_id',
        'status',
        'estimated_delivery_date',
        'note',
    ];

    protected $casts = [
        'estimated_delivery_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    protected static function booted(): void
    {
        static::updated(function (PurchaseOrder $purchaseOrder) {
            // Check if status changed to 'done'
            if ($purchaseOrder->wasChanged('status') && $purchaseOrder->status === 'done') {
                foreach ($purchaseOrder->items as $item) {
                    $stock = Stock::firstOrCreate(
                        ['ingredient_id' => $item->ingredient_id],
                        [
                            'quantity' => 0,
                            'min_quantity' => 10,
                            'unit_price' => $item->unit_price,
                        ]
                    );

                    $oldQty = $stock->quantity;
                    $newQty = $oldQty + $item->quantity_received;

                    $stock->update([
                        'quantity' => $newQty,
                        'unit_price' => $item->unit_price > 0 ? $item->unit_price : $stock->unit_price,
                    ]);

                    StockTransaction::create([
                        'ingredient_id' => $item->ingredient_id,
                        'type' => 'Nhập kho',
                        'quantity' => $item->quantity_received,
                        'after_quantity' => $newQty,
                        'note' => "Nhập kho tự động từ đơn đặt hàng: {$purchaseOrder->code}",
                    ]);
                }
            }
        });
    }
}
