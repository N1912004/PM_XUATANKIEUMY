<?php

namespace App\Providers;

use BladeUI\Icons\Factory;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteAction as TableForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (class_exists(Factory::class)) {
            app(Factory::class)->add('fa', [
                'path' => resource_path('svg/fa'),
                'prefix' => 'fa',
            ]);
        }

        $configureDelete = function ($action) {
            $action
                ->modalAlignment(Alignment::Left)
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->modalIconColor('danger');
        };

        DeleteAction::configureUsing($configureDelete);
        TableDeleteAction::configureUsing($configureDelete);
        DeleteBulkAction::configureUsing($configureDelete);
        ForceDeleteAction::configureUsing($configureDelete);
        TableForceDeleteAction::configureUsing($configureDelete);
        ForceDeleteBulkAction::configureUsing($configureDelete);
    }
}
