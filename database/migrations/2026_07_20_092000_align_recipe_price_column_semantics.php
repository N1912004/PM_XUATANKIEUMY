<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table): void {
            $table->renameColumn('selling_price_per_portion', 'cost_per_portion');
        });

        Schema::table('recipes', function (Blueprint $table): void {
            $table->renameColumn('standard_price_per_portion', 'selling_price_per_portion');
        });
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table): void {
            $table->renameColumn('selling_price_per_portion', 'standard_price_per_portion');
        });

        Schema::table('recipes', function (Blueprint $table): void {
            $table->renameColumn('cost_per_portion', 'selling_price_per_portion');
        });
    }
};
