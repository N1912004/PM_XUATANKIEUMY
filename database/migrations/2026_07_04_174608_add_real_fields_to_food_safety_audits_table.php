<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung các trường ghi nhận THẬT cho biểu mẫu kiểm thực 3 bước (QĐ 1246/QĐ-BYT):
     * - Bước 2: giờ bắt đầu/hoàn thành chế biến, nhiệt độ, người chế biến.
     * - Bước 3 / Lưu mẫu: người lưu mẫu + thời điểm lưu mẫu, mã mẫu, dụng cụ chứa đựng.
     */
    public function up(): void
    {
        Schema::table('food_safety_audits', function (Blueprint $table) {
            $table->foreignId('recipe_id')->nullable()->after('shift_id')
                ->constrained('recipes')->nullOnDelete();
            $table->time('cook_start_at')->nullable()->after('inspected_by');
            $table->time('cook_end_at')->nullable()->after('cook_start_at');
            $table->string('temperature')->nullable()->after('cook_end_at');
            $table->string('sample_kept_by')->nullable()->after('temperature');
            $table->dateTime('sample_kept_at')->nullable()->after('sample_kept_by');
            $table->string('sample_code')->nullable()->after('sample_kept_at');
            $table->string('utensil')->nullable()->after('sample_code');
        });
    }

    public function down(): void
    {
        Schema::table('food_safety_audits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recipe_id');
            $table->dropColumn([
                'cook_start_at',
                'cook_end_at',
                'temperature',
                'sample_kept_by',
                'sample_kept_at',
                'sample_code',
                'utensil',
            ]);
        });
    }
};
