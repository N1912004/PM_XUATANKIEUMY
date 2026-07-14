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

        $this->warnAboutResourcesWithoutPermissions($all);

        return self::SUCCESS;
    }

    /**
     * Resource thêm mới mà quên chạy `shield:generate` sẽ vẫn hiện checkbox trên trang Vai trò,
     * nhưng tick xong lưu lại không ăn (không có bản ghi quyền để gán) — cảnh báo sớm.
     *
     * @param  array<int, string>  $permissions
     */
    protected function warnAboutResourcesWithoutPermissions(array $permissions): void
    {
        $missing = [];

        foreach (glob(app_path('Filament/Resources/*Resource.php')) as $file) {
            $model = str_replace('Resource', '', basename($file, '.php'));
            // Shield đặt tên quyền theo model, ngăn cách các từ bằng '::' (VD: view_any_recipe::type).
            $key = strtolower(preg_replace('/(?<!^)[A-Z]/', '::$0', $model));

            if (! \in_array('view_any_'.$key, $permissions, true)) {
                $missing[] = $model;
            }
        }

        if ($missing !== []) {
            $this->warn('Các màn hình sau CHƯA có quyền — chạy: php artisan shield:generate --resource='
                .implode('Resource,', $missing).'Resource --panel=admin');
        }
    }
}
