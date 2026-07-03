<?php

namespace App\Filament\Resources\TimekeepingResource\Widgets;

use App\Models\Employee;
use App\Models\Shift;
use App\Models\Timekeeping;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class AttendanceCheck extends Widget
{
    protected static string $view = 'filament.widgets.attendance-check';

    protected int|string|array $columnSpan = 'full';

    public function checkIn(): void
    {
        $employee = $this->getEmployee();
        if (! $employee) {
            return;
        }

        $today = Carbon::today()->toDateString();

        // Find or create timekeeping record for today
        $timekeeping = Timekeeping::firstOrNew([
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        if (! $timekeeping->check_in) {
            $timekeeping->check_in = Carbon::now()->format('H:i:s');

            // Assign default shift (e.g., Ca 1 / CA 1)
            $shift = Shift::where('name', 'CA 1')->orWhere('name', 'Ca 1')->first() ?: Shift::first();
            if ($shift) {
                $timekeeping->shift_id = $shift->id;
            }

            // Check if late (e.g., after 07:00:00)
            $limit = Carbon::createFromTimeString('07:00:00');
            if (Carbon::now()->gt($limit)) {
                $timekeeping->status = 'Đi trễ';
            } else {
                $timekeeping->status = 'Đúng giờ';
            }

            $timekeeping->save();

            // Refresh Filament table
            $this->dispatch('refreshTimekeepingsList');
        }
    }

    public function checkOut(): void
    {
        $employee = $this->getEmployee();
        if (! $employee) {
            return;
        }

        $today = Carbon::today()->toDateString();

        $timekeeping = Timekeeping::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($timekeeping && $timekeeping->check_in && ! $timekeeping->check_out) {
            $timekeeping->check_out = Carbon::now()->format('H:i:s');

            // Determine if there's overtime (e.g., after 16:00:00)
            $checkOutTime = Carbon::now();
            $shiftEndTime = Carbon::createFromTimeString('16:00:00');
            if ($checkOutTime->gt($shiftEndTime)) {
                $diffHours = round($checkOutTime->diffInMinutes($shiftEndTime) / 60, 2);
                $timekeeping->overtime_hours = $diffHours;
                $timekeeping->status = 'Tăng ca';
            }

            $timekeeping->save();

            // Refresh Filament table
            $this->dispatch('refreshTimekeepingsList');
        }
    }

    public function getEmployee(): ?Employee
    {
        $user = auth()->user();
        if ($user && $user->employee_id) {
            return Employee::find($user->employee_id);
        }

        // Fallback to the first employee for admin testing
        return Employee::first();
    }

    public function getViewData(): array
    {
        $employee = $this->getEmployee();
        $today = Carbon::today();

        $timekeeping = null;
        if ($employee) {
            $timekeeping = Timekeeping::with('shift')
                ->where('employee_id', $employee->id)
                ->where('date', $today->toDateString())
                ->first();
        }

        return [
            'employee' => $employee,
            'todayDateFormatted' => $today->format('d/m/Y'),
            'todayDayName' => $this->getDayNameVietnamese($today->dayOfWeek),
            'timekeeping' => $timekeeping,
        ];
    }

    private function getDayNameVietnamese(int $dayOfWeek): string
    {
        $days = [
            0 => 'Chủ Nhật',
            1 => 'Thứ Hai',
            2 => 'Thứ Ba',
            3 => 'Thứ Tư',
            4 => 'Thứ Năm',
            5 => 'Thứ Sáu',
            6 => 'Thứ Bảy',
        ];

        return $days[$dayOfWeek] ?? '';
    }
}
