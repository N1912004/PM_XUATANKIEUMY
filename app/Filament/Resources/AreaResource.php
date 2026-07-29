<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AreaResource\Pages;
use App\Models\Area;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

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
        return __('catalog.area.plural_label');
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
                                    ->unique(Area::class, 'name', ignoreRecord: true)
                                    ->placeholder(__('catalog.area.placeholders.name'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('code')
                                    ->label(__('catalog.area.fields.code'))
                                    ->required()
                                    ->unique(Area::class, 'code', ignoreRecord: true)
                                    ->placeholder(__('catalog.area.placeholders.code'))
                                    ->maxLength(255),
                                Forms\Components\Select::make('manager_id')
                                    ->label(__('catalog.area.fields.manager'))
                                    ->relationship('manager', 'name')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\TextInput::make('notes')
                                    ->label(__('catalog.common.notes'))
                                    ->placeholder(__('catalog.area.placeholders.notes'))
                                    ->maxLength(500),
                            ]),
                        Forms\Components\Toggle::make('status')
                            ->label(__('catalog.common.active_status'))
                            ->default(true)
                            ->required()
                            ->inline()
                            ->columnSpanFull(),
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
