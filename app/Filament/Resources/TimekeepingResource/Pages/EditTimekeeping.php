<?php

namespace App\Filament\Resources\TimekeepingResource\Pages;

use App\Filament\Resources\TimekeepingResource;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class EditTimekeeping extends Page
{
    use InteractsWithRecord;

    protected static string $resource = TimekeepingResource::class;

    protected static string $view = 'filament.resources.timekeepings.pages.edit-timekeeping';

    public $employee_id;

    public $date;

    public $shift_id;

    public $check_in;

    public $check_out;

    public $overtime_hours;

    public $status;

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->employee_id = $this->record->employee_id;
        $this->date = $this->record->date ? Carbon::parse($this->record->date)->toDateString() : null;
        $this->shift_id = $this->record->shift_id;
        // Chuẩn hóa về HH:MM (DB có thể lưu HH:MM:SS) để khớp rule date_format:H:i
        $this->check_in = $this->record->check_in ? substr($this->record->check_in, 0, 5) : null;
        $this->check_out = $this->record->check_out ? substr($this->record->check_out, 0, 5) : null;
        $this->overtime_hours = $this->record->overtime_hours;
        $this->status = $this->record->status;
    }

    public function save()
    {
        abort_unless(TimekeepingResource::canEdit($this->record), 403);

        $this->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'shift_id' => 'required|exists:shifts,id',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'status' => 'required',
        ], [
            'employee_id.required' => 'Nhân viên là bắt buộc.',
            'employee_id.exists' => 'Nhân viên không hợp lệ.',
            'date.required' => 'Ngày làm việc là bắt buộc.',
            'shift_id.required' => 'Ca làm việc là bắt buộc.',
            'shift_id.exists' => 'Ca làm việc không hợp lệ.',
            'check_in.date_format' => 'Giờ vào phải theo định dạng HH:MM (VD: 07:01).',
            'check_out.date_format' => 'Giờ ra phải theo định dạng HH:MM (VD: 16:05).',
            'status.required' => 'Trạng thái là bắt buộc.',
        ]);

        $this->record->update([
            'employee_id' => $this->employee_id,
            'date' => $this->date,
            'shift_id' => $this->shift_id,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'overtime_hours' => $this->overtime_hours,
            'status' => $this->status,
        ]);

        session()->flash('message', 'Cập nhật bản ghi chấm công thành công!');

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
