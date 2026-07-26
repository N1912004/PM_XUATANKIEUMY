<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUnit extends CreateRecord
{
    protected static string $resource = UnitResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('ingredient.breadcrumb.home'),
            UnitResource::getUrl('index') => __('ingredient.breadcrumb.unit_list'),
            __('ingredient.breadcrumb.unit_create'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
