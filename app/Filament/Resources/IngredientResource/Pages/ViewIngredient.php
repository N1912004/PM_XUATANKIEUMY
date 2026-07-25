<?php

namespace App\Filament\Resources\IngredientResource\Pages;

use App\Filament\Resources\IngredientResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewIngredient extends ViewRecord
{
    protected static string $resource = IngredientResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function getSubheading(): ?string
    {
        return __('ingredient.actions.view_desc');
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/admin' => __('ingredient.breadcrumb.home'),
            $this->getResource()::getUrl('index') => __('ingredient.breadcrumb.list'),
            __('ingredient.breadcrumb.view'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label(__('ingredient.actions.back'))
                ->icon('heroicon-o-arrow-left')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
            Actions\EditAction::make()
                ->label(__('ingredient.actions.edit'))
                ->icon('heroicon-o-pencil'),
        ];
    }
}
