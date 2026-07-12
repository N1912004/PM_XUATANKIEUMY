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
        // 1. Tạo bảng units
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 2. Tạo bảng ingredient_types
        Schema::create('ingredient_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 3. Thêm cột mới unit_id và ingredient_type_id vào bảng ingredients
        Schema::table('ingredients', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable()->after('unit');
            $table->unsignedBigInteger('ingredient_type_id')->nullable()->after('type');

            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
            $table->foreign('ingredient_type_id')->references('id')->on('ingredient_types')->onDelete('set null');
        });

        // 4. Di chuyển dữ liệu cũ
        // Bước 4.1: Chuyển dữ liệu từ cấu hình settings cũ vào bảng mới
        $rawUnits = DB::table('settings')->where('key', 'ingredient_units')->value('value');
        if (! $rawUnits) {
            $rawUnits = 'Kg, Quả, Gói, Chai, Thùng, Lít';
        }
        $units = array_unique(array_map('trim', explode(',', $rawUnits)));
        foreach ($units as $unitName) {
            if (! empty($unitName)) {
                DB::table('units')->updateOrInsert(['name' => $unitName], ['created_at' => now(), 'updated_at' => now()]);
            }
        }

        $rawTypes = DB::table('settings')->where('key', 'ingredient_types')->value('value');
        if (! $rawTypes) {
            $rawTypes = 'Động vật, Thực vật, Thực phẩm khô, Gia vị';
        }
        $types = array_unique(array_map('trim', explode(',', $rawTypes)));
        foreach ($types as $typeName) {
            if (! empty($typeName)) {
                DB::table('ingredient_types')->updateOrInsert(['name' => $typeName], ['created_at' => now(), 'updated_at' => now()]);
            }
        }

        // Bước 4.2: Lấy các đơn vị/loại khác biệt trong bảng ingredients hiện tại (nếu có phần tử tự phát)
        $existingUnits = DB::table('ingredients')->distinct()->pluck('unit')->filter()->toArray();
        foreach ($existingUnits as $unitName) {
            $unitName = trim($unitName);
            DB::table('units')->updateOrInsert(['name' => $unitName], ['created_at' => now(), 'updated_at' => now()]);
        }

        $existingTypes = DB::table('ingredients')->distinct()->pluck('type')->filter()->toArray();
        foreach ($existingTypes as $typeName) {
            $typeName = trim($typeName);
            DB::table('ingredient_types')->updateOrInsert(['name' => $typeName], ['created_at' => now(), 'updated_at' => now()]);
        }

        // Bước 4.3: Ánh xạ unit_id và ingredient_type_id cho các nguyên liệu hiện tại
        $ingredients = DB::table('ingredients')->get();
        foreach ($ingredients as $ingredient) {
            $unitId = null;
            if (! empty($ingredient->unit)) {
                $unitId = DB::table('units')->where('name', trim($ingredient->unit))->value('id');
            }

            $typeId = null;
            if (! empty($ingredient->type)) {
                $typeId = DB::table('ingredient_types')->where('name', trim($ingredient->type))->value('id');
            }

            DB::table('ingredients')->where('id', $ingredient->id)->update([
                'unit_id' => $unitId,
                'ingredient_type_id' => $typeId,
            ]);
        }

        // 5. Đổi tên cột cũ unit và type sang old_unit và old_type để tránh xung đột với Accessor
        Schema::table('ingredients', function (Blueprint $table) {
            $table->renameColumn('unit', 'old_unit');
            $table->renameColumn('type', 'old_type');
        });

        // 6. Cột legacy phải NULLABLE: nguyên liệu tạo mới chỉ ghi unit_id/ingredient_type_id,
        // không ai ghi cột cũ nữa — giữ NOT NULL sẽ làm mọi INSERT mới thất bại
        Schema::table('ingredients', function (Blueprint $table) {
            $table->string('old_unit')->nullable()->change();
            $table->string('old_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khôi phục cột cũ
        Schema::table('ingredients', function (Blueprint $table) {
            $table->renameColumn('old_unit', 'unit');
            $table->renameColumn('old_type', 'type');

            $table->dropForeign(['unit_id']);
            $table->dropForeign(['ingredient_type_id']);
            $table->dropColumn(['unit_id', 'ingredient_type_id']);
        });

        Schema::dropIfExists('ingredient_types');
        Schema::dropIfExists('units');
    }
};
