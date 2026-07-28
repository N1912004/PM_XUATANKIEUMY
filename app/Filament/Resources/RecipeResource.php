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
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static ?string $navigationIcon = 'fa-utensils';

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
        return __('catalog.groups.catering');
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
                            ->placeholder(__('recipe.placeholders.code'))
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('type')
                            ->label(__('recipe.fields.type'))
                            ->required()
                            ->options(fn () => RecipeType::pluck('name', 'name')->all())
                            ->default('Món mặn')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Forms\Components\TextInput::make('selling_price_per_portion')
                            ->label(__('recipe.fields.selling_price_per_portion'))
                            ->required()
                            ->default(20000)
                            ->numeric()
                            ->minValue(0)
                            ->formatStateUsing(fn (mixed $state): string => number_format((float) $state, 0, '', ''))
                            ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                            ->stripCharacters(['.', ','])
                            ->suffix(__('recipe.currency.unit')),
                        Forms\Components\TextInput::make('cost_per_portion')
                            ->label(__('recipe.fields.cost_per_portion'))
                            ->required()
                            ->default(20000)
                            ->numeric()
                            ->minValue(0)
                            ->formatStateUsing(fn (mixed $state): string => number_format((float) $state, 0, '', ''))
                            ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                            ->stripCharacters(['.', ','])
                            ->suffix(__('recipe.currency.unit'))
                            ->live(onBlur: true),
                        /* Tạm ẩn luồng cost điều chỉnh theo yêu cầu nghiệp vụ.
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
                        */
                        Forms\Components\Select::make('price_option')
                            ->label(__('recipe.fields.price_option'))
                            ->required()
                            ->options([
                                Recipe::PRICE_OPTION_NONE => __('recipe.options.none'),
                                Recipe::PRICE_OPTION_BY_UNIT => __('recipe.options.by_unit'),
                                Recipe::PRICE_OPTION_BY_CONTRACT => __('recipe.options.by_contract'),
                            ])
                            ->rules([Rule::in(Recipe::PRICE_OPTIONS)])
                            ->default(Recipe::PRICE_OPTION_NONE)
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
                            ->maxLength(255)
                            ->columnSpan(2),
                    ]),

                Forms\Components\Section::make(__('recipe.sections.ingredients_cost'))
                    ->extraAttributes(['class' => 'recipe-cost-section'])
                    ->schema([
                        Forms\Components\ViewField::make('ingredient_table_header')
                            ->view('filament.resources.recipes.partials.ingredient-table-header')
                            ->dehydrated(false)
                            ->extraAttributes(['class' => 'recipe-ingredient-table-header-field'])
                            ->hiddenLabel(),
                        Forms\Components\Repeater::make('recipeIngredients')
                            ->hiddenLabel()
                            ->relationship()
                            ->defaultItems(1)
                            ->schema([
                                Forms\Components\Select::make('ingredient_id')
                                    ->label(__('recipe.fields.ingredient'))
                                    ->hiddenLabel()
                                    ->extraFieldWrapperAttributes(['class' => 'recipe-ingredient-cell recipe-ingredient-cell-name'])
                                    ->relationship('ingredient', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->optionsLimit(50)
                                    ->live()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->afterStateHydrated(fn (Set $set, ?int $state): mixed => $set('ingredient_price', self::ingredientPrice($state)))
                                    ->afterStateUpdated(fn (Set $set, ?int $state): mixed => $set('ingredient_price', self::ingredientPrice($state)))
                                    ->placeholder(__('recipe.placeholders.ingredient'))
                                    ->columnSpan(3),
                                Forms\Components\TextInput::make('quantity_per_portion')
                                    ->label(__('recipe.fields.quantity_kg'))
                                    ->hiddenLabel()
                                    ->extraFieldWrapperAttributes(['class' => 'recipe-ingredient-cell recipe-ingredient-cell-quantity'])
                                    ->numeric()
                                    ->required()
                                    ->default(0.1)
                                    ->minValue(0.001)
                                    ->step(0.001)
                                    ->suffix('kg')
                                    ->live(onBlur: true)
                                    ->columnSpan(3),
                                Forms\Components\TextInput::make('ingredient_price')
                                    ->label(__('recipe.fields.ingredient_price'))
                                    ->hiddenLabel()
                                    ->extraFieldWrapperAttributes(['class' => 'recipe-ingredient-cell recipe-ingredient-cell-price'])
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->prefixIcon('heroicon-m-lock-closed')
                                    ->formatStateUsing(fn (mixed $state): string => self::formatCurrency((float) $state))
                                    ->columnSpan(5),
                                Forms\Components\Placeholder::make('line_total')
                                    ->label(__('recipe.fields.line_total'))
                                    ->hiddenLabel()
                                    ->content(fn (Get $get): string => self::formatCurrency(
                                        (float) ($get('quantity_per_portion') ?? 0) * self::ingredientPrice($get('ingredient_id'))
                                    ))
                                    ->extraAttributes(['class' => 'recipe-line-total'])
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('note')
                                    ->label(__('recipe.fields.note'))
                                    ->hiddenLabel()
                                    ->extraFieldWrapperAttributes(['class' => 'recipe-ingredient-cell recipe-ingredient-cell-note'])
                                    ->placeholder(__('recipe.placeholders.note'))
                                    ->maxLength(255)
                                    ->columnSpan(3),
                            ])
                            ->columns(16)
                            ->itemNumbers()
                            ->addAction(fn (Action $action): Action => $action
                                ->label(__('recipe.actions.add_ingredient'))
                                ->icon('heroicon-m-plus')
                                ->extraAttributes(['class' => 'recipe-add-ingredient-action']))
                            ->addActionAlignment(Alignment::End)
                            ->deleteAction(fn (Action $action): Action => $action
                                ->icon('heroicon-m-trash')
                                ->label(__('recipe.actions.delete'))
                                ->tooltip(__('recipe.actions.delete'))
                                ->extraAttributes([
                                    'class' => 'recipe-delete-ingredient-action',
                                    'data-column-label' => __('recipe.fields.actions'),
                                ]))
                            ->reorderable(false)
                            ->collapsible(false)
                            ->itemLabel(fn (): HtmlString => new HtmlString(
                                '<span class="recipe-stt-label">'.e(__('recipe.fields.row_number')).'</span>'
                            )),
                        Forms\Components\Placeholder::make('total_cost')
                            ->hiddenLabel()
                            ->content(fn (Get $get) => view('filament.resources.recipes.partials.total-cost', [
                                'cost' => self::formatCurrency(self::recipeIngredientsTotal($get('recipeIngredients') ?? [])),
                            ]))
                            ->extraAttributes(['class' => 'recipe-total-cost'])
                            ->columnSpanFull(),
                        /* Tạm ẩn cảnh báo lãi/lỗ trên form món ăn.
                        Forms\Components\Placeholder::make('margin_check')
                            ->hiddenLabel()
                            ->content(function (Get $get): HtmlString {
                                $cost = self::recipeIngredientsTotal($get('recipeIngredients') ?? []);
                                $price = (float) ($get('selling_price_per_portion') ?? 0);

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
                        */
                        Forms\Components\Placeholder::make('cost_note')
                            ->hiddenLabel()
                            ->content(fn () => view('filament.resources.recipes.partials.cost-note'))
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
                Tables\Columns\TextColumn::make('selling_price_per_portion')
                    ->label(__('recipe.table.selling_price_per_portion'))
                    ->formatStateUsing(fn ($state): string => self::formatCurrency((float) $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost_per_portion')
                    ->label(__('recipe.table.cost_per_portion'))
                    ->formatStateUsing(fn ($state): string => self::formatCurrency((float) $state))
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
                    ->formatStateUsing(fn ($state): string => self::formatCurrency((float) $state))
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('recipe.table.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->searchPlaceholder(__('recipe.placeholders.search'))
            ->filters([
                Tables\Filters\Filter::make('selling_price_range')
                    ->form([
                        Forms\Components\TextInput::make('selling_price_from')
                            ->label(__('recipe.filters.selling_price_from'))
                            ->numeric()
                            ->suffix(__('recipe.currency.unit')),
                        Forms\Components\TextInput::make('selling_price_to')
                            ->label(__('recipe.filters.selling_price_to'))
                            ->numeric()
                            ->suffix(__('recipe.currency.unit')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['selling_price_from'] ?? null, fn ($q, $from) => $q->where('selling_price_per_portion', '>=', (float) $from))
                            ->when($data['selling_price_to'] ?? null, fn ($q, $to) => $q->where('selling_price_per_portion', '<=', (float) $to));
                    }),
                Tables\Filters\Filter::make('cost_price_range')
                    ->form([
                        Forms\Components\TextInput::make('cost_price_from')
                            ->label(__('recipe.filters.cost_price_from'))
                            ->numeric()
                            ->suffix(__('recipe.currency.unit')),
                        Forms\Components\TextInput::make('cost_price_to')
                            ->label(__('recipe.filters.cost_price_to'))
                            ->numeric()
                            ->suffix(__('recipe.currency.unit')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['cost_price_from'] ?? null, fn ($q, $from) => $q->where('cost_per_portion', '>=', (float) $from))
                            ->when($data['cost_price_to'] ?? null, fn ($q, $to) => $q->where('cost_per_portion', '<=', (float) $to));
                    }),
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
        return __('recipe.currency.amount', [
            'value' => number_format($value, 0, ',', '.'),
        ]);
    }
}
