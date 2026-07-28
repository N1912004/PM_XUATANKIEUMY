<?php

namespace App\Filament\Resources\ShiftResource\Pages;

use App\Filament\Resources\ShiftResource;
use App\Models\Menu;
use App\Models\Shift;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;

class ListShifts extends Page
{
    use WithPagination;

    protected static string $resource = ShiftResource::class;

    protected static string $view = 'filament.resources.shifts.pages.list-shifts';

    public string $shiftSearch = '';

    public int $shiftPerPage = 10;

    protected $queryString = [
        'shiftSearch' => ['except' => ''],
        'shiftPerPage' => ['except' => 10],
    ];

    public function getTitle(): string
    {
        return __('catalog.shift.list.title');
    }

    public function updatedShiftSearch(): void
    {
        $this->resetPage('shiftsPage');
    }

    public function updatedShiftPerPage(): void
    {
        $this->shiftPerPage = $this->resolvePerPage($this->shiftPerPage);
        $this->resetPage('shiftsPage');
    }

    public function resetFilters(): void
    {
        $this->shiftSearch = '';
        $this->resetPage('shiftsPage');
    }

    public function shifts()
    {
        $query = Shift::query();

        if ($this->shiftSearch) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->shiftSearch.'%')
                    ->orWhere('time_from', 'like', '%'.$this->shiftSearch.'%')
                    ->orWhere('time_to', 'like', '%'.$this->shiftSearch.'%');
            });
        }

        return $query->orderBy('sort_order', 'asc')->paginate($this->resolvePerPage($this->shiftPerPage), ['*'], 'shiftsPage');
    }

    private function resolvePerPage(int|string $value): int
    {
        $value = (int) $value;

        return in_array($value, [5, 10, 20, 50], true) ? $value : 10;
    }

    public function deleteShift($id): void
    {
        $shift = Shift::find($id);
        if ($shift) {
            if ($shift->menus()->exists()) {
                session()->flash('error', __('catalog.shift.list.errors.in_use'));

                return;
            }
            $shift->delete();
            session()->flash('message', __('catalog.shift.notifications.deleted'));
        }
    }

    public function getStats(): array
    {
        $total = Shift::count();
        $inUseIds = Menu::distinct()->pluck('shift_id')->filter()->toArray();
        $inUse = count($inUseIds);

        return [
            'total' => $total,
            'in_use' => $inUse,
            'unused' => max(0, $total - $inUse),
        ];
    }
}
