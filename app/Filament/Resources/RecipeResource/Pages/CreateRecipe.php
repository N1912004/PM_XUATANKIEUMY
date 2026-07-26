<?php

namespace App\Filament\Resources\RecipeResource\Pages;

use App\Filament\Resources\RecipeResource;
use App\Models\RecipeCostLog;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateRecipe extends CreateRecord
{
    protected static string $resource = RecipeResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('recipe.breadcrumb.home'),
            RecipeResource::getUrl('index') => __('recipe.breadcrumb.list'),
            __('recipe.breadcrumb.create'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return __('recipe.pages.create.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('recipe.pages.create.subtitle');
    }

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->getCancelFormAction()
                ->label(__('recipe.actions.cancel'))
                ->icon('heroicon-m-x-mark'),
            $this->getCreateFormAction()
                ->label(__('recipe.actions.create'))
                ->icon('heroicon-m-plus')
                ->formId('form'),
        ];
    }

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [];
    }

    protected function afterCreate(): void
    {
        // Món mới tạo đã đặt cost override → ghi log ngay từ đầu (old = null: chưa từng có)
        if ($this->record->cost_override !== null) {
            RecipeCostLog::create([
                'recipe_id' => $this->record->id,
                'old_value' => null,
                'new_value' => $this->record->cost_override,
                'reason' => trim((string) ($this->data['cost_override_reason'] ?? '')) ?: 'Đặt cost điều chỉnh khi tạo món',
                'user_id' => auth()->id(),
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
