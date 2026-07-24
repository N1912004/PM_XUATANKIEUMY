<?php

namespace Tests\Feature;

use App\Filament\Resources\SupplierResource\Pages\CreateSupplier;
use App\Models\Ingredient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Ô "Loại thực phẩm cung cấp" của NCC được suy ra từ danh sách nguyên liệu đã chọn.
 * Tích/bỏ tích nguyên liệu (wire:model.live) phải dội ngược lên ô "Loại thực phẩm cung cấp",
 * nếu không form báo thiếu loại thực phẩm và không lưu được.
 */
class SupplierIngredientPickerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        foreach (['view_any_supplier', 'create_supplier'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role = Role::create(['name' => 'NCC'.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo(['view_any_supplier', 'create_supplier']);

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

    public function test_them_nguyen_lieu_tu_dong_cap_nhat_loai_thuc_pham(): void
    {
        $beef = $this->makeIngredient('Bắp bò', 'BÒ');
        $fish = $this->makeIngredient('Cá cơm khô', 'CÁ');

        $page = Livewire::test(CreateSupplier::class)
            ->set('selectedIngredients', [$beef->id => true, $fish->id => true]);

        $types = array_filter(array_map('trim', explode(',', $page->get('type'))));

        sort($types);
        $this->assertSame(['BÒ', 'CÁ'], $types, 'Loại thực phẩm phải tự suy ra từ nguyên liệu đã chọn');

        // Bỏ tích nguyên liệu thì loại thực phẩm cũng phải co lại theo
        $page->set('selectedIngredients', [$beef->id => true, $fish->id => false]);
        $this->assertSame('BÒ', trim($page->get('type')));
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

    public function test_luu_duoc_ncc_sau_khi_chon_nguyen_lieu_bang_o_tim_kiem(): void
    {
        $ingredient = $this->makeIngredient('Bắp mỹ', 'Thực vật');

        Livewire::test(CreateSupplier::class)
            ->set('name', 'NCC Rau sạch')
            ->set('code', 'NCC-TEST-1')
            ->set('phone', '0900000000')
            ->set('selectedIngredients', [$ingredient->id => true])
            ->set('ingredientCosts.'.$ingredient->id, 45000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('suppliers', [
            'code' => 'NCC-TEST-1',
            'type' => 'Thực vật', // suy ra từ nguyên liệu, không nhập tay
        ]);

        // Giá NCC ↔ nguyên liệu được lưu vào bảng báo giá
        $this->assertDatabaseHas('ingredient_supplier', [
            'ingredient_id' => $ingredient->id,
            'reference_price' => 45000,
        ]);
    }

    public function test_saved_ingredient_auto_syncs_pivot_table(): void
    {
        $supplier = \App\Models\Supplier::create([
            'name' => 'NCC Tự Động Sync',
            'code' => 'NCC-SYNC-1',
            'phone' => '0901111111',
            'type' => 'Rau củ',
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
        $supplierProcessed = \App\Models\Supplier::create([
            'name' => 'Đậu Hủ Vũ Biên',
            'code' => 'NCC-DH-1',
            'phone' => '0902222222',
            'type' => 'Thực phẩm chế biến',
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
}
