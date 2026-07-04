<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Bổ sung trạng thái 'sent' (Đã gửi khách hàng) vào ENUM menus.status.
     * Chỉ áp dụng cho MySQL — trên SQLite cột status là string nên đã chấp nhận sẵn.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `menus` MODIFY `status` ENUM('draft', 'sent', 'locked') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Đưa các bản ghi 'sent' về 'draft' trước khi thu hẹp ENUM để tránh mất dữ liệu
        DB::table('menus')->where('status', 'sent')->update(['status' => 'draft']);
        DB::statement("ALTER TABLE `menus` MODIFY `status` ENUM('draft', 'locked') NOT NULL DEFAULT 'draft'");
    }
};
