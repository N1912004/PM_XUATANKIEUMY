<?php

namespace App\Filament\Concerns;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

/**
 * Phân cấp dữ liệu theo Bếp: tự động lọc bản ghi theo bếp của người dùng đang đăng nhập,
 * và cung cấp sẵn field/cột "Bếp" cho Resource. Nếu người dùng chưa gắn bếp (null) thì xem toàn bộ.
 */
trait BelongsToKitchen
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $kitchenId = Filament::auth()->user()?->currentKitchenId();

        if ($kitchenId) {
            $query->where($query->getModel()->getTable().'.kitchen_id', $kitchenId);
        }

        return $query;
    }

    public static function kitchenSelect(): Select
    {
        return Select::make('kitchen_id')
            ->label('Bếp ăn')
            ->relationship('kitchen', 'name')
            ->searchable()
            ->preload()
            ->default(fn (): ?int => Filament::auth()->user()?->currentKitchenId());
    }

    public static function kitchenColumn(): TextColumn
    {
        return TextColumn::make('kitchen.name')
            ->label('BẾP')
            ->placeholder('— Chung —')
            ->sortable()
            ->toggleable();
    }
}
