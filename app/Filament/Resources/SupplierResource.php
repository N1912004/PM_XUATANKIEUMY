<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Nhà cung cấp';

    protected static ?string $modelLabel = 'Nhà cung cấp';

    protected static ?string $pluralModelLabel = 'Nhà cung cấp';

    protected static ?string $navigationGroup = 'CUNG ỨNG & KHO';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Section::make('Thông tin nhà cung cấp')
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Tên nhà cung cấp')
                                    ->required()
                                    ->placeholder('Nhập tên nhà cung cấp'),
                                Forms\Components\TextInput::make('code')
                                    ->label('Mã nhà cung cấp')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Nhập mã nhà cung cấp'),
                                Forms\Components\Select::make('type')
                                    ->label('Phân loại')
                                    ->required()
                                    ->options([
                                        'Thịt' => 'Thịt',
                                        'Rau củ' => 'Rau củ',
                                        'Thực phẩm khô' => 'Thực phẩm khô',
                                        'Gia vị' => 'Gia vị',
                                        'Hải sản' => 'Hải sản',
                                        'Tổng hợp' => 'Tổng hợp',
                                    ])
                                    ->placeholder('Chọn phân loại'),
                                Forms\Components\TextInput::make('contact_name')
                                    ->label('Người đại diện')
                                    ->placeholder('Nhập tên người đại diện'),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Số điện thoại')
                                    ->tel()
                                    ->placeholder('Nhập số điện thoại'),
                                Forms\Components\TextInput::make('email')
                                    ->label('Địa chỉ email')
                                    ->email()
                                    ->placeholder('Nhập địa chỉ email'),
                                Forms\Components\Textarea::make('notes')
                                    ->label('Ghi chú')
                                    ->placeholder('Nhập ghi chú chi tiết về nhà cung cấp...')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Forms\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\Section::make('Thiết lập nhanh')
                                    ->schema([
                                        Forms\Components\Toggle::make('status')
                                            ->label('Trạng thái')
                                            ->default(true)
                                            ->onColor('success')
                                            ->offColor('danger'),
                                    ]),
                            ]),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('STT')
                    ->state(static function (Tables\Contracts\HasTable $livewire, \stdClass $rowLoop): string {
                        return (string) ($rowLoop->iteration);
                    }),
                Tables\Columns\TextColumn::make('code')
                    ->label('MÃ NCC')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->label('TÊN NHÀ CUNG CẤP')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label('PHÒNG GIAO DỊCH / PHÂN LOẠI')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Thịt' => 'danger',
                        'Rau củ' => 'success',
                        'Thực phẩm khô' => 'warning',
                        'Hải sản' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('contact_name')
                    ->label('NGƯỜI ĐẠI DIỆN'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('SỐ ĐIỆN THOẠI'),
                Tables\Columns\TextColumn::make('email')
                    ->label('ĐỊA CHỈ EMAIL'),
                Tables\Columns\TextColumn::make('status')
                    ->label('TRẠNG THÁI')
                    ->badge()
                    ->state(fn ($record) => $record->status ? 'Đang hoạt động' : 'Tạm ngưng')
                    ->color(fn ($state) => $state === 'Đang hoạt động' ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Phân loại')
                    ->options([
                        'Thịt' => 'Thịt',
                        'Rau củ' => 'Rau củ',
                        'Thực phẩm khô' => 'Thực phẩm khô',
                        'Gia vị' => 'Gia vị',
                        'Hải sản' => 'Hải sản',
                        'Tổng hợp' => 'Tổng hợp',
                    ]),
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Trạng thái hoạt động')
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Tạm ngưng'),
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
