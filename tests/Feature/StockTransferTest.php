<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Ingredient;
use App\Models\Kitchen;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_transfer_freezes_source_stock(): void
    {
        [$source, $dest, $ingredient] = $this->scenario(sourceQty: 50);

        $transfer = $this->makeTransfer($source, $dest, $ingredient, 20);
        $transfer->freezeSourceStock();

        $stock = Stock::where('kitchen_id', $source->id)->where('ingredient_id', $ingredient->id)->first();
        $this->assertEquals(20, $stock->frozen_quantity);
        $this->assertEquals(30, $stock->available_quantity); // 50 - 20 đóng băng
        $this->assertEquals(50, $stock->quantity); // tồn thật chưa đổi
    }

    public function test_confirming_receipt_moves_stock_between_kitchens(): void
    {
        [$source, $dest, $ingredient] = $this->scenario(sourceQty: 50);

        $transfer = $this->makeTransfer($source, $dest, $ingredient, 20);
        $transfer->freezeSourceStock();
        $transfer->confirmReceived();

        $sourceStock = Stock::where('kitchen_id', $source->id)->where('ingredient_id', $ingredient->id)->first();
        $destStock = Stock::where('kitchen_id', $dest->id)->where('ingredient_id', $ingredient->id)->first();

        $this->assertEquals(30, $sourceStock->quantity);
        $this->assertEquals(0, $sourceStock->frozen_quantity);
        $this->assertEquals(20, $destStock->quantity);
        $this->assertSame(StockTransfer::STATUS_DONE, $transfer->fresh()->status);

        $this->assertDatabaseHas('stock_transactions', ['kitchen_id' => $source->id, 'type' => 'Xuất chuyển kho']);
        $this->assertDatabaseHas('stock_transactions', ['kitchen_id' => $dest->id, 'type' => 'Nhập chuyển kho']);
    }

    public function test_cancelling_transfer_releases_frozen_without_moving_stock(): void
    {
        [$source, $dest, $ingredient] = $this->scenario(sourceQty: 50);

        $transfer = $this->makeTransfer($source, $dest, $ingredient, 20);
        $transfer->freezeSourceStock();
        $transfer->cancel();

        $sourceStock = Stock::where('kitchen_id', $source->id)->where('ingredient_id', $ingredient->id)->first();

        $this->assertEquals(0, $sourceStock->frozen_quantity);
        $this->assertEquals(50, $sourceStock->quantity);
        $this->assertSame(StockTransfer::STATUS_CANCELLED, $transfer->fresh()->status);
        $this->assertSame(0, Stock::where('kitchen_id', $dest->id)->count());
    }

    /**
     * @return array{0: Kitchen, 1: Kitchen, 2: Ingredient}
     */
    protected function scenario(float $sourceQty): array
    {
        $area = Area::create(['code' => 'KV1', 'name' => 'HCM', 'status' => 'Đang hoạt động']);
        $source = Kitchen::create(['area_id' => $area->id, 'name' => 'Bếp Nguồn', 'type' => 'Canteen', 'capacity' => 100]);
        $dest = Kitchen::create(['area_id' => $area->id, 'name' => 'Bếp Đích', 'type' => 'Canteen', 'capacity' => 100]);

        $supplier = Supplier::create(['code' => 'S1', 'name' => 'NCC', 'type' => 'Thịt', 'status' => true]);
        $ingredient = Ingredient::create([
            'code' => 'I1', 'name' => 'Gạo', 'type' => 'Thực phẩm khô', 'unit' => 'Kg',
            'supplier_id' => $supplier->id, 'reference_price' => 20000, 'status' => true,
        ]);

        Stock::create([
            'kitchen_id' => $source->id, 'ingredient_id' => $ingredient->id,
            'quantity' => $sourceQty, 'min_quantity' => 10, 'unit_price' => 20000,
        ]);

        return [$source, $dest, $ingredient];
    }

    protected function makeTransfer(Kitchen $source, Kitchen $dest, Ingredient $ingredient, float $qty): StockTransfer
    {
        $transfer = StockTransfer::create([
            'code' => 'CK-'.uniqid(),
            'source_kitchen_id' => $source->id,
            'dest_kitchen_id' => $dest->id,
            'status' => StockTransfer::STATUS_IN_TRANSIT,
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => $qty,
        ]);

        return $transfer->load('items');
    }
}
