<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('supplier.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('supplier.navigation.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('supplier.navigation.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.groups.supply_inventory');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Section::make(__('supplier.form.information'))
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('supplier.fields.name'))
                                    ->required()
                                    ->placeholder(__('supplier.placeholders.name')),
                                Forms\Components\TextInput::make('code')
                                    ->label(__('supplier.fields.code'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder(__('supplier.placeholders.code')),
                                Forms\Components\Select::make('type')
                                    ->label(__('supplier.fields.type'))
                                    ->required()
                                    ->options([
                                        'Thịt' => __('supplier.types.meat'),
                                        'Rau củ' => __('supplier.types.vegetables'),
                                        'Thực phẩm khô' => __('supplier.types.dry_food'),
                                        'Gia vị' => __('supplier.types.spices'),
                                        'Hải sản' => __('supplier.types.seafood'),
                                        'Tổng hợp' => __('supplier.types.general'),
                                    ])
                                    ->placeholder(__('supplier.placeholders.type')),
                                Forms\Components\TextInput::make('contact_name')
                                    ->label(__('supplier.fields.contact_name'))
                                    ->placeholder(__('supplier.placeholders.contact_name')),
                                Forms\Components\TextInput::make('phone')
                                    ->label(__('supplier.fields.phone'))
                                    ->tel()
                                    ->placeholder(__('supplier.placeholders.phone')),
                                Forms\Components\TextInput::make('email')
                                    ->label(__('supplier.fields.email'))
                                    ->email()
                                    ->placeholder(__('supplier.placeholders.email')),
                                Forms\Components\Textarea::make('notes')
                                    ->label(__('supplier.fields.notes'))
                                    ->placeholder(__('supplier.placeholders.notes'))
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Forms\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\Section::make(__('supplier.form.quick_settings'))
                                    ->schema([
                                        Forms\Components\Toggle::make('status')
                                            ->label(__('supplier.fields.active'))
                                            ->default(true)
                                            ->onColor('success')
                                            ->offColor('danger'),
                                    ]),
                            ]),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label(__('supplier.table.index'))
                    ->state(static function (Tables\Contracts\HasTable $livewire, \stdClass $rowLoop): string {
                        return (string) ($rowLoop->iteration);
                    }),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('supplier.table.code'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('supplier.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('supplier.table.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Thịt' => 'danger',
                        'Rau củ' => 'success',
                        'Thực phẩm khô' => 'warning',
                        'Hải sản' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('contact_name')
                    ->label(__('supplier.table.contact_name')),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('supplier.table.phone')),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('supplier.table.email')),
                Tables\Columns\IconColumn::make('status')
                    ->label(__('supplier.table.status'))
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('supplier.fields.type'))
                    ->options([
                        'Thịt' => __('supplier.types.meat'),
                        'Rau củ' => __('supplier.types.vegetables'),
                        'Thực phẩm khô' => __('supplier.types.dry_food'),
                        'Gia vị' => __('supplier.types.spices'),
                        'Hải sản' => __('supplier.types.seafood'),
                        'Tổng hợp' => __('supplier.types.general'),
                    ]),
                Tables\Filters\TernaryFilter::make('status')
                    ->label(__('supplier.filters.active_status'))
                    ->trueLabel(__('supplier.status.active'))
                    ->falseLabel(__('supplier.status.inactive')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
