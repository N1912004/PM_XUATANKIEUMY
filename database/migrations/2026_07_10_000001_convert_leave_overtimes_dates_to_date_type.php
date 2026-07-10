<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chuẩn hóa leave_overtimes.start_date/end_date từ VARCHAR (lẫn lộn 'd/m/Y' và 'Y-m-d')
     * sang kiểu DATE thật — cho phép sort/lọc/index bằng SQL thay vì LIKE trên chuỗi.
     */
    public function up(): void
    {
        // Backfill: đưa mọi giá trị 'd/m/Y' về 'Y-m-d' trước khi đổi kiểu cột
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE leave_overtimes SET start_date = DATE_FORMAT(STR_TO_DATE(start_date, '%d/%m/%Y'), '%Y-%m-%d') WHERE start_date LIKE '%/%'");
            DB::statement("UPDATE leave_overtimes SET end_date = DATE_FORMAT(STR_TO_DATE(end_date, '%d/%m/%Y'), '%Y-%m-%d') WHERE end_date LIKE '%/%'");
        } else {
            // SQLite (môi trường test): chuyển bằng PHP
            foreach (DB::table('leave_overtimes')->get(['id', 'start_date', 'end_date']) as $row) {
                $convert = function (?string $value): ?string {
                    if ($value && str_contains($value, '/')) {
                        [$d, $m, $y] = explode('/', $value);

                        return sprintf('%04d-%02d-%02d', $y, $m, $d);
                    }

                    return $value;
                };
                DB::table('leave_overtimes')->where('id', $row->id)->update([
                    'start_date' => $convert($row->start_date),
                    'end_date' => $convert($row->end_date),
                ]);
            }
        }

        Schema::table('leave_overtimes', function (Blueprint $table): void {
            $table->date('start_date')->change();
            $table->date('end_date')->nullable()->change();
        });

        Schema::table('leave_overtimes', function (Blueprint $table): void {
            $table->index(['start_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('leave_overtimes', function (Blueprint $table): void {
            $table->dropIndex(['start_date', 'status']);
            $table->string('start_date')->change();
            $table->string('end_date')->nullable()->change();
        });
    }
};
