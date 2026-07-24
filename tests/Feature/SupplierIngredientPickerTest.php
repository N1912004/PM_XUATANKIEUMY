<?php

namespace Tests\Feature;

use App\Filament\Resources\SupplierResource\Pages\CreateSupplier;
use App\Filament\Resources\SupplierResource\Pages\ListSuppliers;
use App\Filament\Resources\SupplierResource\Pages\ViewSupplier;
use App\Models\Ingredient;
use App\Models\IngredientType;
use App\Models\Supplier;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Ô "Loại thực phẩm cung cấp" của NCC là read-only: suy trực tiếp từ loại
 * (`ingredient_type_id`) của các nguyên liệu đã tích, lưu ID chuẩn qua pivot
 * `ingredient_type_supplier`. Không còn chọn loại thủ công, không còn cột `type` chuỗi.
 */
class SupplierIngredientPickerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        foreach (['view_any_supplier', 'view_supplier', 'create_supplier'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role = Role::create(['name' => 'NCC'.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo(['view_any_supplier', 'view_supplier', 'create_supplier']);

        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);
    }

    protected function makeIngredient(string $name, string $type): Ingredient
    {
        return Ingredient::create([
            'code' => 'NL'.uniqid(), 'name' => $name, 'type' => $type,
            'unit' => 'Kg', 'reference_price' => 50000, 'status' => true,
        ]);
    }

    public function test_loai_thuc_pham_suy_tu_nguyen_lieu_da_tich(): void
    {
        $bo = $this->makeIngredient('Thịt bò', 'Động vật');
        $bap = $this->makeIngredient('Bắp mỹ', 'Thực vật');

        $page = Livewire::test(CreateSupplier::class)
            ->set('selectedIngredients', [$bo->id => true, $bap->id => true]);

        $names = $page->instance()->derivedTypeNames();
        sort($names);
        $this->assertSame(['Thực vật', 'Động vật'], $names, 'Loại hiển thị suy từ loại nguyên liệu đã tích');

        // Bỏ tích 1 nguyên liệu thì loại tương ứng biến mất.
        $page->set('selectedIngredients', [$bo->id => true, $bap->id => false]);
        $this->assertSame(['Động vật'], array_values($page->instance()->derivedTypeNames()));
    }

    public function test_type_options_method_exists_and_returns_derived_types(): void
    {
        $bo = $this->makeIngredient('Thịt bò', 'Động vật');
        $bap = $this->makeIngredient('Bắp mỹ', 'Thực vật');

        $page = Livewire::test(CreateSupplier::class)
            ->set('selectedIngredients', [$bo->id => true, $bap->id => true]);

        $names = $page->instance()->typeOptions();
        sort($names);
        $this->assertSame(['Thực vật', 'Động vật'], $names);
    }

    public function test_nguyen_lieu_da_tich_luon_nam_dau_va_khong_bien_mat_khi_tim_kiem(): void
    {
        $chosen = $this->makeIngredient('Thịt bò Úc', 'BÒ');
        $this->makeIngredient('Cá thu', 'CÁ');

        $page = Livewire::test(CreateSupplier::class)
            ->set('selectedIngredients', [$chosen->id => true])
            // gõ tìm nguyên liệu KHÁC — dòng đã tích vẫn phải còn, nếu không người dùng tưởng bị mất
            ->set('ingredientSearch', 'Cá thu');

        $rows = collect($page->instance()->ingredients());

        $this->assertSame($chosen->id, $rows->first()->id, 'Nguyên liệu đã tích phải đứng đầu bảng');
        $this->assertTrue($rows->contains('name', 'Cá thu'), 'Kết quả tìm kiếm vẫn hiển thị');
    }

    public function test_luu_ncc_loai_suy_tu_nguyen_lieu_da_tich(): void
    {
        $ingredient = $this->makeIngredient('Bắp mỹ', 'Thực vật');
        $type = IngredientType::where('name', 'Thực vật')->firstOrFail();

        Livewire::test(CreateSupplier::class)
            ->set('name', 'NCC Rau sạch')
            ->set('code', 'NCC-TEST-1')
            ->set('phone', '0900000000')
            ->set('selectedIngredients', [$ingredient->id => true])
            ->set('ingredientCosts.'.$ingredient->id, 45000)
            ->call('save')
            ->assertHasNoErrors();

        $supplier = Supplier::where('code', 'NCC-TEST-1')->firstOrFail();

        // Loại thực phẩm lưu qua pivot bằng ID chuẩn, suy từ loại nguyên liệu đã tích.
        $this->assertDatabaseHas('ingredient_type_supplier', [
            'supplier_id' => $supplier->id,
            'ingredient_type_id' => $type->id,
        ]);

        // Giá NCC ↔ nguyên liệu được lưu vào bảng báo giá
        $this->assertDatabaseHas('ingredient_supplier', [
            'ingredient_id' => $ingredient->id,
            'reference_price' => 45000,
        ]);
    }

    public function test_saved_ingredient_auto_syncs_pivot_table(): void
    {
        $supplier = Supplier::create([
            'name' => 'NCC Tự Động Sync',
            'code' => 'NCC-SYNC-1',
            'phone' => '0901111111',
            'status' => true,
        ]);

        $ingredient = Ingredient::create([
            'code' => 'NL-SYNC-1',
            'name' => 'Cải bắp sync',
            'type' => 'Rau củ',
            'unit' => 'Kg',
            'supplier_id' => $supplier->id,
            'reference_price' => 25000,
            'status' => true,
        ]);

        // Model hook saved tự động chèn dòng vào pivot ingredient_supplier
        $this->assertDatabaseHas('ingredient_supplier', [
            'supplier_id' => $supplier->id,
            'ingredient_id' => $ingredient->id,
            'reference_price' => 25000,
        ]);
    }

    public function test_can_provide_ingredient_strict_option_a(): void
    {
        $supplierProcessed = Supplier::create([
            'name' => 'Đậu Hủ Vũ Biên',
            'code' => 'NCC-DH-1',
            'phone' => '0902222222',
            'status' => true,
        ]);

        $ingredientDauHu = Ingredient::create([
            'code' => 'NL-DH-1',
            'name' => 'Đậu hủ vàng',
            'type' => 'Thực phẩm chế biến',
            'unit' => 'Kg',
            'supplier_id' => $supplierProcessed->id,
            'reference_price' => 28000,
            'status' => true,
        ]);

        $ingredientGao = Ingredient::create([
            'code' => 'NL-GAO-1',
            'name' => 'Gạo Tơm',
            'type' => 'Lương thực',
            'unit' => 'Kg',
            'supplier_id' => null,
            'reference_price' => 19000,
            'status' => true,
        ]);

        // Đậu hủ vàng thuộc NCC Đậu Hủ Vũ Biên -> TRUE
        $this->assertTrue($supplierProcessed->canProvideIngredient($ingredientDauHu));

        // Gạo không thuộc NCC Đậu Hủ Vũ Biên -> FALSE (chặt chẽ Option A, không từ khóa mờ)
        $this->assertFalse($supplierProcessed->canProvideIngredient($ingredientGao));
    }

    public function test_suppliers_list_orders_newest_first(): void
    {
        $old = Supplier::create([
            'name' => 'NCC Cũ',
            'code' => 'NCC-OLD-1',
            'phone' => '0900000001',
            'status' => true,
        ]);

        $new = Supplier::create([
            'name' => 'NCC Mới Tạo',
            'code' => 'NCC-NEW-1',
            'phone' => '0900000002',
            'status' => true,
        ]);

        $page = Livewire::test(ListSuppliers::class);
        $suppliers = $page->instance()->suppliers();

        $this->assertSame($new->id, $suppliers->first()->id, 'NCC mới tạo phải đứng đầu danh sách');
    }

    public function test_xem_chi_tiet_ncc_trang_read_only(): void
    {
        $supplier = Supplier::create([
            'name' => 'NCC Chi Tiết Test',
            'code' => 'NCC-VIEW-1',
            'phone' => '0901234567',
            'status' => true,
        ]);

        Livewire::test(ViewSupplier::class, ['record' => $supplier->id])
            ->assertSuccessful()
            ->assertSee('NCC Chi Tiết Test')
            ->assertSee('NCC-VIEW-1')
            ->assertSee('0901234567');
    }
}
