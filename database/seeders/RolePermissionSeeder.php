<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Bộ quyền mặc định theo nghiệp vụ cho 3 role vận hành.
 * super_admin không cần gán (Shield Gate::before cho qua toàn bộ);
 * Quản trị viên được gán full trừ trang cài đặt hệ thống & quản lý role.
 *
 * Chạy lại được (sync): php artisan db:seed --class=RolePermissionSeeder
 * Sau đó tinh chỉnh thêm trong Shield UI nếu cần.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $all = Permission::pluck('name');

        // Helper: lấy các quyền CRUD cơ bản của 1 entity (bỏ force_delete/restore/reorder/replicate)
        $crud = fn (string $entity, array $actions) => collect($actions)
            ->map(fn (string $action) => "{$action}_{$entity}")
            ->filter(fn (string $name) => $all->contains($name))
            ->all();

        $view = ['view_any', 'view'];
        $manage = ['view_any', 'view', 'create', 'update'];
        $full = ['view_any', 'view', 'create', 'update', 'delete'];

        // ===== Quản trị viên: full nghiệp vụ, trừ SystemSettings & Role =====
        $adminPerms = $all
            ->reject(fn (string $name) => str_starts_with($name, 'force_delete')
                || str_starts_with($name, 'restore')
                || str_contains($name, '_role')
                || $name === 'page_SystemSettings')
            ->all();

        // ===== Bếp trưởng: vận hành trọn pipeline của bếp mình =====
        // (dữ liệu đã được kitchen-scope trong code; quyền chỉ mở chức năng)
        $bepTruong = array_merge(
            $crud('menu', $full),
            $crud('recipe', $full),
            $crud('ingredient', $manage),
            $crud('unit', $manage),
            $crud('ingredient::type', $manage),
            $crud('supplier', $view),
            $crud('purchase::order', $full),
            $crud('stock', $manage),
            $crud('stock::transaction', $view),
            $crud('stock::transfer', $manage),
            $crud('timekeeping', $manage),
            $crud('leave::overtime', $full),
            $crud('employee', $view),
            $crud('food::safety::audit', $full),
            $crud('shift', $view),
            $crud('kitchen', $view),
            $crud('area', $view),
            ['page_BaoCao', 'page_ChatNhom', 'page_LapThucDonTuan', 'page_ListHang', 'widget_RecipeAlertWidget'],
        );

        // ===== Thủ kho: kho + nhận PO, xem danh mục liên quan =====
        $thuKho = array_merge(
            $crud('stock', $manage),
            $crud('unit', $view),
            $crud('ingredient::type', $view),
            $crud('stock::transaction', $view),
            $crud('stock::transfer', $manage),
            $crud('purchase::order', ['view_any', 'view', 'update']),
            $crud('ingredient', $view),
            $crud('supplier', $view),
            $crud('menu', $view),
            $crud('shift', $view),
            $crud('kitchen', $view),
            $crud('timekeeping', $view),
            $crud('leave::overtime', ['view_any', 'view', 'create']),
            ['page_ChatNhom', 'page_ListHang'],
        );

        // ===== Nhân viên: chấm công + nghỉ phép của mình, xem thực đơn =====
        // (phạm vi "chỉ bản ghi của mình" đã được siết trong baseQuery các trang)
        $nhanVien = array_merge(
            $crud('timekeeping', $view),
            $crud('leave::overtime', ['view_any', 'view', 'create', 'update']),
            $crud('menu', $view),
            $crud('recipe', $view),
            $crud('shift', $view),
            ['page_ChatNhom'],
        );

        foreach ([
            'Quản trị viên' => $adminPerms,
            'Bếp trưởng' => $bepTruong,
            'Thủ kho' => $thuKho,
            'Nhân viên' => $nhanVien,
        ] as $roleName => $permissions) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web'])
                ->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
