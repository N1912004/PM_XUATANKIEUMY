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

    protected static ?string $navigationLabel = 'Đặt hàng';

    protected static ?string $modelLabel = 'Đơn đặt hàng';

    protected static ?string $pluralModelLabel = 'Đặt hàng';

    protected static ?string $navigationGroup = 'CUNG ỨNG & KHO';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Mã đơn hàng')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                static::kitchenSelect(),
                Forms\Components\Select::make('supplier_id')
                    ->label('Nhà cung cấp')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->required()
                    ->options([
                        'draft' => 'Nháp (Draft)',
                        'sent' => 'Đã gửi NCC (Sent)',
                        'checking' => 'Đang kiểm (Checking)',
                        'done' => 'Hoàn thành (Done)',
                    ])
                    ->default('draft'),
                Forms\Components\DatePicker::make('estimated_delivery_date')
                    ->label('Ngày giao dự kiến'),
                Forms\Components\Textarea::make('note')
                    ->label('Ghi chú')
                    ->columnSpanFull(),

                Forms\Components\Section::make('Chi tiết đơn hàng')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Select::make('ingredient_id')
                                    ->label('Nguyên liệu')
                                    ->options(Ingredient::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\TextInput::make('quantity_ordered')
                                    ->label('SL đặt')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('quantity_received')
                                    ->label('SL nhận')
                                    ->numeric()
                                    ->default(0.00),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Đơn giá')
                                    ->numeric()
                                    ->default(0.00),
                            ])
                            ->columns(4)
                            ->label('Mặt hàng đặt')
                            ->createItemButtonLabel('Thêm mặt hàng'),
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
                    ->label('MÃ ĐƠN')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                static::kitchenColumn(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('NHÀ CUNG CẤP')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('estimated_delivery_date')
                    ->label('NGÀY GIAO DỰ KIẾN')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_value')
                    ->label('TỔNG GIÁ TRỊ')
                    ->money('VND')
                    ->state(fn ($record) => $record->items->sum(fn ($item) => $item->quantity_ordered * $item->unit_price))
                    ->weight('bold')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('status')
                    ->label('TRẠNG THÁI')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'checking' => 'warning',
                        'done' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Nháp',
                        'sent' => 'Đã gửi NCC',
                        'checking' => 'Đang kiểm hàng',
                        'done' => 'Hoàn thành',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kitchen_id')
                    ->label('Bếp ăn')
                    ->relationship('kitchen', 'name'),
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Nhà cung cấp')
                    ->relationship('supplier', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'draft' => 'Nháp',
                        'sent' => 'Đã gửi NCC',
                        'checking' => 'Đang kiểm hàng',
                        'done' => 'Hoàn thành',
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
