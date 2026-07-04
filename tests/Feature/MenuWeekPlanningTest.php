<?php

namespace Tests\Feature;

use App\Filament\Pages\LapThucDonTuan;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MenuWeekPlanningTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_week_creates_menu_rows_per_day(): void
    {
        $this->actingAs($this->createSuperAdmin());
        [$shift, $recipe] = $this->seedData();

        Livewire::test(LapThucDonTuan::class)
            ->set('data.kitchen_id', null)
            ->set('data.week_start', '2026-06-01')
            ->set('data.entries', [
                ['day' => 0, 'shift_id' => $shift->id, 'recipe_id' => $recipe->id, 'estimated_portions' => 100],
                ['day' => 2, 'shift_id' => $shift->id, 'recipe_id' => $recipe->id, 'estimated_portions' => 120],
            ])
            ->call('lockWeek')
            ->assertHasNoErrors();

        $this->assertTrue(
            Menu::whereDate('date', '2026-06-01')->where('recipe_id', $recipe->id)
                ->where('estimated_portions', 100)->where('status', 'locked')->exists(),
        );
        $this->assertTrue(
            Menu::whereDate('date', '2026-06-03')->where('recipe_id', $recipe->id)
                ->where('estimated_portions', 120)->where('status', 'locked')->exists(),
        );
    }

    public function test_duplicate_warning_flags_recipe_used_within_three_weeks(): void
    {
        [$shift, $recipe] = $this->seedData();

        // Món đã dùng 10 ngày trước tuần đang lập
        Menu::create([
            'date' => '2026-05-22', 'shift_id' => $shift->id, 'recipe_id' => $recipe->id,
            'estimated_portions' => 80, 'status' => 'locked',
        ]);

        $page = new LapThucDonTuan;
        $warnings = $page->duplicateWarnings(
            [['recipe_id' => $recipe->id]],
            Carbon::parse('2026-06-01'),
            null,
        );

        $this->assertContains($recipe->name, $warnings);
    }

    public function test_no_duplicate_warning_when_recipe_is_older_than_three_weeks(): void
    {
        [$shift, $recipe] = $this->seedData();

        // Món dùng 30 ngày trước → ngoài cửa sổ 21 ngày → không cảnh báo
        Menu::create([
            'date' => '2026-05-02', 'shift_id' => $shift->id, 'recipe_id' => $recipe->id,
            'estimated_portions' => 80, 'status' => 'locked',
        ]);

        $warnings = (new LapThucDonTuan)->duplicateWarnings(
            [['recipe_id' => $recipe->id]],
            Carbon::parse('2026-06-01'),
            null,
        );

        $this->assertEmpty($warnings);
    }

    /**
     * @return array{0: Shift, 1: Recipe}
     */
    protected function seedData(): array
    {
        $recipe = Recipe::create([
            'code' => 'R1', 'name' => 'Gà chiên', 'type' => 'Món mặn',
            'price_level' => 30000, 'actual_price' => 25000, 'status' => 'active',
        ]);
        $shift = Shift::create(['name' => 'Ca Trưa', 'time_range' => '06:00 - 14:00']);

        return [$shift, $recipe];
    }
}
