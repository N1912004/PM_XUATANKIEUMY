<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    /**
     * Tạo một user có vai trò super_admin (bỏ qua toàn bộ kiểm tra phân quyền của Shield)
     * để test các trang panel không bị chặn bởi policy.
     */
    protected function createSuperAdmin(array $attributes = []): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::create(array_merge([
            'name' => 'Admin Test',
            'email' => 'admin-'.uniqid().'@example.com',
            'password' => bcrypt('password'),
        ], $attributes));

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $user->assignRole($role);

        return $user;
    }
}
