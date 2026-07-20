<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use App\Models\Menu;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                // Thực đơn quá khứ đã chốt bị khóa cứng — chặn cả đường xóa chuẩn của Filament
                ->before(function (Actions\DeleteAction $action, Menu $record): void {
                    if ($record->isPastLocked()) {
                        Notification::make()
                            ->title(__('menu.errors.past_locked_delete'))
                            ->danger()
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }

    /**
     * Trang edit chuẩn của Filament phải tuân cùng bộ guard vòng đời như 2 trang lập
     * thực đơn custom (khóa quá khứ / không hạ cấp / bắt lý do khi sửa đã chốt) —
     * nếu không đây là cửa hậu hạ cấp menu đã chốt.
     */
    protected function beforeSave(): void
    {
        /** @var Menu $menu */
        $menu = $this->record;
        $newStatus = (string) ($this->data['status'] ?? $menu->status);
        $reason = trim((string) ($this->data['edit_reason'] ?? ''));

        $blocked = $menu->editBlockReason($newStatus, $reason);

        if ($blocked !== null) {
            Notification::make()
                ->title(match ($blocked) {
                    'past' => 'Thực đơn quá khứ đã chốt — bị khóa cứng, không thể sửa',
                    'downgrade' => 'Không được hạ cấp trạng thái thực đơn (chỉ đi tiến Nháp → Gửi → Xác nhận → Chốt)',
                    default => 'Sửa thực đơn ĐÃ CHỐT bắt buộc nhập "Lý do sửa"',
                })
                ->danger()
                ->send();

            $this->halt();
        }

        $menu->auditReason = $reason !== '' ? $reason : null;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
