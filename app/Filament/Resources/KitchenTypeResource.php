<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KitchenTypeResource\Pages;
use App\Models\KitchenType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KitchenTypeResource extends Resource
{
    protected static ?string $model = KitchenType::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('KHU VỰC & NHÀ ĂN');
    }

    public static function getNavigationLabel(): string
    {
        return __('Loại bếp / nhà ăn');
    }

    public static function getModelLabel(): string
    {
        return __('Loại bếp / nhà ăn');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Loại bếp / nhà ăn');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Tên loại')
                    ->placeholder('Nhập tên loại bếp / nhà ăn (VD: Bếp sản xuất, Nhà ăn phục vụ...)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('sort')
                    ->label('Thứ tự hiển thị')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('active')
                    ->label('Đang sử dụng')
                    ->default(true)
                    ->helperText('Tắt thì không còn xuất hiện ở các form chọn loại, dữ liệu cũ giữ nguyên.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('STT')
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
                Tables\Columns\TextColumn::make('name')
                    ->label('TÊN LOẠI')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('sort')
                    ->label('THỨ TỰ')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('active')
                    ->label('ĐANG DÙNG')
                    ->boolean(),
                Tables\Columns\TextColumn::make('kitchens_count')
                    ->label('SỐ NHÀ ĂN / BẾP')
                    ->counts('kitchens')
                    ->alignCenter()
                    ->sortable()
                    ->color('primary')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('NGÀY TẠO')
                    ->dateTime('d/m/Y H:i')
                    ->extraAttributes([
                        'style' => 'font-variant-numeric: tabular-nums;',
                    ])
                    ->sortable()
                    ->color('gray'),
            ])
            ->defaultSort('sort')
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Đang sử dụng'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->before(function (Tables\Actions\DeleteAction $action, $record): void {
                        if ($record->kitchens()->exists()) {
                            Notification::make()
                                ->title('Không thể xóa loại này vì đang có '.$record->kitchens()->count().' nhà ăn/bếp sử dụng.')
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records): void {
                            $inUse = $records->filter(fn ($r) => $r->kitchens()->exists());
                            if ($inUse->isNotEmpty()) {
                                Notification::make()
                                    ->title('Không thể xóa hàng loạt. Các loại sau đang được sử dụng: '.$inUse->pluck('name')->implode(', '))
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKitchenTypes::route('/'),
            'create' => Pages\CreateKitchenType::route('/create'),
            'edit' => Pages\EditKitchenType::route('/{record}/edit'),
        ];
    }
}
