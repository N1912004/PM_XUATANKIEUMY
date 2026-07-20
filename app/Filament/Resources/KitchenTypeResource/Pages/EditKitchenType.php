<?php

namespace App\Filament\Resources\KitchenTypeResource\Pages;

use App\Filament\Resources\KitchenTypeResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditKitchenType extends EditRecord
{
    protected static string $resource = KitchenTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action): void {
                    $record = $this->getRecord();
                    if ($record->kitchens()->exists()) {
                        Notification::make()
                            ->title(__('catalog.kitchen_type.errors.in_use', ['count' => $record->kitchens()->count()]))
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
