<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mỗi hồ sơ nhân viên chỉ được gắn TỐI ĐA 1 tài khoản đăng nhập (quan hệ 1-1).
 * Trước đây chỉ có khóa ngoại nên 2 tài khoản có thể trỏ cùng 1 nhân viên → hai người
 * cùng "đứng tên" một nhân sự, kéo theo lọc dữ liệu theo bếp và chấm công bị nhập nhằng.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('employee_id', 'users_employee_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_employee_id_unique');
        });
    }
};
