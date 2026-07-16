<?php

namespace App\Filament\Resources\RecipeTypeResource\Pages;

use App\Filament\Resources\RecipeTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRecipeType extends CreateRecord
{
    protected static string $resource = RecipeTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
