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
        // 1. Tạo bảng recipe_types
        Schema::create('recipe_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 2. Thêm cột recipe_type_id vào recipes
        Schema::table('recipes', function (Blueprint $table) {
            $table->unsignedBigInteger('recipe_type_id')->nullable()->after('type');
            $table->foreign('recipe_type_id')->references('id')->on('recipe_types')->onDelete('set null');
        });

        // 3. Seed các nhóm món mặc định
        $defaultTypes = ['Món mặn', 'Món 1', 'Món 2', 'Món 3', 'Món xào', 'Rau xào/Luộc', 'Món canh', 'Món chay', 'Tráng miệng', 'Món khác'];
        foreach ($defaultTypes as $typeName) {
            DB::table('recipe_types')->updateOrInsert(
                ['name' => $typeName],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // 4. Ánh xạ dữ liệu cũ từ `type` sang `recipe_type_id`
        $recipes = DB::table('recipes')->get();
        foreach ($recipes as $recipe) {
            $typeId = null;
            if (! empty($recipe->type)) {
                $typeName = trim($recipe->type);
                // Tìm hoặc tự tạo loại nhóm món nếu chưa có
                $typeId = DB::table('recipe_types')->where('name', $typeName)->value('id');
                if (! $typeId) {
                    $typeId = DB::table('recipe_types')->insertGetId([
                        'name' => $typeName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('recipes')->where('id', $recipe->id)->update([
                'recipe_type_id' => $typeId,
            ]);
        }

        // 5. Đổi tên cột type sang old_type và cho phép nullable
        Schema::table('recipes', function (Blueprint $table) {
            $table->renameColumn('type', 'old_type');
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->string('old_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->renameColumn('old_type', 'type');
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->dropForeign(['recipe_type_id']);
            $table->dropColumn('recipe_type_id');
        });

        Schema::dropIfExists('recipe_types');
    }
};
