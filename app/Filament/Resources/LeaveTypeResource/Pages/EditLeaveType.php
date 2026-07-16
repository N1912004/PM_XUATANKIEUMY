<?php

namespace App\Filament\Resources\LeaveTypeResource\Pages;

use App\Filament\Resources\LeaveTypeResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLeaveType extends EditRecord
{
    protected static string $resource = LeaveTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action): void {
                    $record = $this->getRecord();
                    if ($record->leaveOvertimes()->exists()) {
                        Notification::make()
                            ->title('Không thể xóa loại này vì đang có '.$record->leaveOvertimes()->count().' đơn từ sử dụng.')
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
