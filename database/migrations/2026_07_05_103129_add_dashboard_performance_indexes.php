<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm index phục vụ hiệu năng cho trang TỔNG QUAN (Dashboard).
 *
 * Các thẻ thống kê và danh sách trên Dashboard lọc/đếm theo status và date.
 * Khi dữ liệu lớn (hàng triệu dòng), thiếu index khiến full-table scan và trang
 * tải rất chậm. Các index dưới đây giúp truy vấn dùng được index seek.
 *
 * Ghi chú: dù tài liệu CSDL mô tả `menus` có unique index (date, shift_id, recipe_id),
 * thực tế migration tạo bảng KHÔNG hề tạo index đó — nên lọc theo ngày đang full-scan.
 * Ở đây thêm index tổ hợp (date, shift_id) phủ cả WHERE date=? và ORDER BY shift_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table): void {
            $table->index(['date', 'shift_id'], 'menus_date_shift_id_index');
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->index('status', 'purchase_orders_status_index');
            $table->index('created_at', 'purchase_orders_created_at_index');
        });

        Schema::table('food_safety_audits', function (Blueprint $table): void {
            $table->index('date', 'food_safety_audits_date_index');
        });

        Schema::table('ingredients', function (Blueprint $table): void {
            $table->index('status', 'ingredients_status_index');
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->index('status', 'employees_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table): void {
            $table->dropIndex('menus_date_shift_id_index');
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropIndex('purchase_orders_status_index');
            $table->dropIndex('purchase_orders_created_at_index');
        });

        Schema::table('food_safety_audits', function (Blueprint $table): void {
            $table->dropIndex('food_safety_audits_date_index');
        });

        Schema::table('ingredients', function (Blueprint $table): void {
            $table->dropIndex('ingredients_status_index');
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->dropIndex('employees_status_index');
        });
    }
};
