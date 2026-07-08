<?php

namespace App\Filament\Resources\RecipeResource\Pages;

use App\Filament\Resources\RecipeResource;
use App\Filament\Widgets\RecipeAlertWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Js;

class ListRecipes extends ListRecords
{
    protected static string $resource = RecipeResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Ngân hàng thực đơn';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Quản lý món ăn theo từng mức giá và cost nguyên liệu trên 1 phần';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Xuất dữ liệu')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->button()
                ->alpineClickHandler(
                    'window.downloadRecipeExcelWithPicker('.
                    Js::from(route('recipes.export-file')).', '.
                    Js::from('ngan-hang-thuc-don-'.now()->format('Ymd-His').'.xlsx').
                    ')'
                ),
            Actions\CreateAction::make()
                ->label('Thêm món ăn')
                ->icon('heroicon-m-plus')
                ->button(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RecipeAlertWidget::class,
        ];
    }
}
