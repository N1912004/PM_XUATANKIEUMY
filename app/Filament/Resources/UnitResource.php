<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?int $navigationSort = 6;

    public static function getNavigationLabel(): string
    {
        return __('Đơn vị tính');
    }

    public static function getModelLabel(): string
    {
        return __('Đơn vị tính');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Danh sách đơn vị tính');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('NGUYÊN LIỆU & KHO');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Tên đơn vị')
                    ->placeholder('Ví dụ: Kg, Quả, Gói...')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('STT')
                    ->state(static function (Tables\Contracts\HasTable $livewire, \stdClass $rowLoop): string {
                        return (string) ($rowLoop->iteration);
                    })
                    ->alignCenter()
                    ->width('56px'),
                Tables\Columns\TextColumn::make('name')
                    ->label('TÊN ĐƠN VỊ')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('NGÀY TẠO')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color('gray'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->before(function (Tables\Actions\DeleteAction $action, $record): void {
                        if ($record->ingredients()->exists()) {
                            Notification::make()
                                ->title('Không thể xóa: đang được '.$record->ingredients()->count().' nguyên liệu sử dụng')
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
                            $inUse = $records->filter(fn ($r) => $r->ingredients()->exists());
                            if ($inUse->isNotEmpty()) {
                                Notification::make()
                                    ->title('Không thể xóa: '.$inUse->pluck('name')->implode(', ').' đang được nguyên liệu sử dụng')
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
            'index' => Pages\ManageUnits::route('/'),
        ];
    }
}
