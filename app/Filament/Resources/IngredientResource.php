<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientResource\Pages;
use App\Models\Ingredient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('Danh sách nguyên liệu');
    }

    public static function getModelLabel(): string
    {
        return __('Nguyên liệu');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Danh sách nguyên liệu');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('CUNG ỨNG & KHO');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin nguyên liệu')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên nguyên liệu')
                            ->required()
                            ->placeholder('Nhập tên nguyên liệu'),
                        Forms\Components\TextInput::make('code')
                            ->label('Mã nguyên liệu')
                            ->unique(ignoreRecord: true)
                            ->placeholder('Nhập mã nguyên liệu'),
                        Forms\Components\Select::make('unit')
                            ->label('Đơn vị')
                            ->required()
                            ->options([
                                'Kg' => 'Kg',
                                'Quả' => 'Quả',
                                'Gói' => 'Gói',
                                'Chai' => 'Chai',
                                'Thùng' => 'Thùng',
                                'Lít' => 'Lít',
                            ])
                            ->placeholder('Chọn đơn vị'),
                        Forms\Components\Select::make('type')
                            ->label('Loại nguyên liệu')
                            ->required()
                            ->options([
                                'Động vật' => 'Động Vật',
                                'Thực vật' => 'Thực Vật',
                                'Thực phẩm khô' => 'Thực Phẩm Khô',
                                'Gia vị' => 'Gia vị',
                            ])
                            ->placeholder('Chọn loại nguyên liệu'),
                        Forms\Components\Toggle::make('status')
                            ->label('Trạng thái')
                            ->default(true)
                            ->onColor('primary')
                            ->offColor('danger')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\ViewEntry::make('header_card')
                            ->view('filament.resources.ingredients.view-header')
                            ->columnSpanFull(),
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('code')
                                    ->label('Mã nguyên liệu')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Tên nguyên liệu')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('unit')
                                    ->label('Đơn vị')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('type')
                                    ->label('Loại nguyên liệu')
                                    ->weight('bold'),
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
                    ->state(static function (Tables\Contracts\HasTable $livewire, \stdClass $rowLoop): string {
                        return (string) ($rowLoop->iteration);
                    })
                    ->alignCenter()
                    ->width('56px'),
                Tables\Columns\TextColumn::make('code')
                    ->label('MÃ NGUYÊN LIỆU')
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->weight('semibold')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('name')
                    ->label('TÊN NGUYÊN LIỆU')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('TÊN NCC')
                    ->sortable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('unit')
                    ->label('ĐƠN VỊ')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('type')
                    ->label('LOẠI NL')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('status')
                    ->label('TRẠNG THÁI')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Đang hoạt động' : 'Ngừng hoạt động')
                    ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
            ])
            ->defaultSort('id', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Nhà cung cấp')
                    ->relationship('supplier', 'name'),
                Tables\Filters\SelectFilter::make('unit')
                    ->label('Đơn vị')
                    ->options([
                        'Kg' => 'Kg',
                        'Quả' => 'Quả',
                        'Gói' => 'Gói',
                        'Chai' => 'Chai',
                        'Thùng' => 'Thùng',
                        'Lít' => 'Lít',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Loại NL')
                    ->options([
                        'Động vật' => 'Động vật',
                        'Thực vật' => 'Thực vật',
                        'Thực phẩm khô' => 'Thực phẩm khô',
                        'Gia vị' => 'Gia vị',
                    ]),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton(),
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIngredients::route('/'),
            'create' => Pages\CreateIngredient::route('/create'),
            'view' => Pages\ViewIngredient::route('/{record}'),
            'edit' => Pages\EditIngredient::route('/{record}/edit'),
        ];
    }
}
