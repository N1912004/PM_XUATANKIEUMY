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

    protected static ?string $navigationGroup = 'XUẤT ĂN';

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
                    ->options([
                        'Bước 1' => 'Bước 1 – Kiểm tra trước chế biến',
                        'Bước 2' => 'Bước 2 – Kiểm tra khi chế biến',
                        'Bước 3' => 'Bước 3 – Kiểm tra trước khi ăn',
                        'Lưu mẫu' => 'Theo dõi lưu mẫu',
                        'Hủy mẫu' => 'Theo dõi hủy mẫu',
                    ]),
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
