<?php

namespace App\Filament\Resources\IngredientTypeResource\Pages;

use App\Filament\Resources\IngredientTypeResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditIngredientType extends EditRecord
{
    protected static string $resource = IngredientTypeResource::class;

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
