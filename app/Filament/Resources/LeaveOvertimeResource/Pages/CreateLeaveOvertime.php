<?php

namespace App\Filament\Resources\LeaveOvertimeResource\Pages;

use App\Filament\Resources\LeaveOvertimeResource;
use App\Models\Employee;
use App\Models\LeaveOvertime;
use App\Models\LeaveType;
use Filament\Resources\Pages\Page;

class CreateLeaveOvertime extends Page
{
    protected static string $resource = LeaveOvertimeResource::class;

    protected static string $view = 'filament.resources.leave-overtimes.pages.edit-leave-overtime';

    // Form tab: 'leave' hoặc 'ot'
    public $formTab = 'leave';

    // Core Fields
    public $employee_id;

    public $leave_type_id;

    public $start_date;

    public $end_date;

    public $duration_text = '1 ngày';

    public $reason;

    public $approver_id;

    public $status = 'pending';

    // Leave-specific extra fields
    public $no_count_leave = false;

    public $handover_time;

    public $contact_name;

    public $contact_phone;

    public $notes;

    // OT-specific extra fields
    public $ot_start_time = '17:30';

    public $ot_end_time = '20:30';

    public $ot_location;

    public $ot_work_description;

    public $co_worker;

    public function mount(): void
    {
        $this->start_date = now()->toDateString();
        $this->end_date = now()->toDateString();
        $this->handover_time = now()->format('Y-m-d\T17:00');
        $this->employee_id = auth()->user()?->employee_id;
        $this->leave_type_id = LeaveType::where('name', 'Nghỉ phép năm')->value('id');
        $this->approver_id = null;
    }

    public function switchFormTab($tab)
    {
        $this->formTab = $tab;
        if ($tab === 'leave') {
            $this->leave_type_id = LeaveType::where('name', 'Nghỉ phép năm')->value('id');
            $this->duration_text = '1 ngày';
        } else {
            $this->leave_type_id = LeaveType::where('name', 'Tăng ca ngày thường')->value('id');
            $this->duration_text = '3 giờ';
        }
    }

    public function save()
    {
        abort_unless(LeaveOvertimeResource::canCreate(), 403);

        $this->validate([
            'employee_id' => 'required',
            'start_date' => 'required|date',
            'leave_type_id' => 'required',
            'reason' => 'required|min:5',
        ], [
            'employee_id.required' => __('leave_overtime.validation.employee_required'),
            'start_date.required' => __('leave_overtime.validation.start_required'),
            'leave_type_id.required' => __('leave_overtime.validation.type_required'),
            'reason.required' => __('leave_overtime.validation.reason_required'),
            'reason.min' => __('leave_overtime.validation.reason_min'),
        ]);

        $fullReason = $this->reason;
        if ($this->formTab === 'leave') {
            if ($this->contact_name || $this->contact_phone) {
                $fullReason .= "\n[Liên hệ khẩn] ".$this->contact_name.' - '.$this->contact_phone;
            }
            if ($this->handover_time) {
                $fullReason .= "\n[Bàn giao] ".date('d/m/Y H:i', strtotime($this->handover_time));
            }
            if ($this->notes) {
                $fullReason .= "\n[Ghi chú] ".$this->notes;
            }
            if ($this->no_count_leave) {
                $fullReason .= "\n[Không tính phép]";
            }
        } else {
            if ($this->ot_start_time || $this->ot_end_time) {
                $fullReason .= "\n[Thời gian OT] ".$this->ot_start_time.' - '.$this->ot_end_time;
            }
            if ($this->ot_location) {
                $fullReason .= "\n[Địa điểm] ".$this->ot_location;
            }
            if ($this->ot_work_description) {
                $fullReason .= "\n[Công việc thực hiện] ".$this->ot_work_description;
            }
            if ($this->co_worker) {
                $fullReason .= "\n[Người phối hợp] ".$this->co_worker;
            }
            if ($this->notes) {
                $fullReason .= "\n[Ghi chú] ".$this->notes;
            }
        }

        LeaveOvertime::create([
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'start_date' => $this->start_date,
            'end_date' => $this->formTab === 'leave' ? $this->end_date : $this->start_date,
            'duration_text' => $this->duration_text,
            'reason' => $fullReason,
            'approver_id' => $this->approver_id,
            // Ép trạng thái phía server: $this->status là public property, client có thể sửa
            // payload Livewire thành 'Đã duyệt' để bỏ qua quy trình duyệt
            'status' => 'pending',
        ]);

        session()->flash('message', __('leave_overtime.messages.created'));

        return redirect($this->getResource()::getUrl('index'));
    }

    public function getEmployees()
    {
        return Employee::orderBy('name')->get();
    }
}
