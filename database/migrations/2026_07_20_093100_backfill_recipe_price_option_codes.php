<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $unexpectedOptions = DB::table('recipes')
            ->whereNotIn('price_option_id', [0, 1])
            ->pluck('price_option_id')
            ->unique()
            ->values();

        if ($unexpectedOptions->isNotEmpty()) {
            throw new RuntimeException('Không thể chuyển price_option_id: '.implode(', ', $unexpectedOptions->all()));
        }

        DB::table('recipes')
            ->where('price_option_id', true)
            ->update(['price_option' => 'by_contract']);
    }

    public function down(): void
    {
        DB::table('recipes')->update(['price_option_id' => false]);

        DB::table('recipes')
            ->where('price_option', 'by_contract')
            ->update(['price_option_id' => true]);
    }
};
