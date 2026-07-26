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
    protected static bool $shouldRegisterNavigation = false; // Ẩn menu 'Loại nguyên liệu' khỏi thanh điều hướng Sidebar

    protected static ?string $model = IngredientType::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('ingredient.navigation.type');
    }

    public static function getModelLabel(): string
    {
        return __('ingredient.navigation.type');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ingredient.navigation.type_plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.groups.system');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('ingredient.type.name'))
                    ->placeholder(__('ingredient.type.name_placeholder'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label(__('ingredient.table.index'))
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
                    ->label(__('ingredient.type.table_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('ingredient.unit.created_at'))
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
                    ->iconButton()
                    ->before(function (Tables\Actions\DeleteAction $action, $record): void {
                        // FK onDelete('set null') — xóa danh mục đang dùng sẽ âm thầm bỏ trống
                        // loại của các nguyên liệu liên quan, nên chặn lại
                        if ($record->ingredients()->exists()) {
                            Notification::make()
                                ->title(__('ingredient.delete.in_use', ['count' => $record->ingredients()->count()]))
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
                                    ->title(__('ingredient.delete.in_use_names', ['names' => $inUse->pluck('name')->implode(', ')]))
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
            'index' => Pages\ListIngredientTypes::route('/'),
            'create' => Pages\CreateIngredientType::route('/create'),
            'edit' => Pages\EditIngredientType::route('/{record}/edit'),
        ];
    }
}
