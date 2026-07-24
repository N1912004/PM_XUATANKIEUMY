<?php

namespace Tests\Feature;

use App\Exports\ListHangExport;
use App\Filament\Pages\ListHang;
use App\Models\Menu;
use App\Models\PurchaseOrder;
use App\Models\Recipe;
use App\Models\Shift;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListHangAutoPoTest extends TestCase
{
    use RefreshDatabase;

    protected Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        $this->shift = Shift::create(['name' => 'CA 1', 'time_range' => '07:00 - 16:00', 'status' => true]);
    }

    protected function makeLockedMenuWithIngredients(): array
    {
        $kitchen = $this->createKitchen();
        $supplierA = $this->createSupplier('NCC A');
        $supplierB = $this->createSupplier('NCC B');

        $ingredientA = $this->createIngredient('Gạo PO', 10000);
        $ingredientA->update(['supplier_id' => $supplierA->id]);
        $ingredientB = $this->createIngredient('Thịt PO', 20000);
        $ingredientB->update(['supplier_id' => $supplierB->id]);
        $ingredientNoSupplier = $this->createIngredient('Rau chưa gán NCC', 30000);

        DB::table('ingredient_supplier')->insert([
            [
                'ingredient_id' => $ingredientA->id,
                'supplier_id' => $supplierA->id,
                'reference_price' => 11000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ingredient_id' => $ingredientB->id,
                'supplier_id' => $supplierB->id,
                'reference_price' => 22000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $recipe = Recipe::create([
            'code' => 'MON'.uniqid(),
            'name' => 'Món auto PO',
            'type' => 'Món mặn',
            'status' => 'active',
        ]);
        $recipe->ingredients()->attach($ingredientA->id, ['quantity_per_portion' => 0.1]);
        $recipe->ingredients()->attach($ingredientB->id, ['quantity_per_portion' => 0.2]);
        $recipe->ingredients()->attach($ingredientNoSupplier->id, ['quantity_per_portion' => 0.3]);

        Menu::create([
            'kitchen_id' => $kitchen->id,
            'date' => today()->toDateString(),
            'shift_id' => $this->shift->id,
            'recipe_id' => $recipe->id,
            'estimated_portions' => 10,
            'status' => 'locked',
        ]);

        return compact('supplierA', 'supplierB', 'ingredientA', 'ingredientB', 'ingredientNoSupplier');
    }

    public function test_auto_po_gom_theo_ncc_dung_gia_pivot_chan_khi_thieu_ncc(): void
    {
        $ctx = $this->makeLockedMenuWithIngredients();

        // Còn dòng tích chọn thiếu NCC ⇒ CHẶN HẲN, không tạo PO nào
        // (trước đây bỏ qua lặng lẽ rồi tạo các PO còn lại — dòng thiếu NCC không ai đặt mà không biết)
        $component = Livewire::test(ListHang::class)
            ->set('poDate', today()->toDateString())
            ->set('poSourceFrom', today()->toDateString())
            ->set('poSourceTo', today()->toDateString())
            ->set('poSelectedShifts', [$this->shift->id])
            ->call('loadPOIngredients')
            ->call('createOrders');

        $this->assertSame(0, PurchaseOrder::count());

        // Bỏ tích dòng thiếu NCC ⇒ tạo được 2 PO như bình thường
        $items = $component->get('poItems');
        foreach ($items as $index => $item) {
            if ($item['ingredient_id'] === $ctx['ingredientNoSupplier']->id) {
                $component->set("poItems.{$index}.checked", false);
            }
        }
        $component->call('createOrders');

        $this->assertSame(2, PurchaseOrder::count());
        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $ctx['supplierA']->id,
            'status' => 'draft',
            'estimated_delivery_date' => today()->toDateTimeString(),
        ]);
        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $ctx['supplierB']->id,
            'status' => 'draft',
            'estimated_delivery_date' => today()->toDateTimeString(),
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'ingredient_id' => $ctx['ingredientA']->id,
            'quantity_ordered' => 1,
            'unit_price' => 11000,
        ]);
        $this->assertDatabaseHas('purchase_order_items', [
            'ingredient_id' => $ctx['ingredientB']->id,
            'quantity_ordered' => 2,
            'unit_price' => 22000,
        ]);
        $this->assertDatabaseMissing('purchase_order_items', [
            'ingredient_id' => $ctx['ingredientNoSupplier']->id,
        ]);
    }

    public function test_gan_nhanh_ncc_chi_fill_nguyen_lieu_ncc_cung_cap_duoc(): void
    {
        $ctx = $this->makeLockedMenuWithIngredients();

        $component = Livewire::test(ListHang::class)
            ->set('poDate', today()->toDateString())
            ->set('poSourceFrom', today()->toDateString())
            ->set('poSourceTo', today()->toDateString())
            ->set('poSelectedShifts', [$this->shift->id])
            ->call('loadPOIngredients')
            ->call('bulkAssignSupplier', 'thit', $ctx['supplierA']->id);

        $byIngredient = collect($component->get('poItems'))->keyBy('ingredient_id');

        // ingredientA thuộc supplierA ⇒ được fill supplierA
        $this->assertSame($ctx['supplierA']->id, $byIngredient[$ctx['ingredientA']->id]['supplier_id']);

        // ingredientNoSupplier không thuộc supplierA ⇒ KHÔNG được gán bừa
        $this->assertNull($byIngredient[$ctx['ingredientNoSupplier']->id]['supplier_id']);
    }

    public function test_auto_po_chan_ngay_dat_qua_hai_ngay_ke_tiep(): void
    {
        $this->makeLockedMenuWithIngredients();

        Livewire::test(ListHang::class)
            ->set('poDate', today()->addDays(3)->toDateString())
            ->set('poSourceFrom', today()->toDateString())
            ->set('poSourceTo', today()->toDateString())
            ->set('poSelectedShifts', [$this->shift->id])
            ->call('loadPOIngredients')
            ->call('createOrders');

        $this->assertSame(0, PurchaseOrder::count());
    }

    public function test_xuat_list_hang_la_file_xlsx(): void
    {
        Excel::fake();
        $this->makeLockedMenuWithIngredients();

        Livewire::test(ListHang::class)
            ->set('date', today()->toDateString())
            ->set('selectedShifts', [$this->shift->id])
            ->call('exportList');

        Excel::assertDownloaded('list_hang_'.today()->toDateString().'.xlsx', function (ListHangExport $export): bool {
            $rows = collect($export->array());

            return $rows->flatten()->contains('Món auto PO')
                && $rows->flatten()->contains('Gạo PO');
        });
    }
}
