<?php

namespace Tests\Feature;

use App\Filament\Resources\RecipeResource\Pages\CreateRecipe;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Supplier;
use Filament\Forms\Components\Repeater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RecipeResourceCreatePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_recipe_page_renders(): void
    {
        $this->actingAs($this->createRecipeAdmin());

        Livewire::test(CreateRecipe::class)
            ->assertSee('Thêm món ăn vào ngân hàng thực đơn')
            ->assertSee('Bảng nguyên liệu &amp; cost trên 1 phần', false);
    }

    public function test_recipe_can_be_created_with_ingredient_cost_rows(): void
    {
        $this->actingAs($this->createRecipeAdmin());

        $supplier = Supplier::create([
            'code' => 'NCC01',
            'name' => 'NCC thực phẩm',
            'type' => 'Thực phẩm',
            'status' => true,
        ]);

        $ingredient = Ingredient::create([
            'code' => 'NL01',
            'name' => 'Thịt bò',
            'type' => 'Động vật',
            'unit' => 'kg',
            'supplier_id' => $supplier->id,
            'reference_price' => 250000,
            'status' => true,
        ]);

        $undoRepeaterFake = Repeater::fake();

        Livewire::test(CreateRecipe::class)
            ->fillForm([
                'name' => 'Bò xào',
                'code' => 'MON99999',
                'type' => 'Món mặn',
                'price_level' => 20000,
                'actual_price' => 20000,
                'price_option' => 'Không',
                'status' => 'active',
                'description' => 'Món ăn ca trưa',
                'recipeIngredients' => [
                    [
                        'ingredient_id' => $ingredient->id,
                        'quantity_per_portion' => 0.2,
                        'note' => 'Cắt lát',
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $undoRepeaterFake();

        $recipe = Recipe::where('code', 'MON99999')->firstOrFail();

        $this->assertSame('Bò xào', $recipe->name);
        $this->assertSame('Không', $recipe->price_option);
        $this->assertSame('Món ăn ca trưa', $recipe->description);
        $this->assertDatabaseHas('recipe_ingredients', [
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity_per_portion' => 0.2,
            'note' => 'Cắt lát',
        ]);
    }

    private function createRecipeAdmin()
    {
        $user = $this->createSuperAdmin();

        $role = $user->roles()->firstOrFail();

        foreach (['view_any_recipe', 'create_recipe'] as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            $role->givePermissionTo($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }
}
