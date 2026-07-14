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
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'NHÂN SỰ';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Quản lý tài khoản';

    protected static ?string $modelLabel = 'tài khoản';

    protected static ?string $pluralModelLabel = 'tài khoản';

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
            Forms\Components\Section::make('Thông tin đăng nhập')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Họ tên tài khoản')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email đăng nhập')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label('Mật khẩu')
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText('Để trống khi sửa nếu không muốn đổi mật khẩu.')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('avatar_url')
                            ->label('Ảnh đại diện (URL, tùy chọn)')
                            ->maxLength(255),
                    ]),
                ]),

            Forms\Components\Section::make('Liên kết hồ sơ nhân sự')
                ->description('Bếp trực thuộc của tài khoản được xác định qua nhân viên. Tài khoản vận hành (Thủ kho / Bếp trưởng / Nhân viên) CHƯA gán nhân viên sẽ không thấy dữ liệu Kho, Thực đơn, Đặt hàng.')
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->label('Nhân viên')
                        ->options(fn (?User $record) => Employee::query()
                            ->where(fn (Builder $q) => $q->whereDoesntHave('user')
                                ->when($record?->employee_id, fn (Builder $q, int $id) => $q->orWhere('id', $id)))
                            ->orderBy('name')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->unique(ignoreRecord: true)
                        ->helperText('Chỉ hiện nhân viên chưa có tài khoản (mỗi nhân viên tối đa 1 tài khoản).')
                        ->live(),
                    Forms\Components\Placeholder::make('kitchen_preview')
                        ->label('Bếp trực thuộc')
                        ->content(fn (Get $get): string => Employee::with('kitchen')->find($get('employee_id'))?->kitchen?->name ?? '— Chưa xác định —'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Vai trò phân quyền')
                ->description(fn (?User $record): string => static::isEditingSelf($record)
                    ? 'Bạn không thể tự đổi vai trò của chính mình — nhờ một tài khoản quản trị khác thực hiện.'
                    : 'Tài khoản không có vai trò nào sẽ KHÔNG đăng nhập được vào hệ thống.')
                ->schema([
                    Forms\Components\CheckboxList::make('roles')
                        ->label('Vai trò')
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
                                    $fail('Đây là tài khoản toàn quyền cuối cùng — không thể gỡ vai trò '.User::superAdminRole().'.');
                                }

                                if ($keepsSuperAdmin && ! static::currentUserCanGrantSuperAdmin()) {
                                    $fail('Bạn không có quyền gán vai trò '.User::superAdminRole().'.');
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
                    ->label('STT')
                    ->state(static fn (HasTable $livewire, \stdClass $rowLoop): string => (string) $rowLoop->iteration),
                Tables\Columns\TextColumn::make('name')
                    ->label('HỌ VÀ TÊN')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->label('EMAIL ĐĂNG NHẬP')
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->copyable(),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('NHÂN VIÊN LIÊN KẾT')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.kitchen.name')
                    ->label('BẾP TRỰC THUỘC')
                    ->placeholder('—')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('VAI TRÒ')
                    ->badge()
                    ->color(fn (string $state): string => $state === User::superAdminRole() ? 'danger' : 'success')
                    ->placeholder('— Không vào được hệ thống —'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('NGÀY TẠO')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Vai trò')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('employee_id')
                    ->label('Đã liên kết nhân viên')
                    ->nullable()
                    ->trueLabel('Đã liên kết')
                    ->falseLabel('Chưa liên kết')
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
