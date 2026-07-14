<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CatalogResource\Pages;
use App\Models\Catalog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class CatalogResource extends Resource
{
    protected static ?string $model = Catalog::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'HỆ THỐNG';

    protected static ?string $navigationLabel = 'Danh mục cấu hình';

    protected static ?string $modelLabel = 'mục danh mục';

    protected static ?string $pluralModelLabel = 'danh mục cấu hình';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Mục danh mục')
                ->description('Danh mục nghiệp vụ sửa được ngay trên hệ thống — không cần lập trình lại. Trạng thái/quy trình cố định (trạng thái PO, kiểm thực, loại giao dịch kho) không nằm ở đây.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('group')
                        ->label('Nhóm danh mục')
                        ->options(Catalog::GROUP_LABELS)
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('name')
                        ->label('Giá trị')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule, Forms\Get $get) => $rule->where('group', $get('group')))
                        ->helperText('Không trùng trong cùng một nhóm.'),
                    Forms\Components\TextInput::make('sort')
                        ->label('Thứ tự hiển thị')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Toggle::make('active')
                        ->label('Đang sử dụng')
                        ->default(true)
                        ->helperText('Tắt thì không còn xuất hiện ở các form, dữ liệu cũ giữ nguyên.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('STT')
                    ->state(static fn (HasTable $livewire, \stdClass $rowLoop): string => (string) $rowLoop->iteration),
                Tables\Columns\TextColumn::make('group')
                    ->label('NHÓM')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Catalog::GROUP_LABELS[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('GIÁ TRỊ')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('sort')
                    ->label('THỨ TỰ')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('ĐANG DÙNG')
                    ->boolean(),
            ])
            ->defaultSort('group')
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label('Nhóm danh mục')
                    ->options(Catalog::GROUP_LABELS),
                Tables\Filters\TernaryFilter::make('active')->label('Đang sử dụng'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCatalogs::route('/'),
            'create' => Pages\CreateCatalog::route('/create'),
            'edit' => Pages\EditCatalog::route('/{record}/edit'),
        ];
    }
}
