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
use Illuminate\Support\HtmlString;
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
                ->modalHeading('Xóa')
                ->modalDescription(function ($record = null) {
                    if (! $record) {
                        return 'Bạn có chắc chắn muốn xóa các mục đã chọn?';
                    }
                    $class = get_class($record);

                    return match (true) {
                        str_contains($class, 'Ingredient') => 'Bạn có chắc chắn muốn xóa nguyên liệu này?',
                        str_contains($class, 'Supplier') => 'Bạn có chắc chắn muốn xóa nhà cung cấp này không?',
                        str_contains($class, 'Recipe') => 'Bạn có chắc chắn muốn xóa món ăn này?',
                        str_contains($class, 'Employee') => 'Bạn có chắc chắn muốn xóa nhân viên này?',
                        default => 'Bạn có chắc chắn muốn xóa mục này?',
                    };
                })
                ->modalSubmitActionLabel('Xóa')
                ->modalCancelActionLabel('Hủy')
                ->modalIcon('heroicon-s-exclamation-triangle')
                ->modalIconColor('danger')
                ->icon(new HtmlString('<i class="fa-solid fa-trash" style="color:#dc2626;font-size:14px"></i>'));
        };

        DeleteAction::configureUsing($configureDelete);
        TableDeleteAction::configureUsing($configureDelete);
        DeleteBulkAction::configureUsing($configureDelete);
        ForceDeleteAction::configureUsing($configureDelete);
        TableForceDeleteAction::configureUsing($configureDelete);
        ForceDeleteBulkAction::configureUsing($configureDelete);
    }
}
