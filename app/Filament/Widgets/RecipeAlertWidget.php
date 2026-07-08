<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class RecipeAlertWidget extends Widget
{
    protected static string $view = 'filament.widgets.recipe-alert-widget';

    protected int|string|array $columnSpan = 'full';
}
