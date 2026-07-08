<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimekeepingResource\Pages;
use App\Models\Timekeeping;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TimekeepingResource extends Resource
{
    protected static ?string $model = Timekeeping::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('Chấm công');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Chấm công');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('NHÂN SỰ');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin ca làm việc')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Nhân viên')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Ngày làm việc')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('shift_id')
                            ->label('Ca làm việc')
                            ->relationship('shift', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                Forms\Components\Section::make('Thời gian ghi nhận')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TimePicker::make('check_in')
                            ->label('Giờ vào (Check-in)')
                            ->placeholder('HH:MM:SS')
                            ->seconds(false),
                        Forms\Components\TimePicker::make('check_out')
                            ->label('Giờ ra (Check-out)')
                            ->placeholder('HH:MM:SS')
                            ->seconds(false),
                        Forms\Components\TextInput::make('overtime_hours')
                            ->label('Số giờ tăng ca')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('status')
                            ->label('Trạng thái')
                            ->required()
                            ->options([
                                'Đúng giờ' => 'Đúng giờ',
                                'Đi trễ' => 'Đi trễ',
                                'Tăng ca' => 'Tăng ca',
                                'Nghỉ phép' => 'Nghỉ phép',
                                'Vắng mặt' => 'Vắng mặt',
                            ])
                            ->default('Đúng giờ'),
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
                Tables\Columns\TextColumn::make('employee.code')
                    ->label('MÃ NV')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('HỌ VÀ TÊN')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->employee?->email),
                Tables\Columns\TextColumn::make('employee.department')
                    ->label('PHÒNG BAN')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift.name')
                    ->label('CA LÀM VIỆC')
                    ->description(fn ($record) => $record->shift?->time_range)
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_in')
                    ->label('CHECK-IN')
                    ->color(fn ($state) => $state && strcmp($state, '07:05:00') <= 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out')
                    ->label('CHECK-OUT')
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_hours')
                    ->label('TỔNG GIỜ')
                    ->state(fn ($record) => $record->check_in && $record->check_out ? '8h54' : '—')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('overtime_hours')
                    ->label('TĂNG CA')
                    ->formatStateUsing(fn ($state) => $state ? $state.'h' : '0h')
                    ->color(fn ($state) => $state > 0 ? 'primary' : 'gray')
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('TRẠNG THÁI')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Đúng giờ' => 'success',
                        'Đi trễ' => 'danger',
                        'Tăng ca' => 'primary',
                        'Nghỉ phép' => 'warning',
                        'Vắng mặt' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Nhân viên')
                    ->relationship('employee', 'name')
                    // Tìm kiếm ajax thay vì render toàn bộ nhân viên vào HTML (~1MB với 1000+ NV)
                    ->searchable()
                    ->optionsLimit(50),
                Tables\Filters\SelectFilter::make('shift_id')
                    ->label('Ca làm việc')
                    ->relationship('shift', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'Đúng giờ' => 'Đúng giờ',
                        'Đi trễ' => 'Đi trễ',
                        'Tăng ca' => 'Tăng ca',
                        'Nghỉ phép' => 'Nghỉ phép',
                        'Vắng mặt' => 'Vắng mặt',
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
            'index' => Pages\ListTimekeepings::route('/'),
            'create' => Pages\CreateTimekeeping::route('/create'),
            'edit' => Pages\EditTimekeeping::route('/{record}/edit'),
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

        if ($user->hasRole('Bếp trưởng') && $kitchenId = $user->currentKitchenId()) {
            return $query->whereHas('employee', fn (Builder $q) => $q->where('kitchen_id', $kitchenId));
        }

        return $query->where('employee_id', $user->employee_id);
    }
}
