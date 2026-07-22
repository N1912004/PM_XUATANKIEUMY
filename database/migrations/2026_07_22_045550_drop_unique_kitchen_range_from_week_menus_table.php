<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $weekMenuIndexes = collect(Schema::getIndexes('week_menus'))->pluck('name')->all();
        if (! in_array('week_menus_kitchen_id_index', $weekMenuIndexes, true)) {
            Schema::table('week_menus', function (Blueprint $table): void {
                $table->index('kitchen_id', 'week_menus_kitchen_id_index');
            });
        }

        foreach (['week_menus_kitchen_range_unique', 'week_menus_kitchen_start_date_unique'] as $uniqueIndex) {
            if (in_array($uniqueIndex, $weekMenuIndexes, true)) {
                Schema::table('week_menus', function (Blueprint $table) use ($uniqueIndex): void {
                    $table->dropUnique($uniqueIndex);
                });
            }
        }

        $weekMenuIndexes = collect(Schema::getIndexes('week_menus'))->pluck('name')->all();
        if (! in_array('week_menus_kitchen_range_index', $weekMenuIndexes, true)) {
            Schema::table('week_menus', function (Blueprint $table): void {
                $table->index(['kitchen_id', 'date_from', 'date_to'], 'week_menus_kitchen_range_index');
            });
        }

        $menuIndexes = collect(Schema::getIndexes('menus'))->pluck('name')->all();
        if (in_array('menus_kitchen_date_shift_recipe_unique', $menuIndexes, true)) {
            Schema::table('menus', function (Blueprint $table): void {
                $table->dropUnique('menus_kitchen_date_shift_recipe_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $weekMenuIndexes = collect(Schema::getIndexes('week_menus'))->pluck('name')->all();
        Schema::table('week_menus', function (Blueprint $table) use ($weekMenuIndexes): void {
            if (in_array('week_menus_kitchen_range_index', $weekMenuIndexes, true)) {
                $table->dropIndex('week_menus_kitchen_range_index');
            }
            if (! in_array('week_menus_kitchen_range_unique', $weekMenuIndexes, true)) {
                $table->unique(['kitchen_id', 'date_from', 'date_to'], 'week_menus_kitchen_range_unique');
            }
        });

        $menuIndexes = collect(Schema::getIndexes('menus'))->pluck('name')->all();
        if (! in_array('menus_kitchen_date_shift_recipe_unique', $menuIndexes, true)) {
            Schema::table('menus', function (Blueprint $table): void {
                $table->unique(['kitchen_id', 'date', 'shift_id', 'recipe_id'], 'menus_kitchen_date_shift_recipe_unique');
            });
        }
    }
};
