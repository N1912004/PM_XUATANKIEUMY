<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeResource\Pages;
use App\Models\Ingredient;
use App\Models\Recipe;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Ngân Hàng Thực đơn';

    protected static ?string $navigationGroup = 'VẬN HÀNH BẾP';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Món ăn';

    protected static ?string $pluralModelLabel = 'Ngân Hàng Thực đơn';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin món ăn')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Tên món ăn')
                                    ->required()
                                    ->placeholder('Nhập tên món ăn'),
                                Forms\Components\TextInput::make('code')
                                    ->label('Mã món')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('MON0001'),
                                Forms\Components\Select::make('type')
                                    ->label('Nhóm món')
                                    ->required()
                                    ->options([
                                        'Món mặn' => 'Món mặn',
                                        'Món 1' => 'Món 1',
                                        'Món 2' => 'Món 2',
                                        'Món 3' => 'Món 3',
                                        'Món xào' => 'Món xào',
                                        'Rau xào/Luộc' => 'Rau xào/Luộc',
                                        'Món canh' => 'Món canh',
                                        'Món chay' => 'Món chay',
                                        'Tráng miệng' => 'Tráng miệng',
                                        'Món khác' => 'Món khác',
                                    ])
                                    ->placeholder('Chọn nhóm món'),
                                Forms\Components\TextInput::make('price_level')
                                    ->label('Mức giá suất ăn (đ)')
                                    ->required()
                                    ->numeric()
                                    ->default(0.00)
                                    ->placeholder('Mức giá'),
                                Forms\Components\TextInput::make('actual_price')
                                    ->label('Đơn giá suất ăn (đ)')
                                    ->required()
                                    ->numeric()
                                    ->default(0.00)
                                    ->placeholder('Đơn giá'),
                                Forms\Components\Select::make('price_option')
                                    ->label('Tùy chọn đơn giá')
                                    ->options([
                                        'Không' => 'Không',
                                        'Có' => 'Có',
                                    ])
                                    ->default('Không'),
                                Forms\Components\Select::make('status')
                                    ->label('Trạng thái')
                                    ->required()
                                    ->options([
                                        'active' => 'Đang áp dụng',
                                        'pending' => 'Chờ rà soát',
                                        'inactive' => 'Ngừng áp dụng',
                                    ])
                                    ->default('active'),
                                Forms\Components\TextInput::make('description')
                                    ->label('Mô tả')
                                    ->placeholder('Mô tả ngắn về món ăn')
                                    ->columnSpan(2),
                            ]),
                    ]),

                Forms\Components\Section::make('Định lượng nguyên liệu (Recipe)')
                    ->schema([
                        Forms\Components\Repeater::make('ingredients')
                            ->relationship('ingredients')
                            ->schema([
                                Forms\Components\Select::make('ingredient_id')
                                    ->label('Nguyên liệu')
                                    ->options(Ingredient::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Chọn nguyên liệu'),
                                Forms\Components\TextInput::make('quantity_per_portion')
                                    ->label('Định lượng (kg/suất) / Số lượng')
                                    ->numeric()
                                    ->required()
                                    ->default(0.1)
                                    ->placeholder('0.1'),
                            ])
                            ->columns(2)
                            ->label('Nguyên liệu thành phần')
                            ->createItemButtonLabel('Thêm nguyên liệu'),
                    ])
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('code')
                    ->label('MÃ MÓN')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->label('TÊN MÓN ĂN')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label('NHÓM MÓN')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Món mặn', 'Món 1' => 'danger',
                        'Món xào', 'Rau xào/Luộc' => 'success',
                        'Món canh' => 'info',
                        'Món chay' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('price_level')
                    ->label('MỨC GIÁ SUẤT ĂN')
                    ->money('VND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('actual_price')
                    ->label('ĐƠN GIÁ SUẤT ĂN')
                    ->money('VND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ingredients_count')
                    ->label('SỐ NGUYÊN LIỆU')
                    ->counts('ingredients')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('TRẠNG THÁI')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Đang áp dụng',
                        'pending' => 'Chờ rà soát',
                        'inactive' => 'Ngừng áp dụng',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Nhóm món')
                    ->options([
                        'Món mặn' => 'Món mặn',
                        'Món 1' => 'Món 1',
                        'Món 2' => 'Món 2',
                        'Món 3' => 'Món 3',
                        'Món xào' => 'Món xào',
                        'Rau xào/Luộc' => 'Rau xào/Luộc',
                        'Món canh' => 'Món canh',
                        'Món chay' => 'Món chay',
                        'Tráng miệng' => 'Tráng miệng',
                        'Món khác' => 'Món khác',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'active' => 'Đang áp dụng',
                        'pending' => 'Chờ rà soát',
                        'inactive' => 'Ngừng áp dụng',
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('ingredients');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecipes::route('/'),
            'create' => Pages\CreateRecipe::route('/create'),
            'edit' => Pages\EditRecipe::route('/{record}/edit'),
        ];
    }
}
