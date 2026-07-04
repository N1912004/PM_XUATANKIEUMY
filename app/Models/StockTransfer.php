<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class StockTransfer extends Model
{
    public const STATUS_IN_TRANSIT = 'Đang chuyển';

    public const STATUS_DONE = 'Hoàn thành';

    public const STATUS_CANCELLED = 'Hủy';

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
     */
    public function freezeSourceStock(): void
    {
        DB::transaction(function (): void {
            foreach ($this->items as $item) {
                $stock = Stock::firstOrCreate(
                    ['kitchen_id' => $this->source_kitchen_id, 'ingredient_id' => $item->ingredient_id],
                    ['quantity' => 0, 'min_quantity' => 10, 'unit_price' => 0],
                );
                $stock->increment('frozen_quantity', $item->quantity);
            }
        });
    }

    /**
     * Bếp nhận xác nhận: chính thức trừ tồn bếp xuất, cộng tồn bếp nhận, sinh giao dịch 2 đầu.
     */
    public function confirmReceived(?int $receivedBy = null): void
    {
        if ($this->status !== self::STATUS_IN_TRANSIT) {
            return;
        }

        DB::transaction(function () use ($receivedBy): void {
            foreach ($this->items as $item) {
                $received = $item->received_quantity ?? $item->quantity;

                // Bếp xuất: trừ tồn thật + nhả đóng băng + giao dịch xuất chuyển
                $source = Stock::firstOrCreate(
                    ['kitchen_id' => $this->source_kitchen_id, 'ingredient_id' => $item->ingredient_id],
                    ['quantity' => 0, 'min_quantity' => 10, 'unit_price' => 0],
                );
                $source->decrement('quantity', $item->quantity);
                $source->decrement('frozen_quantity', $item->quantity);

                StockTransaction::create([
                    'kitchen_id' => $this->source_kitchen_id,
                    'ingredient_id' => $item->ingredient_id,
                    'type' => 'Xuất chuyển kho',
                    'voucher_code' => $this->code,
                    'quantity' => -$item->quantity,
                    'after_quantity' => $source->fresh()->quantity,
                    'note' => 'Điều chuyển đến bếp #'.$this->dest_kitchen_id,
                ]);

                // Bếp nhận: cộng tồn thật + giao dịch nhập chuyển
                $dest = Stock::firstOrCreate(
                    ['kitchen_id' => $this->dest_kitchen_id, 'ingredient_id' => $item->ingredient_id],
                    ['quantity' => 0, 'min_quantity' => 10, 'unit_price' => 0],
                );
                $dest->increment('quantity', $received);

                StockTransaction::create([
                    'kitchen_id' => $this->dest_kitchen_id,
                    'ingredient_id' => $item->ingredient_id,
                    'type' => 'Nhập chuyển kho',
                    'voucher_code' => $this->code,
                    'quantity' => $received,
                    'after_quantity' => $dest->fresh()->quantity,
                    'note' => 'Nhận điều chuyển từ bếp #'.$this->source_kitchen_id,
                ]);

                $item->update(['received_quantity' => $received]);
            }

            $this->update([
                'status' => self::STATUS_DONE,
                'received_by' => $receivedBy ?? $this->received_by,
            ]);
        });
    }

    /**
     * Hủy phiếu: chỉ nhả lượng đóng băng về lại bếp xuất, không thay đổi tồn thật.
     */
    public function cancel(): void
    {
        if ($this->status !== self::STATUS_IN_TRANSIT) {
            return;
        }

        DB::transaction(function (): void {
            foreach ($this->items as $item) {
                $stock = Stock::where('kitchen_id', $this->source_kitchen_id)
                    ->where('ingredient_id', $item->ingredient_id)
                    ->first();
                $stock?->decrement('frozen_quantity', $item->quantity);
            }

            $this->update(['status' => self::STATUS_CANCELLED]);
        });
    }
}
