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
        'kitchen_id',
        'supplier_id',
        'status',
        'stocked_at',
        'estimated_delivery_date',
        'note',
    ];

    protected $casts = [
        'estimated_delivery_date' => 'date',
        'stocked_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    protected static function booted(): void
    {
        static::updated(function (PurchaseOrder $purchaseOrder) {
            // Chỉ tự động nhập kho 1 LẦN DUY NHẤT: khi đơn chuyển sang 'done' và chưa từng nhập kho.
            // Cờ stocked_at chống nhập lặp nếu trạng thái đổi done → khác → done.
            if ($purchaseOrder->wasChanged('status')
                && $purchaseOrder->status === 'done'
                && is_null($purchaseOrder->stocked_at)) {
                foreach ($purchaseOrder->items as $item) {
                    // Tồn kho theo từng bếp: khóa theo (kitchen_id, ingredient_id)
                    $stock = Stock::firstOrCreate(
                        [
                            'kitchen_id' => $purchaseOrder->kitchen_id,
                            'ingredient_id' => $item->ingredient_id,
                        ],
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
                        'kitchen_id' => $purchaseOrder->kitchen_id,
                        'ingredient_id' => $item->ingredient_id,
                        'type' => 'Nhập kho',
                        'quantity' => $item->quantity_received,
                        'after_quantity' => $newQty,
                        'note' => "Nhập kho tự động từ đơn đặt hàng: {$purchaseOrder->code}",
                    ]);
                }

                // Đánh dấu đã nhập kho (updateQuietly để không kích hoạt lại hook updated)
                $purchaseOrder->updateQuietly(['stocked_at' => now()]);
            }
        });
    }
}
