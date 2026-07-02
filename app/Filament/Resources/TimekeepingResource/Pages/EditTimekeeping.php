<?php

namespace App\Filament\Resources\TimekeepingResource\Pages;

use App\Filament\Resources\TimekeepingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTimekeeping extends EditRecord
{
    protected static string $resource = TimekeepingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
