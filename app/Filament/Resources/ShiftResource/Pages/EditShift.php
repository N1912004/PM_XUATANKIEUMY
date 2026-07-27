<?php

namespace App\Filament\Resources\ShiftResource\Pages;

use App\Filament\Resources\ShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditShift extends EditRecord
{
    protected static string $resource = ShiftResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('catalog.breadcrumb.shift.edit');
    }

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('catalog.breadcrumb.home'),
            ShiftResource::getUrl('index') => __('catalog.breadcrumb.shift.list'),
            __('catalog.breadcrumb.shift.edit'),
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
