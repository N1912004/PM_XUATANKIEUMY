<?php

namespace App\Filament\Resources\FoodSafetyAuditResource\Pages;

use App\Filament\Resources\FoodSafetyAuditResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFoodSafetyAudit extends EditRecord
{
    protected static string $resource = FoodSafetyAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
