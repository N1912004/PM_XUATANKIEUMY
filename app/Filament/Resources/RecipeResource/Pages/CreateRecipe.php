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

    private bool $savingDraft = false;

    public function getTitle(): string|Htmlable
    {
        return 'Thêm món ăn vào ngân hàng thực đơn';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Khai báo món ăn, mức giá suất ăn và cost nguyên liệu trên 1 phần';
    }

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->getCancelFormAction()
                ->label('Hủy')
                ->icon('heroicon-m-x-mark'),
            Action::make('saveDraft')
                ->label('Lưu nháp')
                ->icon('heroicon-m-archive-box')
                ->color('gray')
                ->action(function (): void {
                    $this->savingDraft = true;
                    $this->create();
                }),
            $this->getCreateFormAction()
                ->label('Tạo món ăn')
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($this->savingDraft) {
            $data['status'] = 'pending';
        }

        return $data;
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
