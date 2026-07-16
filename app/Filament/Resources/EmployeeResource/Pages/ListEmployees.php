<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\Area;
use App\Models\Employee;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListEmployees extends Page
{
    use WithPagination;

    protected static string $resource = EmployeeResource::class;

    protected static string $view = 'filament.resources.employees.pages.list-employees';

    public $search = '';

    public $departmentFilter = '';

    public $positionFilter = '';

    public $areaFilter = '';

    public $statusFilter = '';

    public $perPage = 10;

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'departmentFilter' => ['except' => ''],
        'positionFilter' => ['except' => ''],
        'areaFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedDepartmentFilter()
    {
        $this->resetPage();
    }

    public function updatedPositionFilter()
    {
        $this->resetPage();
    }

    public function updatedAreaFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    /**
     * Tính toán các thông số thống kê (KPIs)
     */
    public function stats(): array
    {
        // Gom 4 chỉ số đếm vào 1 query SQL thay vì 4 query riêng lẻ
        $counts = Employee::query()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(CASE WHEN status = 'working' THEN 1 ELSE 0 END) AS working")
            ->selectRaw("SUM(CASE WHEN status = 'on_leave' THEN 1 ELSE 0 END) AS on_leave")
            ->selectRaw("SUM(CASE WHEN status = 'resigned' THEN 1 ELSE 0 END) AS resigned")
            ->first();

        // Đếm số tài liệu đính kèm sắp hết hạn trong vòng 30 ngày hoặc đã hết hạn
        // (JSON column nên phải duyệt PHP, nhưng chỉ nạp đúng cột documents)
        $expDocsCount = 0;
        foreach (Employee::whereNotNull('documents')->pluck('documents') as $docs) {
            if (is_string($docs)) {
                $docs = json_decode($docs, true);
            }
            if (! is_array($docs)) {
                continue;
            }
            foreach ($docs as $doc) {
                if (! empty($doc['expired_at'])) {
                    try {
                        // Carbon 3: diffInDays có dấu (âm khi expired_at ở tương lai) nên điều kiện cũ
                        // "<= 30" đúng với MỌI hồ sơ còn hạn — đếm "sắp hết hạn" bằng isBetween cho chuẩn
                        $expDate = Carbon::parse($doc['expired_at']);
                        if ($expDate->isPast() || $expDate->isBetween(now(), now()->addDays(30))) {
                            $expDocsCount++;
                        }
                    } catch (\Exception $e) {
                        // Bỏ qua nếu lỗi format ngày
                    }
                }
            }
        }

        return [
            'total' => (int) $counts->total,
            'working' => (int) $counts->working,
            'leave' => (int) $counts->on_leave,
            'resign' => (int) $counts->resigned,
            'exp_docs' => $expDocsCount,
        ];
    }

    /**
     * Query gốc áp dụng tìm kiếm + bộ lọc — dùng chung cho bảng và export
     */
    protected function baseQuery()
    {
        return Employee::query()
            ->when($this->search !== '', function ($query) {
                $query->where(fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%'));
            })
            ->when($this->departmentFilter !== '', fn ($query) => $query->where('department', $this->departmentFilter))
            ->when($this->positionFilter !== '', fn ($query) => $query->where('position', $this->positionFilter))
            ->when($this->areaFilter !== '', fn ($query) => $query->where('area_id', $this->areaFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->orderBy('code', 'asc');
    }

    /**
     * Lấy danh sách nhân viên được lọc và phân trang
     */
    public function employees()
    {
        return $this->baseQuery()
            ->with(['area', 'kitchen'])
            ->paginate($this->perPage);
    }

    /**
     * Reset các bộ lọc về mặc định
     */
    public function resetFilters()
    {
        $this->reset(['search', 'departmentFilter', 'positionFilter', 'areaFilter', 'statusFilter']);
        $this->resetPage();
    }

    /**
     * Xóa nhân viên
     */
    public function deleteEmployee(int $id)
    {
        $emp = Employee::find($id);
        if ($emp) {
            abort_unless(EmployeeResource::canDelete($emp), 403);
            $emp->delete();
            session()->flash('message', 'Đã xóa nhân viên thành công!');
        }
    }

    /**
     * Xuất dữ liệu nhân viên ra CSV/Excel
     */
    public function exportEmployees(): StreamedResponse
    {
        abort_unless(EmployeeResource::canViewAny(), 403);

        // Export tôn trọng đúng bộ lọc/tìm kiếm đang áp dụng trên bảng
        $employees = $this->baseQuery()->with('area')->get();
        $fileName = 'DANH_SACH_NHAN_VIEN_'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($employees): void {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, ['STT', 'Mã nhân viên', 'Họ và tên', 'Email', 'Số điện thoại', 'Phòng ban', 'Vị trí', 'Khu vực', 'Ngày vào làm', 'Trạng thái']);

            $i = 1;
            foreach ($employees as $emp) {
                fputcsv($output, [
                    $i++,
                    $emp->code,
                    $emp->name,
                    $emp->email,
                    $emp->phone,
                    $emp->department,
                    $emp->position,
                    $emp->area?->name ?? '',
                    $emp->start_date ? $emp->start_date->format('d/m/Y') : '',
                    $emp->status,
                ]);
            }
            fclose($output);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Danh sách phòng ban phục vụ bộ lọc
     */
    public function getDepartments(): array
    {
        return Employee::select('department')
            ->distinct()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->pluck('department')
            ->toArray();
    }

    /**
     * Danh sách chức vụ phục vụ bộ lọc
     */
    public function getPositions(): array
    {
        return Employee::select('position')
            ->distinct()
            ->whereNotNull('position')
            ->where('position', '!=', '')
            ->pluck('position')
            ->toArray();
    }

    /**
     * Danh sách khu vực phục vụ bộ lọc
     */
    public function getAreas()
    {
        return Area::all();
    }
}
