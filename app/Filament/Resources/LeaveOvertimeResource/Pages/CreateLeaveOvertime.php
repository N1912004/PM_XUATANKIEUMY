<?php

namespace App\Filament\Resources\LeaveOvertimeResource\Pages;

use App\Filament\Resources\LeaveOvertimeResource;
use App\Models\Employee;
use App\Models\LeaveOvertime;
use Filament\Resources\Pages\Page;

class CreateLeaveOvertime extends Page
{
    protected static string $resource = LeaveOvertimeResource::class;

    protected static string $view = 'filament.resources.leave-overtimes.pages.edit-leave-overtime';

    // Form tab: 'leave' hoặc 'ot'
    public $formTab = 'leave';

    // Core Fields
    public $employee_id;

    public $type = 'Nghỉ phép năm';

    public $start_date;

    public $end_date;

    public $duration_text = '1 ngày';

    public $reason;

    public $approver_id;

    public $status = 'Chờ duyệt';

    // Leave-specific extra fields
    public $no_count_leave = false;

    public $handover_time;

    public $contact_name;

    public $contact_phone;

    public $notes;

    // OT-specific extra fields
    public $ot_start_time = '17:30';

    public $ot_end_time = '20:30';

    public $ot_location = 'Bếp trung tâm - Khu A';

    public $ot_work_description = 'Chuẩn bị nguyên liệu, hỗ trợ chế biến, đóng gói và kiểm tra chất lượng sản phẩm.';

    public $co_worker = 'Trần Thị Bích Ngọc';

    public function mount(): void
    {
        $this->start_date = now()->toDateString();
        $this->end_date = now()->toDateString();
        $this->handover_time = now()->format('Y-m-d\T17:00');
        $this->employee_id = Employee::first()?->id;
        $this->approver_id = Employee::where('id', '!=', $this->employee_id)->first()?->id;
    }

    public function switchFormTab($tab)
    {
        $this->formTab = $tab;
        if ($tab === 'leave') {
            $this->type = 'Nghỉ phép năm';
            $this->duration_text = '1 ngày';
        } else {
            $this->type = 'Tăng ca ngày thường';
            $this->duration_text = '3 giờ';
        }
    }

    public function save()
    {
        $this->validate([
            'employee_id' => 'required',
            'start_date' => 'required|date',
            'type' => 'required',
            'reason' => 'required|min:5',
        ], [
            'employee_id.required' => 'Nhân viên là bắt buộc.',
            'start_date.required' => 'Ngày bắt đầu là bắt buộc.',
            'type.required' => 'Loại yêu cầu là bắt buộc.',
            'reason.required' => 'Lý do là bắt buộc.',
            'reason.min' => 'Lý do phải có ít nhất 5 ký tự.',
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
            'type' => $this->type,
            'start_date' => date('d/m/Y', strtotime($this->start_date)),
            'end_date' => $this->formTab === 'leave' ? date('d/m/Y', strtotime($this->end_date)) : date('d/m/Y', strtotime($this->start_date)),
            'duration_text' => $this->duration_text,
            'reason' => $fullReason,
            'approver_id' => $this->approver_id,
            'status' => $this->status,
        ]);

        session()->flash('message', 'Tạo yêu cầu mới thành công!');

        return redirect($this->getResource()::getUrl('index'));
    }

    public function getEmployees()
    {
        return Employee::orderBy('name')->get();
    }
}
