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

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('Danh sách nguyên liệu');
    }

    public static function getModelLabel(): string
    {
        return __('Nguyên liệu');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Danh sách nguyên liệu');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('NGUYÊN LIỆU & KHO');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin nguyên liệu')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên nguyên liệu')
                            ->required()
                            ->placeholder('Nhập tên nguyên liệu'),
                        Forms\Components\TextInput::make('code')
                            ->label('Mã nguyên liệu')
                            ->unique(ignoreRecord: true)
                            ->placeholder('Nhập mã nguyên liệu'),
                        Forms\Components\Select::make('unit_id')
                            ->label(__('Đơn vị'))
                            ->relationship('unitRelation', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Tên đơn vị')
                                    ->required()
                                    ->unique('units', 'name'),
                            ])
                            ->placeholder(__('Chọn đơn vị')),
                        Forms\Components\Select::make('ingredient_type_id')
                            ->label(__('Loại nguyên liệu'))
                            ->relationship('typeRelation', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Tên loại nguyên liệu')
                                    ->required()
                                    ->unique('ingredient_types', 'name'),
                            ])
                            ->placeholder(__('Chọn loại nguyên liệu')),
                        Forms\Components\TextInput::make('reference_price')
                            ->label('Đơn giá tham chiếu gốc')
                            ->required()
                            ->default(0)
                            ->prefix('VND')
                            ->placeholder('Nhập đơn giá tham chiếu cơ bản')
                            ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                            ->stripCharacters(['.', ','])
                            ->formatStateUsing(fn ($state) => $state ? round((float) $state) : 0)
                            ->dehydrateStateUsing(fn ($state) => $state ? (float) str_replace([',', '.'], '', (string) $state) : 0),
                        Forms\Components\Toggle::make('status')
                            ->label('Trạng thái')
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
                                    ->label('Mã nguyên liệu')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Tên nguyên liệu')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('unit')
                                    ->label('Đơn vị')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('type')
                                    ->label('Loại nguyên liệu')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('reference_price')
                                    ->label('Đơn giá tham chiếu gốc')
                                    ->money('VND')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Trạng thái')
                                    ->badge()
                                    ->color(fn ($state) => $state ? 'success' : 'danger')
                                    ->formatStateUsing(fn ($state) => $state ? 'Đang hoạt động' : 'Ngừng hoạt động')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('suppliers.name')
                                    ->label('Nhà cung cấp liên kết')
                                    ->badge()
                                    ->separator(',')
                                    ->weight('bold')
                                    ->columnSpanFull(),
                                Infolists\Components\RepeatableEntry::make('suppliers')
                                    ->label('Bảng báo giá của các nhà cung cấp')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Tên nhà cung cấp')
                                            ->weight('semibold'),
                                        Infolists\Components\TextEntry::make('code')
                                            ->label('Mã nhà cung cấp'),
                                        Infolists\Components\TextEntry::make('pivot.reference_price')
                                            ->label('Đơn giá cung cấp')
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
                    ->label('STT')
                    ->state(static function (Tables\Contracts\HasTable $livewire, \stdClass $rowLoop): string {
                        return (string) ($rowLoop->iteration);
                    })
                    ->alignCenter()
                    ->width('56px'),
                Tables\Columns\TextColumn::make('code')
                    ->label('MÃ NGUYÊN LIỆU')
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->weight('semibold')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('name')
                    ->label('TÊN NGUYÊN LIỆU')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('suppliers_display')
                    ->label('TÊN NCC')
                    ->badge()
                    ->state(function ($record) {
                        $names = $record->suppliers->pluck('name')->toArray();
                        $total = count($names);
                        if ($total <= 2) {
                            return $names;
                        }

                        return [...array_slice($names, 0, 2), '+'.($total - 2).' NCC'];
                    })
                    // Cột ảo (state) không map cột DB → phải tự viết query tìm qua quan hệ n-n suppliers.
                    // Filament tự OR khối này với search của code/name trong ô tìm kiếm chung.
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('suppliers', fn (Builder $q) => $q->where('name', 'like', "%{$search}%")))
                    ->color(fn (string $state): string => str_starts_with($state, '+') ? 'gray' : 'success')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('unitRelation.name')
                    ->label('ĐƠN VỊ')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('typeRelation.name')
                    ->label('LOẠI NL')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('reference_price')
                    ->label('ĐƠN GIÁ THAM CHIẾU')
                    ->money('VND')
                    ->sortable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('status')
                    ->label('TRẠNG THÁI')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Đang hoạt động' : 'Ngừng hoạt động')
                    ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
            ])
            ->defaultSort('id', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Nhà cung cấp')
                    ->relationship('suppliers', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('unit_id')
                    ->label(__('Đơn vị'))
                    ->relationship('unitRelation', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('ingredient_type_id')
                    ->label(__('Loại NL'))
                    ->relationship('typeRelation', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton(),
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
}
