<?php

namespace App\Filament\Resources\MenuResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'auditLogs';

    protected static ?string $title = 'Lịch sử chỉnh sửa';

    protected static ?string $icon = 'heroicon-o-clock';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('field')
            ->columns([
                Tables\Columns\TextColumn::make('edited_at')
                    ->label('Thời điểm')
                    ->dateTime('H:i d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Người sửa')
                    ->placeholder('Hệ thống'),
                Tables\Columns\TextColumn::make('action')
                    ->label('Hành động')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'updated' => 'Chỉnh sửa',
                        'deleted' => 'Xóa',
                        default => $state,
                    })
                    ->color(fn (string $state): string => $state === 'deleted' ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('field')
                    ->label('Trường')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('old_value')
                    ->label('Giá trị cũ')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('new_value')
                    ->label('Giá trị mới')
                    ->placeholder('—'),
            ])
            ->defaultSort('edited_at', 'desc')
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
