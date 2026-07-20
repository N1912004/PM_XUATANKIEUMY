<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $unexpectedOptions = DB::table('recipes')
            ->whereNotIn('price_option', ['Không', 'Có'])
            ->pluck('price_option')
            ->unique()
            ->values();

        if ($unexpectedOptions->isNotEmpty()) {
            throw new RuntimeException('Không thể chuyển tùy chọn đơn giá: '.implode(', ', $unexpectedOptions->all()));
        }

        Schema::table('recipes', function (Blueprint $table): void {
            $table->boolean('price_option_id')
                ->default(false)
                ->after('selling_price_per_portion');
        });

        DB::table('recipes')
            ->where('price_option', 'Có')
            ->update(['price_option_id' => true]);

        Schema::table('recipes', function (Blueprint $table): void {
            $table->dropColumn('price_option');
        });
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table): void {
            $table->string('price_option')->default('Không')->after('selling_price_per_portion');
        });

        DB::table('recipes')
            ->where('price_option_id', true)
            ->update(['price_option' => 'Có']);

        Schema::table('recipes', function (Blueprint $table): void {
            $table->dropColumn('price_option_id');
        });
    }
};
