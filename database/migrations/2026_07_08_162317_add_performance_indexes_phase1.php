<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index bổ sung theo audit hiệu năng (đợt 1):
     * - timekeepings(employee_id, date): tra cứu chấm công theo NV + ngày (bảng 100k+ dòng)
     * - purchase_orders.estimated_delivery_date: ListHang lọc PO đã đặt theo ngày giao
     * - recipes.status: select "món active" trên form thực đơn
     */
    public function up(): void
    {
        Schema::table('timekeepings', function (Blueprint $table) {
            $table->index(['employee_id', 'date'], 'timekeepings_employee_date_index');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index('estimated_delivery_date', 'purchase_orders_estimated_delivery_date_index');
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->index('status', 'recipes_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('timekeepings', function (Blueprint $table) {
            $table->dropIndex('timekeepings_employee_date_index');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex('purchase_orders_estimated_delivery_date_index');
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->dropIndex('recipes_status_index');
        });
    }
};
