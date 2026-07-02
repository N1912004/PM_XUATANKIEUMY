<?php

namespace App\Filament\Resources\FoodSafetyAuditResource\Pages;

use App\Filament\Resources\FoodSafetyAuditResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFoodSafetyAudits extends ListRecords
{
    protected static string $resource = FoodSafetyAuditResource::class;

    protected static string $view = 'filament.pages.food-safety-audit';

    public ?string $date = '2026-05-18';

    public ?int $selectedShift = 1;

    public string $activeStep = 'Bước 1';

    public string $canteen = 'Canteen Summit';

    public string $inspector = 'Nguyễn Văn An';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tạo dữ liệu'),
        ];
    }

    public function getStats(): array
    {
        return [
            'ingredients' => 22,
            'portions' => 4410,
            'dishes' => 24,
            'forms' => 5,
        ];
    }
}
