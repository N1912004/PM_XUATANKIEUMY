<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientResource\Pages;
use App\Models\Ingredient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
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
        return __('catalog.groups.ingredients_inventory');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        // Left Column: Main Form Fields (span 2)
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make(__('ingredient.form.section'))
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label(__('ingredient.form.name'))
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->validationMessages([
                                                'unique' => __('ingredient.validation.name_unique'),
                                            ])
                                            ->placeholder(__('ingredient.form.name_placeholder')),
                                        Forms\Components\TextInput::make('code')
                                            ->label(__('ingredient.form.code'))
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(50)
                                            ->regex('/^[A-Za-z0-9_-]+$/')
                                            ->live(onBlur: true)
                                            ->validationMessages([
                                                'unique' => __('ingredient.validation.code_unique'),
                                                'regex' => __('ingredient.validation.code_regex'),
                                            ])
                                            ->placeholder(__('ingredient.form.code_placeholder')),
                                        Forms\Components\Select::make('supplier_id')
                                            ->label(__('ingredient.filter.supplier'))
                                            ->relationship('supplier', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->placeholder(__('ingredient.form.supplier_placeholder')),
                                        Forms\Components\Select::make('unit_id')
                                            ->label(__('ingredient.form.unit'))
                                            ->relationship('unitRelation', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->placeholder(__('ingredient.form.unit_placeholder')),
                                        Forms\Components\Select::make('ingredient_type_id')
                                            ->label(__('ingredient.form.type'))
                                            ->relationship('typeRelation', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->columnSpanFull()
                                            ->placeholder(__('ingredient.form.type_placeholder')),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpan(['lg' => 2]),

                        // Right Sidebar: Quick Settings, Summary & Notice (span 1)
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make(__('ingredient.form.quick_settings'))
                                    ->schema([
                                        Forms\Components\ViewField::make('status')
                                            ->label(__('ingredient.form.status'))
                                            ->hiddenLabel()
                                            ->default(true)
                                            ->view('filament.resources.ingredients.form-status-toggle'),
                                    ]),

                                Forms\Components\Section::make(__('ingredient.form.summary'))
                                    ->schema([
                                        Forms\Components\Placeholder::make('form_summary')
                                            ->hiddenLabel()
                                            ->content(fn (Forms\Get $get) => view('filament.resources.ingredients.form-summary', [
                                                'get' => $get,
                                            ])),
                                    ]),

                                Forms\Components\Placeholder::make('form_notice')
                                    ->hiddenLabel()
                                    ->content(fn () => view('filament.resources.ingredients.form-notice')),
                            ])
                            ->columnSpan(['lg' => 1]),
                    ]),
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
                    ->alignStart()
                    ->color('primary')
                    ->weight('semibold')
                    ->size('sm')
                    // Mã thật dài ~13 ký tự (p90 = 14, max thường 14); chỉ rút gọn những mã bất
                    // thường dài hơn 18 ký tự để một dòng xấu không kéo rộng cả cột.
                    ->limit(18)
                    ->tooltip(fn ($state): ?string => is_string($state) && mb_strlen($state) > 18 ? $state : null)
                    ->width('180px'),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('ingredient.table.name'))
                    ->searchable()
                    ->sortable()
                    ->alignStart()
                    ->weight('bold')
                    ->wrap(),
                Tables\Columns\TextColumn::make('suppliers_display')
                    ->label(__('ingredient.table.supplier'))
                    // Chữ thường, không dùng ->badge(): padding ngang của badge đẩy chữ thụt vào
                    // 8px so với tiêu đề cột, làm cột này lệch hẳn so với các cột còn lại.
                    ->state(function ($record) {
                        $names = $record->suppliers->pluck('name')->toArray();
                        $total = count($names);
                        if ($total > 2) {
                            $names = [...array_slice($names, 0, 2), __('ingredient.table.more_suppliers', ['count' => $total - 2])];
                        }

                        return implode(', ', $names);
                    })
                    // Cột ảo (state) không map cột DB → phải tự viết query tìm qua quan hệ n-n suppliers.
                    // Filament tự OR khối này với search của code/name trong ô tìm kiếm chung.
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('suppliers', fn (Builder $q) => $q->where('name', 'like', "%{$search}%")))
                    ->alignStart()
                    ->size('sm')
                    ->wrap(),
                Tables\Columns\TextColumn::make('unitRelation.name')
                    ->label(__('ingredient.table.unit'))
                    ->alignStart()
                    ->size('sm')
                    ->width('110px'),
                Tables\Columns\TextColumn::make('typeRelation.name')
                    ->label(__('ingredient.table.type'))
                    ->alignStart()
                    ->size('sm')
                    ->width('140px'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('ingredient.table.status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('ingredient.status.active') : __('ingredient.status.inactive'))
                    ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->alignStart()
                    ->width('160px'),
            ])
            ->defaultSort('id', 'desc')
            ->searchPlaceholder(__('ingredient.table.search_placeholder'))
            ->paginated([10, 20, 50])
            ->defaultPaginationPageOption(10)
            ->filters([
                Tables\Filters\SelectFilter::make('suppliers')
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
                Tables\Filters\TrashedFilter::make()
                    ->label(__('ingredient.filter.trashed')),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->emptyStateIcon('heroicon-o-magnifying-glass')
            ->emptyStateHeading(__('ingredient.table.empty_heading'))
            ->emptyStateDescription(__('ingredient.table.empty_description'))
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
            ->actionsColumnLabel(__('ingredient.table.action'))
            ->actionsPosition(Tables\Enums\ActionsPosition::AfterColumns)
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
            // Eager-load suppliers: cột "Nhà cung cấp" đọc $record->suppliers trong mỗi dòng,
            // không nạp sẵn thì mỗi dòng bảng phát sinh thêm 1 query (N+1).
            ->with('suppliers')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
