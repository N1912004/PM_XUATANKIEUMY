<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phân cấp dữ liệu Khu vực → Bếp → Kho: mỗi bếp có kho riêng, thực đơn / đơn đặt hàng /
     * giao dịch kho đều gắn với 1 bếp. Cột kitchen_id để nullable để tương thích dữ liệu cũ.
     */
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->foreignId('kitchen_id')->nullable()->after('id')
                ->constrained('kitchens')->nullOnDelete();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('kitchen_id')->nullable()->after('id')
                ->constrained('kitchens')->nullOnDelete();
        });

        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->foreignId('kitchen_id')->nullable()->after('id')
                ->constrained('kitchens')->nullOnDelete();
        });

        // Kho: 1 nguyên liệu = 1 bản ghi kho trên MỖI bếp → unique (ingredient_id, kitchen_id).
        // Đặt ingredient_id đứng đầu composite để index này vẫn đỡ được FK ingredient_id,
        // cho phép drop unique đơn cũ mà không lỗi trên MySQL.
        Schema::table('stocks', function (Blueprint $table) {
            $table->foreignId('kitchen_id')->nullable()->after('id')
                ->constrained('kitchens')->nullOnDelete();
            $table->unique(['ingredient_id', 'kitchen_id']);
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropUnique('stocks_ingredient_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->unique('ingredient_id');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropUnique(['ingredient_id', 'kitchen_id']);
            $table->dropConstrainedForeignId('kitchen_id');
        });

        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kitchen_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kitchen_id');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kitchen_id');
        });
    }
};
