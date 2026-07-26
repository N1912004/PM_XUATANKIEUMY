<?php

namespace App\Filament\Resources\KitchenResource\Pages;

use App\Filament\Resources\KitchenResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKitchen extends CreateRecord
{
    protected static string $resource = KitchenResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('catalog.breadcrumb.home'),
            KitchenResource::getUrl('index') => __('catalog.breadcrumb.kitchen.list'),
            __('catalog.breadcrumb.kitchen.create'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
