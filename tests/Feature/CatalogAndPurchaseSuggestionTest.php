<?php

namespace Tests\Feature;

use App\Filament\Pages\ListHang;
use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\Shift;
use App\Models\Stock;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Hai mảnh cuối để 9 menu đạt chuẩn BA:
 * - Đặt hàng: số lượng đề xuất mua phải TRỪ tồn kho hiện có (R13).
 * - Danh mục cấu hình động thay cho mảng hard-code (R37).
 */
class CatalogAndPurchaseSuggestionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function userWith(array $permissions, ?Kitchen $kitchen = null): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role = Role::create(['name' => 'R'.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);

        $employee = $this->createEmployee('Nhân viên Test', $kitchen?->id ?? $this->createKitchen()->id);
        $user = User::factory()->create(['employee_id' => $employee->id]);
        $user->assignRole($role);

        return $user;
    }

    public function test_so_luong_de_xuat_mua_tru_ton_kho_hien_co(): void
    {
        $kitchen = $this->createKitchen();
        $this->actingAs($this->userWith(['page_ListHang'], $kitchen));

        $shift = Shift::create(['name' => 'Ca 1', 'time_range' => '06:00 - 14:00']);
        $ingredient = $this->createIngredient('Gạo', 20000);

        $recipe = Recipe::create([
            'code' => 'M010', 'name' => 'Cơm trắng', 'type' => 'Món chính',
            'selling_price_per_portion' => 20000, 'cost_per_portion' => 20000, 'status' => 'active',
        ]);
        // 0,2 kg/suất × 100 suất = 20 kg nhu cầu
        $recipe->ingredients()->attach($ingredient->id, ['quantity_per_portion' => 0.2]);

        Menu::create([
            'kitchen_id' => $kitchen->id, 'date' => now()->toDateString(), 'shift_id' => $shift->id,
            'recipe_id' => $recipe->id, 'estimated_portions' => 100, 'status' => 'locked',
        ]);

        // Kho còn 8 kg (trong đó 3 kg đang đóng băng cho phiếu chuyển) → khả dụng 5 kg
        Stock::create([
            'kitchen_id' => $kitchen->id, 'ingredient_id' => $ingredient->id,
            'quantity' => 8, 'frozen_quantity' => 3, 'min_quantity' => 1, 'unit_price' => 20000,
        ]);

        $page = Livewire::test(ListHang::class)
            ->set('poSourceFrom', now()->toDateString())
            ->set('poSourceTo', now()->toDateString())
            ->set('poSelectedShifts', [$shift->id])
            ->call('loadPOIngredients')
            ->instance();

        $item = collect($page->poItems)->firstWhere('ingredient_id', $ingredient->id);

        $this->assertNotNull($item);
        $this->assertEqualsWithDelta(20.0, $item['total_kg'], 0.001, 'Nhu cầu từ thực đơn = 20kg');
        $this->assertEqualsWithDelta(5.0, $item['stock_qty'], 0.001, 'Tồn khả dụng = 8 − 3 đóng băng');
        $this->assertEqualsWithDelta(15.0, $item['quantity_manual'], 0.001, 'Đề xuất mua = 20 − 5 = 15kg');
    }

    public function test_tồn_kho_du_thi_khong_de_xuat_mua_am(): void
    {
        $kitchen = $this->createKitchen();
        $this->actingAs($this->userWith(['page_ListHang'], $kitchen));

        $shift = Shift::create(['name' => 'Ca 1', 'time_range' => '06:00 - 14:00']);
        $ingredient = $this->createIngredient('Muối', 5000);

        $recipe = Recipe::create([
            'code' => 'M011', 'name' => 'Canh', 'type' => 'Món canh',
            'selling_price_per_portion' => 20000, 'cost_per_portion' => 20000, 'status' => 'active',
        ]);
        $recipe->ingredients()->attach($ingredient->id, ['quantity_per_portion' => 0.01]);

        Menu::create([
            'kitchen_id' => $kitchen->id, 'date' => now()->toDateString(), 'shift_id' => $shift->id,
            'recipe_id' => $recipe->id, 'estimated_portions' => 100, 'status' => 'locked',
        ]);

        // Nhu cầu 1kg, kho còn 50kg → không cần mua
        Stock::create([
            'kitchen_id' => $kitchen->id, 'ingredient_id' => $ingredient->id,
            'quantity' => 50, 'min_quantity' => 1, 'unit_price' => 5000,
        ]);

        $page = Livewire::test(ListHang::class)
            ->set('poSourceFrom', now()->toDateString())
            ->set('poSourceTo', now()->toDateString())
            ->set('poSelectedShifts', [$shift->id])
            ->call('loadPOIngredients')
            ->instance();

        $item = collect($page->poItems)->firstWhere('ingredient_id', $ingredient->id);

        $this->assertEqualsWithDelta(0.0, $item['quantity_manual'], 0.001, 'Không đề xuất số âm khi kho đã đủ');
    }

    public function test_danh_muc_cau_hinh_dong_co_du_gia_tri_seed_va_chi_lay_muc_dang_bat(): void
    {
        // 4 danh mục nghiệp vụ nay là bảng riêng (thay cho các nhóm trong catalogs cũ):
        // migration phải nạp sẵn giá trị mặc định cho từng bảng.
        $this->assertNotEmpty(\App\Models\KitchenType::options(), 'kitchen_types phải có giá trị seed');
        $this->assertNotEmpty(\App\Models\Department::options(), 'departments phải có giá trị seed');
        $this->assertNotEmpty(\App\Models\Position::options(), 'positions phải có giá trị seed');
        $this->assertNotEmpty(\App\Models\LeaveType::options(), 'leave_types phải có giá trị seed');

        // Mục đã tắt (active = false) không được hiện ở form/bộ lọc.
        \App\Models\Department::create(['name' => 'Phòng thử nghiệm', 'active' => false]);

        $this->assertNotContains(
            'Phòng thử nghiệm',
            \App\Models\Department::options(),
            'Mục đã tắt không được hiện ở form'
        );
    }
}
