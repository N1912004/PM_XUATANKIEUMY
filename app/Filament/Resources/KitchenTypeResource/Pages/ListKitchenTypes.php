<?php

namespace App\Filament\Resources\KitchenTypeResource\Pages;

use App\Filament\Resources\KitchenTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKitchenTypes extends ListRecords
{
    protected static string $resource = KitchenTypeResource::class;

    public function getTitle(): string
    {
        return __('Loại bếp / nhà ăn');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
