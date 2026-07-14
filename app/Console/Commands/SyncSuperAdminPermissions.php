<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Cấp TOÀN BỘ quyền hiện có cho vai trò toàn quyền (super_admin).
 *
 * Vì sao cần: Shield cho super_admin đi tắt qua Gate nên vai trò này chạy được mà không giữ
 * quyền nào — bảng "Vai trò" hiển thị 0 quyền, và nếu tắt `define_via_gate` trong config thì
 * admin mất sạch quyền ngay lập tức. Đồng bộ quyền thật vào vai trò giúp hiển thị đúng số quyền
 * và hệ thống vẫn đứng vững kể cả khi không còn cơ chế Gate.
 *
 * Chạy lại sau mỗi lần `php artisan shield:generate` (thêm Resource/Page/Widget mới).
 */
class SyncSuperAdminPermissions extends Command
{
    protected $signature = 'shield:sync-super-admin';

    protected $description = 'Đồng bộ toàn bộ quyền hệ thống cho vai trò toàn quyền (super_admin)';

    public function handle(): int
    {
        $roleName = User::superAdminRole();
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

        $before = $role->permissions()->count();
        $all = Permission::where('guard_name', 'web')->pluck('name')->all();

        $role->syncPermissions($all);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info("Vai trò [{$roleName}]: {$before} → ".count($all).' quyền.');

        return self::SUCCESS;
    }
}
