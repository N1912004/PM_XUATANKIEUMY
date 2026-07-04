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

    protected static ?string $navigationLabel = 'Kho';

    protected static ?string $modelLabel = 'Kho hàng';

    protected static ?string $pluralModelLabel = 'Kho';

    protected static ?string $navigationGroup = 'CUNG ỨNG & KHO';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::kitchenSelect(),
                Forms\Components\Select::make('ingredient_id')
                    ->label('Nguyên liệu')
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
                        'unique' => 'Nguyên liệu này đã tồn tại trong kho của bếp đã chọn.',
                    ])
                    ->createOptionForm([
                        Forms\Components\TextInput::make('code')
                            ->label('Mã nguyên liệu')
                            ->required()
                            ->default(fn () => 'NL'.str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT))
                            ->unique('ingredients', 'code'),
                        Forms\Components\TextInput::make('name')
                            ->label('Tên nguyên liệu')
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label('Phân loại')
                            ->options([
                                'Động vật' => 'Động vật',
                                'Thực vật' => 'Thực vật',
                                'Thực phẩm khô' => 'Thực phẩm khô',
                                'Gia vị' => 'Gia vị',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('unit')
                            ->label('Đơn vị tính')
                            ->required()
                            ->placeholder('VD: Kg, Hộp, Quả, ...'),
                        Forms\Components\Select::make('supplier_id')
                            ->label('Nhà cung cấp')
                            ->relationship('supplier', 'name')
                            ->nullable(),
                        Forms\Components\TextInput::make('reference_price')
                            ->label('Đơn giá tham chiếu (đ)')
                            ->numeric()
                            ->default(0),
                    ]),
                Forms\Components\TextInput::make('quantity')
                    ->label('Số lượng tồn')
                    ->required()
                    ->numeric()
                    ->default(0.000),
                Forms\Components\TextInput::make('min_quantity')
                    ->label('Định mức tối thiểu (min)')
                    ->required()
                    ->numeric()
                    ->default(0.000),
                Forms\Components\TextInput::make('unit_price')
                    ->label('Đơn giá (đ)')
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
                    ->label('MÃ NL')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('ingredient.name')
                    ->label('NGUYÊN LIỆU')
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => 'Click để xem Thẻ kho')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('ingredient.type')
                    ->label('LOẠI')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Động vật' => 'danger',
                        'Thực vật' => 'success',
                        'Thực phẩm khô' => 'warning',
                        'Gia vị' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('ingredient.supplier.name')
                    ->label('NCC')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('TỒN HIỆN TẠI')
                    ->state(fn ($record) => $record->quantity.' '.$record->ingredient->unit)
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_quantity')
                    ->label('TỐI THIỂU')
                    ->state(fn ($record) => $record->min_quantity.' '.$record->ingredient->unit)
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('ĐƠN GIÁ')
                    ->money('VND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_value')
                    ->label('GIÁ TRỊ')
                    ->money('VND')
                    ->state(fn ($record) => $record->quantity * $record->unit_price)
                    ->weight('bold')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('CẬP NHẬT LẦN CUỐI')
                    ->dateTime('H:i d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_status')
                    ->label('TRẠNG THÁI')
                    ->badge()
                    ->state(fn ($record): string => match (true) {
                        $record->quantity == 0 => 'Hết hàng',
                        $record->quantity <= $record->min_quantity => 'Sắp hết',
                        default => 'Đủ hàng',
                    })
                    ->color(fn ($state): string => match ($state) {
                        'Hết hàng' => 'danger',
                        'Sắp hết' => 'warning',
                        default => 'success',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kitchen_id')
                    ->label('Bếp ăn')
                    ->relationship('kitchen', 'name'),
                Tables\Filters\SelectFilter::make('ingredient_id')
                    ->label('Nguyên liệu')
                    ->relationship('ingredient', 'name'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('externalInbound')
                    ->label('Nhập kho ngoài')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->modalHeading('Nhập kho ngoài (mua trực tiếp không qua PO)')
                    ->form([
                        Forms\Components\Select::make('kitchen_id')
                            ->label('Bếp ăn')
                            ->relationship('kitchen', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => Filament::auth()->user()?->currentKitchenId()),
                        Forms\Components\Select::make('ingredient_id')
                            ->label('Nguyên liệu')
                            ->relationship('ingredient', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Số lượng nhập')
                            ->numeric()
                            ->required()
                            ->minValue(0.001),
                        Forms\Components\TextInput::make('unit_price')
                            ->label('Đơn giá (đ)')
                            ->numeric()
                            ->default(0),
                        Forms\Components\FileUpload::make('attachment')
                            ->label('Ảnh/File hóa đơn chứng từ')
                            ->disk('public')
                            ->directory('stock-vouchers')
                            ->required()
                            ->helperText('Bắt buộc đính kèm chứng từ trước khi lưu.'),
                        Forms\Components\Textarea::make('note')
                            ->label('Ghi chú'),
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
                            ->title('Đã nhập kho ngoài thành công')
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
