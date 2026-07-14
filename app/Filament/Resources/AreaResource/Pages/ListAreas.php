<?php

namespace App\Filament\Resources\AreaResource\Pages;

use App\Filament\Resources\AreaResource;
use App\Models\Area;
use App\Models\Employee;
use App\Models\Kitchen;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;

class ListAreas extends Page
{
    use WithPagination;
    protected static string $resource = AreaResource::class;

    protected static string $view = 'filament.resources.areas.pages.list-areas';

    // Tabs: 'area' hoặc 'canteen'
    public $activeTab = 'area';

    // AREA FORM FIELDS
    public $areaId;

    public $areaName;

    public $areaCode;

    public $areaManagerId;

    public $areaStatus = 'Đang hoạt động';

    public $areaNotes;

    // AREA SEARCH
    public $areaSearch = '';

    // CANTEEN FORM FIELDS
    public $kitchenId;

    public $kitchenAreaId;

    public $kitchenName;

    public $kitchenType = 'Bếp sản xuất';

    public $kitchenCapacity;

    public $kitchenManagerId;

    public $kitchenStatus = 'Đang hoạt động';

    // CANTEEN FILTERS
    public $kitchenSearch = '';

    public $kitchenAreaFilter = '';

    public $kitchenTypeFilter = '';

    public $kitchenStatusFilter = '';

    protected $queryString = [
        'activeTab' => ['except' => 'area'],
        'areaSearch' => ['except' => ''],
        'kitchenSearch' => ['except' => ''],
        'kitchenAreaFilter' => ['except' => ''],
        'kitchenTypeFilter' => ['except' => ''],
        'kitchenStatusFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        $firstArea = Area::first();
        if ($firstArea) {
            $this->kitchenAreaId = $firstArea->id;
            $this->kitchenAreaFilter = $firstArea->id;
        }
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage('areasPage');
        $this->resetPage('kitchensPage');
    }

    public function updatedAreaSearch(): void
    {
        $this->resetPage('areasPage');
    }

    public function updatedKitchenSearch(): void
    {
        $this->resetPage('kitchensPage');
    }

    public function updatedKitchenAreaFilter(): void
    {
        $this->resetPage('kitchensPage');
    }

    public function updatedKitchenTypeFilter(): void
    {
        $this->resetPage('kitchensPage');
    }

    public function updatedKitchenStatusFilter(): void
    {
        $this->resetPage('kitchensPage');
    }

    // ==========================================
    // AREA ACTIONS
    // ==========================================
    public function editArea($id)
    {
        $area = Area::find($id);
        if ($area) {
            $this->areaId = $area->id;
            $this->areaName = $area->name;
            $this->areaCode = $area->code;
            $this->areaManagerId = $area->manager_id;
            $this->areaStatus = $area->status;
            $this->areaNotes = $area->notes;
        }
    }

    public function saveArea()
    {
        $this->validate([
            'areaName' => 'required|string|max:255',
            'areaCode' => 'required|string|max:50',
            'areaStatus' => 'required',
        ], [
            'areaName.required' => 'Tên khu vực là bắt buộc.',
            'areaCode.required' => 'Mã khu vực là bắt buộc.',
            'areaStatus.required' => 'Trạng thái là bắt buộc.',
        ]);

        $data = [
            'name' => $this->areaName,
            'code' => $this->areaCode,
            'manager_id' => $this->areaManagerId ?: null,
            'status' => $this->areaStatus,
            'notes' => $this->areaNotes,
        ];

        if ($this->areaId) {
            $area = Area::find($this->areaId);
            if ($area) {
                $area->update($data);
                session()->flash('message', 'Cập nhật khu vực thành công!');
            }
        } else {
            // Kiểm tra mã trùng
            if (Area::where('code', $this->areaCode)->exists()) {
                $this->addError('areaCode', 'Mã khu vực đã tồn tại.');

                return;
            }
            Area::create($data);
            session()->flash('message', 'Thêm khu vực mới thành công!');
        }

        $this->resetAreaForm();
    }

    public function deleteArea($id)
    {
        $area = Area::find($id);
        if ($area) {
            // Kiểm tra ràng buộc
            if ($area->kitchens()->exists()) {
                session()->flash('error', 'Không thể xóa khu vực này vì vẫn còn nhà ăn/bếp trực thuộc.');

                return;
            }
            $area->delete();
            session()->flash('message', 'Xóa khu vực thành công.');
        }
    }

    public function resetAreaForm()
    {
        $this->areaId = null;
        $this->areaName = null;
        $this->areaCode = null;
        $this->areaManagerId = null;
        $this->areaStatus = 'Đang hoạt động';
        $this->areaNotes = null;
    }

