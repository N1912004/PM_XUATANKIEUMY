<?php

namespace App\Filament\Resources\TimekeepingResource\Pages;

use App\Filament\Resources\TimekeepingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTimekeepings extends ListRecords
{
    protected static string $resource = TimekeepingResource::class;

    public function getSubheading(): ?string
    {
        return 'Theo dõi check-in, check-out, ca làm việc và tình trạng đi làm của nhân viên';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Xuất dữ liệu')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->button(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TimekeepingResource\Widgets\AttendanceCheck::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
