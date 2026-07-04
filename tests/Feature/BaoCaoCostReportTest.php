<?php

namespace Tests\Feature;

use App\Filament\Pages\BaoCao;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\Shift;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class BaoCaoCostReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_stats_compute_total_food_cost(): void
    {
        $shift = $this->seedData();
        $this->actingAs($this->makeUser());

        $component = Livewire::test(BaoCao::class)
            ->set('fromDate', '2026-05-18')
            ->set('toDate', '2026-05-18')
            ->set('selectedShifts', [$shift->id]);

        $stats = $component->instance()->getStats();

        // cost/suất = 0.2kg × 50.000đ = 10.000đ; 100 suất => 1.000.000đ
        $this->assertEquals(1000000, $stats['cost']);
        $this->assertEquals(100, $stats['suat']);
    }

    public function test_export_excel_downloads_financial_report(): void
    {
        Excel::fake();

        $shift = $this->seedData();
        $this->actingAs($this->makeUser());

        Livewire::test(BaoCao::class)
            ->set('fromDate', '2026-05-18')
            ->set('toDate', '2026-05-18')
            ->set('selectedShifts', [$shift->id])
            ->call('exportExcel')
            ->assertHasNoErrors();

        Excel::assertDownloaded('BaoCao_TaiChinh_20260518_20260518.xlsx');
    }

    protected function seedData(): Shift
    {
        $supplier = Supplier::create([
            'code' => 'SUP001', 'name' => 'Supplier A', 'type' => 'Thịt', 'status' => true,
        ]);

        $ingredient = Ingredient::create([
            'code' => 'ING001', 'name' => 'Thịt Vịt', 'type' => 'Động vật', 'unit' => 'Kg',
            'supplier_id' => $supplier->id, 'reference_price' => 50000.00, 'status' => true,
        ]);

        $recipe = Recipe::create([
            'code' => 'REC001', 'name' => 'Vịt luộc', 'type' => 'Món mặn',
            'price_level' => 30000.00, 'actual_price' => 30000.00, 'status' => 'active',
        ]);
        $recipe->ingredients()->attach($ingredient->id, ['quantity_per_portion' => 0.2]);

        $shift = Shift::create(['name' => 'Ca Trưa', 'time_range' => '06:00 - 14:00']);

        Menu::create([
            'date' => '2026-05-18', 'shift_id' => $shift->id, 'recipe_id' => $recipe->id,
            'estimated_portions' => 100, 'status' => 'locked',
        ]);

        return $shift;
    }

    protected function makeUser(): User
    {
        return $this->createSuperAdmin();
    }
}
