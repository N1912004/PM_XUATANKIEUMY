<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveOvertimeResource\Pages;
use App\Models\Catalog;
use App\Models\LeaveOvertime;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveOvertimeResource extends Resource
{
    protected static ?string $model = LeaveOvertime::class;

    protected static ?string $navigationIcon = 'heroicon-o-sun';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('Nghỉ phép & Tăng ca');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Nghỉ phép & Tăng ca');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('NHÂN SỰ');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin yêu cầu')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Nhân viên yêu cầu')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label('Loại yêu cầu')
                            ->options(fn (): array => Catalog::options(Catalog::LEAVE_TYPE))
                            ->searchable()
                            ->native(false)
                            ->required(),
                        Forms\Components\TextInput::make('duration_text')
                            ->label('Số ngày / Số giờ')
                            ->placeholder('VD: 1 ngày hoặc 4 giờ')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Từ ngày / Ngày áp dụng')
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Đến ngày')
                            ->nullable(),
                    ]),
                Forms\Components\Section::make('Chi tiết & Duyệt')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('reason')
                            ->label('Lý do chi tiết')
                            ->columnSpanFull()
                            ->rows(3),
                        Forms\Components\Select::make('approver_id')
                            ->label('Người duyệt')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->label('Trạng thái phê duyệt')
                            ->options([
                                'pending' => 'Chờ duyệt',
                                'approved' => 'Đã duyệt',
                                'rejected' => 'Từ chối',
                                'cancelled' => 'Đã hủy',
                            ])
                            ->default('pending')
                            ->required(),
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
                Tables\Columns\TextColumn::make('type')
                    ->label('LOẠI YÊU CẦU')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Nghỉ phép năm' => 'success',
                        'Nghỉ phép bệnh' => 'danger',
                        'Nghỉ không lương' => 'warning',
                        'Tăng ca ngày thường', 'Tăng ca cuối tuần' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('time_range')
                    ->label('THỜI GIAN / NGÀY ÁP DỤNG')
                    ->state(fn ($record) => $record->start_date?->format('d/m/Y').($record->end_date ? ' đến '.$record->end_date->format('d/m/Y') : ''))
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_text')
                    ->label('SỐ NGÀY / SỐ GIỜ')
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('LÝ DO')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('NGƯỜI DUYỆT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('TRẠNG THÁI')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Chờ duyệt',
                        'approved' => 'Đã duyệt',
                        'rejected' => 'Từ chối',
                        'cancelled' => 'Đã hủy',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Nhân viên')
                    ->relationship('employee', 'name')
                    // Tìm kiếm ajax thay vì render toàn bộ nhân viên vào HTML
                    ->searchable()
                    ->optionsLimit(50),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Loại yêu cầu')
                    ->options([
                        'Nghỉ phép năm' => 'Nghỉ phép năm',
                        'Nghỉ phép bệnh' => 'Nghỉ phép bệnh',
                        'Nghỉ không lương' => 'Nghỉ không lương',
                        'Tăng ca ngày thường' => 'Tăng ca ngày thường',
                        'Tăng ca cuối tuần' => 'Tăng ca cuối tuần',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'pending' => 'Chờ duyệt',
                        'approved' => 'Đã duyệt',
                        'rejected' => 'Từ chối',
                        'cancelled' => 'Đã hủy',
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
            'index' => Pages\ListLeaveOvertimes::route('/'),
            'create' => Pages\CreateLeaveOvertime::route('/create'),
            'edit' => Pages\EditLeaveOvertime::route('/{record}/edit'),
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
