<?php

namespace App\Filament\Resources\TimekeepingResource\Pages;

use App\Filament\Resources\TimekeepingResource;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\Timekeeping;
use Filament\Resources\Pages\Page;

class CreateTimekeeping extends Page
{
    protected static string $resource = TimekeepingResource::class;

    protected static string $view = 'filament.resources.timekeepings.pages.edit-timekeeping';

    public $employee_id;

    public $date;

    public $shift_id;

    public $check_in;

    public $check_out;

    public $overtime_hours = '0h';

    public $status = 'Đúng giờ';

    public function mount(): void
    {
        $this->date = now()->toDateString();
        $this->shift_id = Shift::first()?->id;
    }

    public function save()
    {
        abort_unless(TimekeepingResource::canCreate(), 403);

        $this->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'shift_id' => 'required|exists:shifts,id',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'status' => 'required',
        ], [
            'employee_id.required' => __('timekeeping.validation.employee_required'),
            'employee_id.exists' => __('timekeeping.validation.employee_exists'),
            'date.required' => __('timekeeping.validation.date_required'),
            'shift_id.required' => __('timekeeping.validation.shift_required'),
            'shift_id.exists' => __('timekeeping.validation.shift_exists'),
            'check_in.date_format' => __('timekeeping.validation.check_in_format'),
            'check_out.date_format' => __('timekeeping.validation.check_out_format'),
            'status.required' => __('timekeeping.validation.status_required'),
        ]);

        Timekeeping::create([
            'employee_id' => $this->employee_id,
            'date' => $this->date,
            'shift_id' => $this->shift_id,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'overtime_hours' => $this->overtime_hours,
            'status' => $this->status,
        ]);

        session()->flash('message', __('timekeeping.messages.created'));

        return redirect($this->getResource()::getUrl('index'));
    }

    public function getEmployees()
    {
        return Employee::orderBy('name')->get();
    }

    public function getShifts()
    {
        return Shift::all();
    }
}
