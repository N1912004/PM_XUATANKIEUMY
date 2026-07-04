<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Ingredient;
use App\Models\Kitchen;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KitchenScopedStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_ingredient_keeps_separate_stock_per_kitchen(): void
    {
        $area = Area::create(['code' => 'KV1', 'name' => 'HCM', 'status' => 'Đang hoạt động']);
        $kitchenA = Kitchen::create(['area_id' => $area->id, 'name' => 'Bếp A', 'type' => 'Canteen', 'capacity' => 100]);
        $kitchenB = Kitchen::create(['area_id' => $area->id, 'name' => 'Bếp B', 'type' => 'Canteen', 'capacity' => 100]);

        $supplier = Supplier::create(['code' => 'S1', 'name' => 'NCC', 'type' => 'Thịt', 'status' => true]);
        $ingredient = Ingredient::create([
            'code' => 'I1', 'name' => 'Gạo', 'type' => 'Thực phẩm khô', 'unit' => 'Kg',
            'supplier_id' => $supplier->id, 'reference_price' => 20000, 'status' => true,
        ]);

        $poA = $this->makeDonePo($kitchenA->id, $supplier->id, $ingredient->id, 30);
        $poB = $this->makeDonePo($kitchenB->id, $supplier->id, $ingredient->id, 50);

        // 2 bản ghi kho riêng biệt theo từng bếp
        $this->assertSame(2, Stock::where('ingredient_id', $ingredient->id)->count());
        $this->assertEquals(30, Stock::where('kitchen_id', $kitchenA->id)->where('ingredient_id', $ingredient->id)->value('quantity'));
        $this->assertEquals(50, Stock::where('kitchen_id', $kitchenB->id)->where('ingredient_id', $ingredient->id)->value('quantity'));
    }

    public function test_stock_transactions_are_tagged_with_kitchen(): void
    {
        $area = Area::create(['code' => 'KV2', 'name' => 'ĐN', 'status' => 'Đang hoạt động']);
        $kitchen = Kitchen::create(['area_id' => $area->id, 'name' => 'Bếp C', 'type' => 'Canteen', 'capacity' => 100]);
        $supplier = Supplier::create(['code' => 'S2', 'name' => 'NCC2', 'type' => 'Thịt', 'status' => true]);
        $ingredient = Ingredient::create([
            'code' => 'I2', 'name' => 'Đường', 'type' => 'Gia vị', 'unit' => 'Kg',
            'supplier_id' => $supplier->id, 'reference_price' => 15000, 'status' => true,
        ]);

        $po = $this->makeDonePo($kitchen->id, $supplier->id, $ingredient->id, 12);

        $this->assertDatabaseHas('stock_transactions', [
            'kitchen_id' => $kitchen->id,
            'ingredient_id' => $ingredient->id,
            'type' => 'Nhập kho',
        ]);
    }

    protected function makeDonePo(int $kitchenId, int $supplierId, int $ingredientId, float $qty): PurchaseOrder
    {
        $po = PurchaseOrder::create([
            'code' => 'PO-'.$kitchenId.'-'.$ingredientId,
            'kitchen_id' => $kitchenId,
            'supplier_id' => $supplierId,
            'status' => 'draft',
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'ingredient_id' => $ingredientId,
            'quantity_ordered' => $qty,
            'quantity_received' => $qty,
            'unit_price' => 20000,
        ]);

        $po->update(['status' => 'done']);

        return $po;
    }
}
