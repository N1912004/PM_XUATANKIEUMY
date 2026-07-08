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

    public function getTitle(): string|Htmlable
    {
        return 'Chi tiết món ăn';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Theo dõi cost nguyên liệu và thông tin định lượng trên 1 phần';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Quay lại')
                ->icon('heroicon-m-arrow-left')
                ->color('gray')
                ->outlined()
                ->url(fn (): string => $this->getResource()::getUrl('index')),
            Actions\EditAction::make()
                ->label('Chỉnh sửa')
                ->icon('heroicon-m-pencil-square')
                ->color('gray')
                ->outlined(),
            Actions\Action::make('approve')
                ->label('Duyệt áp dụng')
                ->icon('heroicon-m-check')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->update(['status' => 'active']);
                    Notification::make()
                        ->title('Đã duyệt món ăn thành công!')
                        ->success()
                        ->send();
                })
                ->visible(fn (): bool => $this->record->status === 'pending'),
        ];
    }
}
