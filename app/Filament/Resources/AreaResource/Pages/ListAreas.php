<?php

namespace App\Filament\Resources\AreaResource\Pages;

use App\Filament\Resources\AreaResource;
use App\Models\Area;
use App\Models\Kitchen;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;

class ListAreas extends Page
{
    use WithPagination;

    protected static string $resource = AreaResource::class;

    protected static string $view = 'filament.resources.areas.pages.list-areas';

    public string $activeTab = 'area';

    public string $areaSearch = '';

    public string $canteenSearch = '';

    public ?int $canteenAreaFilter = null;

    public int $areaPerPage = 10;

    public int $canteenPerPage = 10;

    protected $queryString = [
        'activeTab' => ['except' => 'area'],
        'areaSearch' => ['except' => ''],
        'canteenSearch' => ['except' => ''],
        'canteenAreaFilter' => ['except' => null],
        'areaPerPage' => ['except' => 10],
        'canteenPerPage' => ['except' => 10],
    ];

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function updatedAreaSearch(): void
    {
        $this->resetPage('areasPage');
    }

    public function updatedCanteenSearch(): void
    {
        $this->resetPage('canteensPage');
    }

    public function updatedCanteenAreaFilter(): void
    {
        $this->resetPage('canteensPage');
    }

    public function updatedAreaPerPage(): void
    {
        $this->areaPerPage = $this->resolvePerPage($this->areaPerPage);
        $this->resetPage('areasPage');
    }

    public function updatedCanteenPerPage(): void
    {
        $this->canteenPerPage = $this->resolvePerPage($this->canteenPerPage);
        $this->resetPage('canteensPage');
    }

    public function resetAreaFilters(): void
    {
        $this->areaSearch = '';
        $this->resetPage('areasPage');
    }

    public function resetCanteenFilters(): void
    {
        $this->canteenSearch = '';
        $this->canteenAreaFilter = null;
        $this->resetPage('canteensPage');
    }

    public function resetForms(): void
    {
        $this->resetAreaFilters();
        $this->resetCanteenFilters();
    }

    public function deleteArea($id): void
    {
        $area = Area::find($id);
        if ($area) {
            if ($area->kitchens()->exists()) {
                session()->flash('error', __('catalog.area.errors.in_use'));

                return;
            }
            $area->delete();
            session()->flash('message', __('catalog.area.notifications.deleted'));
        }
    }

    public function deleteCanteen($id): void
    {
        $kitchen = Kitchen::find($id);
        if ($kitchen) {
            $kitchen->delete();
            session()->flash('message', __('catalog.kitchen.notifications.deleted'));
        }
    }

    public function areas()
    {
        $query = Area::query()->with('manager')->withCount('kitchens');
        if ($this->areaSearch) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->areaSearch.'%')
                    ->orWhere('code', 'like', '%'.$this->areaSearch.'%');
            });
        }

        return $query->paginate($this->resolvePerPage($this->areaPerPage), ['*'], 'areasPage');
    }

    public function kitchens()
    {
        $query = Kitchen::with(['area', 'kitchenType', 'manager']);
        if ($this->canteenSearch) {
            $query->where('name', 'like', '%'.$this->canteenSearch.'%');
        }
        if ($this->canteenAreaFilter) {
            $query->where('area_id', $this->canteenAreaFilter);
        }

        return $query->paginate($this->resolvePerPage($this->canteenPerPage), ['*'], 'canteensPage');
    }

    private function resolvePerPage(int|string $value): int
    {
        $value = (int) $value;

        return in_array($value, [5, 10, 20, 30, 50], true) ? $value : 10;
    }

    public function getStats(): array
    {
        $areaManagers = Area::whereNotNull('manager_id')->pluck('manager_id')->toArray();
        $kitchenManagers = Kitchen::whereNotNull('manager_id')->pluck('manager_id')->toArray();
        $uniqueManagers = count(array_unique(array_merge($areaManagers, $kitchenManagers)));

        return [
            'total_areas' => Area::count(),
            'active_areas' => Area::where('status', true)->count(),
            'total_kitchens' => Kitchen::count(),
            'managers' => $uniqueManagers ?: Area::whereNotNull('manager_id')->count(),
        ];
    }
}
