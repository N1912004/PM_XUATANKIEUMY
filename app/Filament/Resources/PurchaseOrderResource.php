<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\BelongsToKitchen;
use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    use BelongsToKitchen;

    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('purchase_order.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('purchase_order.navigation.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('purchase_order.navigation.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('purchase_order.navigation.group');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label(__('purchase_order.fields.code'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                static::kitchenSelect(),
                Forms\Components\Select::make('supplier_id')
                    ->label(__('purchase_order.fields.supplier'))
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label(__('purchase_order.fields.status'))
                    ->required()
                    ->options([
                        'draft' => __('purchase_order.status.draft_with_code'),
                        'sent' => __('purchase_order.status.sent_with_code'),
                        'checking' => __('purchase_order.status.checking_with_code'),
                        'done' => __('purchase_order.status.done_with_code'),
                        'cancelled' => __('purchase_order.status.cancelled_with_code'),
                    ])
                    ->default('draft')
                    ->rule(fn (?PurchaseOrder $record) => function (string $attribute, $value, \Closure $fail) use ($record): void {
                        if (! $record) {
                            return;
                        }
                        $order = ['draft' => 0, 'sent' => 1, 'checking' => 2, 'done' => 3, 'cancelled' => 4];
                        if ($record->status === 'done' && $value !== 'done') {
                            $fail(__('purchase_order.validation.completed_status_locked'));
                        }
                        if (($order[$value] ?? 0) < ($order[$record->status] ?? 0) && $value !== 'cancelled') {
                            $fail(__('purchase_order.validation.status_cannot_go_back'));
                        }
                    }),
                Forms\Components\DatePicker::make('estimated_delivery_date')
                    ->label(__('purchase_order.fields.estimated_delivery_date'))
                    // Quy định nghiệp vụ: chỉ được đặt hàng cho tối đa 2 ngày kế tiếp.
                    // Chỉ ràng buộc khi tạo mới — đơn cũ (ngày quá khứ) vẫn sửa được các trường khác.
                    ->minDate(fn (string $operation) => $operation === 'create' ? today() : null)
                    ->maxDate(fn (string $operation) => $operation === 'create' ? today()->addDays(2) : null)
                    ->helperText(__('purchase_order.help.delivery_date')),
                Forms\Components\Textarea::make('note')
                    ->label(__('purchase_order.fields.note'))
                    ->columnSpanFull(),

                Forms\Components\Section::make(__('purchase_order.form.details'))
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Select::make('ingredient_id')
                                    ->label(__('purchase_order.fields.ingredient'))
                                    ->options(Ingredient::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\TextInput::make('quantity_ordered')
                                    ->label(__('purchase_order.fields.quantity_ordered'))
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('quantity_received')
                                    ->label(__('purchase_order.fields.quantity_received'))
                                    ->numeric()
                                    ->default(0.00),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label(__('purchase_order.fields.unit_price'))
                                    ->numeric()
                                    ->default(0.00),
                            ])
                            ->columns(4)
                            ->label(__('purchase_order.form.ordered_items'))
                            ->createItemButtonLabel(__('purchase_order.actions.add_item')),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label(__('purchase_order.table.index'))
                    ->state(static function (HasTable $livewire, \stdClass $rowLoop): string {
                        $currentPage = method_exists($livewire, 'getTablePage') ? $livewire->getTablePage() : 1;
                        $recordsPerPage = method_exists($livewire, 'getTableRecordsPerPage') ? $livewire->getTableRecordsPerPage() : 10;
                        $perPage = is_numeric($recordsPerPage) ? (int) $recordsPerPage : 10;

                        return (string) ($rowLoop->iteration + ($perPage * ($currentPage - 1)));
                    })
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'font-variant-numeric: tabular-nums; font-weight: 600; color: #64748b;',
                    ])
                    ->width('56px'),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('purchase_order.table.code'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                static::kitchenColumn(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label(__('purchase_order.table.supplier'))
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('estimated_delivery_date')
                    ->label(__('purchase_order.table.estimated_delivery_date'))
                    ->date('d/m/Y')
                    ->extraAttributes([
                        'style' => 'font-variant-numeric: tabular-nums;',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_value')
                    ->label(__('purchase_order.table.total_value'))
                    ->html()
                    ->formatStateUsing(function ($state) {
                        if ($state === null) {
                            return '—';
                        }
                        $formatted = number_format($state, 0, ',', '.');

                        return "<strong>{$formatted}</strong><span style=\"font-size: 10px; font-weight: 500; color: #94a3b8; margin-left: 2px;\">đ</span>";
                    })
                    ->alignRight()
                    ->extraAttributes([
                        'style' => 'font-variant-numeric: tabular-nums;',
                    ])
                    ->state(fn ($record) => $record->items->sum(fn ($item) => $item->quantity_ordered * $item->unit_price)),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('purchase_order.table.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'checking' => 'warning',
                        'done' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => __('purchase_order.status.draft'),
                        'sent' => __('purchase_order.status.sent'),
                        'checking' => __('purchase_order.status.checking'),
                        'done' => __('purchase_order.status.done'),
                        'cancelled' => __('purchase_order.status.cancelled'),
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kitchen_id')
                    ->label(__('purchase_order.fields.kitchen'))
                    ->relationship('kitchen', 'name'),
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label(__('purchase_order.fields.supplier'))
                    ->relationship('supplier', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('purchase_order.fields.status'))
                    ->options([
                        'draft' => __('purchase_order.status.draft'),
                        'sent' => __('purchase_order.status.sent'),
                        'checking' => __('purchase_order.status.checking'),
                        'done' => __('purchase_order.status.done'),
                        'cancelled' => __('purchase_order.status.cancelled'),
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
