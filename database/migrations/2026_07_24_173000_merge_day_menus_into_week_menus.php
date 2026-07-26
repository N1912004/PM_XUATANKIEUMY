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
        if (! Schema::hasColumn('week_menus', 'type')) {
            Schema::table('week_menus', function (Blueprint $table): void {
                $table->string('type', 20)->default('week')->after('kitchen_id');
            });
        }

        if (Schema::hasTable('day_menus')) {
            $dayMenus = DB::table('day_menus')->get();
            foreach ($dayMenus as $dayMenu) {
                $weekMenuId = DB::table('week_menus')->insertGetId([
                    'kitchen_id' => $dayMenu->kitchen_id,
                    'type' => 'day',
                    'date_from' => $dayMenu->date,
                    'date_to' => $dayMenu->date,
                    'status' => $dayMenu->status ?? 'draft',
                    'audit_reason' => $dayMenu->audit_reason ?? null,
                    'created_at' => $dayMenu->created_at ?? now(),
                    'updated_at' => $dayMenu->updated_at ?? now(),
                ]);

                if (Schema::hasColumn('menus', 'day_menu_id')) {
                    DB::table('menus')
                        ->where('day_menu_id', $dayMenu->id)
                        ->update(['week_menu_id' => $weekMenuId]);
                }
            }
        }

        if (Schema::hasColumn('menus', 'day_menu_id')) {
            Schema::table('menus', function (Blueprint $table) {
                try {
                    $table->dropForeign(['day_menu_id']);
                } catch (Throwable $e) {
                    // Ignore if foreign key didn't exist or already dropped
                }

                try {
                    $table->dropUnique('menus_day_shift_recipe_unique');
                } catch (Throwable $e) {
                    // Ignore if index didn't exist or already dropped
                }

                $table->dropColumn('day_menu_id');
            });
        }

        Schema::dropIfExists('day_menus');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
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
    }
};
