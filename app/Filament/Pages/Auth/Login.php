<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

/**
 * Trang đăng nhập theo mẫu BlueFire (layout 2 cột: panel thương hiệu + card đăng nhập).
 *
 * Chỉ thay GIAO DIỆN — toàn bộ luồng xác thực kế thừa nguyên từ Filament
 * (validate, rate limiting, remember me, session regenerate, redirect):
 * blade custom bind thẳng vào state `data.email` / `data.password` / `data.remember`
 * và submit qua `authenticate()` của trang gốc.
 */
class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    /** Bỏ layout card đơn của Filament — dùng layout nền để vẽ full-page 2 cột theo mẫu. */
    protected static string $layout = 'filament-panels::components.layout.base';

    public function mount(): void
    {
        parent::mount();

        // Mẫu thiết kế: ô "Ghi nhớ đăng nhập" bật sẵn
        $this->data['remember'] = true;
    }
}
