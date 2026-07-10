<?php

namespace App\Filament\Resources\IngredientTypeResource\Pages;

use App\Filament\Resources\IngredientTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageIngredientTypes extends ManageRecords
{
    protected static string $resource = IngredientTypeResource::class;

    public function getTitle(): string
    {
        return __('ingredient.navigation.type_plural');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
