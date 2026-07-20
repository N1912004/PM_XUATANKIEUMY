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

    public $areaSearch = '';

    protected $queryString = [
        'areaSearch' => ['except' => ''],
    ];

    public function updatedAreaSearch(): void
    {
        $this->resetPage('areasPage');
    }

    public function deleteArea($id): void
    {
        $area = Area::find($id);
        if ($area) {
            // Kiểm tra ràng buộc: còn nhà ăn/bếp trực thuộc thì không cho xóa
            if ($area->kitchens()->exists()) {
                session()->flash('error', __('catalog.area.errors.in_use'));

                return;
            }
            $area->delete();
            session()->flash('message', __('catalog.area.notifications.deleted'));
        }
    }

    public function resetFilters(): void
    {
        $this->areaSearch = '';
        $this->resetPage('areasPage');
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

    public function getStats(): array
    {
        return [
            'total_areas' => Area::count(),
            'active_areas' => Area::where('status', true)->count(),
            'total_kitchens' => Kitchen::count(),
            'managers' => Area::whereNotNull('manager_id')->distinct('manager_id')->count('manager_id'),
        ];
    }
}
