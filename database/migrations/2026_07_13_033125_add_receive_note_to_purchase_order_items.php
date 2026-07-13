<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BA 07/07/2026 — kiểm hàng PO: khi số thực nhận lệch số đặt, bắt buộc ghi
     * chú/lý do theo từng dòng. Cột này lưu lý do lệch của mỗi item.
     */
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->string('receive_note')->nullable()->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('receive_note');
        });
    }
};
