<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\BelongsToKitchen;
use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    use BelongsToKitchen;

    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Lập thực đơn';

    protected static ?string $modelLabel = 'Thực đơn';

    protected static ?string $pluralModelLabel = 'Lập thực đơn';

    protected static ?string $navigationGroup = 'VẬN HÀNH BẾP';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::kitchenSelect(),
                Forms\Components\DatePicker::make('date')
                    ->label('Ngày áp dụng')
                    ->required(),
                Forms\Components\Select::make('shift_id')
                    ->label('Ca làm việc')
                    ->relationship('shift', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('recipe_id')
                    ->label('Món ăn')
                    ->relationship('recipe', 'name', fn ($query) => $query->where('status', 'active'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('estimated_portions')
                    ->label('Số suất ăn dự kiến')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->required()
                    ->options([
                        'draft' => 'Nháp (Draft)',
                        'sent' => 'Đã gửi khách hàng (Sent)',
                        'locked' => 'Đã chốt (Locked)',
                    ])
                    ->default('draft'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                static::kitchenColumn(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Ngày áp dụng')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift.name')
                    ->label('Ca làm việc')
                    ->sortable(),
                Tables\Columns\TextColumn::make('recipe.name')
                    ->label('Tên món ăn')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('recipe.type')
                    ->label('Nhóm món')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Món mặn', 'Món 1' => 'danger',
                        'Món xào', 'Rau xào/Luộc' => 'success',
                        'Món canh' => 'info',
                        'Món chay' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('estimated_portions')
                    ->label('Số suất ăn dự kiến')
                    ->numeric()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'sent' => 'info',
                        'locked' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Nháp',
                        'sent' => 'Đã gửi khách hàng',
                        'locked' => 'Đã chốt',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kitchen_id')
                    ->label('Bếp ăn')
                    ->relationship('kitchen', 'name'),
                Tables\Filters\SelectFilter::make('shift_id')
                    ->label('Ca làm việc')
                    ->relationship('shift', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'draft' => 'Nháp',
                        'sent' => 'Đã gửi khách hàng',
                        'locked' => 'Đã chốt',
                    ]),
            ])
            ->actions([
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
            MenuResource\RelationManagers\AuditLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
