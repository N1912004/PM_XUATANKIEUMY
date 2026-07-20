<?php

namespace App\Filament\Resources\EmployeeResource\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('employee.widgets.total'), Employee::count())
                ->description(__('employee.widgets.total_description'))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make(__('employee.widgets.working'), Employee::where('status', 'working')->count())
                ->description(__('employee.widgets.working_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make(__('employee.widgets.absent'), Employee::whereIn('status', ['on_leave', 'resigned'])->count())
                ->description(__('employee.widgets.absent_description'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
