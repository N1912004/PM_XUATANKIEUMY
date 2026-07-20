<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\BelongsToKitchen;
use App\Filament\Resources\StockTransactionResource\Pages;
use App\Models\StockTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class StockTransactionResource extends Resource
{
    use BelongsToKitchen;

    protected static ?string $model = StockTransaction::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label(__('warehouse.table.transaction_type'))
                    ->required()
                    ->options([
                        'Nhập kho' => __('warehouse.transaction_type_labels.inbound'),
                        'Xuất kho' => __('warehouse.transaction_type_labels.outbound'),
                        'Kiểm kê' => __('warehouse.transaction_type_labels.stock_check'),
                    ]),
                Forms\Components\TextInput::make('voucher_code')
                    ->label(__('warehouse.table.voucher_code'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('ingredient_id')
                    ->label(__('warehouse.table.ingredient'))
                    ->relationship('ingredient', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label(__('warehouse.resource.transaction.quantity_change_signed'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('after_quantity')
                    ->label(__('warehouse.resource.transaction.after_quantity'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('note')
                    ->label(__('warehouse.table.note'))
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('warehouse.table.time'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('warehouse.table.transaction_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Nhập kho' => 'success',
                        'Xuất kho' => 'danger',
                        'Kiểm kê' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('voucher_code')
                    ->label(__('warehouse.table.voucher_code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('ingredient.name')
                    ->label(__('warehouse.table.ingredient'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('warehouse.resource.transaction.quantity_change'))
                    ->numeric()
                    ->sortable()
                    ->color(fn (float $state): string => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (float $state): string => $state >= 0 ? "+{$state}" : "{$state}"),
                Tables\Columns\TextColumn::make('after_quantity')
                    ->label(__('warehouse.resource.transaction.after_quantity'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->label(__('warehouse.table.note'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('warehouse.table.transaction_type'))
                    ->options([
                        'Nhập kho' => __('warehouse.transaction_type_labels.inbound'),
                        'Xuất kho' => __('warehouse.transaction_type_labels.outbound'),
                        'Kiểm kê' => __('warehouse.transaction_type_labels.stock_check'),
                    ]),
                Tables\Filters\SelectFilter::make('ingredient_id')
                    ->label(__('warehouse.table.ingredient'))
                    ->relationship('ingredient', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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

    /**
     * Sổ thẻ kho là LEDGER BẤT BIẾN: mọi biến động tồn phải đi qua các luồng nghiệp vụ
     * (nhập PO, nhập ngoài, xuất sản xuất, điều chuyển, kiểm kê) — không tạo/sửa/xóa tay.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockTransactions::route('/'),
            'create' => Pages\CreateStockTransaction::route('/create'),
            'edit' => Pages\EditStockTransaction::route('/{record}/edit'),
        ];
    }
}
