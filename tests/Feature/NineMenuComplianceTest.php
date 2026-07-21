<?php

namespace Tests\Feature;

use App\Filament\Pages\BaoCao;
use App\Filament\Resources\MenuResource\Pages\ListMenus;
use App\Filament\Resources\StockResource\Pages\ListStocks;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\Shift;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Các quy tắc nghiệp vụ BA đã bổ sung để 9 menu đạt chuẩn luồng:
 * tổng khối lượng nguyên liệu (kg đúng đơn vị), kiểm kê kho theo ngày + tồn đầu kỳ,
 * cảnh báo lặp món 3 tuần trên grid thực đơn tuần.
 */
class NineMenuComplianceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function userWith(array $permissions, ?int $kitchenId = null): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role = Role::create(['name' => 'R'.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);

        $user = User::factory()->create();
        $user->assignRole($role);

        if ($kitchenId) {
            $emp = $this->createEmployee('NV Test', $kitchenId);
            $user->update(['employee_id' => $emp->id]);
        }

        return $user;
    }

    protected function makeShift(): Shift
    {
        return Shift::create(['name' => 'Ca 1', 'time_range' => '06:00 - 14:00']);
    }

    /** MENU 9 — Báo cáo: tổng khối lượng từng nguyên liệu tính bằng KG (không lệch 1000 lần). */
    public function test_bao_cao_tong_khoi_luong_nguyen_lieu_dung_don_vi_kg(): void
    {
        // Cấp quản lý — user thường bị khóa vào bếp của mình (BaoCao::enforcedKitchenId())
        $manager = $this->userWith(['page_BaoCao']);
        $manager->assignRole(Role::findOrCreate('Quản trị viên', 'web'));
        $this->actingAs($manager);

        $kitchen = $this->createKitchen();
        $shift = $this->makeShift();
        $ingredient = $this->createIngredient('Thịt heo', 100000);

        $recipe = Recipe::create([
            'code' => 'M001', 'name' => 'Thịt kho', 'type' => 'Món mặn',
            'selling_price_per_portion' => 20000, 'cost_per_portion' => 20000, 'status' => 'active',
        ]);
        // 0,15 kg/suất × 200 suất = 30 kg
        $recipe->ingredients()->attach($ingredient->id, ['quantity_per_portion' => 0.15]);

        Menu::create([
            'kitchen_id' => $kitchen->id, 'date' => now()->toDateString(), 'shift_id' => $shift->id,
            'recipe_id' => $recipe->id, 'estimated_portions' => 200, 'status' => 'locked',
        ]);

        $page = Livewire::test(BaoCao::class)
            ->set('fromDate', now()->toDateString())
            ->set('toDate', now()->toDateString())
            ->set('selectedShifts', [$shift->id]);

        $totals = $page->instance()->getIngredientTotals();

        $this->assertCount(1, $totals);
        $this->assertSame('Thịt heo', $totals[0]['name']);
        $this->assertEqualsWithDelta(30.0, $totals[0]['quantity'], 0.001, 'Phải là 30 kg, không phải 0,03 kg');
        $this->assertEqualsWithDelta(3_000_000.0, $totals[0]['cost'], 0.01);
    }

    /** MENU 2 — Kiểm kê: tồn hệ thống phải theo NGÀY được chọn, không phải tồn hôm nay. */
    public function test_kiem_ke_lay_ton_theo_ngay_duoc_chon(): void
    {
        $this->actingAs($this->userWith(['view_any_stock', 'update_stock']));

        $kitchen = $this->createKitchen();
        $ingredient = $this->createIngredient();

        // Tồn hiện tại 100kg, trong đó 40kg vừa nhập HÔM NAY → tồn cuối ngày HÔM QUA phải là 60kg
        $stock = Stock::create([
            'kitchen_id' => $kitchen->id, 'ingredient_id' => $ingredient->id,
            'quantity' => 100, 'min_quantity' => 10, 'unit_price' => 100000,
        ]);
        StockTransaction::create([
            'kitchen_id' => $kitchen->id, 'ingredient_id' => $ingredient->id,
            'type' => 'Nhập kho', 'quantity' => 40, 'after_quantity' => 100,
        ]);

        $page = Livewire::test(ListStocks::class)->instance();

        $today = $page->getSystemQuantities(now()->toDateString());
        $yesterday = $page->getSystemQuantities(now()->subDay()->toDateString());

        $this->assertEqualsWithDelta(100.0, $today[$stock->id], 0.001);
        $this->assertEqualsWithDelta(60.0, $yesterday[$stock->id], 0.001, 'Tồn cuối ngày hôm qua phải loại trừ giao dịch hôm nay');
    }

    /** MENU 2 — Chốt kiểm kê là ĐIỀU CHỈNH (+/−), có mã phiếu, không ghi đè mất biến động sau đó. */
    public function test_chot_kiem_ke_ghi_dieu_chinh_va_co_ma_phieu(): void
    {
        $kitchen = $this->createKitchen();
        $this->actingAs($this->userWith(['view_any_stock', 'update_stock'], $kitchen->id));

        $ingredient = $this->createIngredient();
        $stock = Stock::create([
            'kitchen_id' => $kitchen->id, 'ingredient_id' => $ingredient->id,
            'quantity' => 100, 'min_quantity' => 10, 'unit_price' => 100000,
        ]);

        Livewire::test(ListStocks::class)
            ->set('checkDate', now()->toDateString())
            ->set('actualQuantities', [$stock->id => 95])   // thiếu 5kg
            ->set('checkNotes', [$stock->id => 'Hao hụt bảo quản'])
            ->call('saveEndDay');

        $this->assertEqualsWithDelta(95.0, $stock->fresh()->quantity, 0.001);

        $log = StockTransaction::where('ingredient_id', $ingredient->id)->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEqualsWithDelta(-5.0, (float) $log->quantity, 0.001);
        $this->assertSame('KK-'.now()->format('Ymd'), $log->voucher_code);
    }

    /** MENU 2 — Lệch tồn mà không ghi lý do thì KHÔNG được chốt. */
    public function test_kiem_ke_lech_ma_thieu_ly_do_thi_khong_luu(): void
    {
        $kitchen = $this->createKitchen();
        $this->actingAs($this->userWith(['view_any_stock', 'update_stock'], $kitchen->id));

        $ingredient = $this->createIngredient();
        $stock = Stock::create([
            'kitchen_id' => $kitchen->id, 'ingredient_id' => $ingredient->id,
            'quantity' => 100, 'min_quantity' => 10, 'unit_price' => 100000,
        ]);

        Livewire::test(ListStocks::class)
            ->set('checkDate', now()->toDateString())
            ->set('actualQuantities', [$stock->id => 80])
            ->set('checkNotes', [$stock->id => ''])
            ->call('saveEndDay');

        $this->assertEqualsWithDelta(100.0, $stock->fresh()->quantity, 0.001, 'Tồn không được đổi khi thiếu lý do');
        $this->assertDatabaseCount('stock_transactions', 0);
    }

    /** MENU 7 — Grid thực đơn tuần cảnh báo món đã dùng trong 3 tuần gần nhất. */
    public function test_grid_tuan_canh_bao_mon_lap_trong_3_tuan_gan_nhat(): void
    {
        $this->actingAs($this->userWith(['view_any_menu', 'create_menu']));

        $kitchen = $this->createKitchen();
        $shift = $this->makeShift();
        $recipe = Recipe::create([
            'code' => 'M002', 'name' => 'Gà chiên', 'type' => 'Món mặn',
            'selling_price_per_portion' => 20000, 'cost_per_portion' => 20000, 'status' => 'active',
        ]);

        $weekStart = now()->startOfWeek();

        // Món đã chạy 10 ngày trước (nằm trong cửa sổ 21 ngày trước tuần này)
        Menu::create([
            'kitchen_id' => $kitchen->id, 'date' => $weekStart->copy()->subDays(10)->toDateString(),
            'shift_id' => $shift->id, 'recipe_id' => $recipe->id,
            'estimated_portions' => 100, 'status' => 'locked',
        ]);

        $page = new ListMenus();
        $page->weekKitchenId = $kitchen->id;
        $page->weekDateFrom = $weekStart->toDateString();
        $page->weekDateTo = $weekStart->copy()->addDays(6)->toDateString();
        $page->weekCells = [0 => [$shift->id => [['recipe_id' => $recipe->id, 'portions' => 150]]]];

        $warnings = $page->getWeekDuplicateWarnings();

        $this->assertSame(['Gà chiên'], $warnings);
    }
}
