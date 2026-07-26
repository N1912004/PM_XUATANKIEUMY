<?php

namespace App\Filament\Resources\IngredientResource\Pages;

use App\Filament\Resources\IngredientResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditIngredient extends EditRecord
{
    protected static string $resource = IngredientResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
            $this->getSaveFormAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('ingredient.actions.save'))
            ->icon('heroicon-m-document-check');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label(__('ingredient.actions.cancel'));
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/admin' => __('ingredient.breadcrumb.home'),
            $this->getResource()::getUrl('index') => __('ingredient.breadcrumb.list'),
            __('ingredient.breadcrumb.edit'),
        ];
    }

    public function getTitle(): string
    {
        return __('ingredient.actions.edit');
    }

    public function getSubheading(): ?string
    {
        return __('ingredient.actions.create_desc');
    }
}
