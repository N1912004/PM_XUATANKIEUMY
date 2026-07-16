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
            Stat::make('Tổng nhân viên', Employee::count())
                ->description('Nhân sự toàn hệ thống')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Đang làm việc', Employee::where('status', 'working')->count())
                ->description('Đang hoạt động')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Nghỉ phép / Nghỉ việc', Employee::whereIn('status', ['on_leave', 'resigned'])->count())
                ->description('Vắng mặt / Thôi việc')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
