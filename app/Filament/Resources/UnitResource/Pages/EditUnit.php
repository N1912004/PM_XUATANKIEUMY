<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUnit extends EditRecord
{
    protected static string $resource = UnitResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('ingredient.breadcrumb.home'),
            UnitResource::getUrl('index') => __('ingredient.breadcrumb.unit_list'),
            __('ingredient.breadcrumb.unit_edit'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action): void {
                    $record = $this->getRecord();
                    if ($record->ingredients()->exists()) {
                        Notification::make()
                            ->title(__('ingredient.delete.in_use', ['count' => $record->ingredients()->count()]))
                            ->danger()
                            ->send();
                        $action->cancel();
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
