<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockTransferResource\Pages;
use App\Models\Ingredient;
use App\Models\StockTransfer;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockTransferResource extends Resource
{
    protected static ?string $model = StockTransfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Điều chuyển kho';

    protected static ?string $modelLabel = 'Phiếu điều chuyển kho';

    protected static ?string $pluralModelLabel = 'Điều chuyển kho';

    protected static ?string $navigationGroup = 'CUNG ỨNG & KHO';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('source_kitchen_id')
                    ->label('Bếp xuất (nguồn)')
                    ->relationship('sourceKitchen', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(fn () => Filament::auth()->user()?->currentKitchenId()),
                Forms\Components\Select::make('dest_kitchen_id')
                    ->label('Bếp nhận (đích)')
                    ->relationship('destKitchen', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->different('source_kitchen_id'),
                Forms\Components\Textarea::make('note')
                    ->label('Ghi chú')
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('items')
                    ->label('Mặt hàng điều chuyển')
                    ->relationship('items')
                    ->schema([
                        Forms\Components\Select::make('ingredient_id')
                            ->label('Nguyên liệu')
                            ->options(fn () => Ingredient::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Số lượng chuyển')
                            ->numeric()
                            ->required()
                            ->minValue(0.001),
                    ])
                    ->columns(2)
                    ->addActionLabel('Thêm mặt hàng')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('MÃ PHIẾU')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('sourceKitchen.name')
                    ->label('BẾP XUẤT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('destKitchen.name')
                    ->label('BẾP NHẬN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('TRẠNG THÁI')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        StockTransfer::STATUS_IN_TRANSIT => 'warning',
                        StockTransfer::STATUS_DONE => 'success',
                        StockTransfer::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('NGÀY TẠO')
                    ->dateTime('H:i d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        StockTransfer::STATUS_IN_TRANSIT => 'Đang chuyển',
                        StockTransfer::STATUS_DONE => 'Hoàn thành',
                        StockTransfer::STATUS_CANCELLED => 'Hủy',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('confirmReceived')
                    ->label('Xác nhận nhận hàng')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (StockTransfer $record): bool => $record->status === StockTransfer::STATUS_IN_TRANSIT)
                    ->action(function (StockTransfer $record): void {
                        $record->confirmReceived(Filament::auth()->id());
                        Notification::make()->title('Đã nhận hàng & cập nhật tồn kho bếp nhận')->success()->send();
                    }),
                Tables\Actions\Action::make('cancelTransfer')
                    ->label('Hủy phiếu')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (StockTransfer $record): bool => $record->status === StockTransfer::STATUS_IN_TRANSIT)
                    ->action(function (StockTransfer $record): void {
                        $record->cancel();
                        Notification::make()->title('Đã hủy phiếu & hoàn tồn về bếp xuất')->warning()->send();
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
}
