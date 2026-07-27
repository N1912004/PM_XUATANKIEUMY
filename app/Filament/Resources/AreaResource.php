<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AreaResource\Pages;
use App\Models\Area;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;

    protected static ?string $navigationIcon = 'fa-location-dot';

    protected static ?int $navigationSort = 3; // Đứng sau 'Nhà ăn / bếp' (2) trong nhóm HỆ THỐNG

    public static function getNavigationLabel(): string
    {
        return __('catalog.area.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('catalog.area.label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.groups.system');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('catalog.area_section'))
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('catalog.area.fields.name'))
                                    ->required()
                                    ->placeholder(__('catalog.area.placeholders.name'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('code')
                                    ->label(__('catalog.area.fields.code'))
                                    ->required()
                                    ->placeholder(__('catalog.area.placeholders.code'))
                                    ->maxLength(255),
                                Forms\Components\Select::make('manager_id')
                                    ->label(__('catalog.area.fields.manager'))
                                    ->relationship('manager', 'name')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Toggle::make('status')
                                    ->label(__('catalog.common.active'))
                                    ->default(true)
                                    ->required(),
                            ]),
                        Forms\Components\Textarea::make('notes')
                            ->label(__('catalog.common.notes'))
                            ->placeholder(__('catalog.area.placeholders.notes'))
                            ->rows(3)
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('code')
                    ->label(__('catalog.area.table.code'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('catalog.area.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('manager.name')
                    ->label(__('catalog.area.table.manager'))
                    ->sortable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('kitchens_count')
                    ->label(__('catalog.area.table.kitchens_count'))
                    ->counts('kitchens')
                    ->badge()
                    ->color('primary')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->label(__('catalog.common.status'))
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status')
                    ->label(__('catalog.common.active_status')),
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
            'index' => Pages\ListAreas::route('/'),
            'create' => Pages\CreateArea::route('/create'),
            'edit' => Pages\EditArea::route('/{record}/edit'),
        ];
    }
}
