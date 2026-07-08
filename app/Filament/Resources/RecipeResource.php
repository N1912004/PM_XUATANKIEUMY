<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeResource\Pages;
use App\Models\Ingredient;
use App\Models\Recipe;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('Ngân Hàng Thực đơn');
    }

    public static function getModelLabel(): string
    {
        return __('Món ăn');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Ngân Hàng Thực đơn');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('VẬN HÀNH BẾP');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin món ăn')
                    ->columns(3)
                    ->extraAttributes(['class' => 'recipe-form-section'])
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên món ăn')
                            ->required()
                            ->placeholder('Nhập tên món ăn'),
                        Forms\Components\TextInput::make('code')
                            ->label('Mã món')
                            ->required()
                            ->default(fn (): string => self::nextRecipeCode())
                            ->disabled()
                            ->dehydrated()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('type')
                            ->label('Nhóm món')
                            ->required()
                            ->options(self::recipeTypeOptions())
                            ->default('Món mặn')
                            ->native(false),
                        Forms\Components\Select::make('price_level')
                            ->label('Mức giá suất ăn')
                            ->required()
                            ->options(self::mealPriceOptions())
                            ->default(20000)
                            ->native(false)
                            ->helperText('Đơn giá suất ăn có thể khác nhau theo đơn vị / hợp đồng.'),
                        Forms\Components\Select::make('actual_price')
                            ->label('Đơn giá suất ăn')
                            ->required()
                            ->options(self::mealPriceOptions())
                            ->default(20000)
                            ->native(false),
                        Forms\Components\Select::make('price_option')
                            ->label('Tùy chọn đơn giá')
                            ->required()
                            ->options([
                                'Không' => 'Không',
                                'Có' => 'Có',
                            ])
                            ->default('Không')
                            ->native(false),
                        Forms\Components\Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->options([
                                'active' => 'Đang hoạt động',
                                'pending' => 'Chờ rà soát',
                                'inactive' => 'Ngừng hoạt động',
                            ])
                            ->default('active')
                            ->native(false),
                        Forms\Components\TextInput::make('description')
                            ->label('Mô tả')
                            ->placeholder('Mô tả ngắn về món ăn')
                            ->columnSpan(2),
                    ]),

                Forms\Components\Section::make('Bảng nguyên liệu & cost trên 1 phần')
                    ->extraAttributes(['class' => 'recipe-cost-section'])
                    ->schema([
                        Forms\Components\Repeater::make('recipeIngredients')
                            ->hiddenLabel()
                            ->relationship()
                            ->defaultItems(1)
                            ->schema([
                                Forms\Components\Select::make('ingredient_id')
                                    ->label('Nguyên liệu')
                                    ->relationship('ingredient', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->afterStateHydrated(fn (Set $set, ?int $state): mixed => $set('ingredient_price', self::ingredientPrice($state)))
                                    ->afterStateUpdated(fn (Set $set, ?int $state): mixed => $set('ingredient_price', self::ingredientPrice($state)))
                                    ->placeholder('Tên nguyên liệu')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantity_per_portion')
                                    ->label('Định lượng (kg) / 1 phần')
                                    ->numeric()
                                    ->required()
                                    ->default(0.1)
                                    ->minValue(0.001)
                                    ->step(0.001)
                                    ->suffix('kg')
                                    ->live(onBlur: true)
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('ingredient_price')
                                    ->label('Đơn giá nguyên liệu')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->prefixIcon('heroicon-m-lock-closed')
                                    ->formatStateUsing(fn (mixed $state): string => self::formatCurrency((float) $state))
                                    ->columnSpan(3),
                                Forms\Components\Placeholder::make('line_total')
                                    ->label('Thành tiền')
                                    ->content(fn (Get $get): string => self::formatCurrency(
                                        (float) ($get('quantity_per_portion') ?? 0) * self::ingredientPrice($get('ingredient_id'))
                                    ))
                                    ->extraAttributes(['class' => 'recipe-line-total'])
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('note')
                                    ->label('Ghi chú')
                                    ->placeholder('Ghi chú')
                                    ->columnSpan(2),
                            ])
                            ->columns(10)
                            ->itemNumbers()
                            ->addActionLabel('Thêm nguyên liệu')
                            ->addActionAlignment(Alignment::End)
                            ->deleteAction(fn (Action $action): Action => $action->icon('heroicon-m-trash')->label(''))
                            ->reorderable(false)
                            ->collapsible(false)
                            ->itemLabel(fn (array $state): ?string => filled($state['ingredient_id'] ?? null)
                                ? Ingredient::query()->find($state['ingredient_id'])?->name
                                : 'Nguyên liệu'),
                        Forms\Components\Placeholder::make('total_cost')
                            ->hiddenLabel()
                            ->content(fn (Get $get): string => 'Tổng cost đơn giá trên 1 phần:  '.self::formatCurrency(self::recipeIngredientsTotal($get('recipeIngredients') ?? [])))
                            ->extraAttributes(['class' => 'recipe-total-cost'])
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('cost_note')
                            ->hiddenLabel()
                            ->content('Đơn giá nguyên liệu được lấy tự động từ module Nguyên liệu & NCC.')
                            ->extraAttributes(['class' => 'recipe-cost-note'])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('MÃ MÓN')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('TÊN MÓN ĂN')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label('NHÓM MÓN')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Món mặn', 'Món 1', 'Món 2', 'Món 3' => 'warning',
                        'Món xào', 'Rau xào/Luộc' => 'success',
                        'Món canh' => 'info',
                        'Món chay' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('price_level')
                    ->label('MỨC GIÁ SUẤT ĂN')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.').' d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('actual_price')
                    ->label('ĐƠN GIÁ SUẤT ĂN')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.').' d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ingredients_count')
                    ->label('SỐ NGUYÊN LIỆU')
                    ->counts('ingredients')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_weight')
                    ->label('TỔNG ĐỊNH LƯỢNG / PHẦN')
                    ->state(fn ($record) => str_replace('.', ',', round($record->ingredients->sum('pivot.quantity_per_portion'), 2)).' kg'),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('TỔNG COST NGUYÊN LIỆU / PHẦN')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.').' d')
                    ->weight('bold')
                    ->color('primary')
                    ->state(fn ($record) => $record->ingredients->sum(fn ($i) => $i->pivot->quantity_per_portion * $i->reference_price)),
                Tables\Columns\TextColumn::make('status')
                    ->label('TRẠNG THÁI')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'inactive' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Đang áp dụng',
                        'pending' => 'Chờ rà soát',
                        'inactive' => 'Ngưng áp dụng',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('CẬP NHẬT')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->searchPlaceholder('Tìm kiếm theo tên món ăn...')
            ->filters([
                Tables\Filters\SelectFilter::make('price_level')
                    ->label('Mức giá / Đơn giá suất ăn')
                    ->options(self::mealPriceOptions()),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Nhóm món')
                    ->options(self::recipeTypeOptions()),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'active' => 'Đang áp dụng',
                        'pending' => 'Chờ rà soát',
                        'inactive' => 'Ngưng áp dụng',
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Xem')
                    ->hiddenLabel()
                    ->button(),
                Tables\Actions\EditAction::make()
                    ->label('Chỉnh sửa')
                    ->hiddenLabel()
                    ->button(),
                Tables\Actions\DeleteAction::make()
                    ->label('Xóa')
                    ->hiddenLabel()
                    ->button(),
            ])
            ->actionsColumnLabel('HOẠT ĐỘNG')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\ViewEntry::make('recipe_detail')
                    ->view('filament.resources.recipes.view-detail')
                    ->columnSpanFull(),
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
        return parent::getEloquentQuery()->with(['ingredients', 'ingredients.supplier']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecipes::route('/'),
            'create' => Pages\CreateRecipe::route('/create'),
            'view' => Pages\ViewRecipe::route('/{record}'),
            'edit' => Pages\EditRecipe::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function recipeTypeOptions(): array
    {
        return [
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
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function mealPriceOptions(): array
    {
        return [
            15000 => 'Mức 15.000 đ',
            20000 => 'Mức 20.000 đ',
            25000 => 'Mức 25.000 đ',
            30000 => 'Mức 30.000 đ',
            35000 => 'Mức 35.000 đ',
            40000 => 'Mức 40.000 đ',
            50000 => 'Mức 50.000 đ',
        ];
    }

    private static function nextRecipeCode(): string
    {
        return 'MON'.str_pad((string) ((Recipe::query()->max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
    }

    private static function ingredientPrice(mixed $ingredientId): float
    {
        if (blank($ingredientId)) {
            return 0;
        }

        return (float) (Ingredient::query()->whereKey($ingredientId)->value('reference_price') ?? 0);
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $items
     */
    private static function recipeIngredientsTotal(array $items): float
    {
        return collect($items)
            ->sum(fn (array $item): float => (float) ($item['quantity_per_portion'] ?? 0) * self::ingredientPrice($item['ingredient_id'] ?? null));
    }

    private static function formatCurrency(float $value): string
    {
        return number_format($value, 0, ',', '.').' đ';
    }
}
