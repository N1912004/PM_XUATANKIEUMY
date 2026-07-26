<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class StockTransfer extends Model
{
    public const STATUS_IN_TRANSIT = 'in_transit';

    public const STATUS_DONE = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'code',
        'source_kitchen_id',
        'dest_kitchen_id',
        'status',
        'created_by',
        'received_by',
        'note',
    ];

    public function sourceKitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class, 'source_kitchen_id');
    }

    public function destKitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class, 'dest_kitchen_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    /**
     * Đóng băng lượng tồn tại bếp xuất khi tạo phiếu (không cho xuất sử dụng phần đang chuyển).
     *
     * @throws \RuntimeException khi tồn khả dụng không đủ để đóng băng — rollback toàn bộ phiếu
     */
    public function freezeSourceStock(): void
    {
        DB::transaction(function (): void {
            foreach ($this->items()->with('ingredient')->get() as $item) {
                $stock = Stock::query()
                    ->where('kitchen_id', $this->source_kitchen_id)
                    ->where('ingredient_id', $item->ingredient_id)
                    ->lockForUpdate()
                    ->first();

                $available = $stock ? ((float) $stock->quantity - (float) $stock->frozen_quantity) : 0.0;
                if ($available < (float) $item->quantity) {
                    $name = $item->ingredient?->name ?? "nguyên liệu #{$item->ingredient_id}";

                    throw new \RuntimeException("Tồn khả dụng của {$name} không đủ để điều chuyển (còn {$available}).");
                }

                $stock->increment('frozen_quantity', $item->quantity);
            }
        });
    }

    /**
     * Bếp nhận xác nhận: chính thức trừ tồn bếp xuất, cộng tồn bếp nhận, sinh giao dịch 2 đầu.
     */
    public function confirmReceived(?int $receivedBy = null): bool
    {
        return DB::transaction(function () use ($receivedBy): bool {
            // Guard status phải nằm TRONG transaction trên bản ghi đã khóa:
            // 2 người cùng bấm xác nhận thì người sau chờ lock, đọc lại status = Hoàn thành và dừng.
            $locked = self::whereKey($this->id)->lockForUpdate()->first();
            if (! $locked || $locked->status !== self::STATUS_IN_TRANSIT) {
                return false;
            }

            foreach ($this->items as $item) {
                $received = $item->received_quantity ?? $item->quantity;

                // Bếp xuất: trừ tồn thật + nhả đóng băng + giao dịch xuất chuyển
                $source = Stock::query()
                    ->where('kitchen_id', $this->source_kitchen_id)
                    ->where('ingredient_id', $item->ingredient_id)
                    ->lockForUpdate()
                    ->first();

                if (! $source) {
                    $source = Stock::create([
                        'kitchen_id' => $this->source_kitchen_id,
                        'ingredient_id' => $item->ingredient_id,
                        'quantity' => 0,
                        'min_quantity' => 10,
                        'unit_price' => 0,
                    ]);
                }

                $sourceQty = max(0, (float) $source->quantity - (float) $item->quantity);
                $sourceFrozen = max(0, (float) $source->frozen_quantity - (float) $item->quantity);
                $source->update(['quantity' => $sourceQty, 'frozen_quantity' => $sourceFrozen]);

                StockTransaction::create([
                    'kitchen_id' => $this->source_kitchen_id,
                    'ingredient_id' => $item->ingredient_id,
                    'type' => 'Xuất chuyển kho',
                    'voucher_code' => $this->code,
                    'quantity' => -$item->quantity,
                    'after_quantity' => $sourceQty,
                    'note' => 'Điều chuyển đến bếp #'.$this->dest_kitchen_id,
                ]);

                // Bếp nhận: cộng tồn thật + giao dịch nhập chuyển
                $dest = Stock::query()
                    ->where('kitchen_id', $this->dest_kitchen_id)
                    ->where('ingredient_id', $item->ingredient_id)
                    ->lockForUpdate()
                    ->first();

                if (! $dest) {
                    $dest = Stock::create([
                        'kitchen_id' => $this->dest_kitchen_id,
                        'ingredient_id' => $item->ingredient_id,
                        'quantity' => 0,
                        'min_quantity' => 10,
                        'unit_price' => 0,
                    ]);
                }

                $destQty = (float) $dest->quantity + (float) $received;
                $dest->update(['quantity' => $destQty]);

                StockTransaction::create([
                    'kitchen_id' => $this->dest_kitchen_id,
                    'ingredient_id' => $item->ingredient_id,
                    'type' => 'Nhập chuyển kho',
                    'voucher_code' => $this->code,
                    'quantity' => $received,
                    'after_quantity' => $destQty,
                    'note' => 'Nhận điều chuyển từ bếp #'.$this->source_kitchen_id,
                ]);

                $item->update(['received_quantity' => $received]);
            }

            $this->update([
                'status' => self::STATUS_DONE,
                'received_by' => $receivedBy ?? $this->received_by,
            ]);

            return true;
        });
    }

    /**
     * Hủy phiếu: chỉ nhả lượng đóng băng về lại bếp xuất, không thay đổi tồn thật.
     */
    public function cancel(): bool
    {
        return DB::transaction(function (): bool {
            // Guard status trong transaction + lock (chống hủy trùng / hủy song song với xác nhận nhận)
            $locked = self::whereKey($this->id)->lockForUpdate()->first();
            if (! $locked || $locked->status !== self::STATUS_IN_TRANSIT) {
                return false;
            }

            foreach ($this->items as $item) {
                $stock = Stock::where('kitchen_id', $this->source_kitchen_id)
                    ->where('ingredient_id', $item->ingredient_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->update([
                        'frozen_quantity' => max(0, (float) $stock->frozen_quantity - (float) $item->quantity),
                    ]);
                }
            }

            $this->update(['status' => self::STATUS_CANCELLED]);

            return true;
        });
    }
}
