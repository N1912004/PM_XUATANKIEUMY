<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PositionResource\Pages;
use App\Models\Position;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return __('NHÂN SỰ');
    }

    public static function getNavigationLabel(): string
    {
        return __('Chức vụ');
    }

    public static function getModelLabel(): string
    {
        return __('Chức vụ');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Chức vụ');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Tên chức vụ')
                    ->placeholder('Nhập tên chức vụ (VD: Tổ trưởng bếp, Chuyên viên...)')
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
                    ->helperText('Tắt thì không còn xuất hiện ở các form chọn chức vụ, dữ liệu cũ giữ nguyên.'),
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
                    ->label('TÊN CHỨC VỤ')
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
                Tables\Columns\TextColumn::make('employees_count')
                    ->label('SỐ NHÂN VIÊN')
                    ->counts('employees')
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
                        if ($record->employees()->exists()) {
                            Notification::make()
                                ->title('Không thể xóa chức vụ này vì đang có '.$record->employees()->count().' nhân viên trực thuộc.')
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
                            $inUse = $records->filter(fn ($r) => $r->employees()->exists());
                            if ($inUse->isNotEmpty()) {
                                Notification::make()
                                    ->title('Không thể xóa hàng loạt. Các chức vụ sau đang có nhân viên: '.$inUse->pluck('name')->implode(', '))
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
            'index' => Pages\ListPositions::route('/'),
            'create' => Pages\CreatePosition::route('/create'),
            'edit' => Pages\EditPosition::route('/{record}/edit'),
        ];
    }
}
