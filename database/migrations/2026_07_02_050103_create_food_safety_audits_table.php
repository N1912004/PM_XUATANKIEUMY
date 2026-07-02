<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('food_safety_audits', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->string('stage'); // Bước 1 / Bước 2 / Bước 3 / Lưu mẫu / Hủy mẫu
            $table->string('status'); // Đạt / Không đạt
            $table->string('inspected_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_safety_audits');
    }
};
