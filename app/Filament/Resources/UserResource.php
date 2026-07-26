<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Employee;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false; // Ẩn menu 'Quản lý tài khoản' khỏi thanh điều hướng Sidebar

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.groups.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('user.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('user.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('user.navigation');
    }

    /**
     * Chỉ tài khoản toàn quyền mới được gán/gỡ vai trò super_admin.
     * Nếu không chặn, bất kỳ ai có quyền sửa tài khoản đều có thể tự nâng mình lên toàn quyền.
     */
    public static function currentUserCanGrantSuperAdmin(): bool
    {
        return Filament::auth()->user()?->isSuperAdmin() ?? false;
    }

    /**
     * Vai trò được phép gán bởi người đang đăng nhập (khóa là ID — CheckboxList dùng quan hệ).
     *
     * @return array<int, string>
     */
    public static function assignableRoles(): array
    {
        return Role::query()
            ->when(! static::currentUserCanGrantSuperAdmin(), fn (Builder $q) => $q->where('name', '!=', User::superAdminRole()))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @param  array<int, int|string>  $roleIds
     * @return array<int, string>
     */
    protected static function roleNames(array $roleIds): array
    {
        return Role::whereIn('id', $roleIds)->pluck('name')->all();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('user.sections.login'))
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('user.fields.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label(__('user.fields.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label(__('user.fields.password'))
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(__('user.help.password'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('avatar_url')
                            ->label(__('user.fields.avatar_url'))
                            ->maxLength(255),
                    ]),
                ]),

            Forms\Components\Section::make(__('user.sections.employee'))
                ->description(__('user.help.employee_section'))
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->label(__('user.fields.employee'))
                        ->options(fn (?User $record) => Employee::query()
                            ->where(fn (Builder $q) => $q->whereDoesntHave('user')
                                ->when($record?->employee_id, fn (Builder $q, int $id) => $q->orWhere('id', $id)))
                            ->orderBy('name')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->unique(ignoreRecord: true)
                        ->helperText(__('user.help.employee'))
                        ->live(),
                    Forms\Components\Placeholder::make('kitchen_preview')
                        ->label(__('user.fields.kitchen'))
                        ->content(fn (Get $get): string => Employee::with('kitchen')->find($get('employee_id'))?->kitchen?->name ?? '— Chưa xác định —'),
                ])
                ->columns(2),

            Forms\Components\Section::make(__('user.sections.roles'))
                ->description(fn (?User $record): string => static::isEditingSelf($record)
                    ? 'Bạn không thể tự đổi vai trò của chính mình — nhờ một tài khoản quản trị khác thực hiện.'
                    : 'Tài khoản không có vai trò nào sẽ KHÔNG đăng nhập được vào hệ thống.')
                ->schema([
                    Forms\Components\CheckboxList::make('roles')
                        ->label(__('user.fields.roles'))
                        ->relationship('roles', 'name')
                        ->options(fn () => static::assignableRoles())
                        ->required()
                        ->columns(2)
                        // Tự sửa vai trò của chính mình = tự nâng quyền / tự khóa mình ra khỏi hệ thống.
                        ->disabled(fn (?User $record): bool => static::isEditingSelf($record))
                        ->dehydrated(fn (?User $record): bool => ! static::isEditingSelf($record))
                        ->rules([
                            fn (?User $record) => function (string $attribute, $value, \Closure $fail) use ($record): void {
                                $names = static::roleNames((array) $value);
                                $keepsSuperAdmin = \in_array(User::superAdminRole(), $names, true);

                                // Không cho gỡ vai trò toàn quyền của người CUỐI CÙNG còn giữ nó.
                                if ($record?->isSuperAdmin() && ! $keepsSuperAdmin && User::countSuperAdmins() <= 1) {
                                    $fail(__('user.validation.last_super_admin', ['role' => User::superAdminRole()]));
                                }

                                if ($keepsSuperAdmin && ! static::currentUserCanGrantSuperAdmin()) {
                                    $fail(__('user.validation.cannot_grant_super_admin', ['role' => User::superAdminRole()]));
                                }
                            },
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['employee.kitchen', 'roles']))
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label(__('common.index'))
                    ->state(static fn (HasTable $livewire, \stdClass $rowLoop): string => (string) $rowLoop->iteration),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('user.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('user.table.email'))
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->copyable(),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label(__('user.table.employee'))
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.kitchen.name')
                    ->label(__('user.table.kitchen'))
                    ->placeholder('—')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label(__('user.table.roles'))
                    ->badge()
                    ->color(fn (string $state): string => $state === User::superAdminRole() ? 'danger' : 'success')
                    ->placeholder(__('user.placeholders.no_access')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('user.table.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label(__('user.filters.roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('employee_id')
                    ->label(__('user.filters.employee_linked'))
                    ->nullable()
                    ->trueLabel(__('user.filters.linked'))
                    ->falseLabel(__('user.filters.not_linked'))
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('employee_id'),
                        false: fn (Builder $q) => $q->whereNull('employee_id'),
                        blank: fn (Builder $q) => $q,
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (User $record): bool => $record->id === Filament::auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected static function isEditingSelf(?User $record): bool
    {
        return $record !== null && $record->id === Filament::auth()->id();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
