<?php

namespace Tests\Feature;

use App\Filament\Resources\FoodSafetyAuditResource\Pages\ListFoodSafetyAudits;
use App\Models\FoodSafetyAudit;
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

class FoodSafetyAuditExcelExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_excel_downloads_xlsx_with_byt_template(): void
    {
        Excel::fake();

        [$shift, $recipe] = $this->seedMenu();

        // Ghi nhận THẬT dữ liệu Bước 2 cho món
        FoodSafetyAudit::create([
            'date' => '2026-05-18',
            'shift_id' => $shift->id,
            'recipe_id' => $recipe->id,
            'stage' => 'Bước 2',
            'status' => 'Đạt',
            'inspected_by' => 'Bếp trưởng Hùng',
            'cook_start_at' => '09:15',
            'cook_end_at' => '10:30',
            'temperature' => '85°C',
        ]);

        $this->actingAs($this->makeUser());

        Livewire::test(ListFoodSafetyAudits::class)
            ->set('date', '2026-05-18')
            ->set('selectedShift', $shift->id)
            ->call('exportExcel')
            ->assertHasNoErrors();

        Excel::assertDownloaded('BaoCao_KiemThuc_3Buoc_20260518.xlsx');
    }

    public function test_step_two_uses_real_recorded_data_not_mock(): void
    {
        [$shift, $recipe] = $this->seedMenu();

        FoodSafetyAudit::create([
            'date' => '2026-05-18',
            'shift_id' => $shift->id,
            'recipe_id' => $recipe->id,
            'stage' => 'Bước 2',
            'status' => 'Đạt',
            'inspected_by' => 'Bếp trưởng Hùng',
            'cook_start_at' => '09:15',
            'cook_end_at' => '10:30',
            'temperature' => '90°C',
        ]);

        $this->actingAs($this->makeUser());

        $component = Livewire::test(ListFoodSafetyAudits::class)
            ->set('date', '2026-05-18')
            ->set('selectedShift', $shift->id)
            ->set('activeStep', 'Bước 2');

        $items = $component->instance()->getAuditItems();

        $this->assertCount(1, $items);
        $this->assertSame('09:15 - 10:30', $items[0]['time']);
        $this->assertSame('90°C', $items[0]['temp']);
        $this->assertSame('Bếp trưởng Hùng', $items[0]['cook']);
    }

    /**
     * @return array{0: Shift, 1: Recipe}
     */
    protected function seedMenu(): array
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

        return [$shift, $recipe];
    }

    protected function makeUser(): User
    {
        return $this->createSuperAdmin();
    }
}
