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
    protected static bool $shouldRegisterNavigation = false; // Ẩn menu 'Loại bếp / nhà ăn' khỏi thanh điều hướng Sidebar

    protected static ?string $model = KitchenType::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.groups.area_kitchen');
    }

    public static function getNavigationLabel(): string
    {
        return __('catalog.kitchen_type.label');
    }

    public static function getModelLabel(): string
    {
        return __('catalog.kitchen_type.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('catalog.kitchen_type.label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('catalog.kitchen_type.fields.name'))
                    ->placeholder(__('catalog.kitchen_type.placeholders.name'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('sort')
                    ->label(__('catalog.common.sort_order'))
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('active')
                    ->label(__('catalog.common.in_use'))
                    ->default(true)
                    ->helperText(__('catalog.kitchen_type.helpers.active')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                Tables\Columns\TextColumn::make('name')
                    ->label(__('catalog.kitchen_type.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('sort')
                    ->label(__('catalog.common.sort_order_upper'))
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('catalog.common.in_use_upper'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('kitchens_count')
                    ->label(__('catalog.kitchen_type.table.count'))
                    ->counts('kitchens')
                    ->alignCenter()
                    ->sortable()
                    ->color('primary')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('catalog.common.created_at'))
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
                    ->label(__('catalog.common.in_use')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->before(function (Tables\Actions\DeleteAction $action, $record): void {
                        if ($record->kitchens()->exists()) {
                            Notification::make()
                                ->title(__('catalog.kitchen_type.errors.in_use', ['count' => $record->kitchens()->count()]))
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
                                    ->title(__('catalog.kitchen_type.errors.bulk_in_use', ['names' => $inUse->pluck('name')->implode(', ')]))
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
