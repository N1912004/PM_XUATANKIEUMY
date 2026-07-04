<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeAutoPendingTest extends TestCase
{
    use RefreshDatabase;

    public function test_changing_ingredient_price_sets_active_recipes_to_pending(): void
    {
        $supplier = Supplier::create(['code' => 'S1', 'name' => 'NCC', 'type' => 'Thịt', 'status' => true]);
        $ingredient = Ingredient::create([
            'code' => 'I1', 'name' => 'Thịt bò', 'type' => 'Động vật', 'unit' => 'Kg',
            'supplier_id' => $supplier->id, 'reference_price' => 250000, 'status' => true,
        ]);

        $recipe = Recipe::create([
            'code' => 'R1', 'name' => 'Bò xào', 'type' => 'Món xào',
            'price_level' => 40000, 'actual_price' => 35000, 'status' => 'active',
        ]);
        $recipe->ingredients()->attach($ingredient->id, ['quantity_per_portion' => 0.1]);

        // Đổi đơn giá tham chiếu → món phải chuyển về 'pending'
        $ingredient->update(['reference_price' => 300000]);

        $this->assertSame('pending', $recipe->fresh()->status);
    }

    public function test_non_price_changes_do_not_affect_recipe_status(): void
    {
        $supplier = Supplier::create(['code' => 'S2', 'name' => 'NCC2', 'type' => 'Thịt', 'status' => true]);
        $ingredient = Ingredient::create([
            'code' => 'I2', 'name' => 'Cá', 'type' => 'Động vật', 'unit' => 'Kg',
            'supplier_id' => $supplier->id, 'reference_price' => 90000, 'status' => true,
        ]);

        $recipe = Recipe::create([
            'code' => 'R2', 'name' => 'Cá kho', 'type' => 'Món mặn',
            'price_level' => 30000, 'actual_price' => 25000, 'status' => 'active',
        ]);
        $recipe->ingredients()->attach($ingredient->id, ['quantity_per_portion' => 0.15]);

        // Đổi tên (không đổi giá) → món giữ nguyên 'active'
        $ingredient->update(['name' => 'Cá lóc']);

        $this->assertSame('active', $recipe->fresh()->status);
    }
}
