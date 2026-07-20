<?php

namespace App\Filament\Resources\MenuResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AuditLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'auditLogs';

    protected static ?string $icon = 'heroicon-o-clock';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('menu.audit.relation_title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('field')
            ->columns([
                Tables\Columns\TextColumn::make('edited_at')
                    ->label(__('menu.audit.fields.occurred_at'))
                    ->dateTime('H:i d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('menu.audit.fields.user'))
                    ->placeholder(__('menu.audit.system')),
                Tables\Columns\TextColumn::make('action')
                    ->label(__('menu.audit.fields.action'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'updated' => __('menu.audit.actions.updated'),
                        'deleted' => __('menu.audit.actions.deleted'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => $state === 'deleted' ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('field')
                    ->label(__('menu.audit.fields.field'))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('old_value')
                    ->label(__('menu.audit.fields.old_value'))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('new_value')
                    ->label(__('menu.audit.fields.new_value'))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('reason')
                    ->label(__('menu.audit.fields.reason'))
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
