<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tạo bảng `kitchen_types` riêng biệt, chuyển `kitchens.type` (string)
 * thành `kitchens.kitchen_type_id` (FK), và dọn dẹp bảng `catalogs`.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tạo bảng kitchen_types
        Schema::create('kitchen_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // 2. Seed dữ liệu mặc định
        $defaults = [
            'Bếp sản xuất',
            'Bếp ăn',
            'Bếp trung tâm',
            'Kho trung chuyển',
            'Điểm chia suất',
            'Nhà ăn phục vụ',
        ];

        foreach ($defaults as $index => $name) {
            DB::table('kitchen_types')->insertOrIgnore([
                'name' => $name,
                'sort' => $index,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2b. Gom nốt giá trị thực tế đang tồn tại trong kitchens.type (tránh mất dữ liệu)
        if (Schema::hasColumn('kitchens', 'type')) {
            $existingTypes = DB::table('kitchens')
                ->whereNotNull('type')
                ->where('type', '!=', '')
                ->distinct()
                ->pluck('type');
            // Bỏ các tên đã seed ở trên để không lặp insertOrIgnore vô ích.
            $existingTypes = $existingTypes->reject(fn ($name) => in_array($name, $defaults, true));

            foreach ($existingTypes as $typeName) {
                DB::table('kitchen_types')->insertOrIgnore([
                    'name' => $typeName,
                    'sort' => 99,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Thêm cột FK kitchen_type_id vào kitchens
        Schema::table('kitchens', function (Blueprint $table) {
            $table->foreignId('kitchen_type_id')
                ->nullable()
                ->after('name')
                ->constrained('kitchen_types')
                ->nullOnDelete();
        });

        // 4. Map dữ liệu: kitchens.type (string) → kitchens.kitchen_type_id (FK).
        //    Dùng Eloquent/Query Builder thay raw SQL để chạy được cả trên MySQL lẫn
        //    SQLite (test): tên bảng kitchen_types là duy nhất nên khớp theo name.
        if (Schema::hasColumn('kitchens', 'type')) {
            $typeIds = DB::table('kitchen_types')->pluck('id', 'name');

            foreach ($typeIds as $name => $id) {
                DB::table('kitchens')->where('type', $name)->update(['kitchen_type_id' => $id]);
            }

            // 5. Drop cột type cũ (giữ kitchen_type_id nullable — dữ liệu cũ có thể chưa có loại)
            Schema::table('kitchens', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        // 6. Dọn dẹp catalogs: xóa các dòng kitchen_type
        DB::table('catalogs')->where('group', 'kitchen_type')->delete();
    }

    public function down(): void
    {
        // 1. Khôi phục cột type (string) trong kitchens
        if (! Schema::hasColumn('kitchens', 'type')) {
            Schema::table('kitchens', function (Blueprint $table) {
                $table->string('type')->nullable()->after('name');
            });

            // Map ngược: kitchen_type_id → type (string)
            DB::statement('
                UPDATE kitchens
                SET type = (
                    SELECT name FROM kitchen_types WHERE kitchen_types.id = kitchens.kitchen_type_id LIMIT 1
                )
                WHERE kitchen_type_id IS NOT NULL
            ');
        }

        // 2. Drop cột FK
        if (Schema::hasColumn('kitchens', 'kitchen_type_id')) {
            Schema::table('kitchens', function (Blueprint $table) {
                $table->dropConstrainedForeignId('kitchen_type_id');
            });
        }

        // 3. Khôi phục dữ liệu catalogs
        $defaults = ['Bếp sản xuất', 'Bếp ăn', 'Bếp trung tâm', 'Kho trung chuyển', 'Điểm chia suất', 'Nhà ăn phục vụ'];
        foreach ($defaults as $index => $name) {
            DB::table('catalogs')->insertOrIgnore([
                'group' => 'kitchen_type',
                'name' => $name,
                'sort' => $index,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Drop bảng kitchen_types
        Schema::dropIfExists('kitchen_types');
    }
};
