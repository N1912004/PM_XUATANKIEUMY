<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('suppliers', 'type')) {
            Schema::table('suppliers', function (Blueprint $table): void {
                $table->dropColumn('type');
            });
        }
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table): void {
            $table->string('type')->default('')->after('name');
        });
    }
};
