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
    protected static bool $shouldRegisterNavigation = false; // Ẩn menu 'Lịch sử sửa thực đơn' khỏi thanh điều hướng Sidebar

    protected static ?string $model = MenuAuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 8;

    public static function getNavigationLabel(): string
    {
        return __('menu.audit.label');
    }

    public static function getModelLabel(): string
    {
        return __('menu.audit.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('menu.audit.label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.groups.catering');
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
                    ->label(__('menu.audit.fields.occurred_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('menu_id')
                    ->label(__('menu.audit.fields.menu'))
                    ->formatStateUsing(fn ($state) => $state ? "#{$state}" : __('menu.audit.deleted_menu'))
                    ->badge()
                    ->color(fn ($state) => $state ? 'gray' : 'danger'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('menu.audit.fields.user'))
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->label(__('menu.audit.fields.action'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'deleted' ? __('menu.audit.actions.deleted') : __('menu.audit.actions.updated'))
                    ->color(fn (string $state): string => $state === 'deleted' ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('field')
                    ->label(__('menu.audit.fields.field'))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('old_value')
                    ->label(__('menu.audit.fields.old_value'))
                    ->limit(30)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('new_value')
                    ->label(__('menu.audit.fields.new_value'))
                    ->limit(30)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('reason')
                    ->label(__('menu.audit.fields.reason'))
                    ->limit(40)
                    ->placeholder('—')
                    ->searchable(),
            ])
            ->defaultSort('edited_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('menu.audit.fields.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('action')
                    ->label(__('menu.audit.fields.action'))
                    ->options(['updated' => __('menu.audit.actions.updated'), 'deleted' => __('menu.audit.actions.deleted')]),
                Tables\Filters\Filter::make('edited_between')
                    ->label(__('menu.audit.fields.date_range'))
                    ->form([
                        Forms\Components\DatePicker::make('from')->label(__('menu.audit.fields.from_date')),
                        Forms\Components\DatePicker::make('to')->label(__('menu.audit.fields.to_date')),
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
