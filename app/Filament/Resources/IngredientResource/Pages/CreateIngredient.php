<?php

namespace App\Filament\Resources\IngredientResource\Pages;

use App\Filament\Resources\IngredientResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIngredient extends CreateRecord
{
    protected static string $resource = IngredientResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return __('Thêm nguyên liệu');
    }

    public function getSubheading(): ?string
    {
        return __('Chỉ khai báo các thông tin cần thiết: tên, mã, đơn vị và loại nguyên liệu');
    }
}
