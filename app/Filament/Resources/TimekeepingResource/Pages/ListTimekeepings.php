<?php

namespace App\Filament\Resources\TimekeepingResource\Pages;

use App\Filament\Resources\TimekeepingResource;
use App\Models\Area;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\Timekeeping;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;

class ListTimekeepings extends Page
{
    use WithPagination;

    protected static string $resource = TimekeepingResource::class;

    protected static string $view = 'filament.resources.timekeepings.pages.list-timekeepings';

    // Search and filters
    public $search = '';

    public $dateFilter = '2026-05-15'; // Khớp mặc định seeder và HTML mẫu

    public $shiftFilter = '';

    public $departmentFilter = '';

    public $areaFilter = '';

    public $statusFilter = '';

    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFilter' => ['except' => ''],
        'shiftFilter' => ['except' => ''],
        'departmentFilter' => ['except' => ''],
        'areaFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        // Kiểm tra xem database có bất cứ bản ghi nào ở ngày 15/05/2026 không, nếu không lấy ngày hôm nay
        $hasData = Timekeeping::where('date', '2026-05-15')->exists();
        if (! $hasData) {
            $this->dateFilter = now()->toDateString();
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function updatingShiftFilter()
    {
        $this->resetPage();
    }

    public function updatingDepartmentFilter()
    {
        $this->resetPage();
    }

    public function updatingAreaFilter()
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
        $hasData = Timekeeping::where('date', '2026-05-15')->exists();
        $this->dateFilter = $hasData ? '2026-05-15' : now()->toDateString();
        $this->shiftFilter = '';
        $this->departmentFilter = '';
        $this->areaFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function getAttendanceCardEmployee(): ?Employee
    {
        $user = auth()->user();
        if ($user && $user->employee_id) {
            return Employee::with('area')->find($user->employee_id);
        }

        if ($user && ! $user->hasRole(['super_admin', 'Quản trị viên', 'Bếp trưởng'])) {
            return null;
        }

        $record = (clone $this->baseQuery())
            ->whereNotNull('employee_id')
            ->first();

        return $record?->employee
            ? Employee::with('area')->find($record->employee_id)
            : Employee::with('area')->first();
    }

    public function getAttendanceCardRecord(): ?Timekeeping
    {
        $employee = $this->getAttendanceCardEmployee();
        if (! $employee) {
            return null;
        }

        return Timekeeping::with('shift')
            ->where('employee_id', $employee->id)
            ->where('date', $this->dateFilter ?: now()->toDateString())
            ->first();
    }

    public function canUseAttendanceActions(?Employee $employee): bool
    {
        $user = auth()->user();

        return (bool) (
            $employee
            && $user
            && (int) $user->employee_id === (int) $employee->id
            && ($this->dateFilter ?: now()->toDateString()) === now()->toDateString()
        );
    }

    public function checkIn()
    {
        $employee = auth()->user()?->employee_id
            ? Employee::find(auth()->user()->employee_id)
            : null;

        if (! $employee) {
            session()->flash('error', 'Không tìm thấy thông tin nhân sự liên kết với tài khoản.');

            return;
        }

        $today = now()->toDateString();
        $timekeeping = Timekeeping::firstOrNew([
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        if (! $timekeeping->check_in) {
            $timekeeping->check_in = now()->format('H:i');

            $shift = Shift::where('name', 'CA 1')->orWhere('name', 'Ca 1')->first() ?: Shift::first();
            if ($shift) {
                $timekeeping->shift_id = $shift->id;
            }

            $timekeeping->status = now()->gt(Carbon::createFromTimeString('07:05:00'))
                ? 'Đi trễ'
                : 'Đúng giờ';

            $timekeeping->save();
            $this->dateFilter = $today;
            session()->flash('message', 'Check-in thành công lúc '.$timekeeping->check_in.'!');
        }
    }

    public function checkOut()
    {
        $employee = auth()->user()?->employee_id
            ? Employee::find(auth()->user()->employee_id)
            : null;

        if (! $employee) {
            session()->flash('error', 'Không tìm thấy thông tin nhân sự.');

            return;
        }

        $timekeeping = Timekeeping::where('employee_id', $employee->id)
            ->where('date', now()->toDateString())
            ->first();

        if ($timekeeping && $timekeeping->check_in && ! $timekeeping->check_out) {
            $timekeeping->check_out = now()->format('H:i');

            $checkOutTime = now();
            $shiftEndTime = Carbon::createFromTimeString('16:00:00');
            if ($checkOutTime->gt($shiftEndTime)) {
                $diffMinutes = $checkOutTime->diffInMinutes($shiftEndTime);
                $hours = floor($diffMinutes / 60);
                $minutes = $diffMinutes % 60;
                $timekeeping->overtime_hours = $hours.'h'.str_pad($minutes, 2, '0', STR_PAD_LEFT);
                $timekeeping->status = 'Tăng ca';
            } else {
                $timekeeping->overtime_hours = '0h';
            }

            $timekeeping->save();
            $this->dateFilter = now()->toDateString();
            session()->flash('message', 'Check-out thành công lúc '.$timekeeping->check_out.'!');
        }
    }

    /**
     * Query gốc: phân quyền + tìm kiếm + bộ lọc — dùng chung cho bảng và export
     */
    protected function baseQuery()
    {
        $query = Timekeeping::with(['employee', 'shift']);

        // Bộ lọc phân quyền
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

        // Bộ lọc ngày
        if ($this->dateFilter) {
            $query->where('date', $this->dateFilter);
        }

        // Bộ lọc ca
        if ($this->shiftFilter) {
            $query->where('shift_id', $this->shiftFilter);
        }

        // Bộ lọc phòng ban
        if ($this->departmentFilter) {
            $query->whereHas('employee', fn ($q) => $q->where('department', $this->departmentFilter));
        }

        // Bộ lọc khu vực
        if ($this->areaFilter) {
            $query->whereHas('employee', fn ($q) => $q->where('area_id', $this->areaFilter));
        }

        // Bộ lọc trạng thái
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return $query;
    }

    public function timekeepings()
    {
        return $this->baseQuery()->paginate($this->perPage);
    }

    /**
     * Tính tổng giờ làm thực tế từ check-in/check-out (định dạng "8h54"),
     * hỗ trợ ca qua đêm; trả về null nếu thiếu dữ liệu
     */
    public static function workedHours(?string $checkIn, ?string $checkOut): ?string
    {
        if (! $checkIn || ! $checkOut) {
            return null;
        }

        try {
            $in = Carbon::createFromFormat('H:i', substr($checkIn, 0, 5));
            $out = Carbon::createFromFormat('H:i', substr($checkOut, 0, 5));
        } catch (\Exception $e) {
            return null;
        }

        if ($out->lessThan($in)) {
            $out->addDay(); // ca qua đêm
        }

        $minutes = $in->diffInMinutes($out);

        return intdiv($minutes, 60).'h'.str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT);
    }

    public function getShifts()
    {
        return Shift::all();
    }

    public function getDepartments()
    {
        return Employee::distinct()->whereNotNull('department')->pluck('department')->toArray();
    }

    public function getAreas()
    {
        return Area::all();
    }

    public function deleteTimekeeping($id)
    {
        $tk = Timekeeping::find($id);
        if ($tk) {
            abort_unless(TimekeepingResource::canDelete($tk), 403);
            $tk->delete();
            session()->flash('message', 'Xóa bản ghi chấm công thành công.');
        }
    }

    public function exportTimekeepings()
    {
        // Dùng chung baseQuery để export tuân thủ đúng phân quyền
        // và bộ lọc như bảng đang hiển thị
        $records = $this->baseQuery()->get();

        $filename = 'cham_cong_'.($this->dateFilter ?: now()->toDateString()).'.csv';

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['STT', 'Mã NV', 'Họ và tên', 'Phòng ban', 'Ca làm việc', 'Check-in', 'Check-out', 'Tổng giờ', 'Tăng ca', 'Trạng thái']);

            foreach ($records as $index => $row) {
                fputcsv($file, [
                    $index + 1,
                    $row->employee?->code,
                    $row->employee?->name,
                    $row->employee?->department,
                    $row->shift?->name.' ('.$row->shift?->time_range.')',
                    $row->check_in ?: '--',
                    $row->check_out ?: '--',
                    self::workedHours($row->check_in, $row->check_out) ?? '—',
                    $row->overtime_hours ?: '0h',
                    $row->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
