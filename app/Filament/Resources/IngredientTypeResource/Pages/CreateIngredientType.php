<?php

namespace App\Filament\Resources\IngredientTypeResource\Pages;

use App\Filament\Resources\IngredientTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIngredientType extends CreateRecord
{
    protected static string $resource = IngredientTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
