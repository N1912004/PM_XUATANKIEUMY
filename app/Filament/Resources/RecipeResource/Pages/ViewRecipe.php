<?php

namespace App\Filament\Resources\RecipeResource\Pages;

use App\Filament\Resources\RecipeResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewRecipe extends ViewRecord
{
    protected static string $resource = RecipeResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('recipe.breadcrumb.home'),
            RecipeResource::getUrl('index') => __('recipe.breadcrumb.list'),
            __('recipe.breadcrumb.view'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return __('recipe.pages.view.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('recipe.pages.view.subtitle');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label(__('recipe.actions.back'))
                ->icon('heroicon-m-arrow-left')
                ->color('gray')
                ->outlined()
                ->url(fn (): string => $this->getResource()::getUrl('index')),
            Actions\EditAction::make()
                ->label(__('recipe.actions.edit'))
                ->icon('heroicon-m-pencil-square')
                ->color('gray')
                ->outlined(),
            Actions\Action::make('approve')
                ->label(__('recipe.actions.approve'))
                ->icon('heroicon-m-check')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->update(['status' => 'active']);
                    Notification::make()
                        ->title(__('recipe.notifications.approved'))
                        ->success()
                        ->send();
                })
                ->visible(fn (): bool => $this->record->status === 'pending'),
        ];
    }
}
