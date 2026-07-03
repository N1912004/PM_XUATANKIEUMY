<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Nhân viên';

    protected static ?string $pluralModelLabel = 'Nhân viên';

    protected static ?string $navigationGroup = 'NHÂN SỰ';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        // Left Column: Avatar
                        Forms\Components\Section::make('Ảnh đại diện')
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\FileUpload::make('avatar_url')
                                    ->label('Ảnh hồ sơ cá nhân')
                                    ->image()
                                    ->avatar()
                                    ->disk('public')
                                    ->directory('avatars')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '1:1',
                                    ])
                                    ->imageCropAspectRatio('1:1')
                                    ->imageResizeTargetWidth('300')
                                    ->imageResizeTargetHeight('300')
                                    ->imagePreviewHeight('200')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(2048)
                                    ->helperText('Tải lên ảnh JPG, PNG hoặc WebP. Tối đa 2MB.')
                                    ->uploadButtonPosition('center')
                                    ->uploadProgressIndicatorPosition('center')
                                    ->removeUploadedFileButtonPosition('center')
                                    ->alignCenter(),
                            ]),

                        // Right Column: Details
                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\Section::make('Thông tin cá nhân')
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Họ và tên')
                                            ->required()
                                            ->placeholder('VD: Nguyễn Văn An')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('code')
                                            ->label('Mã nhân viên')
                                            ->required()
                                            ->placeholder('VD: NV001')
                                            ->maxLength(255),
                                        Forms\Components\Select::make('gender')
                                            ->label('Giới tính')
                                            ->options([
                                                'Nam' => 'Nam',
                                                'Nữ' => 'Nữ',
                                                'Khác' => 'Khác',
                                            ])
                                            ->dehydrated(false)
                                            ->default('Nam'),
                                        Forms\Components\DatePicker::make('dob')
                                            ->label('Ngày sinh')
                                            ->dehydrated(false),
                                        Forms\Components\TextInput::make('id_card')
                                            ->label('Số CCCD / CMND')
                                            ->dehydrated(false)
                                            ->placeholder('Số giấy tờ tùy thân'),
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Số điện thoại')
                                            ->tel()
                                            ->placeholder('Liên hệ cá nhân')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->placeholder('Nhập email')
                                            ->maxLength(255),
                                    ]),

                                Forms\Components\Section::make('Thông tin công việc')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\Select::make('department')
                                            ->label('Phòng ban')
                                            ->options([
                                                'Nhân sự' => 'Nhân sự',
                                                'Kế toán' => 'Kế toán',
                                                'Kho' => 'Kho',
                                                'Sản xuất' => 'Sản xuất',
                                            ])
                                            ->required(),
                                        Forms\Components\TextInput::make('position')
                                            ->label('Chức danh / Chức vụ')
                                            ->placeholder('VD: Tổ trưởng bếp, Thủ kho...')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Select::make('area_id')
                                            ->label('Khu vực làm việc')
                                            ->relationship('area', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Forms\Components\DatePicker::make('start_date')
                                            ->label('Ngày vào làm')
                                            ->required(),
                                        Forms\Components\Select::make('status')
                                            ->label('Trạng thái')
                                            ->options([
                                                'Đang làm việc' => 'Đang làm việc',
                                                'Nghỉ phép' => 'Nghỉ phép',
                                                'Nghỉ việc' => 'Nghỉ việc',
                                            ])
                                            ->default('Đang làm việc')
                                            ->required(),
                                    ]),
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
                    ->state(static function (HasTable $livewire, \stdClass $rowLoop): string {
                        return (string) ($rowLoop->iteration);
                    }),
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('AVATAR')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=NV&color=7F9CF5&background=EBF4FF'),
                Tables\Columns\TextColumn::make('code')
                    ->label('MÃ NHÂN VIÊN')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->label('HỌ VÀ TÊN')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->email),
                Tables\Columns\TextColumn::make('department')
                    ->label('PHÒNG BAN')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('VỊ TRÍ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('area.name')
                    ->label('KHU VỰC')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('NGÀY VÀO LÀM')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('TRẠNG THÁI')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Đang làm việc' => 'success',
                        'Nghỉ phép' => 'warning',
                        'Nghỉ việc' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->label('Phòng ban')
                    ->options([
                        'Nhân sự' => 'Nhân sự',
                        'Kế toán' => 'Kế toán',
                        'Kho' => 'Kho',
                        'Sản xuất' => 'Sản xuất',
                    ]),
                Tables\Filters\SelectFilter::make('area_id')
                    ->label('Khu vực')
                    ->relationship('area', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Đang làm việc' => 'Đang làm việc',
                        'Nghỉ phép' => 'Nghỉ phép',
                        'Nghỉ việc' => 'Nghỉ việc',
                    ]),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
