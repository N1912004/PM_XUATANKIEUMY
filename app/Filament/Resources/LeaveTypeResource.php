<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveTypeResource\Pages;
use App\Models\LeaveType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeaveTypeResource extends Resource
{
    protected static ?string $model = LeaveType::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): ?string
    {
        return __('leave_overtime.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('leave_overtime.type_navigation');
    }

    public static function getModelLabel(): string
    {
        return __('leave_overtime.type_navigation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('leave_overtime.type_navigation');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('leave_overtime.type.name'))
                    ->placeholder(__('leave_overtime.type.name_placeholder'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_ot')
                    ->label(__('leave_overtime.type.is_overtime'))
                    ->helperText(__('leave_overtime.type.is_overtime_help'))
                    ->default(false),
                Forms\Components\TextInput::make('sort')
                    ->label(__('leave_overtime.type.sort'))
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('active')
                    ->label(__('leave_overtime.type.active'))
                    ->default(true)
                    ->helperText(__('leave_overtime.type.active_help')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label(__('common.index'))
                    ->state(static function (Tables\Contracts\HasTable $livewire, \stdClass $rowLoop): string {
                        $currentPage = method_exists($livewire, 'getTablePage') ? $livewire->getTablePage() : 1;
                        $recordsPerPage = method_exists($livewire, 'getTableRecordsPerPage') ? $livewire->getTableRecordsPerPage() : 10;
                        $perPage = is_numeric($recordsPerPage) ? (int) $recordsPerPage : 10;

                        return (string) ($rowLoop->iteration + ($perPage * ($currentPage - 1)));
                    })
                    ->alignCenter()
                    ->extraAttributes([
                        'style' => 'font-variant-numeric: tabular-nums; font-weight: 600; color: #64748b;',
                    ])
                    ->width('56px'),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('leave_overtime.type.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('is_ot')
                    ->label(__('leave_overtime.type.is_overtime'))
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('sort')
                    ->label(__('leave_overtime.type.sort'))
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('leave_overtime.type.active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('leave_overtimes_count')
                    ->label(__('leave_overtime.type.request_count'))
                    ->counts('leaveOvertimes')
                    ->alignCenter()
                    ->sortable()
                    ->color('primary')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('leave_overtime.type.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->extraAttributes([
                        'style' => 'font-variant-numeric: tabular-nums;',
                    ])
                    ->sortable()
                    ->color('gray'),
            ])
            ->defaultSort('sort')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_ot')
                    ->label(__('leave_overtime.type.classification')),
                Tables\Filters\TernaryFilter::make('active')
                    ->label(__('leave_overtime.type.active')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->before(function (Tables\Actions\DeleteAction $action, $record): void {
                        if ($record->leaveOvertimes()->exists()) {
                            Notification::make()
                                ->title(__('leave_overtime.type.cannot_delete', ['count' => $record->leaveOvertimes()->count()]))
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records): void {
                            $inUse = $records->filter(fn ($r) => $r->leaveOvertimes()->exists());
                            if ($inUse->isNotEmpty()) {
                                Notification::make()
                                    ->title(__('leave_overtime.type.bulk_cannot_delete', ['names' => $inUse->pluck('name')->implode(', ')]))
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveTypes::route('/'),
            'create' => Pages\CreateLeaveType::route('/create'),
            'edit' => Pages\EditLeaveType::route('/{record}/edit'),
        ];
    }
}
