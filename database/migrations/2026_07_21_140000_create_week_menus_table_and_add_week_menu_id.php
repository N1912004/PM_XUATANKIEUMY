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
        if (! Schema::hasTable('week_menus')) {
            Schema::create('week_menus', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('kitchen_id')->constrained('kitchens')->cascadeOnDelete();
                $table->date('date_from');
                $table->date('date_to');
                $table->string('status')->default('draft');
                $table->string('audit_reason')->nullable();
                $table->timestamps();

                $table->unique(['kitchen_id', 'date_from', 'date_to'], 'week_menus_kitchen_range_unique');
            });
        } else {
            Schema::table('week_menus', function (Blueprint $table): void {
                if (Schema::hasColumn('week_menus', 'start_date')) {
                    $table->renameColumn('start_date', 'date_from');
                }
                if (Schema::hasColumn('week_menus', 'end_date')) {
                    $table->renameColumn('end_date', 'date_to');
                }
            });
        }

        if (! Schema::hasColumn('menus', 'week_menu_id')) {
            Schema::table('menus', function (Blueprint $table): void {
                $table->foreignId('week_menu_id')->nullable()->after('kitchen_id')->constrained('week_menus')->nullOnDelete();
            });
        }

        $indexNames = collect(Schema::getIndexes('menus'))->pluck('name')->all();

        Schema::table('menus', function (Blueprint $table) use ($indexNames): void {
            if (in_array('menus_kitchen_id_date_shift_id_recipe_id_unique', $indexNames, true)) {
                $table->dropUnique('menus_kitchen_id_date_shift_id_recipe_id_unique');
            }

            if (! in_array('menus_kitchen_date_shift_recipe_week_unique', $indexNames, true)) {
                $table->unique(['kitchen_id', 'date', 'shift_id', 'recipe_id', 'week_menu_id'], 'menus_kitchen_date_shift_recipe_week_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexNames = collect(Schema::getIndexes('menus'))->pluck('name')->all();

        Schema::table('menus', function (Blueprint $table) use ($indexNames): void {
            if (in_array('menus_kitchen_date_shift_recipe_week_unique', $indexNames, true)) {
                $table->dropUnique('menus_kitchen_date_shift_recipe_week_unique');
            }

            $table->dropForeign(['week_menu_id']);
            $table->dropColumn('week_menu_id');
        });

        Schema::dropIfExists('week_menus');
    }
};
