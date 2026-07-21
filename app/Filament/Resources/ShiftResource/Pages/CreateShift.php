<?php

namespace App\Filament\Resources\ShiftResource\Pages;

use App\Filament\Resources\MenuResource;
use App\Filament\Resources\ShiftResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateShift extends CreateRecord
{
    protected static string $resource = ShiftResource::class;

    public ?string $fromView = null;

    public ?string $fromDate = null;

    public ?string $fromKitchen = null;

    public function mount(): void
    {
        parent::mount();

        $this->fromView = request()->query('from', session('shift_from'));
        $this->fromDate = request()->query('date', session('shift_date'));
        $this->fromKitchen = request()->query('kitchen', session('shift_kitchen'));

        if (request()->has('from')) {
            session([
                'shift_from' => $this->fromView,
                'shift_date' => $this->fromDate,
                'shift_kitchen' => $this->fromKitchen,
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        $actions = parent::getHeaderActions();

        if ($this->fromView === 'day_menu') {
            $actions[] = Action::make('backToDayMenu')
                ->label(__('menu.actions.back_to_day_menu'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(MenuResource::getUrl('index', array_filter([
                    'activeView' => 'day',
                    'dayDate' => $this->fromDate,
                    'dayKitchenId' => $this->fromKitchen,
                ])));
        }

        return $actions;
    }

    protected function getRedirectUrl(): string
    {
        if ($this->fromView === 'day_menu') {
            $date = $this->fromDate;
            $kitchen = $this->fromKitchen;
            session()->forget(['shift_from', 'shift_date', 'shift_kitchen']);

            return MenuResource::getUrl('index', array_filter([
                'activeView' => 'day',
                'dayDate' => $date,
                'dayKitchenId' => $kitchen,
            ]));
        }

        return $this->getResource()::getUrl('index');
    }
}
