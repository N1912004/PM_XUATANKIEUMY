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
                    ->label('Loại giao dịch')
                    ->required()
                    ->options([
                        'Nhập kho' => 'Nhập kho',
                        'Xuất kho' => 'Xuất kho',
                        'Kiểm kê' => 'Kiểm kê',
                    ]),
                Forms\Components\TextInput::make('voucher_code')
                    ->label('Mã phiếu')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('ingredient_id')
                    ->label('Nguyên liệu')
                    ->relationship('ingredient', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Số lượng thay đổi (+/-)')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('after_quantity')
                    ->label('Tồn kho sau giao dịch')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('note')
                    ->label('Ghi chú')
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Loại giao dịch')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Nhập kho' => 'success',
                        'Xuất kho' => 'danger',
                        'Kiểm kê' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('voucher_code')
                    ->label('Mã phiếu')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ingredient.name')
                    ->label('Nguyên liệu')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Số lượng thay đổi')
                    ->numeric()
                    ->sortable()
                    ->color(fn (float $state): string => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (float $state): string => $state >= 0 ? "+{$state}" : "{$state}"),
                Tables\Columns\TextColumn::make('after_quantity')
                    ->label('Tồn sau giao dịch')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->label('Ghi chú')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Loại giao dịch')
                    ->options([
                        'Nhập kho' => 'Nhập kho',
                        'Xuất kho' => 'Xuất kho',
                        'Kiểm kê' => 'Kiểm kê',
                    ]),
                Tables\Filters\SelectFilter::make('ingredient_id')
                    ->label('Nguyên liệu')
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

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
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
