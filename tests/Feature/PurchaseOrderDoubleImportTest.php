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

class PurchaseOrderDoubleImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reverting_and_redoing_done_does_not_import_stock_twice(): void
    {
        $supplier = Supplier::create(['code' => 'S1', 'name' => 'NCC', 'type' => 'Thịt', 'status' => true]);
        $ingredient = Ingredient::create([
            'code' => 'I1', 'name' => 'Gạo', 'type' => 'Thực phẩm khô', 'unit' => 'Kg',
            'supplier_id' => $supplier->id, 'reference_price' => 20000, 'status' => true,
        ]);

        $po = PurchaseOrder::create([
            'code' => 'PO-DUP-1', 'supplier_id' => $supplier->id, 'status' => 'draft',
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id, 'ingredient_id' => $ingredient->id,
            'quantity_ordered' => 40, 'quantity_received' => 40, 'unit_price' => 20000,
        ]);

        // Lần 1: chuyển sang done → nhập kho 40
        $po->update(['status' => 'done']);
        $this->assertEquals(40, Stock::where('ingredient_id', $ingredient->id)->value('quantity'));

        // Đổi done → checking → done lần nữa: KHÔNG được nhập kho lặp
        $po->update(['status' => 'checking']);
        $po->update(['status' => 'done']);

        $this->assertEquals(40, Stock::where('ingredient_id', $ingredient->id)->value('quantity'));
        $this->assertSame(1, StockTransaction::where('ingredient_id', $ingredient->id)->count());
        $this->assertNotNull($po->fresh()->stocked_at);
    }
}
