<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Cờ chống nhập kho lặp: đánh dấu thời điểm đơn đã tự động nhập kho.
     * Đảm bảo mỗi đơn chỉ cộng tồn 1 lần dù trạng thái đổi done → khác → done.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->timestamp('stocked_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('stocked_at');
        });
    }
};
