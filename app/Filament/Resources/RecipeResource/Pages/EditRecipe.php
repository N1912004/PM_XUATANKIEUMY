<?php

namespace App\Filament\Resources\RecipeResource\Pages;

use App\Filament\Resources\RecipeResource;
use App\Models\RecipeCostLog;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRecipe extends EditRecord
{
    protected static string $resource = RecipeResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected ?float $costOverrideBeforeSave = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        // Chụp giá override đang lưu trong DB trước khi ghi đè để so cũ → mới
        $original = $this->record->getOriginal('cost_override');
        $this->costOverrideBeforeSave = $original !== null ? (float) $original : null;
    }

    protected function afterSave(): void
    {
        $new = $this->record->cost_override !== null ? (float) $this->record->cost_override : null;
        $old = $this->costOverrideBeforeSave;

        if ($new === $old) {
            return;
        }

        // Log giá cũ → mới kèm lý do (bắt buộc trên form khi đặt/đổi override)
        RecipeCostLog::create([
            'recipe_id' => $this->record->id,
            'old_value' => $old,
            'new_value' => $new,
            'reason' => trim((string) ($this->data['cost_override_reason'] ?? '')) ?: 'Gỡ cost điều chỉnh, quay về cost tự tính',
            'user_id' => auth()->id(),
        ]);

        // Đổi cost ảnh hưởng báo giá — đưa món đang hoạt động về Chờ rà soát (quy định BA)
        if ($this->record->status === 'active') {
            $this->record->updateQuietly(['status' => 'pending']);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
