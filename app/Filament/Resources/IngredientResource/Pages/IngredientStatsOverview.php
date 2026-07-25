<?php

namespace App\Filament\Resources\IngredientResource\Pages;

use App\Models\Ingredient;
use App\Models\IngredientType;
use App\Models\Supplier;
use App\Models\Unit;
use Filament\Widgets\StatsOverviewWidget;

class IngredientStatsOverview extends StatsOverviewWidget
{
    protected static string $view = 'filament.resources.ingredients.widgets.ingredient-stats';

    protected function getViewData(): array
    {
        return [
            'totalIngredients' => Ingredient::count(),
            'totalSuppliers' => Supplier::whereHas('ingredients')->count(),
            'totalUnits' => Unit::count(),
            'totalTypes' => IngredientType::count(),
        ];
    }
}
