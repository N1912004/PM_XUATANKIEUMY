<?php

namespace App\Filament\Resources\RecipeTypeResource\Pages;

use App\Filament\Resources\RecipeTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRecipeTypes extends ManageRecords
{
    protected static string $resource = RecipeTypeResource::class;

    public function getTitle(): string
    {
        return __('Nhóm món');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
