<?php

namespace App\Filament\Resources\TimekeepingResource\Pages;

use App\Filament\Resources\TimekeepingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTimekeeping extends CreateRecord
{
    protected static string $resource = TimekeepingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
