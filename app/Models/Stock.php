<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

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

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'frozen_quantity' => 'float',
            'min_quantity' => 'float',
            'unit_price' => 'float',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class)->withTrashed();
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
        // Toàn bộ cộng tồn + ghi thẻ kho phải nguyên tử: lỗi giữa chừng thì rollback cả hai,
        // và lockForUpdate chặn lost-update khi 2 phiếu nhập cùng (bếp, nguyên liệu) chạy song song.
        return DB::transaction(function () use ($kitchenId, $ingredientId, $quantity, $unitPrice, $attachmentUrl, $note): StockTransaction {
            $stock = self::query()
                ->where('kitchen_id', $kitchenId)
                ->where('ingredient_id', $ingredientId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $stock = self::create([
                    'kitchen_id' => $kitchenId,
                    'ingredient_id' => $ingredientId,
                    'quantity' => 0,
                    'min_quantity' => 10,
                    'unit_price' => $unitPrice,
                ]);
            }

            $newQty = (float) $stock->quantity + $quantity;
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
        });
    }

    protected static function generateExternalVoucherCode(): string
    {
        // Sinh tuần tự theo ngày (max hiện có + 1) thay vì random_int(1,999):
        // hết dải số không lặp vô hạn, xác suất trùng chỉ còn ở mức 2 transaction cùng mili-giây.
        $prefix = 'NX-EXT-'.now()->format('Ymd').'-';
        $last = StockTransaction::where('voucher_code', 'like', $prefix.'%')
            ->orderByDesc('voucher_code')
            ->value('voucher_code');
        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
