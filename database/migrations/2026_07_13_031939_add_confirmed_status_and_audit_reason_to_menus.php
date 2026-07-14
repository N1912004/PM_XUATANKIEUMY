<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BA 07/07/2026: vòng đời thực đơn chuẩn là
     * Nháp (draft) → Gửi xác nhận (sent) → Khách đã xác nhận (confirmed) → Đã chốt (locked).
     * Đồng thời bổ sung cột `reason` trên menu_audit_logs — bắt buộc ghi lý do khi sửa
     * thực đơn đã chốt.
     */
    public function up(): void
    {
        // Chỉ áp dụng cho MySQL — trên SQLite cột status là string nên đã chấp nhận sẵn
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `menus` MODIFY `status` ENUM('draft', 'sent', 'confirmed', 'locked') NOT NULL DEFAULT 'draft'");
        }

        Schema::table('menu_audit_logs', function (Blueprint $table) {
            $table->string('reason')->nullable()->after('new_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_audit_logs', function (Blueprint $table) {
            $table->dropColumn('reason');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::table('menus')->where('status', 'confirmed')->update(['status' => 'sent']);
            DB::statement("ALTER TABLE `menus` MODIFY `status` ENUM('draft', 'sent', 'locked') NOT NULL DEFAULT 'draft'");
        }
    }
};
