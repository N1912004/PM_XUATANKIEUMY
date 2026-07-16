<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Chuẩn hoá giá trị cột `status` toàn hệ thống (GIỮ NGUYÊN TÊN CỘT `status`):
 *  - Cột 2 trạng thái  -> boolean 0/1  (areas). suppliers & ingredients vốn đã boolean nên không đụng.
 *  - Cột >= 3 trạng thái -> chuỗi tiếng Anh không dấu (kitchens, employees, timekeepings,
 *    food_safety_audits, leave_overtimes, stock_transfers). menus vốn đã English (draft/sent/...).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. kitchens: status (chuỗi VN, 3 trạng thái) -> English, giữ cột string
        DB::table('kitchens')->where('status', 'Đang hoạt động')->update(['status' => 'active']);
        DB::table('kitchens')->where('status', 'Tạm dừng')->update(['status' => 'paused']);
        DB::table('kitchens')->where('status', 'Bảo trì')->update(['status' => 'maintenance']);

        // 2. areas: status (chuỗi VN, 2 trạng thái) -> boolean 0/1, giữ tên cột 'status'
        Schema::table('areas', function (Blueprint $table) {
            $table->boolean('status_new')->default(true)->after('status');
        });
        DB::table('areas')->where('status', '<>', 'Đang hoạt động')->update(['status_new' => false]);
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('areas', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });

        // 3. employees: status + marital_status -> English
        DB::table('employees')->where('status', 'Đang làm việc')->update(['status' => 'working']);
        DB::table('employees')->where('status', 'Nghỉ phép')->update(['status' => 'on_leave']);
        DB::table('employees')->whereIn('status', ['Nghỉ việc', 'Đã nghỉ việc'])->update(['status' => 'resigned']);

        DB::table('employees')->where('marital_status', 'Độc thân')->update(['marital_status' => 'single']);
        DB::table('employees')->where('marital_status', 'Đã kết hôn')->update(['marital_status' => 'married']);
        DB::table('employees')->where('marital_status', 'Ly hôn')->update(['marital_status' => 'divorced']);
        DB::table('employees')->where('marital_status', 'Góa')->update(['marital_status' => 'widowed']);

        // (timekeepings: CỐ Ý KHÔNG đổi ở migration này — bộ giá trị thực tế là
        //  Đúng giờ/Đi trễ/Tăng ca/Nghỉ phép/Vắng mặt và toàn bộ code timekeeping vẫn dùng tiếng Việt.
        //  Giữ nguyên tiếng Việt để nhất quán code↔DB; chuẩn hoá riêng khi cập nhật đồng bộ cả module.)

        // 5. food_safety_audits: status -> English + default pending
        DB::table('food_safety_audits')->where('status', 'Đạt')->update(['status' => 'passed']);
        DB::table('food_safety_audits')->where('status', 'Không đạt')->update(['status' => 'failed']);
        Schema::table('food_safety_audits', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });

        // 6. leave_overtimes: status -> English
        DB::table('leave_overtimes')->whereIn('status', ['Chờ duyệt', 'cho_duyet'])->update(['status' => 'pending']);
        DB::table('leave_overtimes')->whereIn('status', ['Đồng ý', 'Đã duyệt', 'dong_y', 'da_duyet'])->update(['status' => 'approved']);
        DB::table('leave_overtimes')->whereIn('status', ['Từ chối', 'Không đồng ý', 'tu_choi', 'khong_dong_y'])->update(['status' => 'rejected']);
        DB::table('leave_overtimes')->where('status', 'Đã hủy')->update(['status' => 'cancelled']);

        // 7. stock_transfers: status -> English
        DB::table('stock_transfers')->where('status', 'Đang chuyển')->update(['status' => 'in_transit']);
        DB::table('stock_transfers')->where('status', 'Hoàn thành')->update(['status' => 'completed']);
        DB::table('stock_transfers')->where('status', 'Hủy')->update(['status' => 'cancelled']);

        // 8. menus: ENUM -> varchar(30) (giá trị draft/sent/confirmed/locked giữ nguyên)
        Schema::table('menus', function (Blueprint $table) {
            $table->string('status', 30)->default('draft')->change();
        });
    }

    public function down(): void
    {
        // 1. kitchens: English -> VN
        DB::table('kitchens')->where('status', 'active')->update(['status' => 'Đang hoạt động']);
        DB::table('kitchens')->where('status', 'paused')->update(['status' => 'Tạm dừng']);
        DB::table('kitchens')->where('status', 'maintenance')->update(['status' => 'Bảo trì']);

        // 2. areas: boolean -> chuỗi VN
        Schema::table('areas', function (Blueprint $table) {
            $table->string('status_old')->default('Đang hoạt động')->after('status');
        });
        DB::table('areas')->where('status', false)->update(['status_old' => 'Tạm dừng']);
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('areas', function (Blueprint $table) {
            $table->renameColumn('status_old', 'status');
        });

        // 3. employees
        DB::table('employees')->where('status', 'working')->update(['status' => 'Đang làm việc']);
        DB::table('employees')->where('status', 'on_leave')->update(['status' => 'Nghỉ phép']);
        DB::table('employees')->where('status', 'resigned')->update(['status' => 'Nghỉ việc']);

        DB::table('employees')->where('marital_status', 'single')->update(['marital_status' => 'Độc thân']);
        DB::table('employees')->where('marital_status', 'married')->update(['marital_status' => 'Đã kết hôn']);
        DB::table('employees')->where('marital_status', 'divorced')->update(['marital_status' => 'Ly hôn']);
        DB::table('employees')->where('marital_status', 'widowed')->update(['marital_status' => 'Góa']);

        // (timekeepings: không đụng — xem chú thích ở up())

        // 5. food_safety_audits
        DB::table('food_safety_audits')->where('status', 'passed')->update(['status' => 'Đạt']);
        DB::table('food_safety_audits')->where('status', 'failed')->update(['status' => 'Không đạt']);

        // 6. leave_overtimes
        DB::table('leave_overtimes')->where('status', 'pending')->update(['status' => 'Chờ duyệt']);
        DB::table('leave_overtimes')->where('status', 'approved')->update(['status' => 'Đồng ý']);
        DB::table('leave_overtimes')->where('status', 'rejected')->update(['status' => 'Từ chối']);
        DB::table('leave_overtimes')->where('status', 'cancelled')->update(['status' => 'Đã hủy']);

        // 7. stock_transfers
        DB::table('stock_transfers')->where('status', 'in_transit')->update(['status' => 'Đang chuyển']);
        DB::table('stock_transfers')->where('status', 'completed')->update(['status' => 'Hoàn thành']);
        DB::table('stock_transfers')->where('status', 'cancelled')->update(['status' => 'Hủy']);

        // 8. menus: giữ varchar (không phục hồi ENUM để tránh phụ thuộc MySQL)
    }
};
