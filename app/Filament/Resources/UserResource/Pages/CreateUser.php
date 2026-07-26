<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('user.breadcrumb.home'),
            UserResource::getUrl('index') => __('user.breadcrumb.list'),
            __('user.breadcrumb.create'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
