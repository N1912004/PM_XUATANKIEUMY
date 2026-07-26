<?php

namespace App\Filament\Resources\KitchenResource\Pages;

use App\Filament\Resources\KitchenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKitchen extends EditRecord
{
    protected static string $resource = KitchenResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('catalog.breadcrumb.home'),
            KitchenResource::getUrl('index') => __('catalog.breadcrumb.kitchen.list'),
            __('catalog.breadcrumb.kitchen.edit'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
