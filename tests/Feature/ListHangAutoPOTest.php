<?php

namespace Tests\Feature;

use App\Filament\Pages\ListHang;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\PurchaseOrder;
use App\Models\Recipe;
use App\Models\Shift;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListHangAutoPOTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_po_generation_creates_draft_pos_from_menus(): void
    {
        // 1. Create a Supplier
        $supplier = Supplier::create([
            'code' => 'SUP001',
            'name' => 'Supplier A',
            'type' => 'Thịt',
            'status' => true,
        ]);

        // 2. Create an Ingredient
        $ingredient = Ingredient::create([
            'code' => 'ING001',
            'name' => 'Ingredient A',
            'type' => 'Động vật',
            'unit' => 'Kg',
            'supplier_id' => $supplier->id,
            'reference_price' => 50000.00,
            'status' => true,
        ]);

        // 3. Create a Recipe
        $recipe = Recipe::create([
            'code' => 'REC001',
            'name' => 'Recipe A',
            'type' => 'Món mặn',
            'price_level' => 30000.00,
            'actual_price' => 30000.00,
            'status' => 'active',
        ]);

        // Attach ingredient to recipe with quantity 0.2 kg/portion
        $recipe->ingredients()->attach($ingredient->id, ['quantity_per_portion' => 0.2]);

        // 4. Create a Shift
        $shift = Shift::create([
            'name' => 'Ca 1',
            'time_range' => '06:00 - 14:00',
        ]);

        // 5. Create a Menu for a specific date
        $menu = Menu::create([
            'date' => '2026-07-04',
            'shift_id' => $shift->id,
            'recipe_id' => $recipe->id,
            'estimated_portions' => 100, // Needs 100 * 0.2 = 20 Kg
            'status' => 'locked',
        ]);

        // 6. Authenticate User (since Filament pages require authentication)
        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-test@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($user);

        // 7. Test the Livewire component ListHang
        Livewire::test(ListHang::class)
            ->set('date', '2026-07-04')
            ->set('selectedShifts', [$shift->id])
            ->call('generatePurchaseOrders')
            ->assertHasNoErrors();

        // 8. Assert PO was created
        $po = PurchaseOrder::where('supplier_id', $supplier->id)->first();
        $this->assertNotNull($po);
        $this->assertEquals('draft', $po->status);
        $this->assertEquals('2026-07-04', $po->estimated_delivery_date->toDateString());
        $this->assertStringContainsString('PO-LH-20260704', $po->code);

        // 9. Assert PO Item was created with correct quantity (20 Kg)
        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'ingredient_id' => $ingredient->id,
            'quantity_ordered' => 20.0,
            'unit_price' => 50000.00,
        ]);
    }
}
