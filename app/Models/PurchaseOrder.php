<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Model
{
    /** Nhãn hiển thị của vòng đời PO — nguồn DUY NHẤT, dùng chung cho UI và các file xuất. */
    public const STATUS_LABELS = [
        'draft' => 'Nháp',
        'sent' => 'Đã gửi NCC',
        'checking' => 'Đang kiểm hàng',
        'done' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];

    use HasFactory;

    protected $fillable = [
        'code',
        'kitchen_id',
        'supplier_id',
        'status',
        'type',
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
            if (! ($purchaseOrder->wasChanged('status')
                && $purchaseOrder->status === 'done'
                && is_null($purchaseOrder->stocked_at))) {
                return;
            }

            DB::transaction(function () use ($purchaseOrder): void {
                // Claim atomic cờ stocked_at: 2 request đồng thời thì chỉ 1 bên UPDATE được dòng
                // còn NULL — bên kia nhận affected = 0 và bỏ qua, chặn nhập kho lặp (double-receive).
                $claimed = self::whereKey($purchaseOrder->id)
                    ->whereNull('stocked_at')
                    ->update(['stocked_at' => now()]);

                if ($claimed === 0) {
                    return;
                }

                foreach ($purchaseOrder->items()->get() as $item) {
                    // Khóa dòng tồn kho (kitchen_id, ingredient_id) để tránh lost-update với luồng khác
                    $stock = Stock::query()
                        ->where('kitchen_id', $purchaseOrder->kitchen_id)
                        ->where('ingredient_id', $item->ingredient_id)
                        ->lockForUpdate()
                        ->first();

                    if (! $stock) {
                        $stock = Stock::create([
                            'kitchen_id' => $purchaseOrder->kitchen_id,
                            'ingredient_id' => $item->ingredient_id,
                            'quantity' => 0,
                            'min_quantity' => 10,
                            'unit_price' => $item->unit_price,
                        ]);
                    }

                    $newQty = (float) $stock->quantity + (float) $item->quantity_received;

                    $stock->update([
                        'quantity' => $newQty,
                        'unit_price' => $item->unit_price > 0 ? $item->unit_price : $stock->unit_price,
                    ]);

                    StockTransaction::create([
                        'kitchen_id' => $purchaseOrder->kitchen_id,
                        'ingredient_id' => $item->ingredient_id,
                        'type' => 'Nhập kho',
                        'voucher_code' => $purchaseOrder->code,
                        'quantity' => $item->quantity_received,
                        'after_quantity' => $newQty,
                        'note' => "Nhập kho tự động từ đơn đặt hàng: {$purchaseOrder->code}",
                    ]);
                }
            });

            $purchaseOrder->refresh();
        });
    }
}
