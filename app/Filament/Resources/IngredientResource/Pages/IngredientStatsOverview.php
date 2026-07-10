<?php

namespace App\Filament\Resources\IngredientResource\Pages;

use App\Models\Ingredient;
use App\Models\IngredientType;
use App\Models\Supplier;
use App\Models\Unit;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IngredientStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalIngredients = Ingredient::count();
        $totalSuppliers = Supplier::whereHas('ingredients')->count();
        // Cột unit/type đã chuyển sang bảng danh mục riêng — đếm trực tiếp từ bảng mới
        $totalUnits = Unit::count();
        $totalTypes = IngredientType::count();

        return [
            Stat::make('Tổng nguyên liệu', $totalIngredients)
                ->description('Đang quản lý')
                ->descriptionIcon('heroicon-o-information-circle')
                ->color('primary')
                ->icon('heroicon-o-beaker'),
            Stat::make('Nhà cung cấp', $totalSuppliers)
                ->description('Đang liên kết')
                ->descriptionIcon('heroicon-o-information-circle')
                ->color('success')
                ->icon('heroicon-o-truck'),
            Stat::make('Đơn vị tính', $totalUnits)
                ->description('Kg, Gói, Chai...')
                ->descriptionIcon('heroicon-o-information-circle')
                ->color('warning')
                ->icon('heroicon-o-chart-bar'),
            Stat::make('Loại nguyên liệu', $totalTypes)
                ->description('Động vật, thực vật...')
                ->descriptionIcon('heroicon-o-information-circle')
                ->color('info')
                ->icon('heroicon-o-tag'),
        ];
    }
}
