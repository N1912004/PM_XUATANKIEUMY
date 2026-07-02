<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientResource\Pages;
use App\Models\Ingredient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationLabel = 'List nguyên liệu';

    protected static ?string $modelLabel = 'Nguyên liệu';

    protected static ?string $pluralModelLabel = 'List nguyên liệu';

    protected static ?string $navigationGroup = 'XUẤT ĂN';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Section::make('Thông tin nguyên liệu')
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Tên nguyên liệu')
                                    ->required()
                                    ->placeholder('Nhập tên nguyên liệu'),
                                Forms\Components\TextInput::make('code')
                                    ->label('Mã nguyên liệu')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Nhập mã nguyên liệu'),
                                Forms\Components\Select::make('supplier_id')
                                    ->label('Nhà cung cấp')
                                    ->relationship('supplier', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Chọn nhà cung cấp'),
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
                                        'Động vật' => 'Động vật',
                                        'Thực vật' => 'Thực vật',
                                        'Thực phẩm khô' => 'Thực phẩm khô',
                                        'Gia vị' => 'Gia vị',
                                    ])
                                    ->placeholder('Chọn loại nguyên liệu')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('reference_price')
                                    ->label('Đơn giá tham chiếu (đ)')
                                    ->required()
                                    ->numeric()
                                    ->default(0.00),
                            ])
                            ->columns(2),
                        Forms\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\Section::make('Thiết lập nhanh')
                                    ->schema([
                                        Forms\Components\Toggle::make('status')
                                            ->label('Trạng thái')
                                            ->default(true)
                                            ->onColor('success')
                                            ->offColor('danger'),
                                    ]),
                            ]),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('STT')
                    ->state(static function (Tables\Contracts\HasTable $livewire, \stdClass $rowLoop): string {
                        return (string) ($rowLoop->iteration);
                    }),
                Tables\Columns\TextColumn::make('code')
                    ->label('MÃ NGUYÊN LIỆU')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->label('TÊN NGUYÊN LIỆU')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('TÊN NCC')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('ĐƠN VỊ'),
                Tables\Columns\TextColumn::make('type')
                    ->label('LOẠI NL')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Động vật' => 'danger',
                        'Thực vật' => 'success',
                        'Thực phẩm khô' => 'warning',
                        'Gia vị' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('TRẠNG THÁI')
                    ->badge()
                    ->state(fn ($record) => $record->status ? 'Đang hoạt động' : 'Ngừng hoạt động')
                    ->color(fn ($state) => $state === 'Đang hoạt động' ? 'success' : 'danger'),
            ])
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
            'index' => Pages\ListIngredients::route('/'),
            'create' => Pages\CreateIngredient::route('/create'),
            'edit' => Pages\EditIngredient::route('/{record}/edit'),
        ];
    }
}
