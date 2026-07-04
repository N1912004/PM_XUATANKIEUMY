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
     * Lượng tồn bị ĐÓNG BĂNG do đang trong phiếu điều chuyển kho (chưa được xuất sử dụng tiếp).
     */
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('frozen_quantity', 15, 3)->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn('frozen_quantity');
        });
    }
};
