<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeResource\Pages;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeType;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('recipe.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('recipe.model.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('recipe.model.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('recipe.navigation.group');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('recipe.sections.information'))
                    ->columns(3)
                    ->extraAttributes(['class' => 'recipe-form-section'])
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('recipe.fields.name'))
                            ->required()
                            ->placeholder(__('recipe.placeholders.name')),
                        Forms\Components\TextInput::make('code')
                            ->label(__('recipe.fields.code'))
                            ->required()
                            ->default(fn (): string => self::nextRecipeCode())
                            ->disabled()
                            ->dehydrated()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('type')
                            ->label(__('recipe.fields.type'))
                            ->required()
                            ->options(fn () => RecipeType::pluck('name', 'name')->all())
                            ->default('Món mặn')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Forms\Components\Select::make('price_level')
                            ->label(__('recipe.fields.price_level'))
                            ->required()
                            ->options(self::mealPriceOptions())
                            ->default(20000)
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText(__('recipe.helpers.price_level')),
                        Forms\Components\Select::make('actual_price')
                            ->label(__('recipe.fields.actual_price'))
                            ->required()
                            ->options(self::mealPriceOptions())
                            ->default(20000)
                            ->searchable()
                            ->preload()
                            ->live() // đổi giá bán → tính lại cảnh báo lãi/lỗ ngay
                            ->native(false),
                        Forms\Components\TextInput::make('cost_override')
                            ->label(__('recipe.fields.cost_override'))
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->live(onBlur: true)
                            ->helperText(__('recipe.helpers.cost_override')),
                        Forms\Components\TextInput::make('cost_override_reason')
                            ->label(__('recipe.fields.cost_override_reason'))
                            ->dehydrated(false)
                            ->maxLength(255)
                            // Bắt buộc lý do khi giá override được đặt/thay đổi so với giá trị đang lưu
                            ->required(function (Get $get, ?Recipe $record): bool {
                                $new = $get('cost_override');
                                $old = $record?->cost_override;

                                return ($new !== null && $new !== '') && (float) $new !== (float) ($old ?? -1);
                            })
                            ->helperText(__('recipe.helpers.cost_override_reason')),
                        Forms\Components\Select::make('price_option')
                            ->label(__('recipe.fields.price_option'))
                            ->required()
                            ->options([
                                'Không' => __('recipe.options.no'),
                                'Có' => __('recipe.options.yes'),
                            ])
                            ->default('Không')
                            ->native(false),
                        Forms\Components\Select::make('status')
                            ->label(__('recipe.fields.status'))
                            ->required()
                            ->options([
                                'active' => __('recipe.status.active'),
                                'pending' => __('recipe.status.pending'),
                                'inactive' => __('recipe.status.inactive'),
                            ])
                            ->default('active')
                            ->native(false),
                        Forms\Components\TextInput::make('description')
                            ->label(__('recipe.fields.description'))
                            ->placeholder(__('recipe.placeholders.description'))
                            ->columnSpan(2),
                    ]),

                Forms\Components\Section::make(__('recipe.sections.ingredients_cost'))
                    ->extraAttributes(['class' => 'recipe-cost-section'])
                    ->schema([
                        Forms\Components\Repeater::make('recipeIngredients')
                            ->hiddenLabel()
                            ->relationship()
                            ->defaultItems(1)
                            ->schema([
                                Forms\Components\Select::make('ingredient_id')
                                    ->label(__('recipe.fields.ingredient'))
                                    ->relationship('ingredient', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->afterStateHydrated(fn (Set $set, ?int $state): mixed => $set('ingredient_price', self::ingredientPrice($state)))
                                    ->afterStateUpdated(fn (Set $set, ?int $state): mixed => $set('ingredient_price', self::ingredientPrice($state)))
                                    ->placeholder(__('recipe.placeholders.ingredient'))
                                    ->columnSpan(3),
                                Forms\Components\TextInput::make('quantity_gram')
                                    ->label(__('recipe.fields.quantity_gram'))
                                    ->numeric()
                                    ->placeholder('gram')
                                    ->suffix('g')
                                    ->dehydrated(false)
                                    ->live(onBlur: true)
                                    ->afterStateHydrated(function (Set $set, $state, Get $get) {
                                        $kg = (float) $get('quantity_per_portion');
                                        if ($kg > 0) {
                                            $set('quantity_gram', $kg * 1000);
                                        }
                                    })
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $gram = (float) $state;
                                        if ($gram > 0) {
                                            $set('quantity_per_portion', $gram / 1000);
                                        }
                                    })
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantity_per_portion')
                                    ->label(__('recipe.fields.quantity_kg'))
                                    ->numeric()
                                    ->required()
                                    ->default(0.1)
                                    ->minValue(0.001)
                                    ->step(0.001)
                                    ->suffix('kg')
                                    ->live(onBlur: true)
                                    ->afterStateHydrated(function (Set $set, $state) {
                                        $kg = (float) $state;
                                        if ($kg > 0) {
                                            $set('quantity_gram', $kg * 1000);
                                        }
                                    })
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $kg = (float) $state;
                                        if ($kg > 0) {
                                            $set('quantity_gram', $kg * 1000);
                                        }
                                    })
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('ingredient_price')
                                    ->label(__('recipe.fields.ingredient_price'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->prefixIcon('heroicon-m-lock-closed')
                                    ->formatStateUsing(fn (mixed $state): string => self::formatCurrency((float) $state))
                                    ->columnSpan(2),
                                Forms\Components\Placeholder::make('line_total')
                                    ->label(__('recipe.fields.line_total'))
                                    ->content(fn (Get $get): string => self::formatCurrency(
                                        (float) ($get('quantity_per_portion') ?? 0) * self::ingredientPrice($get('ingredient_id'))
                                    ))
                                    ->extraAttributes(['class' => 'recipe-line-total'])
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('note')
                                    ->label(__('recipe.fields.note'))
                                    ->placeholder(__('recipe.placeholders.note'))
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->itemNumbers()
                            ->addActionLabel(__('recipe.actions.add_ingredient'))
                            ->addActionAlignment(Alignment::End)
                            ->deleteAction(fn (Action $action): Action => $action->icon('heroicon-m-trash')->label(''))
                            ->reorderable(false)
                            ->collapsible(false)
                            ->itemLabel(fn (array $state): ?string => filled($state['ingredient_id'] ?? null)
                                ? Ingredient::query()->find($state['ingredient_id'])?->name
                                : __('recipe.fields.ingredient')),
                        Forms\Components\Placeholder::make('total_cost')
                            ->hiddenLabel()
                            ->content(fn (Get $get): string => __('recipe.messages.total_cost', ['cost' => self::formatCurrency(self::recipeIngredientsTotal($get('recipeIngredients') ?? []))]))
                            ->extraAttributes(['class' => 'recipe-total-cost'])
                            ->columnSpanFull(),
                        // Kiểm soát lãi/lỗ (BA R6): so cost thực tế với đơn giá bán cho khách
                        Forms\Components\Placeholder::make('margin_check')
                            ->hiddenLabel()
                            ->content(function (Get $get): HtmlString {
                                $cost = self::recipeIngredientsTotal($get('recipeIngredients') ?? []);
                                $price = (float) ($get('actual_price') ?? 0);

                                if ($price <= 0 || $cost <= 0) {
                                    return new HtmlString('<span style="color:#64748b">'.e(__('recipe.messages.select_sale_price')).'</span>');
                                }

                                $margin = $price - $cost;
                                $rate = round($margin / $price * 100, 1);

                                if ($margin < 0) {
                                    return new HtmlString('<span style="color:#dc2626;font-weight:700">'.e(__('recipe.messages.loss', ['margin' => self::formatCurrency(abs($margin)), 'price' => self::formatCurrency($price)])).'</span>');
                                }

                                $color = $rate < 10 ? '#d97706' : '#059669';

                                return new HtmlString('<span style="color:'.$color.';font-weight:700">'.e(__('recipe.messages.gross_margin', ['margin' => self::formatCurrency($margin), 'rate' => $rate])).($rate < 10 ? ' '.e(__('recipe.messages.thin_margin')) : '').'</span>');
                            })
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('cost_note')
                            ->hiddenLabel()
                            ->content(__('recipe.messages.ingredient_price_source'))
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
                    ->label(__('recipe.table.code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('recipe.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('recipe.table.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Món mặn', 'Món 1', 'Món 2', 'Món 3' => 'warning',
                        'Món xào', 'Rau xào/Luộc' => 'success',
                        'Món canh' => 'info',
                        'Món chay' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('price_level')
                    ->label(__('recipe.table.price_level'))
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.').' d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('actual_price')
                    ->label(__('recipe.table.actual_price'))
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.').' d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ingredients_count')
                    ->label(__('recipe.table.ingredients_count'))
                    ->counts('ingredients')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_weight')
                    ->label(__('recipe.table.total_weight'))
                    ->state(fn ($record) => str_replace('.', ',', round($record->ingredients->sum('pivot.quantity_per_portion'), 2)).' kg'),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label(__('recipe.table.total_cost'))
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.').' d')
                    ->weight('bold')
                    ->color(fn ($record) => $record->cost_override !== null ? 'warning' : 'primary')
                    ->description(fn ($record) => $record->cost_override !== null ? __('recipe.fields.cost_override') : null)
                    ->state(fn ($record) => $record->effectiveCostPerPortion()),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('recipe.table.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'inactive' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => __('recipe.status.active_applied'),
                        'pending' => __('recipe.status.pending'),
                        'inactive' => __('recipe.status.inactive_applied'),
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('recipe.table.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->searchPlaceholder(__('recipe.placeholders.search'))
            ->filters([
                Tables\Filters\SelectFilter::make('price_level')
                    ->label(__('recipe.filters.price'))
                    ->options(self::mealPriceOptions()),
                Tables\Filters\SelectFilter::make('recipe_type_id')
                    ->label(__('recipe.fields.type'))
                    ->relationship('recipeType', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('recipe.fields.status'))
                    ->options([
                        'active' => __('recipe.status.active_applied'),
                        'pending' => __('recipe.status.pending'),
                        'inactive' => __('recipe.status.inactive_applied'),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('recipe.actions.view'))
                    ->hiddenLabel()
                    ->button(),
                Tables\Actions\EditAction::make()
                    ->label(__('recipe.actions.edit'))
                    ->hiddenLabel()
                    ->button(),
                Tables\Actions\DeleteAction::make()
                    ->label(__('recipe.actions.delete'))
                    ->hiddenLabel()
                    ->button(),
            ])
            ->actionsColumnLabel(__('recipe.table.actions'))
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
        return parent::getEloquentQuery()
            ->with(['ingredients', 'ingredients.supplier', 'recipeType'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
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

    /** @var array<int, float> Memo giá theo request — Placeholder line_total gọi hàm này cho từng dòng repeater mỗi re-render */
    private static array $priceMemo = [];

    private static function ingredientPrice(mixed $ingredientId): float
    {
        if (blank($ingredientId)) {
            return 0;
        }

        return self::$priceMemo[(int) $ingredientId]
            ??= (float) (Ingredient::query()->whereKey($ingredientId)->value('reference_price') ?? 0);
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
