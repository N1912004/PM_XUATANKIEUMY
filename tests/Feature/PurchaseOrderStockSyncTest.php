<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderStockSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_order_status_change_to_done_syncs_to_stock_and_creates_transaction(): void
    {
        // 1. Create a Supplier
        $supplier = Supplier::create([
            'code' => 'SUP001',
            'name' => 'Supplier Test',
            'type' => 'Thịt',
            'status' => true,
        ]);

        // 2. Create an Ingredient
        $ingredient = Ingredient::create([
            'code' => 'ING001',
            'name' => 'Ingredient Test',
            'type' => 'Động vật',
            'unit' => 'Kg',
            'supplier_id' => $supplier->id,
            'reference_price' => 100000.00,
            'status' => true,
        ]);

        // 3. Create a PO in draft
        $po = PurchaseOrder::create([
            'code' => 'PO-TEST-001',
            'supplier_id' => $supplier->id,
            'status' => 'draft',
        ]);

        // 4. Create a PO item
        $item = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'ingredient_id' => $ingredient->id,
            'quantity_ordered' => 10.0,
            'quantity_received' => 8.0,
            'unit_price' => 95000.0,
        ]);

        // Verify initial stock doesn't exist
        $this->assertDatabaseMissing('stocks', ['ingredient_id' => $ingredient->id]);

        // 5. Change PO status to done
        $po->update(['status' => 'done']);

        // Verify stock is created and quantity is correct
        $stock = Stock::where('ingredient_id', $ingredient->id)->first();
        $this->assertNotNull($stock);
        $this->assertEquals(8.0, $stock->quantity);
        $this->assertEquals(95000.0, $stock->unit_price);

        // Verify transaction is recorded
        $transaction = StockTransaction::where('ingredient_id', $ingredient->id)->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('Nhập kho', $transaction->type);
        $this->assertEquals(8.0, $transaction->quantity);
        $this->assertEquals(8.0, $transaction->after_quantity);
        $this->assertStringContainsString('PO-TEST-001', $transaction->note);
    }
}
