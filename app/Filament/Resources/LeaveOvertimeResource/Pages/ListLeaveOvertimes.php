<?php

namespace App\Filament\Resources\LeaveOvertimeResource\Pages;

use App\Filament\Resources\LeaveOvertimeResource;
use App\Models\Employee;
use App\Models\LeaveOvertime;
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

    public $monthFilter = '2026-05'; // Mặc định tháng seeder

    public $departmentFilter = '';

    public $typeFilter = '';

    public $statusFilter = '';

    public $perPage = 10;

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
        // Kiểm tra xem database có bất cứ bản ghi nào ở tháng 05/2026 không, nếu không lấy tháng hiện tại
        $hasData = LeaveOvertime::where('start_date', 'like', '%05/2026%')
            ->orWhere('start_date', 'like', '%2026-05%')
            ->exists();
        if (! $hasData) {
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
        $hasData = LeaveOvertime::where('start_date', 'like', '%05/2026%')
            ->orWhere('start_date', 'like', '%2026-05%')
            ->exists();
        $this->monthFilter = $hasData ? '2026-05' : now()->format('Y-m');
        $this->departmentFilter = '';
        $this->typeFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function getDepartments()
    {
        return Employee::distinct()->whereNotNull('department')->pluck('department')->toArray();
    }

    public function getTypes()
    {
        return [
            'Nghỉ phép năm',
            'Nghỉ phép bệnh',
            'Nghỉ không lương',
            'Tăng ca ngày thường',
            'Tăng ca cuối tuần',
            'Tăng ca ngày lễ',
        ];
    }

    public function deleteLeaveOvertime($id)
    {
        $item = LeaveOvertime::find($id);
        if ($item) {
            $item->delete();
            session()->flash('message', 'Xóa yêu cầu thành công.');
        }
    }

    public function leaveOvertimes()
    {
        $query = LeaveOvertime::with(['employee', 'approver']);

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

        // Tab lọc
        if ($this->activeTab === 'history') {
            $query->whereIn('status', ['Đã duyệt', 'Từ chối', 'Đã hủy']);
        }

        // Tìm kiếm
        if ($this->search) {
            $query->whereHas('employee', function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        // Bộ lọc tháng
        if ($this->monthFilter) {
            $parts = explode('-', $this->monthFilter);
            if (count($parts) === 2) {
                $monthStr = $parts[1].'/'.$parts[0]; // "MM/YYYY"
                $query->where(function ($q) use ($monthStr) {
                    $q->where('start_date', 'like', '%'.$monthStr.'%')
                        ->orWhere('start_date', 'like', '%'.$this->monthFilter.'%');
                });
            }
        }

        // Bộ lọc phòng ban
        if ($this->departmentFilter) {
            $query->whereHas('employee', fn ($q) => $q->where('department', $this->departmentFilter));
        }

        // Bộ lọc loại yêu cầu
        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        // Bộ lọc trạng thái
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return $query->paginate($this->perPage);
    }

    public function exportLeaveOvertimes()
    {
        $query = LeaveOvertime::with(['employee', 'approver']);

        if ($this->search) {
            $query->whereHas('employee', function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%');
            });
        }
        if ($this->monthFilter) {
            $parts = explode('-', $this->monthFilter);
            if (count($parts) === 2) {
                $monthStr = $parts[1].'/'.$parts[0];
                $query->where(function ($q) use ($monthStr) {
                    $q->where('start_date', 'like', '%'.$monthStr.'%')
                        ->orWhere('start_date', 'like', '%'.$this->monthFilter.'%');
                });
            }
        }
        if ($this->departmentFilter) {
            $query->whereHas('employee', fn ($q) => $q->where('department', $this->departmentFilter));
        }
        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $records = $query->get();
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
                    $row->employee?->department,
                    $row->type,
                    $row->start_date.($row->end_date ? ' - '.$row->end_date : ''),
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
