<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table): void {
            $table->renameColumn('price_level', 'standard_price_per_portion');
            $table->renameColumn('actual_price', 'selling_price_per_portion');
        });
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table): void {
            $table->renameColumn('standard_price_per_portion', 'price_level');
            $table->renameColumn('selling_price_per_portion', 'actual_price');
        });
    }
};
