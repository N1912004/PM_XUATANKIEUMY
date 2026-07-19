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
            Stat::make(__('ingredient.stats.total'), $totalIngredients)
                ->description(__('ingredient.stats.total_desc'))
                ->descriptionIcon('heroicon-o-information-circle')
                ->color('primary')
                ->icon('heroicon-o-beaker'),
            Stat::make(__('ingredient.stats.suppliers'), $totalSuppliers)
                ->description(__('ingredient.stats.suppliers_desc'))
                ->descriptionIcon('heroicon-o-information-circle')
                ->color('primary')
                ->icon('heroicon-o-truck'),
            Stat::make(__('ingredient.stats.units'), $totalUnits)
                ->description(__('ingredient.stats.units_desc'))
                ->descriptionIcon('heroicon-o-information-circle')
                ->color('primary')
                ->icon('heroicon-o-chart-bar'),
            Stat::make(__('ingredient.stats.types'), $totalTypes)
                ->description(__('ingredient.stats.types_desc'))
                ->descriptionIcon('heroicon-o-information-circle')
                ->color('primary')
                ->icon('heroicon-o-tag'),
        ];
    }
}
