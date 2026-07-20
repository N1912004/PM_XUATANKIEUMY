<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockTransferResource\Pages;
use App\Models\Ingredient;
use App\Models\Kitchen;
use App\Models\Stock;
use App\Models\StockTransfer;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockTransferResource extends Resource
{
    protected static ?string $model = StockTransfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('warehouse.resource.transfer.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('warehouse.resource.transfer.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('warehouse.resource.transfer.navigation');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('warehouse.resource.navigation_group');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('source_kitchen_id')
                    ->label(__('warehouse.resource.transfer.source_kitchen'))
                    ->relationship('sourceKitchen', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(fn () => Filament::auth()->user()?->currentKitchenId()),
                Forms\Components\Select::make('dest_kitchen_id')
                    ->label(__('warehouse.resource.transfer.destination_kitchen'))
                    // Chỉ liệt kê bếp CÙNG KHU VỰC với bếp nguồn (quy định điều chuyển nội khu vực)
                    ->relationship(
                        'destKitchen',
                        'name',
                        function (Builder $query, Forms\Get $get): Builder {
                            $sourceAreaId = Kitchen::whereKey($get('source_kitchen_id'))->value('area_id');

                            return $sourceAreaId ? $query->where('area_id', $sourceAreaId) : $query;
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->different('source_kitchen_id')
                    ->rule(fn (Forms\Get $get) => function (string $attribute, $value, \Closure $fail) use ($get): void {
                        $sourceAreaId = Kitchen::whereKey($get('source_kitchen_id'))->value('area_id');
                        $destAreaId = Kitchen::whereKey($value)->value('area_id');
                        if ($sourceAreaId && $destAreaId !== $sourceAreaId) {
                            $fail(__('warehouse.resource.transfer.same_area'));
                        }
                    }),
                Forms\Components\Textarea::make('note')
                    ->label(__('warehouse.table.note'))
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('items')
                    ->label(__('warehouse.resource.transfer.items'))
                    ->relationship('items')
                    ->schema([
                        Forms\Components\Select::make('ingredient_id')
                            ->label(__('warehouse.table.ingredient'))
                            ->options(fn () => Ingredient::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label(__('warehouse.table.transfer_qty'))
                            ->numeric()
                            ->required()
                            ->minValue(0.001)
                            ->rules([
                                fn (Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $sourceKitchenId = $get('../../source_kitchen_id');
                                    $ingredientId = $get('ingredient_id');

                                    if (! $sourceKitchenId || ! $ingredientId || ! $value) {
                                        return;
                                    }

                                    $stock = Stock::query()
                                        ->where('kitchen_id', $sourceKitchenId)
                                        ->where('ingredient_id', $ingredientId)
                                        ->first();

                                    $available = $stock ? ((float) $stock->quantity - (float) $stock->frozen_quantity) : 0.0;

                                    if ($available < (float) $value) {
                                        $fail(__('warehouse.resource.transfer.insufficient', ['available' => $available]));
                                    }
                                },
                            ]),
                    ])
                    ->columns(2)
                    ->addActionLabel(__('warehouse.actions.add_item'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label(__('warehouse.resource.table.index'))
                    ->state(static function (Tables\Contracts\HasTable $livewire, \stdClass $rowLoop): string {
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
                    ->label(__('warehouse.resource.table.voucher_code'))
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('sourceKitchen.name')
                    ->label(__('warehouse.resource.table.source_kitchen'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('destKitchen.name')
                    ->label(__('warehouse.resource.table.destination_kitchen'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('warehouse.resource.table.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        StockTransfer::STATUS_IN_TRANSIT => 'warning',
                        StockTransfer::STATUS_DONE => 'success',
                        StockTransfer::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        StockTransfer::STATUS_IN_TRANSIT => __('warehouse.status.in_transit'),
                        StockTransfer::STATUS_DONE => __('warehouse.resource.status.completed'),
                        StockTransfer::STATUS_CANCELLED => __('warehouse.resource.status.cancelled'),
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('warehouse.resource.table.created_at'))
                    ->dateTime('H:i d/m/Y')
                    ->extraAttributes([
                        'style' => 'font-variant-numeric: tabular-nums;',
                    ])
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('warehouse.resource.fields.status'))
                    ->options([
                        StockTransfer::STATUS_IN_TRANSIT => __('warehouse.status.in_transit'),
                        StockTransfer::STATUS_DONE => __('warehouse.resource.status.completed'),
                        StockTransfer::STATUS_CANCELLED => __('warehouse.resource.status.cancelled'),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('confirmReceived')
                    ->label(__('warehouse.actions.confirm_receive'))
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->requiresConfirmation()
                    // Chỉ BẾP NHẬN (hoặc quản trị) mới thấy/bấm được nút nhận hàng — bếp xuất không tự xác nhận thay
                    ->visible(function (StockTransfer $record): bool {
                        if ($record->status !== StockTransfer::STATUS_IN_TRANSIT) {
                            return false;
                        }
                        $user = Filament::auth()->user();
                        if (! $user || $user->hasRole(['super_admin', 'Quản trị viên'])) {
                            return true;
                        }

                        return (int) $record->dest_kitchen_id === (int) $user->currentKitchenId();
                    })
                    ->action(function (StockTransfer $record): void {
                        $user = Filament::auth()->user();
                        abort_if(
                            $user && ! $user->hasRole(['super_admin', 'Quản trị viên'])
                                && (int) $record->dest_kitchen_id !== (int) $user->currentKitchenId(),
                            403
                        );
                        $record->confirmReceived(Filament::auth()->id());
                        Notification::make()->title(__('warehouse.resource.transfer.received'))->success()->send();
                    }),
                Tables\Actions\Action::make('cancelTransfer')
                    ->label(__('warehouse.resource.transfer.cancel'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    // Hủy phiếu: chỉ bếp XUẤT (nơi tạo phiếu) hoặc quản trị
                    ->visible(function (StockTransfer $record): bool {
                        if ($record->status !== StockTransfer::STATUS_IN_TRANSIT) {
                            return false;
                        }
                        $user = Filament::auth()->user();
                        if (! $user || $user->hasRole(['super_admin', 'Quản trị viên'])) {
                            return true;
                        }

                        return (int) $record->source_kitchen_id === (int) $user->currentKitchenId();
                    })
                    ->action(function (StockTransfer $record): void {
                        $user = Filament::auth()->user();
                        abort_if(
                            $user && ! $user->hasRole(['super_admin', 'Quản trị viên'])
                                && (int) $record->source_kitchen_id !== (int) $user->currentKitchenId(),
                            403
                        );
                        $record->cancel();
                        Notification::make()->title(__('warehouse.resource.transfer.cancelled'))->warning()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockTransfers::route('/'),
            'create' => Pages\CreateStockTransfer::route('/create'),
            'edit' => Pages\EditStockTransfer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Filament::auth()->user();

        if (! $user) {
            return $query;
        }

        if ($user->hasRole(['super_admin', 'Quản trị viên'])) {
            return $query;
        }

        $kitchenId = $user->currentKitchenId();

        if ($kitchenId) {
            $query->where(function (Builder $q) use ($kitchenId) {
                $q->where('source_kitchen_id', $kitchenId)
                    ->orWhere('dest_kitchen_id', $kitchenId);
            });
        }

        return $query;
    }
}
