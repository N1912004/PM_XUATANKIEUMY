<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Danh mục cấu hình động (BA R37): các danh mục nghiệp vụ phải sửa được trên UI có phân quyền,
 * KHÔNG hard-code trong mã nguồn (khách đổi danh mục thì không phải deploy lại).
 *
 * Gom vào MỘT bảng có cột `group` thay vì mỗi danh mục một bảng: cùng một màn quản trị,
 * cùng một bộ quyền; thêm nhóm mới chỉ cần thêm 1 hằng số.
 *
 * KHÔNG đưa vào đây: trạng thái/quy trình cố định (trạng thái PO, trạng thái kiểm thực,
 * loại giao dịch kho, trạng thái nhân viên) — BA yêu cầu giữ hard-code vì gắn chặt với logic.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('group')->index();   // kitchen_type | department | position | leave_type
            $table->string('name');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['group', 'name']);
        });

        // Seed đúng các giá trị đang hard-code trong code để không đổi hành vi hiện hữu.
        // Dùng literal string (không tham chiếu hằng Catalog::*) để migration là snapshot
        // độc lập — nhóm kitchen_type sau này đã tách sang bảng kitchen_types riêng.
        $seed = [
            'kitchen_type' => ['Bếp sản xuất', 'Bếp ăn', 'Bếp trung tâm', 'Kho trung chuyển', 'Điểm chia suất', 'Nhà ăn phục vụ'],
            'department' => ['Nhân sự', 'Kế toán', 'Kho', 'Sản xuất', 'IT', 'Kinh doanh', 'Chăm sóc KH'],
            'position' => ['Bếp trưởng', 'Bếp phó', 'Tổ trưởng bếp', 'Thủ kho', 'Nhân viên bếp', 'Nhân viên phục vụ', 'Chuyên viên', 'Quản lý'],
            'leave_type' => ['Nghỉ phép năm', 'Nghỉ phép bệnh', 'Nghỉ không lương', 'Nghỉ thai sản', 'Tăng ca ngày thường', 'Tăng ca cuối tuần'],
        ];

        $rows = [];
        foreach ($seed as $group => $names) {
            foreach (array_values($names) as $index => $name) {
                $rows[] = [
                    'group' => $group,
                    'name' => $name,
                    'sort' => $index,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('catalogs')->insert($rows);

        // Gom nốt các giá trị ĐANG TỒN TẠI trong dữ liệu thật (có thể do seeder/import sinh ra
        // và không nằm trong danh sách trên) — nếu bỏ sót, bản ghi cũ sẽ hiển thị giá trị mà
        // form không còn chọn lại được.
        $existing = [
            'kitchen_type' => ['kitchens', 'type'],
            'department' => ['employees', 'department'],
            'position' => ['employees', 'position'],
            'leave_type' => ['leave_overtimes', 'type'],
        ];

        foreach ($existing as $group => [$table, $column]) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $values = DB::table($table)
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->distinct()
                ->pluck($column);

            foreach ($values as $value) {
                DB::table('catalogs')->insertOrIgnore([
                    'group' => $group,
                    'name' => $value,
                    'sort' => 99,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogs');
    }
};
