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

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Kiểm thực 3 bước';

    protected static ?string $modelLabel = 'Nhật ký kiểm thực';

    protected static ?string $pluralModelLabel = 'Kiểm thực 3 bước';

    protected static ?string $navigationGroup = 'VẬN HÀNH BẾP';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->label('Ngày kiểm tra')
                    ->required(),
                Forms\Components\Select::make('shift_id')
                    ->label('Ca phục vụ')
                    ->relationship('shift', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('stage')
                    ->label('Bước kiểm thực')
                    ->required()
                    ->live()
                    ->options([
                        'Bước 1' => 'Bước 1 – Kiểm tra trước chế biến',
                        'Bước 2' => 'Bước 2 – Kiểm tra khi chế biến',
                        'Bước 3' => 'Bước 3 – Kiểm tra trước khi ăn',
                        'Lưu mẫu' => 'Theo dõi lưu mẫu',
                        'Hủy mẫu' => 'Theo dõi hủy mẫu',
                    ]),
                Forms\Components\Select::make('recipe_id')
                    ->label('Món ăn')
                    ->relationship('recipe', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Áp dụng cho kiểm thực theo từng món (Bước 2, 3, lưu mẫu).')
                    ->visible(fn (Forms\Get $get): bool => in_array($get('stage'), ['Bước 2', 'Bước 3', 'Lưu mẫu', 'Hủy mẫu'])),
                Forms\Components\Select::make('status')
                    ->label('Kết luận')
                    ->required()
                    ->options([
                        'Đạt' => 'Đạt',
                        'Không đạt' => 'Không đạt',
                    ])
                    ->default('Đạt'),
                Forms\Components\TextInput::make('inspected_by')
                    ->label('Người thực hiện')
                    ->maxLength(255)
                    ->default(null),

                // Bước 2 – giờ bắt đầu / hoàn thành chế biến, nhiệt độ (ghi nhận THẬT)
                Forms\Components\TimePicker::make('cook_start_at')
                    ->label('Giờ bắt đầu chế biến')
                    ->seconds(false)
                    ->visible(fn (Forms\Get $get): bool => $get('stage') === 'Bước 2'),
                Forms\Components\TimePicker::make('cook_end_at')
                    ->label('Giờ hoàn thành chế biến')
                    ->seconds(false)
                    ->visible(fn (Forms\Get $get): bool => $get('stage') === 'Bước 2'),
                Forms\Components\TextInput::make('temperature')
                    ->label('Nhiệt độ')
                    ->placeholder('VD: 85°C')
                    ->maxLength(50)
                    ->visible(fn (Forms\Get $get): bool => in_array($get('stage'), ['Bước 2', 'Bước 3'])),

                // Bước 3 / Lưu mẫu – người lưu mẫu + thời điểm lưu mẫu (ghi nhận THẬT)
                Forms\Components\TextInput::make('sample_kept_by')
                    ->label('Người lưu mẫu')
                    ->maxLength(255)
                    ->visible(fn (Forms\Get $get): bool => in_array($get('stage'), ['Bước 3', 'Lưu mẫu', 'Hủy mẫu'])),
                Forms\Components\DateTimePicker::make('sample_kept_at')
                    ->label('Thời điểm lưu mẫu')
                    ->seconds(false)
                    ->visible(fn (Forms\Get $get): bool => in_array($get('stage'), ['Bước 3', 'Lưu mẫu', 'Hủy mẫu'])),
                Forms\Components\TextInput::make('sample_code')
                    ->label('Mã số mẫu lưu')
                    ->maxLength(255)
                    ->visible(fn (Forms\Get $get): bool => in_array($get('stage'), ['Lưu mẫu', 'Hủy mẫu'])),
                Forms\Components\TextInput::make('utensil')
                    ->label('Dụng cụ chứa đựng / ăn uống')
                    ->maxLength(255)
                    ->visible(fn (Forms\Get $get): bool => in_array($get('stage'), ['Bước 3', 'Lưu mẫu'])),

                Forms\Components\Textarea::make('notes')
                    ->label('Ghi nhận chi tiết/chỉ tiêu')
                    ->placeholder('Ví dụ: cảm quan tốt, nhiệt độ tủ lưu 4°C...')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Ngày kiểm')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift.name')
                    ->label('Ca')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stage')
                    ->label('Bước kiểm thực')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Kết luận')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Đạt' => 'success',
                        'Không đạt' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('inspected_by')
                    ->label('Người thực hiện')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('shift_id')
                    ->label('Ca phục vụ')
                    ->relationship('shift', 'name'),
                Tables\Filters\SelectFilter::make('stage')
                    ->label('Bước kiểm thực')
                    ->options([
                        'Bước 1' => 'Bước 1',
                        'Bước 2' => 'Bước 2',
                        'Bước 3' => 'Bước 3',
                        'Lưu mẫu' => 'Lưu mẫu',
                        'Hủy mẫu' => 'Hủy mẫu',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Kết luận')
                    ->options([
                        'Đạt' => 'Đạt',
                        'Không đạt' => 'Không đạt',
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
