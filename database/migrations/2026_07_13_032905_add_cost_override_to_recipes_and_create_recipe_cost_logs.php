<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BA 07/07/2026 — Cost chuẩn của món TỰ TÍNH từ định mức × đơn giá nguyên liệu.
     * Trường hợp đặc biệt cho phép nhập cost điều chỉnh (override) nhưng bắt buộc
     * ghi lý do và lưu log giá cũ → mới vào recipe_cost_logs.
     */
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->decimal('cost_override', 15, 2)->nullable()->after('actual_price');
        });

        Schema::create('recipe_cost_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->decimal('old_value', 15, 2)->nullable(); // null = trước đó dùng cost tự tính
            $table->decimal('new_value', 15, 2)->nullable(); // null = gỡ override, quay về tự tính
            $table->string('reason');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('recipe_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_cost_logs');

        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn('cost_override');
        });
    }
};
