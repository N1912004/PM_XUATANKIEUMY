<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AreaResource\Pages;
use App\Models\Area;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Khu vực';

    protected static ?string $pluralModelLabel = 'Khu vực';

    protected static ?string $navigationGroup = 'CHAT NHÓM';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin khu vực')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Tên khu vực')
                                    ->required()
                                    ->placeholder('VD: Đông Nai / Hồ Chí Minh')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('code')
                                    ->label('Mã khu vực')
                                    ->required()
                                    ->placeholder('VD: KV-DN')
                                    ->maxLength(255),
                                Forms\Components\Select::make('manager_id')
                                    ->label('Quản lý phụ trách')
                                    ->relationship('manager', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\Select::make('status')
                                    ->label('Trạng thái')
                                    ->options([
                                        'Đang hoạt động' => 'Đang hoạt động',
                                        'Tạm dừng' => 'Tạm dừng',
                                    ])
                                    ->default('Đang hoạt động')
                                    ->required(),
                            ]),
                        Forms\Components\Textarea::make('notes')
                            ->label('Ghi chú')
                            ->placeholder('Phạm vi vận hành, ca sản xuất, khách hàng chính...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
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
                    ->label('MÃ')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->label('KHU VỰC')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('manager.name')
                    ->label('QUẢN LÝ PHỤ TRÁCH')
                    ->sortable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('kitchens_count')
                    ->label('SỐ NHÀ ĂN / BẾP')
                    ->counts('kitchens')
                    ->badge()
                    ->color('primary')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('TRẠNG THÁI')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Đang hoạt động' => 'success',
                        'Tạm dừng' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Đang hoạt động' => 'Đang hoạt động',
                        'Tạm dừng' => 'Tạm dừng',
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
            'index' => Pages\ListAreas::route('/'),
            'create' => Pages\CreateArea::route('/create'),
            'edit' => Pages\EditArea::route('/{record}/edit'),
        ];
    }
}
