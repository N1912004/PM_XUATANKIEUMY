<?php

namespace Tests\Feature;

use App\Filament\Resources\IngredientResource\Pages\CreateIngredient;
use App\Filament\Resources\IngredientResource\Pages\EditIngredient;
use App\Models\Ingredient;
use App\Models\IngredientType;
use App\Models\Unit;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IngredientReferencePriceFormTest extends TestCase
{
    use RefreshDatabase;

    protected Unit $unit;

    protected IngredientType $type;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->unit = Unit::firstOrCreate(['name' => 'Kg']);
        $this->type = IngredientType::firstOrCreate(['name' => 'Rau củ quả']);
    }

    protected function adminUser(): User
    {
        $permissions = ['view_any_ingredient', 'create_ingredient', 'update_ingredient'];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role = Role::create(['name' => 'Admin '.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    /** Case 1: Lưu số lớn có định dạng dấu chấm phân cách hàng nghìn (VD: 5.555.557) */
    public function test_lưu_thành_công_số_lớn_có_dấu_chấm_hàng_nghìn(): void
    {
        $user = $this->adminUser();

        Livewire::actingAs($user)
            ->test(CreateIngredient::class)
            ->fillForm([
                'name' => 'Hành tây test',
                'code' => 'HT_001',
                'unit_id' => $this->unit->id,
                'ingredient_type_id' => $this->type->id,
                'reference_price' => '5.555.557',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('ingredients', [
            'code' => 'HT_001',
            'reference_price' => 5555557,
        ]);
    }

    /** Case 2: Kiểm tra khi cập nhật số cực lớn (VD: 100.000.000) trên trang Edit */
    public function test_cập_nhật_số_cực_lớn_trên_trang_edit(): void
    {
        $user = $this->adminUser();

        $ingredient = Ingredient::create([
            'name' => 'Thịt bò Úc',
            'code' => 'TB_002',
            'unit_id' => $this->unit->id,
            'ingredient_type_id' => $this->type->id,
            'reference_price' => 200000,
        ]);

        Livewire::actingAs($user)
            ->test(EditIngredient::class, ['record' => $ingredient->getKey()])
            ->fillForm([
                'reference_price' => '100.000.000',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'reference_price' => 100000000,
        ]);
    }

    /** Case 3: Nhập số có số 0 ở đầu (VD: '050000') -> Tự động chuyển thành 50000 */
    public function test_nhập_số_có_số_không_ở_đầu(): void
    {
        $user = $this->adminUser();

        Livewire::actingAs($user)
            ->test(CreateIngredient::class)
            ->fillForm([
                'name' => 'Cà rốt test',
                'code' => 'CR_003',
                'unit_id' => $this->unit->id,
                'ingredient_type_id' => $this->type->id,
                'reference_price' => '050000',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('ingredients', [
            'code' => 'CR_003',
            'reference_price' => 50000,
        ]);
    }

    /** Case 4: Nhập số âm -> Hệ thống kích hoạt Validation rule minValue(0) và chặn lưu vào DB */
    public function test_nhập_số_âm_bị_chặn_bởi_validation(): void
    {
        $user = $this->adminUser();

        Livewire::actingAs($user)
            ->test(CreateIngredient::class)
            ->fillForm([
                'name' => 'Rau muống test',
                'code' => 'RM_004',
                'unit_id' => $this->unit->id,
                'ingredient_type_id' => $this->type->id,
                'reference_price' => '-50000',
            ])
            ->call('create')
            ->assertHasFormErrors(['reference_price' => 'min']);

        $this->assertDatabaseMissing('ingredients', [
            'code' => 'RM_004',
        ]);
    }

    /** Case 5: Nhập số tiền có dấu chấm phân cách chuẩn tệ (VD: '75.000') */
    public function test_nhập_số_tiền_có_dấu_chấm_phân_cách(): void
    {
        $user = $this->adminUser();

        Livewire::actingAs($user)
            ->test(CreateIngredient::class)
            ->fillForm([
                'name' => 'Tỏi tép test',
                'code' => 'TT_005',
                'unit_id' => $this->unit->id,
                'ingredient_type_id' => $this->type->id,
                'reference_price' => '75.000',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('ingredients', [
            'code' => 'TT_005',
            'reference_price' => 75000,
        ]);
    }
}
