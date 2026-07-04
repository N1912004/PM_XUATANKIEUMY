<?php

namespace Tests\Feature;

use App\Filament\Resources\FoodSafetyAuditResource\Pages\ListFoodSafetyAudits;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\Shift;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FoodSafetyAuditExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_food_safety_audit_export_returns_csv_download(): void
    {
        // 1. Setup minimal data
        $supplier = Supplier::create([
            'code' => 'SUP001',
            'name' => 'Supplier A',
            'type' => 'Thịt',
            'status' => true,
        ]);

        $ingredient = Ingredient::create([
            'code' => 'ING001',
            'name' => 'Thịt Vịt',
            'type' => 'Động vật',
            'unit' => 'Kg',
            'supplier_id' => $supplier->id,
            'reference_price' => 50000.00,
            'status' => true,
        ]);

        $recipe = Recipe::create([
            'code' => 'REC001',
            'name' => 'Vịt luộc',
            'type' => 'Món mặn',
            'price_level' => 30000.00,
            'actual_price' => 30000.00,
            'status' => 'active',
        ]);
        $recipe->ingredients()->attach($ingredient->id, ['quantity_per_portion' => 0.2]);

        $shift = Shift::create([
            'name' => 'Ca Trưa',
            'time_range' => '06:00 - 14:00',
        ]);

        $menu = Menu::create([
            'date' => '2026-05-18',
            'shift_id' => $shift->id,
            'recipe_id' => $recipe->id,
            'estimated_portions' => 100,
            'status' => 'locked',
        ]);

        // 2. Authenticate (super_admin để bỏ qua kiểm tra phân quyền của Shield)
        $this->actingAs($this->createSuperAdmin());

        // 3. Test Livewire exportCSV action
        $response = Livewire::test(ListFoodSafetyAudits::class)
            ->set('date', '2026-05-18')
            ->set('selectedShift', $shift->id)
            ->set('activeStep', 'Bước 1')
            ->call('exportCSV');

        $response->assertHasNoErrors();

        // Assert download using Livewire's native download assertion
        $response->assertFileDownloaded('BaoCao_KiemThuc_3Buoc_20260518.csv');
    }
}
