<?php

namespace App\Filament\Resources\MenuAuditLogResource\Pages;

use App\Filament\Resources\MenuAuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListMenuAuditLogs extends ListRecords
{
    protected static string $resource = MenuAuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
