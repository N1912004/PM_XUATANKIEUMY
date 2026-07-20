<?php

namespace App\Filament\Resources\LeaveOvertimeResource\Pages;

use App\Filament\Resources\LeaveOvertimeResource;
use App\Models\Employee;
use App\Models\LeaveType;
use Carbon\Carbon;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class EditLeaveOvertime extends Page
{
    use InteractsWithRecord;

    protected static string $resource = LeaveOvertimeResource::class;

    protected static string $view = 'filament.resources.leave-overtimes.pages.edit-leave-overtime';

    // Form tab: 'leave' hoặc 'ot'
    public $formTab = 'leave';

    // Core Fields
    public $employee_id;

    public $leave_type_id;

    public $start_date;

    public $end_date;

    public $duration_text;

    public $reason;

    public $approver_id;

    public $status;

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

    public $ot_work_description;

    public $co_worker;

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->employee_id = $this->record->employee_id;
        $this->leave_type_id = $this->record->leave_type_id;
        $this->duration_text = $this->record->duration_text;
        $this->approver_id = $this->record->approver_id;
        $this->status = $this->record->status;

        // Cột đã là DATE (cast Carbon) — chỉ cần đưa về Y-m-d cho input type=date
        $this->start_date = $this->record->start_date?->toDateString();
        $this->end_date = $this->record->end_date?->toDateString();

        // Xác định tab đang chọn
        $leaveType = LeaveType::find($this->leave_type_id);
        if ($leaveType?->is_ot) {
            $this->formTab = 'ot';
        } else {
            $this->formTab = 'leave';
        }

        // Tách lý do gộp
        $fullReason = $this->record->reason;
        $lines = explode("\n", $fullReason);
        $cleanReason = [];
        foreach ($lines as $line) {
            if (str_starts_with($line, '[Liên hệ khẩn] ')) {
                $parts = explode(' - ', str_replace('[Liên hệ khẩn] ', '', $line));
                $this->contact_name = $parts[0] ?? null;
                $this->contact_phone = $parts[1] ?? null;
            } elseif (str_starts_with($line, '[Bàn giao] ')) {
                $timeStr = str_replace('[Bàn giao] ', '', $line);
                try {
                    $this->handover_time = Carbon::createFromFormat('d/m/Y H:i', $timeStr)->format('Y-m-d\TH:i');
                } catch (\Exception $e) {
                    $this->handover_time = null;
                }
            } elseif (str_starts_with($line, '[Ghi chú] ')) {
                $this->notes = str_replace('[Ghi chú] ', '', $line);
            } elseif (str_starts_with($line, '[Không tính phép]')) {
                $this->no_count_leave = true;
            } elseif (str_starts_with($line, '[Thời gian OT] ')) {
                $parts = explode(' - ', str_replace('[Thời gian OT] ', '', $line));
                $this->ot_start_time = $parts[0] ?? '17:30';
                $this->ot_end_time = $parts[1] ?? '20:30';
            } elseif (str_starts_with($line, '[Địa điểm] ')) {
                $this->ot_location = str_replace('[Địa điểm] ', '', $line);
            } elseif (str_starts_with($line, '[Công việc thực hiện] ')) {
                $this->ot_work_description = str_replace('[Công việc thực hiện] ', '', $line);
            } elseif (str_starts_with($line, '[Người phối hợp] ')) {
                $this->co_worker = str_replace('[Người phối hợp] ', '', $line);
            } else {
                $cleanReason[] = $line;
            }
        }
        $this->reason = implode("\n", $cleanReason);
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
        abort_unless(LeaveOvertimeResource::canEdit($this->record), 403);

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

        $this->record->update([
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'start_date' => $this->start_date,
            'end_date' => $this->formTab === 'leave' ? $this->end_date : $this->start_date,
            'duration_text' => $this->duration_text,
            'reason' => $fullReason,
            'approver_id' => $this->approver_id,
            'status' => $this->status,
        ]);

        session()->flash('message', __('leave_overtime.messages.updated'));

        return redirect($this->getResource()::getUrl('index'));
    }

    public function getEmployees()
    {
        return Employee::orderBy('name')->get();
    }
}
