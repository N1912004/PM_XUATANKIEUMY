<?php

namespace App\Filament\Resources\AreaResource\Pages;

use App\Filament\Resources\AreaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateArea extends CreateRecord
{
    protected static string $resource = AreaResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('catalog.breadcrumb.area.create');
    }

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('catalog.breadcrumb.home'),
            AreaResource::getUrl('index') => __('catalog.breadcrumb.area.list'),
            __('catalog.breadcrumb.area.create'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
