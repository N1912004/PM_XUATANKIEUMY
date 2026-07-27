<?php

namespace App\Filament\Resources\KitchenResource\Pages;

use App\Filament\Resources\AreaResource;
use App\Filament\Resources\KitchenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditKitchen extends EditRecord
{
    protected static string $resource = KitchenResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('catalog.breadcrumb.kitchen.edit');
    }

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('catalog.breadcrumb.home'),
            AreaResource::getUrl('index').'?activeTab=canteen' => __('catalog.breadcrumb.kitchen.list'),
            __('catalog.breadcrumb.kitchen.edit'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(fn () => redirect()->to(AreaResource::getUrl('index').'?activeTab=canteen')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return AreaResource::getUrl('index').'?activeTab=canteen';
    }
}
