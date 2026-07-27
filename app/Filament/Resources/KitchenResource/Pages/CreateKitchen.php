<?php

namespace App\Filament\Resources\KitchenResource\Pages;

use App\Filament\Resources\AreaResource;
use App\Filament\Resources\KitchenResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateKitchen extends CreateRecord
{
    protected static string $resource = KitchenResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('catalog.breadcrumb.kitchen.create');
    }

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('catalog.breadcrumb.home'),
            AreaResource::getUrl('index').'?activeTab=canteen' => __('catalog.breadcrumb.kitchen.list'),
            __('catalog.breadcrumb.kitchen.create'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return AreaResource::getUrl('index').'?activeTab=canteen';
    }
}
