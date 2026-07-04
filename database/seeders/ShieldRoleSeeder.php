<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ShieldRoleSeeder extends Seeder
{
    /**
     * Tạo bộ vai trò vận hành và gán super_admin cho toàn bộ user hiện hữu
     * (tránh khóa panel sau khi bật phân quyền). Các vai trò nghiệp vụ khác để trống,
     * cấu hình quyền chi tiết sau qua trang Shield (Vai trò).
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => $guard]);

        foreach (['Quản trị viên', 'Thủ kho', 'Bếp trưởng', 'Nhân viên'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
        }

        // Gán super_admin cho mọi user hiện có nếu chưa có vai trò nào
        User::query()->each(function (User $user) use ($superAdmin): void {
            if (! $user->roles()->exists()) {
                $user->assignRole($superAdmin);
            }
        });
    }
}
