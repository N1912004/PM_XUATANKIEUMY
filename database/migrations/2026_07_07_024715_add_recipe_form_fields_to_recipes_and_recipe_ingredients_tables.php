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
        Schema::table('recipes', function (Blueprint $table): void {
            $table->string('price_option')->default('Không')->after('actual_price');
            $table->text('description')->nullable()->after('status');
        });

        Schema::table('recipe_ingredients', function (Blueprint $table): void {
            $table->string('note')->nullable()->after('quantity_per_portion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_ingredients', function (Blueprint $table): void {
            $table->dropColumn('note');
        });

        Schema::table('recipes', function (Blueprint $table): void {
            $table->dropColumn(['price_option', 'description']);
        });
    }
};
