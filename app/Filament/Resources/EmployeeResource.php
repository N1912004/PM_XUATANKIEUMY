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
use Illuminate\Database\Eloquent\Builder;

class EmployeeResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false; // Ẩn menu 'Nhân viên' khỏi thanh điều hướng Sidebar

    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'fa-user-group';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('employee.navigation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('employee.navigation');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.groups.hr');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        // Left Column: Avatar
                        Forms\Components\Section::make(__('employee.sections.avatar'))
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\FileUpload::make('avatar_url')
                                    ->label(__('employee.fields.avatar'))
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
                                    ->helperText(__('employee.upload.hint'))
                                    ->uploadButtonPosition('center')
                                    ->uploadProgressIndicatorPosition('center')
                                    ->removeUploadedFileButtonPosition('center')
                                    ->alignCenter(),
                            ]),

                        // Right Column: Details
                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\Section::make(__('employee.sections.personal'))
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label(__('employee.fields.name'))
                                            ->required()
                                            ->placeholder(__('employee.placeholders.name'))
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('code')
                                            ->label(__('employee.fields.code'))
                                            ->required()
                                            ->placeholder(__('employee.placeholders.code'))
                                            ->maxLength(255),
                                        Forms\Components\Select::make('gender')
                                            ->label(__('employee.fields.gender'))
                                            ->options([
                                                'Nam' => 'Nam',
                                                'Nữ' => __('employee.options.female'),
                                                'Khác' => __('employee.options.other'),
                                            ])
                                            ->dehydrated(false)
                                            ->default('Nam'),
                                        Forms\Components\DatePicker::make('dob')
                                            ->label(__('employee.fields.birth_date'))
                                            ->dehydrated(false),
                                        Forms\Components\TextInput::make('id_card')
                                            ->label(__('employee.fields.identity_number'))
                                            ->dehydrated(false)
                                            ->placeholder(__('employee.placeholders.identity_number')),
                                        Forms\Components\TextInput::make('phone')
                                            ->label(__('employee.fields.phone'))
                                            ->tel()
                                            ->placeholder(__('employee.placeholders.phone'))
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->placeholder(__('employee.placeholders.email'))
                                            ->maxLength(255),
                                    ]),

                                Forms\Components\Section::make(__('employee.sections.work'))
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\Select::make('department_id')
                                            ->label(__('employee.fields.department'))
                                            ->relationship('department', 'name', fn ($query) => $query->where('active', true)->orderBy('sort')->orderBy('name'))
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Forms\Components\Select::make('position_id')
                                            ->label(__('employee.fields.position'))
                                            ->relationship('position', 'name', fn ($query) => $query->where('active', true)->orderBy('sort')->orderBy('name'))
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Forms\Components\Select::make('area_id')
                                            ->label(__('employee.fields.area'))
                                            ->relationship('area', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live(),
                                        Forms\Components\Select::make('kitchen_id')
                                            ->label(__('employee.fields.kitchen'))
                                            ->relationship(
                                                'kitchen',
                                                'name',
                                                fn (Builder $query, Forms\Get $get) => $query->when($get('area_id'), fn ($q, $areaId) => $q->where('area_id', $areaId)),
                                            )
                                            ->searchable()
                                            ->preload(),
                                        Forms\Components\DatePicker::make('start_date')
                                            ->label(__('employee.fields.start_date'))
                                            ->required(),
                                        Forms\Components\Select::make('status')
                                            ->label(__('employee.fields.status'))
                                            ->options([
                                                'working' => __('employee.status.working'),
                                                'on_leave' => __('employee.status.on_leave'),
                                                'resigned' => __('employee.status.resigned'),
                                            ])
                                            ->default('working')
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
                    ->label(__('employee.table.index'))
                    ->state(static function (HasTable $livewire, \stdClass $rowLoop): string {
                        return (string) ($rowLoop->iteration);
                    }),
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label(__('employee.table.avatar'))
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=NV&color=7F9CF5&background=EBF4FF'),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('employee.table.code'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('employee.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->email),
                Tables\Columns\TextColumn::make('department.name')
                    ->label(__('employee.table.department'))
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('position.name')
                    ->label(__('employee.table.position'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('area.name')
                    ->label(__('employee.table.area'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('kitchen.name')
                    ->label(__('employee.table.kitchen'))
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('employee.table.start_date'))
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('employee.table.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'working' => __('employee.status.working'),
                        'on_leave' => __('employee.status.on_leave'),
                        'resigned' => __('employee.status.resigned'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'working' => 'success',
                        'on_leave' => 'warning',
                        'resigned' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label(__('employee.fields.department'))
                    ->relationship('department', 'name', fn ($query) => $query->where('active', true)->orderBy('sort')->orderBy('name')),
                Tables\Filters\SelectFilter::make('area_id')
                    ->label(__('employee.fields.area'))
                    ->relationship('area', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('employee.fields.status'))
                    ->options([
                        'working' => __('employee.status.working'),
                        'on_leave' => __('employee.status.on_leave'),
                        'resigned' => __('employee.status.resigned'),
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = filament()->auth()->user();

        if (! $user) {
            return $query;
        }

        if ($user->hasRole(['super_admin', 'Quản trị viên'])) {
            return $query;
        }

        if ($user->hasRole(['Bếp trưởng', 'Thủ kho']) && $kitchenId = $user->currentKitchenId()) {
            return $query->where('kitchen_id', $kitchenId);
        }

        return $query->where('id', $user->employee_id);
    }
}
