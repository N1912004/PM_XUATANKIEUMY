<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\BelongsToKitchen;
use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    use BelongsToKitchen;

    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 7;

    public static function getNavigationLabel(): string
    {
        return __('menu.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('menu.model.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('menu.model.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.groups.kitchen_operations');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::kitchenSelect(),
                Forms\Components\DatePicker::make('date')
                    ->label(__('menu.fields.date'))
                    ->required(),
                Forms\Components\Select::make('shift_id')
                    ->label(__('menu.fields.shift'))
                    ->relationship('shift', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('recipe_id')
                    ->label(__('menu.fields.recipe'))
                    ->relationship('recipe', 'name', fn ($query) => $query->where('status', 'active'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('estimated_portions')
                    ->label(__('menu.fields.estimated_portions'))
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(1),
                Forms\Components\Select::make('status')
                    ->label(__('menu.fields.status'))
                    ->required()
                    ->options([
                        'draft' => __('menu.status.draft_detailed'),
                        'sent' => __('menu.status.sent_detailed'),
                        'locked' => __('menu.status.locked_detailed'),
                    ])
                    ->default('draft'),
                Forms\Components\TextInput::make('edit_reason')
                    ->label(__('menu.fields.audit_reason'))
                    ->placeholder(__('menu.placeholders.audit_reason'))
                    ->maxLength(255)
                    ->dehydrated(false)
                    ->required(fn (?Menu $record): bool => $record?->status === 'locked')
                    ->visible(fn (string $operation, ?Menu $record): bool => $operation === 'edit'
                        && $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                static::kitchenColumn(),
                Tables\Columns\TextColumn::make('date')
                    ->label(__('menu.fields.date'))
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift.name')
                    ->label(__('menu.fields.shift'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('recipe.name')
                    ->label(__('menu.fields.recipe_name'))
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('recipe.type')
                    ->label(__('menu.fields.recipe_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Món mặn', 'Món 1' => 'danger',
                        'Món xào', 'Rau xào/Luộc' => 'success',
                        'Món canh' => 'info',
                        'Món chay' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('estimated_portions')
                    ->label(__('menu.fields.estimated_portions'))
                    ->numeric()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('menu.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'sent' => 'info',
                        'locked' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("menu.status.{$state}")),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kitchen_id')
                    ->label(__('menu.fields.kitchen'))
                    ->relationship('kitchen', 'name'),
                Tables\Filters\SelectFilter::make('shift_id')
                    ->label(__('menu.fields.shift'))
                    ->relationship('shift', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('menu.fields.status'))
                    ->options(collect(array_keys(Menu::STATUS_LABELS))->mapWithKeys(fn (string $status): array => [$status => __("menu.status.{$status}")])->all()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MenuResource\RelationManagers\AuditLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
