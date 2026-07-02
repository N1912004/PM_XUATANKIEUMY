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
                                Forms\Components\Select::make('type')
                                    ->label('Phân loại')
                                    ->options([
                                        'Bếp sản xuất' => 'Bếp sản xuất',
                                        'Bếp ăn' => 'Bếp ăn',
                                        'Kho trung chuyển' => 'Kho trung chuyển',
                                    ])
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
                                    ->preload()
                                    ->required(),
                                Forms\Components\Select::make('status')
                                    ->label('Trạng thái')
                                    ->options([
                                        'Đang hoạt động' => 'Đang hoạt động',
                                        'Tạm dừng' => 'Tạm dừng',
                                    ])
                                    ->default('Đang hoạt động')
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
                Tables\Columns\TextColumn::make('type')
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
                    ->color(fn (string $state): string => match ($state) {
                        'Đang hoạt động' => 'success',
                        'Tạm dừng' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('area_id')
                    ->label('Khu vực')
                    ->relationship('area', 'name'),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Phân loại')
                    ->options([
                        'Bếp sản xuất' => 'Bếp sản xuất',
                        'Bếp ăn' => 'Bếp ăn',
                        'Kho trung chuyển' => 'Kho trung chuyển',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Đang hoạt động' => 'Đang hoạt động',
                        'Tạm dừng' => 'Tạm dừng',
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
