<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftResource\Pages;
use App\Models\Shift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 9;

    public static function getNavigationLabel(): string
    {
        return __('Cấu hình ca làm việc');
    }

    public static function getModelLabel(): string
    {
        return __('Ca làm việc');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Ca làm việc');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('VẬN HÀNH BẾP');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Tên ca')
                    ->placeholder('Ví dụ: Ca 1')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TimePicker::make('start_time')
                            ->label('Giờ bắt đầu')
                            ->seconds(false)
                            ->required()
                            ->afterStateHydrated(function (Forms\Components\TimePicker $component, ?Shift $record) {
                                if ($record && $record->time_range) {
                                    $parts = explode(' - ', $record->time_range);
                                    if (count($parts) === 2) {
                                        $component->state($parts[0]);
                                    }
                                }
                            })
                            ->dehydrated(false),
                        Forms\Components\TimePicker::make('end_time')
                            ->label('Giờ kết thúc')
                            ->seconds(false)
                            ->required()
                            ->afterStateHydrated(function (Forms\Components\TimePicker $component, ?Shift $record) {
                                if ($record && $record->time_range) {
                                    $parts = explode(' - ', $record->time_range);
                                    if (count($parts) === 2) {
                                        $component->state($parts[1]);
                                    }
                                }
                            })
                            ->dehydrated(false),
                    ]),
                Forms\Components\Hidden::make('time_range')
                    ->dehydrateStateUsing(fn ($state, Forms\Get $get) => $get('start_time') && $get('end_time')
                            ? substr($get('start_time'), 0, 5).' - '.substr($get('end_time'), 0, 5)
                            : null
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('TT')
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
                    ->label('Tên ca')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('time_range')
                    ->label('Khung giờ')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes([
                        'style' => 'font-variant-numeric: tabular-nums;',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->extraAttributes([
                        'style' => 'font-variant-numeric: tabular-nums;',
                    ])
                    ->sortable()
                    ->color('gray'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
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
            'index' => Pages\ManageShifts::route('/'),
        ];
    }
}