    public function areas()
    {
        $query = Area::with(['manager', 'kitchens']);
        if ($this->areaSearch) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->areaSearch.'%')
                    ->orWhere('code', 'like', '%'.$this->areaSearch.'%');
            });
        }

        return $query->paginate(10, ['*'], 'areasPage');
    }

    // ==========================================
    // CANTEEN / KITCHEN ACTIONS
    // ==========================================
    public function editKitchen($id)
    {
        $kitchen = Kitchen::find($id);
        if ($kitchen) {
            $this->kitchenId = $kitchen->id;
            $this->kitchenAreaId = $kitchen->area_id;
            $this->kitchenName = $kitchen->name;
            $this->kitchenType = $kitchen->type;
            $this->kitchenCapacity = $kitchen->capacity;
            $this->kitchenManagerId = $kitchen->manager_id;
            $this->kitchenStatus = $kitchen->status;
        }
    }

    public function saveKitchen()
    {
        $this->validate([
            'kitchenAreaId' => 'required',
            'kitchenName' => 'required|string|max:255',
            'kitchenType' => 'required',
            'kitchenCapacity' => 'required|numeric|min:0',
            'kitchenStatus' => 'required',
        ], [
            'kitchenAreaId.required' => 'Vui lòng chọn khu vực.',
            'kitchenName.required' => 'Tên nhà ăn/bếp là bắt buộc.',
            'kitchenType.required' => 'Vui lòng chọn loại.',
            'kitchenCapacity.required' => 'Công suất là bắt buộc.',
            'kitchenCapacity.numeric' => 'Công suất phải là số.',
            'kitchenStatus.required' => 'Trạng thái là bắt buộc.',
        ]);

        $data = [
            'area_id' => $this->kitchenAreaId,
            'name' => $this->kitchenName,
            'type' => $this->kitchenType,
            'capacity' => $this->kitchenCapacity,
            'manager_id' => $this->kitchenManagerId ?: null,
            'status' => $this->kitchenStatus,
        ];

        if ($this->kitchenId) {
            $kitchen = Kitchen::find($this->kitchenId);
            if ($kitchen) {
                $kitchen->update($data);
                session()->flash('message', 'Cập nhật nhà ăn/bếp thành công!');
            }
        } else {
            Kitchen::create($data);
            session()->flash('message', 'Thêm nhà ăn/bếp mới thành công!');
        }

        $this->resetKitchenForm();
    }

    public function deleteKitchen($id)
    {
        $kitchen = Kitchen::find($id);
        if ($kitchen) {
            $kitchen->delete();
            session()->flash('message', 'Xóa nhà ăn/bếp thành công.');
        }
    }

    public function resetKitchenForm()
    {
        $this->kitchenId = null;
        $this->kitchenName = null;
        $this->kitchenCapacity = null;
        $this->kitchenManagerId = null;
        $this->kitchenStatus = 'Đang hoạt động';
        $this->kitchenType = 'Bếp sản xuất';

        $firstArea = Area::first();
        if ($firstArea) {
            $this->kitchenAreaId = $firstArea->id;
        }
    }

    public function kitchens()
    {
        $query = Kitchen::with(['area', 'manager']);

        if ($this->kitchenSearch) {
            $query->where('name', 'like', '%'.$this->kitchenSearch.'%');
        }
        if ($this->kitchenAreaFilter) {
            $query->where('area_id', $this->kitchenAreaFilter);
        }
        if ($this->kitchenTypeFilter) {
            $query->where('type', $this->kitchenTypeFilter);
        }
        if ($this->kitchenStatusFilter) {
            $query->where('status', $this->kitchenStatusFilter);
        }

        return $query->paginate(10, ['*'], 'kitchensPage');
    }

    public function resetFilters()
    {
        if ($this->activeTab === 'area') {
            $this->areaSearch = '';
            $this->resetAreaForm();
        } else {
            $this->kitchenSearch = '';
            $firstArea = Area::first();
            $this->kitchenAreaFilter = $firstArea ? $firstArea->id : '';
            $this->kitchenTypeFilter = '';
            $this->kitchenStatusFilter = '';
            $this->resetKitchenForm();
        }
    }

    // ==========================================
    // HELPERS
    // ==========================================
    public function getEmployees()
    {
        return Employee::orderBy('name')->get();
    }

    public function getStats()
    {
        return [
            'total_areas' => Area::count(),
            'total_kitchens' => Kitchen::count(),
            'active_kitchens' => Kitchen::where('status', 'Đang hoạt động')->count(),
            'managers' => count(array_unique(array_filter(array_merge(
                Area::whereNotNull('manager_id')->pluck('manager_id')->toArray(),
                Kitchen::whereNotNull('manager_id')->pluck('manager_id')->toArray()
            )))),
        ];
    }
}
