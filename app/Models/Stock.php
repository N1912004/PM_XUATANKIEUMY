<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'kitchen_id',
        'ingredient_id',
        'quantity',
        'frozen_quantity',
        'min_quantity',
        'unit_price',
    ];

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /**
     * Lượng tồn thực sự có thể xuất sử dụng = tồn hiện tại − lượng đang bị đóng băng (điều chuyển).
     */
    public function getAvailableQuantityAttribute(): float
    {
        return (float) $this->quantity - (float) $this->frozen_quantity;
    }

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }

    /**
     * Nhập kho ngoài (mua trực tiếp không qua PO): cộng tồn của bếp + sinh giao dịch
     * loại 'Nhập kho ngoài' kèm mã phiếu NX-EXT-YYYYMMDD-xxx và link chứng từ bắt buộc.
     */
    public static function recordExternalInbound(
        ?int $kitchenId,
        int $ingredientId,
        float $quantity,
        float $unitPrice,
        string $attachmentUrl,
        ?string $note = null,
    ): StockTransaction {
        $stock = self::firstOrCreate(
            ['kitchen_id' => $kitchenId, 'ingredient_id' => $ingredientId],
            ['quantity' => 0, 'min_quantity' => 10, 'unit_price' => $unitPrice],
        );

        $newQty = $stock->quantity + $quantity;
        $stock->update([
            'quantity' => $newQty,
            'unit_price' => $unitPrice > 0 ? $unitPrice : $stock->unit_price,
        ]);

        return StockTransaction::create([
            'kitchen_id' => $kitchenId,
            'ingredient_id' => $ingredientId,
            'type' => 'Nhập kho ngoài',
            'voucher_code' => self::generateExternalVoucherCode(),
            'quantity' => $quantity,
            'after_quantity' => $newQty,
            'note' => $note ?: 'Nhập kho ngoài (mua trực tiếp)',
            'attachment_url' => $attachmentUrl,
        ]);
    }

    protected static function generateExternalVoucherCode(): string
    {
        do {
            $code = 'NX-EXT-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        } while (StockTransaction::where('voucher_code', $code)->exists());

        return $code;
    }
}
