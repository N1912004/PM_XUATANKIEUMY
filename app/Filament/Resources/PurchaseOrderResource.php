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

    protected static ?int $navigationSort = 6;

    public static function getNavigationLabel(): string
    {
        return __('Đặt hàng');
    }

    public static function getModelLabel(): string
    {
        return __('Đơn đặt hàng');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Đặt hàng');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('CUNG ỨNG');
    }

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
                        'cancelled' => 'Đã hủy (Cancelled)',
                    ])
                    ->default('draft')
                    ->rule(fn (?PurchaseOrder $record) => function (string $attribute, $value, \Closure $fail) use ($record): void {
                        if (! $record) {
                            return;
                        }
                        $order = ['draft' => 0, 'sent' => 1, 'checking' => 2, 'done' => 3, 'cancelled' => 4];
                        if ($record->status === 'done' && $value !== 'done') {
                            $fail('Đơn hàng đã hoàn thành nhập kho không được phép thay đổi trạng thái.');
                        }
                        if (($order[$value] ?? 0) < ($order[$record->status] ?? 0) && $value !== 'cancelled') {
                            $fail('Không được lùi trạng thái đơn hàng (vòng đời chỉ đi tiến Nháp → Đã gửi → Đang kiểm → Hoàn thành).');
                        }
                    }),
                Forms\Components\DatePicker::make('estimated_delivery_date')
                    ->label('Ngày giao dự kiến')
                    // Quy định nghiệp vụ: chỉ được đặt hàng cho tối đa 2 ngày kế tiếp.
                    // Chỉ ràng buộc khi tạo mới — đơn cũ (ngày quá khứ) vẫn sửa được các trường khác.
                    ->minDate(fn (string $operation) => $operation === 'create' ? today() : null)
                    ->maxDate(fn (string $operation) => $operation === 'create' ? today()->addDays(2) : null)
                    ->helperText('Chỉ được chọn trong vòng 2 ngày kế tiếp từ hôm nay'),
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
                    ->extraAttributes([
                        'style' => 'font-variant-numeric: tabular-nums;',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_value')
                    ->label('TỔNG GIÁ TRỊ')
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
                    ->label('TRẠNG THÁI')
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
                        'draft' => 'Nháp',
                        'sent' => 'Đã gửi NCC',
                        'checking' => 'Đang kiểm hàng',
                        'done' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
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
                        'cancelled' => 'Đã hủy',
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
