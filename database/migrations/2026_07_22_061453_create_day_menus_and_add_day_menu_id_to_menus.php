<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('day_menus')) {
            Schema::create('day_menus', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('kitchen_id')->constrained('kitchens')->cascadeOnDelete();
                $table->date('date');
                $table->string('status')->default('draft');
                $table->string('audit_reason')->nullable();
                $table->timestamps();

                $table->index(['kitchen_id', 'date'], 'day_menus_kitchen_date_index');
            });
        }

        if (! Schema::hasColumn('menus', 'day_menu_id')) {
            Schema::table('menus', function (Blueprint $table): void {
                $table->foreignId('day_menu_id')->nullable()->after('week_menu_id')->constrained('day_menus')->nullOnDelete();
            });
        }

        $menuIndexes = collect(Schema::getIndexes('menus'))->pluck('name')->all();
        if (! in_array('menus_day_shift_recipe_unique', $menuIndexes, true)) {
            Schema::table('menus', function (Blueprint $table): void {
                $table->unique(['day_menu_id', 'shift_id', 'recipe_id'], 'menus_day_shift_recipe_unique');
            });
        }

        $statusRanks = ['draft' => 0, 'sent' => 1, 'confirmed' => 1, 'locked' => 2];
        $legacyGroups = DB::table('menus')
            ->whereNull('week_menu_id')
            ->whereNull('day_menu_id')
            ->whereNotNull('kitchen_id')
            ->select('kitchen_id', 'date')
            ->distinct()
            ->get();

        foreach ($legacyGroups as $group) {
            $statuses = DB::table('menus')
                ->whereNull('week_menu_id')
                ->whereNull('day_menu_id')
                ->where('kitchen_id', $group->kitchen_id)
                ->where('date', $group->date)
                ->pluck('status');
            $status = $statuses->sortByDesc(fn (string $value): int => $statusRanks[$value] ?? 0)->first() ?? 'draft';
            $dayMenuId = DB::table('day_menus')->insertGetId([
                'kitchen_id' => $group->kitchen_id,
                'date' => $group->date,
                'status' => $status === 'confirmed' ? 'sent' : $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('menus')
                ->whereNull('week_menu_id')
                ->whereNull('day_menu_id')
                ->where('kitchen_id', $group->kitchen_id)
                ->where('date', $group->date)
                ->update(['day_menu_id' => $dayMenuId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table): void {
            $table->dropUnique('menus_day_shift_recipe_unique');
            $table->dropForeign(['day_menu_id']);
            $table->dropColumn('day_menu_id');
        });

        Schema::dropIfExists('day_menus');
    }
};
