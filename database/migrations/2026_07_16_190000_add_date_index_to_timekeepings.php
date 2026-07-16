<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm index đơn cho `timekeepings.date`: trang Chấm công lọc mặc định theo NGÀY đơn,
 * nhưng chỉ có composite (employee_id, date) — filter theo date không dùng được leftmost
 * của composite nên quét toàn bảng. Bảng chấm công lớn dần theo ngày.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timekeepings', function (Blueprint $table) {
            $table->index('date', 'timekeepings_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('timekeepings', function (Blueprint $table) {
            $table->dropIndex('timekeepings_date_index');
        });
    }
};
