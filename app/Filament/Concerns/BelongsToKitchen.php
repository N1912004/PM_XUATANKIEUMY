<?php

namespace App\Filament\Concerns;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

/**
 * Phân cấp dữ liệu theo Bếp: tự động lọc bản ghi theo bếp của người dùng đang đăng nhập,
 * và cung cấp sẵn field/cột "Bếp" cho Resource.
 *
 * Fail-closed: người dùng thường CHƯA được gắn bếp thì không thấy bản ghi nào
 * (trước đây fail-open — user chưa gán bếp xem được dữ liệu của TẤT CẢ các bếp).
 */
trait BelongsToKitchen
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Filament::auth()->user();

        if (! $user) {
            return $query;
        }

        if ($user->hasRole(['super_admin', 'Quản trị viên'])) {
            return $query;
        }

        $kitchenId = $user->currentKitchenId();

        if ($kitchenId) {
            $query->where($query->getModel()->getTable().'.kitchen_id', $kitchenId);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function kitchenSelect(): Select
    {
        return Select::make('kitchen_id')
            ->label(__('common.kitchen'))
            ->relationship('kitchen', 'name')
            ->searchable()
            ->preload()
            ->default(fn (): ?int => Filament::auth()->user()?->currentKitchenId());
    }

    public static function kitchenColumn(): TextColumn
    {
        return TextColumn::make('kitchen.name')
            ->label(__('common.kitchen_column'))
            ->placeholder(__('common.shared'))
            ->sortable()
            ->toggleable();
    }
}
