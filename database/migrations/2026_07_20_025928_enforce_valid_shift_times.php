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
        DB::table('shifts')
            ->select(['id', 'name', 'time_from', 'time_to'])
            ->orderBy('id')
            ->each(function (object $shift): void {
                if ($this->isValidShift($shift)) {
                    return;
                }

                if ($this->isReferenced((int) $shift->id)) {
                    throw new RuntimeException("Ca #{$shift->id} ({$shift->name}) có khung giờ không hợp lệ và đang được sử dụng. Hãy sửa dữ liệu trước khi chạy migration.");
                }

                DB::table('shifts')->where('id', $shift->id)->delete();
            });

        Schema::table('shifts', function (Blueprint $table): void {
            $table->time('time_from')->nullable(false)->change();
            $table->time('time_to')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table): void {
            $table->time('time_from')->nullable()->change();
            $table->time('time_to')->nullable()->change();
        });
    }

    private function isValidShift(object $shift): bool
    {
        if (! is_string($shift->time_from) || ! is_string($shift->time_to)) {
            return false;
        }

        $from = $this->timeToMinutes($shift->time_from);
        $to = $this->timeToMinutes($shift->time_to);
        $duration = ($to - $from + (24 * 60)) % (24 * 60);

        return $duration > 0
            && $duration <= 12 * 60;
    }

    private function isReferenced(int $shiftId): bool
    {
        foreach (['menus', 'timekeepings', 'food_safety_audits'] as $table) {
            if (Schema::hasTable($table) && DB::table($table)->where('shift_id', $shiftId)->exists()) {
                return true;
            }
        }

        return false;
    }

    private function timeToMinutes(string $time): int
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));

        return ($hour * 60) + $minute;
    }
};
