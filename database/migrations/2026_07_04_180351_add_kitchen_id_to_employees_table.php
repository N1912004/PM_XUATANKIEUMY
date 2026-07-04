<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gắn nhân viên với 1 bếp trực thuộc (kitchen_id) phục vụ lọc & phân quyền dữ liệu.
     * Đồng thời dọn nợ kỹ thuật: hợp nhất cột area (chuỗi cũ) về area_id giữa các môi trường.
     */
    public function up(): void
    {
        // Dọn nợ kỹ thuật area: đảm bảo tồn tại area_id (FK) và bỏ cột area chuỗi cũ nếu còn.
        if (! Schema::hasColumn('employees', 'area_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->foreignId('area_id')->nullable()->after('position')
                    ->constrained('areas')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('employees', 'area')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('area');
            });
        }

        if (! Schema::hasColumn('employees', 'kitchen_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->foreignId('kitchen_id')->nullable()->after('area_id')
                    ->constrained('kitchens')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'kitchen_id')) {
                $table->dropConstrainedForeignId('kitchen_id');
            }
        });
    }
};
