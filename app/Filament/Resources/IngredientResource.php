<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientResource\Pages;
use App\Models\Ingredient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('ingredient.navigation.ingredient_plural');
    }

    public static function getModelLabel(): string
    {
        return __('ingredient.navigation.ingredient');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ingredient.navigation.ingredient_plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('NGUYÊN LIỆU & KHO');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('ingredient.form.section'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('ingredient.form.name'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->validationMessages([
                                'unique' => __('ingredient.validation.name_unique'),
                            ])
                            ->placeholder(__('ingredient.form.name_placeholder')),
                        Forms\Components\TextInput::make('code')
                            ->label(__('ingredient.form.code'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->regex('/^[A-Za-z0-9_-]+$/')
                            ->validationMessages([
                                'unique' => __('ingredient.validation.code_unique'),
                                'regex' => __('ingredient.validation.code_regex'),
                            ])
                            ->placeholder(__('ingredient.form.code_placeholder')),
                        Forms\Components\Select::make('unit_id')
                            ->label(__('ingredient.form.unit'))
                            ->relationship('unitRelation', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('ingredient.form.unit_name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique('units', 'name'),
                            ])
                            ->placeholder(__('ingredient.form.unit_placeholder')),
                        Forms\Components\Select::make('ingredient_type_id')
                            ->label(__('ingredient.form.type'))
                            ->relationship('typeRelation', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('ingredient.form.type_name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique('ingredient_types', 'name'),
                            ])
                            ->placeholder(__('ingredient.form.type_placeholder')),
                        Forms\Components\TextInput::make('reference_price')
                            ->label(__('ingredient.form.reference_price'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('VND')
                            ->placeholder(__('ingredient.form.reference_price_placeholder'))
                            ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                            ->stripCharacters(['.', ','])
                            ->formatStateUsing(fn ($state) => $state ? round((float) $state) : 0)
                            ->dehydrateStateUsing(fn ($state) => $state ? (float) str_replace([',', '.'], '', (string) $state) : 0),
                        Forms\Components\Toggle::make('status')
                            ->label(__('ingredient.form.status'))
                            ->default(true)
                            ->onColor('primary')
                            ->offColor('danger')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\ViewEntry::make('header_card')
                            ->view('filament.resources.ingredients.view-header')
                            ->columnSpanFull(),
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('code')
                                    ->label(__('ingredient.form.code'))
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('name')
                                    ->label(__('ingredient.form.name'))
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('unit')
                                    ->label(__('ingredient.form.unit'))
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('type')
                                    ->label(__('ingredient.form.type'))
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('reference_price')
                                    ->label(__('ingredient.form.reference_price'))
                                    ->money('VND')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label(__('ingredient.form.status'))
                                    ->badge()
                                    ->color(fn ($state) => $state ? 'success' : 'danger')
                                    ->formatStateUsing(fn ($state) => $state ? __('ingredient.status.active') : __('ingredient.status.inactive'))
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('suppliers.name')
                                    ->label(__('ingredient.infolist.linked_suppliers'))
                                    ->badge()
                                    ->separator(',')
                                    ->weight('bold')
                                    ->columnSpanFull(),
                                Infolists\Components\RepeatableEntry::make('suppliers')
                                    ->label(__('ingredient.infolist.quotes_table'))
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label(__('ingredient.infolist.supplier_name'))
                                            ->weight('semibold'),
                                        Infolists\Components\TextEntry::make('code')
                                            ->label(__('ingredient.infolist.supplier_code')),
                                        Infolists\Components\TextEntry::make('pivot.reference_price')
                                            ->label(__('ingredient.infolist.supply_price'))
                                            ->money('VND')
                                            ->color('primary')
                                            ->weight('bold'),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),
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
                Tables\Columns\TextColumn::make('code')
                    ->label(__('ingredient.table.code'))
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->weight('semibold')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('ingredient.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('suppliers_display')
                    ->label(__('ingredient.table.supplier'))
                    ->badge()
                    ->state(function ($record) {
                        $names = $record->suppliers->pluck('name')->toArray();
                        $total = count($names);
                        if ($total <= 2) {
                            return $names;
                        }

                        return [...array_slice($names, 0, 2), __('ingredient.table.more_suppliers', ['count' => $total - 2])];
                    })
                    // Cột ảo (state) không map cột DB → phải tự viết query tìm qua quan hệ n-n suppliers.
                    // Filament tự OR khối này với search của code/name trong ô tìm kiếm chung.
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('suppliers', fn (Builder $q) => $q->where('name', 'like', "%{$search}%")))
                    ->color(fn (string $state): string => str_starts_with($state, '+') ? 'gray' : 'success')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('unitRelation.name')
                    ->label(__('ingredient.table.unit'))
                    ->size('sm'),
                Tables\Columns\TextColumn::make('typeRelation.name')
                    ->label(__('ingredient.table.type'))
                    ->size('sm'),
                Tables\Columns\TextColumn::make('reference_price')
                    ->label(__('ingredient.table.reference_price'))
                    ->html()
                    ->formatStateUsing(function ($state) {
                        if ($state === null) return '—';
                        $formatted = number_format($state, 0, ',', '.');
                        return "<strong>{$formatted}</strong><span style=\"font-size: 10px; font-weight: 500; color: #94a3b8; margin-left: 2px;\">đ</span>";
                    })
                    ->alignRight()
                    ->extraAttributes([
                        'style' => 'font-variant-numeric: tabular-nums;',
                    ])
                    ->sortable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('ingredient.table.status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('ingredient.status.active') : __('ingredient.status.inactive'))
                    ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label(__('ingredient.filter.supplier'))
                    ->relationship('suppliers', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('unit_id')
                    ->label(__('ingredient.filter.unit'))
                    ->relationship('unitRelation', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('ingredient_type_id')
                    ->label(__('ingredient.filter.type'))
                    ->relationship('typeRelation', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton(),
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
                Tables\Actions\RestoreAction::make()
                    ->iconButton(),
                Tables\Actions\ForceDeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIngredients::route('/'),
            'create' => Pages\CreateIngredient::route('/create'),
            'view' => Pages\ViewIngredient::route('/{record}'),
            'edit' => Pages\EditIngredient::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
