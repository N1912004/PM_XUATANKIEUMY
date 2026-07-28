<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KitchenResource\Pages;
use App\Models\Kitchen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class KitchenResource extends Resource
{
    protected static ?string $model = Kitchen::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'fa-fire-burner';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('catalog.kitchen.label');
    }

    public static function getModelLabel(): string
    {
        return __('catalog.kitchen.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('catalog.kitchen.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.groups.system');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('catalog.kitchen_section'))
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('catalog.kitchen.fields.name'))
                                    ->required()
                                    ->unique(Kitchen::class, 'name', ignoreRecord: true)
                                    ->placeholder(__('catalog.kitchen.placeholders.name'))
                                    ->maxLength(255),
                                Forms\Components\Select::make('area_id')
                                    ->label(__('catalog.kitchen.fields.area'))
                                    ->relationship('area', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\Select::make('kitchen_type_id')
                                    ->label(__('catalog.kitchen.fields.type'))
                                    ->relationship('kitchenType', 'name', fn ($query) => $query->where('active', true)->orderBy('sort')->orderBy('name'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\TextInput::make('capacity')
                                    ->label(__('catalog.kitchen.fields.capacity'))
                                    ->numeric()
                                    ->default(0)
                                    ->required()
                                    ->dehydrateStateUsing(fn ($state) => abs((int) $state))
                                    ->extraInputAttributes([
                                        'x-on:keydown' => "if(['-', '+', 'e', 'E'].includes(\$event.key)) \$event.preventDefault()",
                                        'x-on:input' => "let v = \$event.target.value.replace(/[^0-9]/g, '').replace(/^0+(?=\\d)/, ''); \$event.target.value = v",
                                    ]),
                                Forms\Components\Select::make('manager_id')
                                    ->label(__('catalog.kitchen.fields.manager'))
                                    ->relationship('manager', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                Forms\Components\Select::make('status')
                                    ->label(__('catalog.common.status'))
                                    ->options([
                                        'active' => __('catalog.kitchen_status.active'),
                                        'paused' => __('catalog.kitchen_status.paused'),
                                        'maintenance' => __('catalog.kitchen_status.maintenance'),
                                    ])
                                    ->searchable()
                                    ->native(false)
                                    ->default('active')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label(__('catalog.common.index'))
                    ->state(static function (HasTable $livewire, \stdClass $rowLoop): string {
                        return (string) ($rowLoop->iteration);
                    }),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('catalog.kitchen.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('area.name')
                    ->label(__('catalog.kitchen.table.area'))
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('kitchenType.name')
                    ->label(__('catalog.kitchen.table.type'))
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->label(__('catalog.kitchen.table.capacity'))
                    ->numeric()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('manager.name')
                    ->label(__('catalog.kitchen.table.manager'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('catalog.common.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => __('catalog.kitchen_status.active'),
                        'paused' => __('catalog.kitchen_status.paused'),
                        'maintenance' => __('catalog.kitchen_status.maintenance'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'paused' => 'danger',
                        'maintenance' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('area_id')
                    ->label(__('catalog.kitchen.fields.area'))
                    ->relationship('area', 'name'),
                Tables\Filters\SelectFilter::make('kitchen_type_id')
                    ->label(__('catalog.kitchen.fields.type'))
                    ->relationship('kitchenType', 'name', fn ($query) => $query->where('active', true)->orderBy('sort')->orderBy('name')),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('catalog.common.status'))
                    ->options([
                        'active' => __('catalog.kitchen_status.active'),
                        'paused' => __('catalog.kitchen_status.paused'),
                        'maintenance' => __('catalog.kitchen_status.maintenance'),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKitchens::route('/'),
            'create' => Pages\CreateKitchen::route('/create'),
            'edit' => Pages\EditKitchen::route('/{record}/edit'),
        ];
    }
}
