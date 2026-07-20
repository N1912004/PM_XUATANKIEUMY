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
            ->whereNotIn('price_option', ['none', 'by_unit', 'by_contract'])
            ->pluck('price_option')
            ->unique()
            ->values();

        if ($unexpectedOptions->isNotEmpty()) {
            throw new RuntimeException('Không thể xóa price_option_id vì price_option không hợp lệ: '.implode(', ', $unexpectedOptions->all()));
        }

        Schema::table('recipes', function (Blueprint $table): void {
            $table->dropColumn('price_option_id');
        });
    }

    public function down(): void
    {
        if (DB::table('recipes')->where('price_option', 'by_unit')->exists()) {
            throw new RuntimeException('Không thể rollback price_option vì "by_unit" không thể biểu diễn bằng boolean cũ.');
        }

        Schema::table('recipes', function (Blueprint $table): void {
            $table->boolean('price_option_id')
                ->default(false)
                ->after('selling_price_per_portion');
        });
    }
};
