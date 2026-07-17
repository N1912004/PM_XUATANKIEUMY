<?php

namespace App\Filament\Resources\LeaveOvertimeResource\Pages;

use App\Filament\Resources\LeaveOvertimeResource;
use App\Models\Department;
use App\Models\LeaveOvertime;
use App\Models\LeaveType;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;

class ListLeaveOvertimes extends Page
{
    use WithPagination;

    protected static string $resource = LeaveOvertimeResource::class;

    protected static string $view = 'filament.resources.leave-overtimes.pages.list-leave-overtimes';

    // State properties
    public $activeTab = 'all';

    // Filters
    public $search = '';

    public $monthFilter = '';

    public $departmentFilter = '';

    public $typeFilter = '';

    public $statusFilter = '';

    public $perPage = 10;

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected $queryString = [
        'activeTab' => ['except' => 'all'],
        'search' => ['except' => ''],
        'monthFilter' => ['except' => ''],
        'departmentFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        if ($this->monthFilter === '') {
            $this->monthFilter = now()->format('Y-m');
        }
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingMonthFilter()
    {
        $this->resetPage();
    }

    public function updatingDepartmentFilter()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->monthFilter = now()->format('Y-m');
        $this->departmentFilter = '';
        $this->typeFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    /**
     * @return array<int, string> [id => name] phòng ban đang bật, cho bộ lọc.
     */
    public function getDepartments(): array
    {
        return Department::options();
    }

    /**
     * @return array<int, string> [id => name] loại nghỉ/tăng ca đang bật, cho bộ lọc.
     */
    public function getTypes(): array
    {
        return LeaveType::options();
    }

    public function deleteLeaveOvertime($id)
    {
        $item = LeaveOvertime::find($id);
        if ($item) {
            abort_unless(LeaveOvertimeResource::canDelete($item), 403);
            $item->delete();
            session()->flash('message', 'Xóa yêu cầu thành công.');
        }
    }

    /**
     * Query gốc: PHÂN QUYỀN + toàn bộ bộ lọc — dùng chung cho bảng và export
     * để export không bao giờ lộ dữ liệu ngoài phạm vi user được thấy.
     */
    protected function baseQuery()
    {
        $query = LeaveOvertime::with(['employee.department', 'leaveType', 'approver.position']);

        // Phân quyền
        $user = auth()->user();
        if ($user) {
            if (! $user->hasRole(['super_admin', 'Quản trị viên'])) {
                if ($user->hasRole('Bếp trưởng') && $kitchenId = $user->currentKitchenId()) {
                    $query->whereHas('employee', fn ($q) => $q->where('kitchen_id', $kitchenId));
                } else {
                    $query->where('employee_id', $user->employee_id);
                }
            }
        }

        // Tìm kiếm
        if ($this->search) {
            $query->whereHas('employee', function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        // Bộ lọc tháng: cột đã là DATE nên lọc bằng khoảng ngày (dùng được index)
        if ($this->monthFilter) {
            $parts = explode('-', $this->monthFilter);
            if (count($parts) === 2) {
                $monthStart = Carbon::createFromDate((int) $parts[0], (int) $parts[1], 1);
                $query->whereBetween('start_date', [
                    $monthStart->toDateString(),
                    $monthStart->copy()->endOfMonth()->toDateString(),
                ]);
            }
        }

        // Bộ lọc phòng ban
        if ($this->departmentFilter) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $this->departmentFilter));
        }

        // Bộ lọc loại yêu cầu
        if ($this->typeFilter) {
            $query->where('leave_type_id', $this->typeFilter);
        }

        // Bộ lọc trạng thái
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return $query;
    }

    public function leaveOvertimes()
    {
        $query = $this->baseQuery();

        // Tab lọc
        if ($this->activeTab === 'history') {
            $query->whereIn('status', ['approved', 'rejected', 'cancelled']);
        }

        return $query->paginate($this->perPage);
    }

    public function exportLeaveOvertimes()
    {
        abort_unless(LeaveOvertimeResource::canViewAny(), 403);

        $records = $this->baseQuery()->get();
        $filename = 'nghi_phep_tang_ca_'.($this->monthFilter ?: now()->format('Y-m')).'.csv';

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['STT', 'Mã NV', 'Họ và tên', 'Phòng ban', 'Loại yêu cầu', 'Thời gian áp dụng', 'Số ngày/Số giờ', 'Lý do', 'Người duyệt', 'Trạng thái']);

            foreach ($records as $index => $row) {
                fputcsv($file, [
                    $index + 1,
                    $row->employee?->code,
                    $row->employee?->name,
                    $row->employee?->department?->name,
                    $row->leaveType?->name,
                    $row->start_date?->format('d/m/Y').($row->end_date && ! $row->end_date->equalTo($row->start_date) ? ' - '.$row->end_date->format('d/m/Y') : ''),
                    $row->duration_text,
                    $row->reason,
                    $row->approver?->name,
                    $row->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
