<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('menu.breadcrumb.home'),
            MenuResource::getUrl('index') => __('menu.breadcrumb.list'),
            __('menu.breadcrumb.create'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
