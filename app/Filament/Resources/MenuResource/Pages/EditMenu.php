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

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('menu.breadcrumb.home'),
            MenuResource::getUrl('index') => __('menu.breadcrumb.list'),
            __('menu.breadcrumb.edit'),
        ];
    }

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
                    'past' => __('menu.errors.past_locked_edit'),
                    'downgrade' => __('menu.errors.status_downgrade'),
                    'need_reason' => __('menu.errors.audit_reason_required'),
                    default => __('menu.errors.invalid_status'),
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
