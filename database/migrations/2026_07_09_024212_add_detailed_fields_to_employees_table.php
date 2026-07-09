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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('id_card')->nullable();
            $table->date('id_card_date')->nullable();
            $table->string('id_card_place')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('nationality')->default('Việt Nam');
            $table->string('ethnic')->nullable();
            $table->string('religion')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('temporary_address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relation')->nullable();
            $table->string('sub_department')->nullable();
            $table->string('level')->nullable();
            $table->string('work_type')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->json('documents')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn([
                'gender',
                'dob',
                'id_card',
                'id_card_date',
                'id_card_place',
                'marital_status',
                'nationality',
                'ethnic',
                'religion',
                'permanent_address',
                'temporary_address',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relation',
                'sub_department',
                'level',
                'work_type',
                'manager_id',
                'documents',
            ]);
        });
    }
};
