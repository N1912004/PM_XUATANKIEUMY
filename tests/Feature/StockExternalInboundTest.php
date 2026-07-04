<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockExternalInboundTest extends TestCase
{
    use RefreshDatabase;

    public function test_external_inbound_adds_stock_and_logs_voucher_with_attachment(): void
    {
        $supplier = Supplier::create(['code' => 'S1', 'name' => 'NCC', 'type' => 'Thịt', 'status' => true]);
        $ingredient = Ingredient::create([
            'code' => 'I1', 'name' => 'Hành lá', 'type' => 'Thực vật', 'unit' => 'Kg',
            'supplier_id' => $supplier->id, 'reference_price' => 15000, 'status' => true,
        ]);

        $transaction = Stock::recordExternalInbound(
            kitchenId: null,
            ingredientId: $ingredient->id,
            quantity: 8,
            unitPrice: 16000,
            attachmentUrl: 'stock-vouchers/hoadon.jpg',
        );

        // Tồn cộng đúng
        $this->assertEquals(8, Stock::where('ingredient_id', $ingredient->id)->value('quantity'));

        // Giao dịch đúng loại + mã NX-EXT + chứng từ
        $this->assertSame('Nhập kho ngoài', $transaction->type);
        $this->assertStringStartsWith('NX-EXT-', $transaction->voucher_code);
        $this->assertSame('stock-vouchers/hoadon.jpg', $transaction->attachment_url);
        $this->assertEquals(8, $transaction->after_quantity);
    }

    public function test_external_inbound_accumulates_onto_existing_stock(): void
    {
        $supplier = Supplier::create(['code' => 'S2', 'name' => 'NCC2', 'type' => 'Thịt', 'status' => true]);
        $ingredient = Ingredient::create([
            'code' => 'I2', 'name' => 'Tỏi', 'type' => 'Gia vị', 'unit' => 'Kg',
            'supplier_id' => $supplier->id, 'reference_price' => 40000, 'status' => true,
        ]);
        Stock::create(['ingredient_id' => $ingredient->id, 'quantity' => 5, 'min_quantity' => 10, 'unit_price' => 40000]);

        Stock::recordExternalInbound(null, $ingredient->id, 3, 42000, 'stock-vouchers/x.png');

        $this->assertEquals(8, Stock::where('ingredient_id', $ingredient->id)->value('quantity'));
        $this->assertSame(1, StockTransaction::where('type', 'Nhập kho ngoài')->count());
    }
}
