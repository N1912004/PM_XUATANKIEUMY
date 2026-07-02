<?php

namespace App\Filament\Resources\TimekeepingResource\Widgets;

use Filament\Widgets\Widget;

class AttendanceCheck extends Widget
{
    protected static string $view = 'filament.widgets.attendance-check';

    protected int|string|array $columnSpan = 'full';
}
