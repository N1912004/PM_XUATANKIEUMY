<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveOvertimeResource\Pages;
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
    protected static bool $shouldRegisterNavigation = false; // Ẩn menu 'Nghỉ phép & Tăng ca' khỏi thanh điều hướng Sidebar

    protected static ?string $model = LeaveOvertime::class;

    protected static ?string $navigationIcon = 'fa-umbrella-beach';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('leave_overtime.navigation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('leave_overtime.navigation');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.groups.hr');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('leave_overtime.sections.request'))
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label(__('leave_overtime.fields.employee'))
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('leave_type_id')
                            ->label(__('leave_overtime.fields.type'))
                            ->relationship('leaveType', 'name', fn ($query) => $query->where('active', true)->orderBy('sort')->orderBy('name'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('duration_text')
                            ->label(__('leave_overtime.fields.duration'))
                            ->placeholder(__('leave_overtime.ui.duration_placeholder'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('start_date')
                            ->label(__('leave_overtime.fields.start_date'))
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label(__('leave_overtime.fields.end_date'))
                            ->nullable(),
                    ]),
                Forms\Components\Section::make(__('leave_overtime.sections.approval'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('reason')
                            ->label(__('leave_overtime.fields.reason'))
                            ->columnSpanFull()
                            ->rows(3),
                        Forms\Components\Select::make('approver_id')
                            ->label(__('leave_overtime.fields.approver'))
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->label(__('leave_overtime.fields.status'))
                            ->options([
                                'pending' => __('leave_overtime.status.pending'),
                                'approved' => __('leave_overtime.status.approved'),
                                'rejected' => __('leave_overtime.status.rejected'),
                                'cancelled' => __('leave_overtime.status.cancelled'),
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
                    ->label(__('common.index'))
                    ->state(static function (HasTable $livewire, \stdClass $rowLoop): string {
                        return (string) ($rowLoop->iteration);
                    }),
                Tables\Columns\TextColumn::make('employee.code')
                    ->label(__('leave_overtime.ui.employee_code'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label(__('leave_overtime.ui.full_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->employee?->email),
                Tables\Columns\TextColumn::make('employee.department.name')
                    ->label(__('leave_overtime.ui.department'))
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label(__('leave_overtime.fields.type'))
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
                    ->label(__('leave_overtime.ui.applied_date'))
                    ->state(fn ($record) => $record->start_date?->format('d/m/Y').($record->end_date ? ' đến '.$record->end_date->format('d/m/Y') : ''))
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_text')
                    ->label(__('leave_overtime.fields.duration'))
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label(__('leave_overtime.fields.reason'))
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('approver.name')
                    ->label(__('leave_overtime.fields.approver'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('leave_overtime.ui.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('leave_overtime.status.pending'),
                        'approved' => __('leave_overtime.status.approved'),
                        'rejected' => __('leave_overtime.status.rejected'),
                        'cancelled' => __('leave_overtime.status.cancelled'),
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
                    ->label(__('leave_overtime.fields.employee'))
                    ->relationship('employee', 'name')
                    // Tìm kiếm ajax thay vì render toàn bộ nhân viên vào HTML
                    ->searchable()
                    ->optionsLimit(50),
                Tables\Filters\SelectFilter::make('leave_type_id')
                    ->label(__('leave_overtime.fields.type'))
                    ->relationship('leaveType', 'name', fn ($query) => $query->where('active', true)->orderBy('sort')->orderBy('name')),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('leave_overtime.fields.status'))
                    ->options([
                        'pending' => __('leave_overtime.status.pending'),
                        'approved' => __('leave_overtime.status.approved'),
                        'rejected' => __('leave_overtime.status.rejected'),
                        'cancelled' => __('leave_overtime.status.cancelled'),
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
