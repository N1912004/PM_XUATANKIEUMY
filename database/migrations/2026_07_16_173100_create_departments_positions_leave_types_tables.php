<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tạo các bảng danh mục mới
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_ot')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // 2. Thu thập dữ liệu từ catalogs trước khi xóa
        $departments = ['Nhân sự', 'Kế toán', 'Kho', 'Sản xuất', 'IT', 'Kinh doanh', 'Chăm sóc KH'];
        $positions = ['Bếp trưởng', 'Bếp phó', 'Tổ trưởng bếp', 'Thủ kho', 'Nhân viên bếp', 'Nhân viên phục vụ', 'Chuyên viên', 'Quản lý'];
        $leaveTypes = ['Nghỉ phép năm', 'Nghỉ phép bệnh', 'Nghỉ không lương', 'Nghỉ thai sản', 'Tăng ca ngày thường', 'Tăng ca cuối tuần'];

        if (Schema::hasTable('catalogs')) {
            $dbDepts = DB::table('catalogs')->where('group', 'department')->orderBy('sort')->pluck('name')->toArray();
            if (! empty($dbDepts)) {
                $departments = $dbDepts;
            }
            $dbPos = DB::table('catalogs')->where('group', 'position')->orderBy('sort')->pluck('name')->toArray();
            if (! empty($dbPos)) {
                $positions = $dbPos;
            }
            $dbLeaveTypes = DB::table('catalogs')->where('group', 'leave_type')->orderBy('sort')->pluck('name')->toArray();
            if (! empty($dbLeaveTypes)) {
                $leaveTypes = $dbLeaveTypes;
            }
        }

        // 3. Seed dữ liệu mặc định vào các bảng mới
        foreach ($departments as $index => $name) {
            DB::table('departments')->insertOrIgnore([
                'name' => $name,
                'sort' => $index,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($positions as $index => $name) {
            DB::table('positions')->insertOrIgnore([
                'name' => $name,
                'sort' => $index,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($leaveTypes as $index => $name) {
            $isOt = str_contains($name, 'Tăng ca') || str_contains($name, 'tăng ca') || str_contains($name, 'OT') || str_contains($name, 'ot');
            DB::table('leave_types')->insertOrIgnore([
                'name' => $name,
                'is_ot' => $isOt,
                'sort' => $index,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Thu thập dữ liệu thực tế hiện hữu trong các bảng nghiệp vụ (nếu có để không mất dữ liệu)
        if (Schema::hasTable('employees')) {
            if (Schema::hasColumn('employees', 'department')) {
                $existingDepts = DB::table('employees')->whereNotNull('department')->where('department', '!=', '')->distinct()->pluck('department');
                foreach ($existingDepts as $name) {
                    DB::table('departments')->insertOrIgnore([
                        'name' => $name,
                        'sort' => 99,
                        'active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            if (Schema::hasColumn('employees', 'position')) {
                $existingPos = DB::table('employees')->whereNotNull('position')->where('position', '!=', '')->distinct()->pluck('position');
                foreach ($existingPos as $name) {
                    DB::table('positions')->insertOrIgnore([
                        'name' => $name,
                        'sort' => 99,
                        'active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        if (Schema::hasTable('leave_overtimes') && Schema::hasColumn('leave_overtimes', 'type')) {
            $existingLeaveTypes = DB::table('leave_overtimes')->whereNotNull('type')->where('type', '!=', '')->distinct()->pluck('type');
            foreach ($existingLeaveTypes as $name) {
                $isOt = str_contains($name, 'Tăng ca') || str_contains($name, 'tăng ca') || str_contains($name, 'OT') || str_contains($name, 'ot');
                DB::table('leave_types')->insertOrIgnore([
                    'name' => $name,
                    'is_ot' => $isOt,
                    'sort' => 99,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 5. Thêm cột khóa ngoại vào employees
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('phone')->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->after('department_id')->constrained('positions')->nullOnDelete();
        });

        // 6. Thêm cột khóa ngoại vào leave_overtimes
        Schema::table('leave_overtimes', function (Blueprint $table) {
            $table->foreignId('leave_type_id')->nullable()->after('employee_id')->constrained('leave_types')->nullOnDelete();
        });

        // 7. Ánh xạ dữ liệu cũ sang khóa ngoại mới.
        //    Dùng Query Builder thay raw SQL để chạy được cả MySQL lẫn SQLite (test):
        //    tên trong bảng danh mục là duy nhất nên khớp theo name.
        if (Schema::hasTable('employees')) {
            if (Schema::hasColumn('employees', 'department')) {
                foreach (DB::table('departments')->pluck('id', 'name') as $name => $id) {
                    DB::table('employees')->where('department', $name)->update(['department_id' => $id]);
                }
            }
            if (Schema::hasColumn('employees', 'position')) {
                foreach (DB::table('positions')->pluck('id', 'name') as $name => $id) {
                    DB::table('employees')->where('position', $name)->update(['position_id' => $id]);
                }
            }
        }

        if (Schema::hasTable('leave_overtimes') && Schema::hasColumn('leave_overtimes', 'type')) {
            foreach (DB::table('leave_types')->pluck('id', 'name') as $name => $id) {
                DB::table('leave_overtimes')->where('type', $name)->update(['leave_type_id' => $id]);
            }
        }

        // 8. Xóa cột cũ
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'department')) {
                $table->dropColumn('department');
            }
            if (Schema::hasColumn('employees', 'position')) {
                $table->dropColumn('position');
            }
        });

        Schema::table('leave_overtimes', function (Blueprint $table) {
            if (Schema::hasColumn('leave_overtimes', 'type')) {
                $table->dropColumn('type');
            }
        });

        // 9. Giữ nguyên bảng catalogs và dữ liệu 3 nhóm (department/position/leave_type)
        //    để CatalogResource và mã cũ vẫn chạy — 3 nhóm này nay quản song song ở cả
        //    bảng riêng lẫn catalogs. Không xóa gì.
    }

    public function down(): void
    {
        // 1. Khôi phục các cột cũ trên bảng employees
        Schema::table('employees', function (Blueprint $table) {
            $table->string('department')->nullable()->after('phone');
            $table->string('position')->nullable()->after('department');
        });

        // 2. Khôi phục cột cũ trên bảng leave_overtimes
        Schema::table('leave_overtimes', function (Blueprint $table) {
            $table->string('type')->nullable()->after('employee_id');
        });

        // 3. Ánh xạ ngược dữ liệu (Query Builder cho tương thích SQLite/MySQL)
        foreach (DB::table('departments')->pluck('name', 'id') as $id => $name) {
            DB::table('employees')->where('department_id', $id)->update(['department' => $name]);
        }
        foreach (DB::table('positions')->pluck('name', 'id') as $id => $name) {
            DB::table('employees')->where('position_id', $id)->update(['position' => $name]);
        }
        foreach (DB::table('leave_types')->pluck('name', 'id') as $id => $name) {
            DB::table('leave_overtimes')->where('leave_type_id', $id)->update(['type' => $name]);
        }

        // 4. Xóa các cột FK mới
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('position_id');
        });

        Schema::table('leave_overtimes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('leave_type_id');
        });

        // 5. Xóa các bảng mới
        Schema::dropIfExists('departments');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('leave_types');
    }
};
