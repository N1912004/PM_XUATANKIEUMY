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
        Schema::create('timekeepings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->foreignId('shift_id')->nullable()->constrained()->onDelete('set null');
            $table->string('check_in')->nullable();
            $table->string('check_out')->nullable();
            $table->string('overtime_hours')->nullable();
            $table->string('status')->default('Đúng giờ');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timekeepings');
    }
};
