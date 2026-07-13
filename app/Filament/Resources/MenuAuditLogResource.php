<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuAuditLogResource\Pages;
use App\Models\MenuAuditLog;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Màn tra cứu TỔNG lịch sử chỉnh sửa thực đơn (BA: lọc theo người sửa, khoảng thời gian) —
 * bổ sung cho RelationManager chỉ xem được log của từng thực đơn riêng lẻ.
 * Read-only: log chỉ được sinh từ hook audit của Menu, không cho tạo/sửa/xóa tay.
 */
class MenuAuditLogResource extends Resource
{
    protected static ?string $model = MenuAuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 8;

    public static function getNavigationLabel(): string
    {
        return __('Lịch sử sửa thực đơn');
    }

    public static function getModelLabel(): string
    {
        return __('Lịch sử sửa thực đơn');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Lịch sử sửa thực đơn');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('VẬN HÀNH BẾP');
    }

    /** Quyền xem gắn theo quyền xem thực đơn — không cần permission Shield riêng */
    public static function canViewAny(): bool
    {
        return MenuResource::canViewAny();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('edited_at')
                    ->label('Thời điểm')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('menu_id')
                    ->label('Thực đơn')
                    ->formatStateUsing(fn ($state) => $state ? "#{$state}" : 'Đã xóa')
                    ->badge()
                    ->color(fn ($state) => $state ? 'gray' : 'danger'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Người sửa')
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Hành động')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'deleted' ? 'Xóa' : 'Sửa')
                    ->color(fn (string $state): string => $state === 'deleted' ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('field')
                    ->label('Trường')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('old_value')
                    ->label('Giá trị cũ')
                    ->limit(30)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('new_value')
                    ->label('Giá trị mới')
                    ->limit(30)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Lý do sửa')
                    ->limit(40)
                    ->placeholder('—')
                    ->searchable(),
            ])
            ->defaultSort('edited_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Người sửa')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('action')
                    ->label('Hành động')
                    ->options(['updated' => 'Sửa', 'deleted' => 'Xóa']),
                Tables\Filters\Filter::make('edited_between')
                    ->label('Khoảng thời gian')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Từ ngày'),
                        Forms\Components\DatePicker::make('to')->label('Đến ngày'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn ($q, $from) => $q->where('edited_at', '>=', $from))
                        ->when($data['to'] ?? null, fn ($q, $to) => $q->where('edited_at', '<', Carbon::parse($to)->addDay()->toDateString()))),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuAuditLogs::route('/'),
        ];
    }
}
