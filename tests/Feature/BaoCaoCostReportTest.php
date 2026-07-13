<?php

namespace Tests\Feature;

use App\Exports\FinancialReportExport;
use App\Filament\Pages\BaoCao;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\Shift;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BaoCaoCostReportTest extends TestCase
{
    use RefreshDatabase;

    protected Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Permission::findOrCreate('page_BaoCao', 'web');
        $role = Role::create(['name' => 'BaoCao'.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo('page_BaoCao');
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);

        $this->shift = Shift::create(['name' => 'CA 1', 'time_range' => '07:00 - 16:00', 'status' => true]);
    }

    protected function createMenuForReport(string $name, int $portions, string $status, ?float $override = null): Recipe
    {
        $kitchen = $this->createKitchen();
        $ingredient = $this->createIngredient('NL '.$name, 100000);

        $recipe = Recipe::create([
            'code' => 'MON'.uniqid(),
            'name' => $name,
            'type' => 'Món mặn',
            'status' => 'active',
            'cost_override' => $override,
        ]);
        $recipe->ingredients()->attach($ingredient->id, ['quantity_per_portion' => 0.2]);

        Menu::create([
            'kitchen_id' => $kitchen->id,
            'date' => today()->toDateString(),
            'shift_id' => $this->shift->id,
            'recipe_id' => $recipe->id,
            'estimated_portions' => $portions,
            'status' => $status,
        ]);

        return $recipe;
    }

    public function test_bao_cao_chi_tinh_menu_da_chot_va_uu_tien_cost_override(): void
    {
        $this->createMenuForReport('Món cost tự tính', 10, 'locked');
        $this->createMenuForReport('Món cost override', 5, 'locked', 15000);
        $this->createMenuForReport('Món nháp không tính', 999, 'draft');

        $component = Livewire::test(BaoCao::class)
            ->set('fromDate', today()->toDateString())
            ->set('toDate', today()->toDateString())
            ->set('selectedShifts', [$this->shift->id]);

        $stats = $component->instance()->getStats();
        $this->assertSame(2, $stats['dishes']);
        $this->assertSame(15, $stats['suat']);
        $this->assertSame(275000.0, $stats['cost']);

        $grouped = $component->instance()->getGroupedData();
        $dishCosts = collect($grouped[0]['shifts'][0]['dishes'])->pluck('dish_cost', 'name');

        $this->assertSame(200000.0, $dishCosts['Món cost tự tính']);
        $this->assertSame(75000.0, $dishCosts['Món cost override']);
        $this->assertArrayNotHasKey('Món nháp không tính', $dishCosts->all());
    }

    public function test_bao_cao_xuat_excel_tai_chinh_xlsx(): void
    {
        Excel::fake();
        $this->createMenuForReport('Món cost tự tính', 10, 'locked');

        Livewire::test(BaoCao::class)
            ->set('fromDate', today()->toDateString())
            ->set('toDate', today()->toDateString())
            ->set('selectedShifts', [$this->shift->id])
            ->call('exportExcel');

        $fileName = 'BaoCao_TaiChinh_'.today()->format('Ymd').'_'.today()->format('Ymd').'.xlsx';
        Excel::assertDownloaded($fileName, function (FinancialReportExport $export): bool {
            $rows = collect($export->array());

            return $rows->flatten()->contains('Món cost tự tính')
                && $rows->flatten()->contains(200000);
        });
    }
}
