<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\BelongsToKitchen;
use App\Filament\Resources\StockResource\Pages;
use App\Models\Stock;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class StockResource extends Resource
{
    use BelongsToKitchen;

    protected static ?string $model = Stock::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('warehouse.resource.stock.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('warehouse.resource.stock.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('warehouse.resource.stock.navigation');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.groups.ingredients_inventory');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::kitchenSelect(),
                Forms\Components\Select::make('ingredient_id')
                    ->label(__('warehouse.table.ingredient'))
                    ->relationship('ingredient', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(
                        'stocks',
                        'ingredient_id',
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule, Forms\Get $get) => $rule->where('kitchen_id', $get('kitchen_id')),
                    )
                    ->validationMessages([
                        'unique' => __('warehouse.resource.stock.ingredient_exists'),
                    ])
                    ->createOptionForm([
                        Forms\Components\TextInput::make('code')
                            ->label(__('warehouse.table.ingredient_code_short'))
                            ->required()
                            ->default(fn () => 'NL'.str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT))
                            ->unique('ingredients', 'code'),
                        Forms\Components\TextInput::make('name')
                            ->label(__('warehouse.table.ingredient'))
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label(__('warehouse.table.type'))
                            ->options([
                                'Động vật' => 'Động vật',
                                'Thực vật' => 'Thực vật',
                                'Thực phẩm khô' => 'Thực phẩm khô',
                                'Gia vị' => 'Gia vị',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('unit')
                            ->label(__('warehouse.table.unit'))
                            ->required()
                            ->placeholder(__('warehouse.resource.stock.unit_placeholder')),
                        Forms\Components\Select::make('supplier_id')
                            ->label(__('warehouse.table.supplier'))
                            ->relationship('supplier', 'name')
                            ->nullable(),
                        Forms\Components\TextInput::make('reference_price')
                            ->label(__('warehouse.resource.stock.reference_price'))
                            ->numeric()
                            ->default(0),
                    ]),
                Forms\Components\TextInput::make('quantity')
                    ->label(__('warehouse.table.current_stock'))
                    ->required()
                    ->numeric()
                    ->default(0.000),
                Forms\Components\TextInput::make('min_quantity')
                    ->label(__('warehouse.table.minimum'))
                    ->required()
                    ->numeric()
                    ->default(0.000),
                Forms\Components\TextInput::make('unit_price')
                    ->label(__('warehouse.table.unit_price'))
                    ->required()
                    ->numeric()
                    ->default(0.00),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('#')
                    ->state(static function (HasTable $livewire, \stdClass $rowLoop): string {
                        return (string) ($rowLoop->iteration);
                    }),
                static::kitchenColumn(),
                Tables\Columns\TextColumn::make('ingredient.code')
                    ->label(__('warehouse.table.ingredient_code_short'))
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('ingredient.name')
                    ->label(__('warehouse.table.ingredient'))
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => __('warehouse.tooltips.open_ledger'))
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('ingredient.type')
                    ->label(__('warehouse.table.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Động vật' => 'danger',
                        'Thực vật' => 'success',
                        'Thực phẩm khô' => 'warning',
                        'Gia vị' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('ingredient.supplier.name')
                    ->label(__('warehouse.table.supplier'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('warehouse.table.current_stock'))
                    ->state(fn ($record) => $record->quantity.' '.$record->ingredient->unit)
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_quantity')
                    ->label(__('warehouse.table.minimum'))
                    ->state(fn ($record) => $record->min_quantity.' '.$record->ingredient->unit)
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label(__('warehouse.table.unit_price'))
                    ->money('VND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_value')
                    ->label(__('warehouse.table.value'))
                    ->money('VND')
                    ->state(fn ($record) => $record->quantity * $record->unit_price)
                    ->weight('bold')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('warehouse.table.last_updated'))
                    ->dateTime('H:i d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_status')
                    ->label(__('warehouse.table.status'))
                    ->badge()
                    ->state(fn ($record): string => match (true) {
                        $record->quantity == 0 => __('warehouse.status.out_of_stock'),
                        $record->quantity <= $record->min_quantity => __('warehouse.status.low_stock'),
                        default => __('warehouse.status.enough_stock'),
                    })
                    ->color(fn ($record): string => $record->quantity == 0 ? 'danger' : ($record->quantity <= $record->min_quantity ? 'warning' : 'success')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kitchen_id')
                    ->label(__('warehouse.filters.selected_kitchen'))
                    ->relationship('kitchen', 'name'),
                Tables\Filters\SelectFilter::make('ingredient_id')
                    ->label(__('warehouse.table.ingredient'))
                    ->relationship('ingredient', 'name'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('externalInbound')
                    ->label(__('warehouse.transaction_type_labels.external_inbound'))
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('gray')
                    ->modalHeading(__('warehouse.resource.stock.external_inbound_heading'))
                    ->form([
                        Forms\Components\Select::make('kitchen_id')
                            ->label(__('warehouse.resource.stock.kitchen'))
                            ->relationship('kitchen', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => Filament::auth()->user()?->currentKitchenId()),
                        Forms\Components\Select::make('ingredient_id')
                            ->label(__('warehouse.table.ingredient'))
                            ->relationship('ingredient', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label(__('warehouse.resource.stock.inbound_quantity'))
                            ->numeric()
                            ->required()
                            ->minValue(0.001),
                        Forms\Components\TextInput::make('unit_price')
                            ->label(__('warehouse.table.unit_price'))
                            ->numeric()
                            ->default(0),
                        Forms\Components\FileUpload::make('attachment')
                            ->label(__('warehouse.resource.stock.attachment'))
                            ->disk('public')
                            ->directory('stock-vouchers')
                            ->required()
                            ->helperText(__('warehouse.resource.stock.attachment_required')),
                        Forms\Components\Textarea::make('note')
                            ->label(__('warehouse.table.note')),
                    ])
                    ->action(function (array $data): void {
                        Stock::recordExternalInbound(
                            $data['kitchen_id'] ?? null,
                            (int) $data['ingredient_id'],
                            (float) $data['quantity'],
                            (float) ($data['unit_price'] ?? 0),
                            $data['attachment'],
                            $data['note'] ?? null,
                        );

                        Notification::make()
                            ->title(__('warehouse.resource.stock.external_inbound_success'))
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
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
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }
}
