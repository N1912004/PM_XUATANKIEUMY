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
    protected static bool $shouldRegisterNavigation = true; // Hiện lại menu 'Ca làm việc' trên thanh điều hướng Sidebar

    protected static ?string $model = Shift::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 9;

    public static function getNavigationLabel(): string
    {
        return __('catalog.shift.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('catalog.shift.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('catalog.shift.label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.groups.hr');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('catalog.shift.fields.name'))
                    ->placeholder(__('catalog.shift.placeholders.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort_order')
                    ->label(__('catalog.shift.fields.sort_order'))
                    ->numeric()
                    ->minValue(1)
                    ->default(fn (): int => ((int) Shift::max('sort_order')) + 1)
                    ->unique(Shift::class, 'sort_order', ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'Thứ tự ưu tiên này đã trùng với một ca khác, vui lòng nhập số khác.',
                    ])
                    ->required(),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TimePicker::make('time_from')
                            ->label(__('catalog.shift.fields.time_from'))
                            ->seconds(false)
                            ->required(),
                        Forms\Components\TimePicker::make('time_to')
                            ->label(__('catalog.shift.fields.time_to'))
                            ->seconds(false)
                            ->required()
                            ->different('time_from')
                            ->rules([
                                fn (Forms\Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                    $durationMinutes = Shift::durationMinutesBetween($get('time_from'), $value);

                                    if ($durationMinutes !== null && $durationMinutes > 12 * 60) {
                                        $fail(__('catalog.shift.validation.max_duration'));
                                    }
                                },
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label(__('catalog.common.index'))
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
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('catalog.shift.table.sort_order'))
                    ->sortable()
                    ->alignCenter()
                    ->width('120px'),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('catalog.shift.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('time_range')
                    ->label(__('catalog.shift.table.time_range'))
                    ->searchable()
                    ->sortable()
                    ->extraAttributes([
                        'style' => 'font-variant-numeric: tabular-nums;',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('catalog.common.created_at'))
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
            'index' => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'edit' => Pages\EditShift::route('/{record}/edit'),
        ];
    }
}
