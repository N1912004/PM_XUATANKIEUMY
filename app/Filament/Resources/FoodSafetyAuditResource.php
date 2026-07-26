<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FoodSafetyAuditResource\Pages;
use App\Models\FoodSafetyAudit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FoodSafetyAuditResource extends Resource
{
    protected static ?string $model = FoodSafetyAudit::class;

    protected static ?string $navigationIcon = 'fa-clipboard-check';

    protected static ?int $navigationSort = 8;

    public static function getNavigationLabel(): string
    {
        return __('food_safety.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('food_safety.navigation.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('food_safety.navigation.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.groups.catering');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->label(__('food_safety.fields.audit_date'))
                    ->required(),
                Forms\Components\Select::make('shift_id')
                    ->label(__('food_safety.fields.shift'))
                    ->relationship('shift', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('stage')
                    ->label(__('food_safety.fields.stage'))
                    ->required()
                    ->live()
                    ->options([
                        'Bước 1' => __('food_safety.stages.step_1_long'),
                        'Bước 2' => __('food_safety.stages.step_2_long'),
                        'Bước 3' => __('food_safety.stages.step_3_long'),
                        'Lưu mẫu' => __('food_safety.stages.sample_storage_long'),
                        'Hủy mẫu' => __('food_safety.stages.sample_disposal_long'),
                    ]),
                Forms\Components\Select::make('recipe_id')
                    ->label(__('food_safety.fields.dish'))
                    ->relationship('recipe', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText(__('food_safety.help.dish'))
                    ->visible(fn (Forms\Get $get): bool => in_array($get('stage'), ['Bước 2', 'Bước 3', 'Lưu mẫu', 'Hủy mẫu'])),
                Forms\Components\Select::make('status')
                    ->label(__('food_safety.fields.conclusion'))
                    ->required()
                    ->options([
                        'pending' => __('food_safety.status.pending'),
                        'passed' => __('food_safety.status.passed'),
                        'failed' => __('food_safety.status.failed'),
                    ])
                    ->default('pending'),
                Forms\Components\TextInput::make('inspected_by')
                    ->label(__('food_safety.fields.inspector'))
                    ->maxLength(255)
                    ->default(null),

                // Bước 2 – giờ bắt đầu / hoàn thành chế biến, nhiệt độ (ghi nhận THẬT)
                Forms\Components\TimePicker::make('cook_start_at')
                    ->label(__('food_safety.fields.cook_start'))
                    ->seconds(false)
                    ->visible(fn (Forms\Get $get): bool => $get('stage') === 'Bước 2'),
                Forms\Components\TimePicker::make('cook_end_at')
                    ->label(__('food_safety.fields.cook_end'))
                    ->seconds(false)
                    ->visible(fn (Forms\Get $get): bool => $get('stage') === 'Bước 2'),
                Forms\Components\TextInput::make('temperature')
                    ->label(__('food_safety.fields.temperature'))
                    ->placeholder('VD: 85°C')
                    ->maxLength(50)
                    ->visible(fn (Forms\Get $get): bool => in_array($get('stage'), ['Bước 2', 'Bước 3'])),

                // Bước 3 / Lưu mẫu – người lưu mẫu + thời điểm lưu mẫu (ghi nhận THẬT)
                Forms\Components\TextInput::make('sample_kept_by')
                    ->label(__('food_safety.fields.sample_keeper'))
                    ->maxLength(255)
                    ->visible(fn (Forms\Get $get): bool => in_array($get('stage'), ['Bước 3', 'Lưu mẫu', 'Hủy mẫu'])),
                Forms\Components\DateTimePicker::make('sample_kept_at')
                    ->label(__('food_safety.fields.sample_kept_at'))
                    ->seconds(false)
                    ->visible(fn (Forms\Get $get): bool => in_array($get('stage'), ['Bước 3', 'Lưu mẫu', 'Hủy mẫu'])),
                Forms\Components\TextInput::make('sample_code')
                    ->label(__('food_safety.fields.sample_code'))
                    ->maxLength(255)
                    ->visible(fn (Forms\Get $get): bool => in_array($get('stage'), ['Lưu mẫu', 'Hủy mẫu'])),
                Forms\Components\TextInput::make('utensil')
                    ->label(__('food_safety.fields.utensils'))
                    ->maxLength(255)
                    ->visible(fn (Forms\Get $get): bool => in_array($get('stage'), ['Bước 3', 'Lưu mẫu'])),

                Forms\Components\Textarea::make('notes')
                    ->label(__('food_safety.fields.notes'))
                    ->placeholder(__('food_safety.placeholders.notes'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label(__('food_safety.table.audit_date'))
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift.name')
                    ->label('Ca')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stage')
                    ->label(__('food_safety.table.stage'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('food_safety.table.conclusion'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('food_safety.status.pending'),
                        'passed' => __('food_safety.status.passed'),
                        'failed' => __('food_safety.status.failed'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'passed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('inspected_by')
                    ->label(__('food_safety.table.inspector'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('shift_id')
                    ->label(__('food_safety.fields.shift'))
                    ->relationship('shift', 'name'),
                Tables\Filters\SelectFilter::make('stage')
                    ->label(__('food_safety.fields.stage'))
                    ->options([
                        'Bước 1' => __('food_safety.stages.step_1'),
                        'Bước 2' => __('food_safety.stages.step_2'),
                        'Bước 3' => __('food_safety.stages.step_3'),
                        'Lưu mẫu' => __('food_safety.stages.sample_storage'),
                        'Hủy mẫu' => __('food_safety.stages.sample_disposal'),
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('food_safety.fields.conclusion'))
                    ->options([
                        'pending' => __('food_safety.status.pending'),
                        'passed' => __('food_safety.status.passed'),
                        'failed' => __('food_safety.status.failed'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFoodSafetyAudits::route('/'),
            'create' => Pages\CreateFoodSafetyAudit::route('/create'),
            'edit' => Pages\EditFoodSafetyAudit::route('/{record}/edit'),
        ];
    }
}
