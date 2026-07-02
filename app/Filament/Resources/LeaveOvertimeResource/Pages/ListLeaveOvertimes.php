<?php

namespace App\Filament\Resources\LeaveOvertimeResource\Pages;

use App\Filament\Resources\LeaveOvertimeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLeaveOvertimes extends ListRecords
{
    protected static string $resource = LeaveOvertimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
