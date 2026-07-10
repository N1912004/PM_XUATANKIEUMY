<?php

namespace App\Filament\Resources\IngredientResource\Pages;

use App\Exports\IngredientsExport;
use App\Filament\Resources\IngredientResource;
use App\Imports\IngredientsImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListIngredients extends ListRecords
{
    protected static string $resource = IngredientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import_excel')
                ->label(__('ingredient.actions.import'))
                ->icon('heroicon-o-document-arrow-up')
                ->color('info')
                ->form([
                    FileUpload::make('excel_file')
                        ->label(__('ingredient.actions.import_file'))
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->required()
                        ->disk('local')
                        ->directory('imports'),
                ])
                ->action(function (array $data) {
                    $filePath = storage_path('app/'.$data['excel_file']);

                    try {
                        Excel::import(
                            new IngredientsImport,
                            $filePath
                        );

                        Notification::make()
                            ->title(__('ingredient.actions.import_success'))
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title(__('ingredient.actions.import_error'))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }),
            Actions\Action::make('export_excel')
                ->label(__('ingredient.actions.export'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return Excel::download(
                        new IngredientsExport,
                        'danh-muc-nguyen-lieu-'.now()->format('Ymd-His').'.xlsx'
                    );
                }),
            Actions\CreateAction::make()
                ->label(__('ingredient.actions.create'))
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            IngredientStatsOverview::class,
        ];
    }
}
