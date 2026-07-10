<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasAvatar
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
