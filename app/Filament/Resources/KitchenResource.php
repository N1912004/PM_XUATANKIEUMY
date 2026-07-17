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

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('Nhà ăn / bếp');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Nhà ăn / bếp');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('KHU VỰC & NHÀ ĂN');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin nhà ăn / bếp')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Tên nhà ăn / bếp')
                                    ->required()
                                    ->placeholder('VD: Bếp chính Nhơn Trạch')
                                    ->maxLength(255),
                                Forms\Components\Select::make('area_id')
                                    ->label('Thuộc khu vực')
                                    ->relationship('area', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\Select::make('kitchen_type_id')
                                    ->label('Phân loại')
                                    ->relationship('kitchenType', 'name', fn ($query) => $query->where('active', true)->orderBy('sort')->orderBy('name'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\TextInput::make('capacity')
                                    ->label('Công suất phục vụ (suất/ngày)')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                Forms\Components\Select::make('manager_id')
                                    ->label('Quản lý nhà bếp')
                                    ->relationship('manager', 'name')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('status')
                                    ->label('Trạng thái')
                                    ->options([
                                        'active' => 'Đang hoạt động',
                                        'paused' => 'Tạm dừng',
                                        'maintenance' => 'Bảo trì',
                                    ])
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
                    ->label('STT')
                    ->state(static function (HasTable $livewire, \stdClass $rowLoop): string {
                        return (string) ($rowLoop->iteration);
                    }),
                Tables\Columns\TextColumn::make('name')
                    ->label('TÊN NHÀ BẾP / NHÀ ĂN')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('area.name')
                    ->label('KHU VỰC')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('kitchenType.name')
                    ->label('PHÂN LOẠI')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('CÔNG SUẤT (SUẤT/NGÀY)')
                    ->numeric()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('manager.name')
                    ->label('QUẢN LÝ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('TRẠNG THÁI')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Đang hoạt động',
                        'paused' => 'Tạm dừng',
                        'maintenance' => 'Bảo trì',
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
                    ->label('Khu vực')
                    ->relationship('area', 'name'),
                Tables\Filters\SelectFilter::make('kitchen_type_id')
                    ->label('Phân loại')
                    ->relationship('kitchenType', 'name', fn ($query) => $query->where('active', true)->orderBy('sort')->orderBy('name')),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'active' => 'Đang hoạt động',
                        'paused' => 'Tạm dừng',
                        'maintenance' => 'Bảo trì',
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
