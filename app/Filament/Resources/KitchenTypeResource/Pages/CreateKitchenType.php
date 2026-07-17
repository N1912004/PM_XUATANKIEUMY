<?php

namespace App\Filament\Resources\KitchenTypeResource\Pages;

use App\Filament\Resources\KitchenTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKitchenType extends CreateRecord
{
    protected static string $resource = KitchenTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
