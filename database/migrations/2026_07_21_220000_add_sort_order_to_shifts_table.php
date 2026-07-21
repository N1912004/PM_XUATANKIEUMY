<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('shifts', 'sort_order')) {
            Schema::table('shifts', function (Blueprint $table): void {
                $table->unsignedInteger('sort_order')->default(1)->after('time_to');
            });
        }

        $shifts = DB::table('shifts')->orderBy('time_from')->orderBy('id')->get();
        foreach ($shifts as $i => $s) {
            DB::table('shifts')->where('id', $s->id)->update(['sort_order' => $i + 1]);
        }

        Schema::table('shifts', function (Blueprint $table): void {
            $table->unique('sort_order', 'shifts_sort_order_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table): void {
            $table->dropUnique('shifts_sort_order_unique');
            $table->dropColumn('sort_order');
        });
    }
};
