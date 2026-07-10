<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientTypeResource\Pages;
use App\Models\IngredientType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IngredientTypeResource extends Resource
{
    protected static ?string $model = IngredientType::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('Loại nguyên liệu');
    }

    public static function getModelLabel(): string
    {
        return __('Loại nguyên liệu');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Danh sách loại nguyên liệu');
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
                    ->label('Tên loại nguyên liệu')
                    ->placeholder('Ví dụ: Động vật, Thực vật, Gia vị...')
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
                    ->label('TÊN LOẠI NGUYÊN LIỆU')
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
                        // FK onDelete('set null') — xóa danh mục đang dùng sẽ âm thầm bỏ trống
                        // loại của các nguyên liệu liên quan, nên chặn lại
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
            'index' => Pages\ManageIngredientTypes::route('/'),
        ];
    }
}
