<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuAuditLog;
use App\Models\Recipe;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuAuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_editing_locked_menu_records_audit_trail(): void
    {
        $user = $this->createSuperAdmin();
        $this->actingAs($user);

        $menu = $this->makeMenu('locked', 200);

        $menu->update(['estimated_portions' => 260]);

        $log = MenuAuditLog::where('menu_id', $menu->id)->where('field', 'estimated_portions')->first();

        $this->assertNotNull($log);
        $this->assertSame('updated', $log->action);
        $this->assertSame('200', $log->old_value);
        $this->assertSame('260', $log->new_value);
        $this->assertSame($user->id, $log->user_id);
    }

    public function test_editing_draft_menu_is_not_audited(): void
    {
        $this->actingAs($this->createSuperAdmin());

        $menu = $this->makeMenu('draft', 100);
        $menu->update(['estimated_portions' => 150]);

        $this->assertSame(0, MenuAuditLog::count());
    }

    public function test_deleting_finalized_menu_is_audited(): void
    {
        $this->actingAs($this->createSuperAdmin());

        $menu = $this->makeMenu('sent', 120);
        $menuId = $menu->id;
        $menu->delete();

        $this->assertDatabaseHas('menu_audit_logs', [
            'action' => 'deleted',
            'old_value' => 'Thực đơn #'.$menuId,
        ]);
    }

    protected function makeMenu(string $status, int $portions): Menu
    {
        $recipe = Recipe::create([
            'code' => 'R'.uniqid(), 'name' => 'Món test', 'type' => 'Món mặn',
            'price_level' => 30000, 'actual_price' => 25000, 'status' => 'active',
        ]);
        $shift = Shift::create(['name' => 'Ca '.uniqid(), 'time_range' => '06:00 - 14:00']);

        return Menu::create([
            'date' => '2026-05-18', 'shift_id' => $shift->id, 'recipe_id' => $recipe->id,
            'estimated_portions' => $portions, 'status' => $status,
        ]);
    }
}
