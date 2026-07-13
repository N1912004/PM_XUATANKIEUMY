<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BA 07/07/2026 — Hồ sơ NCC (hợp đồng, chứng nhận ATTP, ngày hết hạn) lưu JSON
     * theo pattern employees.documents; bảng supplier_price_logs lưu lịch sử mỗi lần
     * đơn giá NCC ↔ nguyên liệu thay đổi (giá cũ → mới, ai sửa, lúc nào).
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->json('documents')->nullable()->after('notes');
        });

        Schema::create('supplier_price_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->decimal('old_price', 15, 2)->nullable(); // null = lần gán giá đầu tiên
            $table->decimal('new_price', 15, 2);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['supplier_id', 'ingredient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_price_logs');

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('documents');
        });
    }
};
