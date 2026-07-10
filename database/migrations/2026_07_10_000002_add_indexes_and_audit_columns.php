<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index tổ hợp theo các pattern truy vấn thực tế + cột truy vết cho sổ thẻ kho
     * + cột type cho PO (thay suy luận LIKE trên note).
     */
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table): void {
            $table->index(['kitchen_id', 'date', 'shift_id']);
            $table->index(['status', 'date']);
        });

        Schema::table('stock_transactions', function (Blueprint $table): void {
            $table->foreignId('created_by')->nullable()->after('note')->constrained('users')->nullOnDelete();
            $table->index(['ingredient_id', 'kitchen_id', 'created_at']);
            $table->index('voucher_code');
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->string('type')->nullable()->after('status'); // 'day' | 'week'
            $table->index(['status', 'stocked_at']);
            $table->index(['kitchen_id', 'estimated_delivery_date']);
        });

        Schema::table('stocks', function (Blueprint $table): void {
            // Mỗi bếp chỉ có 1 dòng tồn cho mỗi nguyên liệu — chốt bằng ràng buộc DB
            // (các luồng đều firstOrCreate/lockForUpdate theo cặp khóa này)
            $table->unique(['kitchen_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table): void {
            $table->dropIndex(['kitchen_id', 'date', 'shift_id']);
            $table->dropIndex(['status', 'date']);
        });

        Schema::table('stock_transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('created_by');
            $table->dropIndex(['ingredient_id', 'kitchen_id', 'created_at']);
            $table->dropIndex(['voucher_code']);
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropColumn('type');
            $table->dropIndex(['status', 'stocked_at']);
            $table->dropIndex(['kitchen_id', 'estimated_delivery_date']);
        });

        Schema::table('stocks', function (Blueprint $table): void {
            $table->dropUnique(['kitchen_id', 'ingredient_id']);
        });
    }
};
