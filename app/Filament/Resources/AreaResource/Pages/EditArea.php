<?php

namespace App\Filament\Resources\AreaResource\Pages;

use App\Filament\Resources\AreaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditArea extends EditRecord
{
    protected static string $resource = AreaResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('catalog.breadcrumb.area.edit');
    }

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('catalog.breadcrumb.home'),
            AreaResource::getUrl('index') => __('catalog.breadcrumb.area.list'),
            __('catalog.breadcrumb.area.edit'),
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
