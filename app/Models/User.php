<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

// FilamentUser BẮT BUỘC phải implements: nếu thiếu, middleware Filament chỉ cho vào khi
// APP_ENV=local — canAccessPanel() không bao giờ chạy ở dev, còn PRODUCTION sẽ 403 toàn bộ.
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Tên vai trò toàn quyền — lấy từ config/filament-shield.php làm NGUỒN DUY NHẤT.
     * Đổi tên vai trò chỉ cần sửa config: model, resource, command và test đều đọc theo đây.
     */
    public static function superAdminRole(): string
    {
        return config('filament-shield.super_admin.name', 'super_admin');
    }

    /**
     * Chặn hai thao tác có thể khóa vĩnh viễn hệ thống:
     * tự xóa chính mình, và xóa tài khoản toàn quyền cuối cùng.
     *
     * Đăng ký ở booting() (KHÔNG phải booted()) để listener này chạy TRƯỚC listener
     * `deleting` của trait HasRoles — trait đó gỡ sạch vai trò của user, nên nếu chạy sau
     * thì không còn nhận ra đây là tài khoản toàn quyền nữa.
     */
    protected static function booting(): void
    {
        static::deleting(function (User $user): void {
            abort_if($user->id === auth()->id(), 403, 'Không thể xóa chính tài khoản đang đăng nhập.');

            if ($user->isSuperAdmin() && static::countSuperAdmins() <= 1) {
                abort(403, 'Không thể xóa tài khoản toàn quyền cuối cùng của hệ thống.');
            }
        });
    }

    /** Số tài khoản còn giữ vai trò toàn quyền. */
    public static function countSuperAdmins(): int
    {
        return static::role(static::superAdminRole())->count();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(static::superAdminRole());
    }

    /**
     * Chỉ tài khoản đã được gán ít nhất 1 vai trò mới được vào panel quản trị.
     * (Mọi user hiện hữu đều được seed vai trò super_admin nên không bị khóa.)
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->roles()->exists();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Bếp trực thuộc của người dùng (qua hồ sơ nhân viên) — dùng để lọc/phân quyền dữ liệu theo bếp.
     */
    public function currentKitchenId(): ?int
    {
        // Tài khoản có vai trò quản trị/toàn quyền có thể xem và đổi bếp linh hoạt qua session
        if ($this->hasRole(['super_admin', 'Quản trị viên'])) {
            $activeId = session('active_kitchen_id');
            if ($activeId === 'all') {
                return null;
            }
            return $activeId ?? Kitchen::first()?->id;
        }

        return $this->employee?->kitchen_id;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar_url) {
            // avatar_url có thể là URL tuyệt đối (seeder dùng Unsplash) hoặc path trong storage
            if (filter_var($this->avatar_url, FILTER_VALIDATE_URL)) {
                return $this->avatar_url;
            }

            return Storage::disk('public')->url($this->avatar_url);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
    }
}
