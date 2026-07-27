<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftResource\Pages;
use App\Models\Shift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationIcon = 'fa-clock';

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
        return __('catalog.groups.system');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('catalog.shift.section'))
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('catalog.shift.fields.name'))
                                    ->placeholder(__('catalog.shift.placeholders.name'))
                                    ->required()
                                    ->unique(Shift::class, 'name', ignoreRecord: true)
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label(__('catalog.shift.fields.sort_order'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(fn (): int => ((int) Shift::max('sort_order')) + 1)
                                    ->unique(Shift::class, 'sort_order', ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => __('catalog.shift.validation.sort_order_unique'),
                                    ])
                                    ->required(),
                            ]),
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
