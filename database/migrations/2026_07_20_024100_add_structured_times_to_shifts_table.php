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
        Schema::table('shifts', function (Blueprint $table): void {
            $table->time('time_from')->nullable()->after('name');
            $table->time('time_to')->nullable()->after('time_from');
        });

        DB::table('shifts')
            ->select(['id', 'time_range'])
            ->orderBy('id')
            ->each(function (object $shift): void {
                if (! is_string($shift->time_range)) {
                    return;
                }

                if (! preg_match('/^\s*([01]\d|2[0-3]):([0-5]\d)\s*-\s*([01]\d|2[0-3]):([0-5]\d)\s*$/', $shift->time_range, $matches)) {
                    DB::table('shifts')->where('id', $shift->id)->update(['time_range' => null]);

                    return;
                }

                $timeFrom = "{$matches[1]}:{$matches[2]}:00";
                $timeTo = "{$matches[3]}:{$matches[4]}:00";
                $duration = $this->durationMinutes($timeFrom, $timeTo);

                if ($duration === 0 || $duration > 12 * 60) {
                    DB::table('shifts')->where('id', $shift->id)->update(['time_range' => null]);

                    return;
                }

                DB::table('shifts')->where('id', $shift->id)->update([
                    'time_from' => $timeFrom,
                    'time_to' => $timeTo,
                ]);
            });

        Schema::table('shifts', function (Blueprint $table): void {
            $table->dropColumn('time_range');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table): void {
            $table->string('time_range')->nullable();
        });

        DB::table('shifts')
            ->select(['id', 'time_from', 'time_to'])
            ->orderBy('id')
            ->each(function (object $shift): void {
                if ($shift->time_from === null || $shift->time_to === null) {
                    return;
                }

                DB::table('shifts')->where('id', $shift->id)->update([
                    'time_range' => substr($shift->time_from, 0, 5).' - '.substr($shift->time_to, 0, 5),
                ]);
            });

        Schema::table('shifts', function (Blueprint $table): void {
            $table->dropColumn(['time_from', 'time_to']);
        });
    }

    private function durationMinutes(string $timeFrom, string $timeTo): int
    {
        [$fromHour, $fromMinute] = array_map('intval', explode(':', $timeFrom));
        [$toHour, $toMinute] = array_map('intval', explode(':', $timeTo));
        $from = ($fromHour * 60) + $fromMinute;
        $to = ($toHour * 60) + $toMinute;

        return ($to - $from + (24 * 60)) % (24 * 60);
    }
};
