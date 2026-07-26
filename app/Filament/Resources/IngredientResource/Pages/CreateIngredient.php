<?php

namespace App\Filament\Resources\IngredientResource\Pages;

use App\Filament\Resources\IngredientResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateIngredient extends CreateRecord
{
    protected static string $resource = IngredientResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
            $this->getCreateAnotherFormAction()
                ->label(__('ingredient.actions.save_draft'))
                ->icon('heroicon-o-document-text'),
            $this->getCreateFormAction(),
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
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
            __('ingredient.breadcrumb.create'),
        ];
    }

    public function getTitle(): string
    {
        return __('ingredient.actions.create');
    }

    public function getSubheading(): ?string
    {
        return __('ingredient.actions.create_desc');
    }
}
