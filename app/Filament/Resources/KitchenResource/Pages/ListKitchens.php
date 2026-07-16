<?php

namespace App\Filament\Resources\KitchenResource\Pages;

use App\Filament\Resources\KitchenResource;
use App\Models\Area;
use App\Models\Kitchen;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;

class ListKitchens extends Page
{
    use WithPagination;

    protected static string $resource = KitchenResource::class;

    protected static string $view = 'filament.resources.kitchens.pages.list-kitchens';

    // FILTERS
    public $kitchenSearch = '';

    public $kitchenAreaFilter = '';

    public $kitchenTypeFilter = '';

    public $kitchenStatusFilter = '';

    protected $queryString = [
        'kitchenSearch' => ['except' => ''],
        'kitchenAreaFilter' => ['except' => ''],
        'kitchenTypeFilter' => ['except' => ''],
        'kitchenStatusFilter' => ['except' => ''],
    ];

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

    public function deleteKitchen($id): void
    {
        $kitchen = Kitchen::find($id);
        if ($kitchen) {
            $kitchen->delete();
            session()->flash('message', 'Xóa nhà ăn/bếp thành công.');
        }
    }

    public function resetFilters(): void
    {
        $this->kitchenSearch = '';
        $this->kitchenAreaFilter = '';
        $this->kitchenTypeFilter = '';
        $this->kitchenStatusFilter = '';
        $this->resetPage('kitchensPage');
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

    public function getStats(): array
    {
        return [
            'total_kitchens' => Kitchen::count(),
            'active_kitchens' => Kitchen::where('status', 'active')->count(),
            'total_areas' => Area::count(),
            'managers' => Kitchen::whereNotNull('manager_id')->distinct('manager_id')->count('manager_id'),
        ];
    }
}
