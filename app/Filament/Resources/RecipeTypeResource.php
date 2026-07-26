<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeTypeResource\Pages;
use App\Models\RecipeType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RecipeTypeResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false; // Ẩn menu 'Nhóm món' khỏi thanh điều hướng Sidebar

    protected static ?string $model = RecipeType::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.groups.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('recipe.recipe_type.label');
    }

    public static function getModelLabel(): string
    {
        return __('recipe.recipe_type.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('recipe.recipe_type.label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('recipe.recipe_type.fields.name'))
                    ->placeholder(__('recipe.recipe_type.placeholders.name'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label(__('recipe.recipe_type.fields.description'))
                    ->placeholder(__('recipe.recipe_type.placeholders.description'))
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label(__('recipe.recipe_type.table.index'))
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
                    ->label(__('recipe.recipe_type.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('recipe.recipe_type.table.description'))
                    ->limit(50)
                    ->color('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('recipe.recipe_type.table.created_at'))
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
                        if ($record->recipes()->exists()) {
                            Notification::make()
                                ->title(__('recipe.recipe_type.errors.in_use', ['count' => $record->recipes()->count()]))
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
                            $inUse = $records->filter(fn ($r) => $r->recipes()->exists());
                            if ($inUse->isNotEmpty()) {
                                Notification::make()
                                    ->title(__('recipe.recipe_type.errors.bulk_in_use', ['names' => $inUse->pluck('name')->implode(', ')]))
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
            'index' => Pages\ListRecipeTypes::route('/'),
            'create' => Pages\CreateRecipeType::route('/create'),
            'edit' => Pages\EditRecipeType::route('/{record}/edit'),
        ];
    }
}
