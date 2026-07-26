<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\EditProfile;
use App\Http\Middleware\SetLocale;
use App\Models\Setting;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $siteName = $this->getSetting('site_name', 'Bluefire Catering');
        $faviconUrl = $this->getSettingFileUrl('site_favicon', asset('favicon.svg'));

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->spa()
            ->darkMode(false)
            ->maxContentWidth(MaxWidth::Full)
            // Trang đăng nhập theo mẫu BlueFire (2 cột) — kế thừa nguyên luồng auth Filament
            ->login(Login::class)
            ->profile(EditProfile::class)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('13.75rem')
            ->collapsedSidebarWidth('4rem')
            ->brandName($siteName)
            ->brandLogo(fn () => request()->routeIs('filament.admin.auth.login') ? new HtmlString('') : view('filament.components.brand-logo', ['siteName' => $this->getSetting('site_name', 'Bluefire Catering')]))
            ->darkModeBrandLogo(fn () => request()->routeIs('filament.admin.auth.login') ? new HtmlString('') : view('filament.components.brand-logo', ['siteName' => $this->getSetting('site_name', 'Bluefire Catering')]))
            ->brandLogoHeight('2.5rem')
            ->favicon($faviconUrl)
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn () => view('filament.components.sidebar-footer'),
            )
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => new HtmlString(
                    view('filament.components.language-switcher')->render().'
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-200 mr-1.5" style="align-self: center; margin-right: 0.35rem;">
                        '.e(filament()->auth()->user()?->name ?? auth()->user()?->name ?? 'Admin').'
                    </span>
                '
                )
            )
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn () => __('Cài đặt tài khoản'))
                    ->icon('heroicon-o-user')
                    ->url("javascript:Livewire.dispatch('open-profile-modal')"),
            ])
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render("@livewire('edit-profile-modal')"),
            )
            // Nạp Laravel Echo (Reverb) cho chat realtime + app.css (bundle Font Awesome thay CDN).
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render("@vite(['resources/css/app.css', 'resources/js/app.js'])"),
            )
            ->colors([
                'primary' => '#1267E8',
            ])
            ->font('Inter')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => new HtmlString('
                    <style>
                        /* Keep the sidebar at full width: let the main content flex item
                           shrink below its min-content width so wide custom tables scroll
                           inside their own overflow-x wrappers instead of squeezing the sidebar. */
                        .fi-main-ctn {
                            min-width: 0 !important;
                            width: 100% !important;
                            max-width: 100% !important;
                        }
                        .fi-sidebar {
                            flex-shrink: 0 !important;
                        }

                        /* Đồng bộ font chữ Inter cho toàn bộ hệ thống (sidebar, body, main content) */
                        body,
                        .fi-body,
                        .fi-main,
                        .fi-sidebar {
                            font-family: \'Inter\', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
                        }

                        /* Đồng bộ cỡ chữ tiêu đề trang chuẩn 26px / Extra Bold giữa trang tiêu chuẩn và trang custom */
                        .fi-header-heading {
                            font-size: 1.625rem !important;
                            font-weight: 800 !important;
                            line-height: 1.25 !important;
                            letter-spacing: -0.03em !important;
                        }

                        @media (max-width: 639.98px) {
                            .fi-header-heading {
                                font-size: 1.3125rem !important;
                            }
                        }

                        /* Fix duplicate select arrows globally on custom pages */
                        select:not([class*="fi-"]) {
                            appearance: none !important;
                            -webkit-appearance: none !important;
                            -moz-appearance: none !important;
                            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'m6 8 4 4 4-4\'/%3E%3C/svg%3E") !important;
                            background-position: right 0.5rem center !important;
                            background-repeat: no-repeat !important;
                            background-size: 1.25rem 1.25rem !important;
                            padding-right: 2rem !important;
                        }

                        /* Force correct branding color and high contrast on all primary buttons */
                        .fi-btn.fi-btn-color-primary,
                        button[type="submit"]:not(.fi-btn-color-gray) {
                            background-color: rgb(var(--primary-600)) !important;
                            color: #ffffff !important;
                            font-weight: 600 !important;
                        }
                        .fi-btn.fi-btn-color-primary *,
                        button[type="submit"]:not(.fi-btn-color-gray) * {
                            color: #ffffff !important;
                        }
                        .fi-btn.fi-btn-color-primary:hover,
                        button[type="submit"]:not(.fi-btn-color-gray):hover {
                            background-color: rgb(var(--primary-700)) !important;
                            box-shadow: 0 4px 6px -1px rgba(var(--primary-600), 0.2), 0 2px 4px -2px rgba(var(--primary-600), 0.2) !important;
                        }

                        /* Parent menu group labels (menu cha) - Extra Bold and High Contrast */
                        .fi-sidebar-group-label {
                            color: #94A3B8 !important;
                            font-weight: 700 !important;
                            font-size: 0.625rem !important;
                            letter-spacing: 0.08em !important;
                            text-transform: uppercase !important;
                        }
                        .dark .fi-sidebar-group-label {
                            color: #ffffff !important; /* White in dark mode */
                        }
                        .fi-sidebar-group-collapse-button {
                            color: #374151 !important; /* Slate-700 */
                        }
                        .dark .fi-sidebar-group-collapse-button {
                            color: #cbd5e1 !important; /* Slate-300 */
                        }

                        /* Base inactive menu item styling - darker, crisp, and bold */
                        .fi-sidebar-item-button {
                            font-size: 0.8125rem !important;
                            letter-spacing: 0 !important;
                            transition: all 0.2s ease !important;
                        }
                        
                        /* Inactive menu item text color and font weight (excluding icons) */
                        .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) .fi-sidebar-item-label {
                            color: #334155 !important;
                            font-weight: 500 !important;
                        }

                        /* Inactive items: Dark Mode base style */
                        .dark .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) .fi-sidebar-item-label {
                            color: #e5e7eb !important; /* Gray-200 */
                        }

                        /* Inactive items hover style: Light Mode */
                        .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > .fi-sidebar-item-button:hover,
                        .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > a:hover {
                            background-color: #f3f4f6 !important; /* Slate-100 */
                            color: #111827 !important; /* Slate-900 */
                        }
                        .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > .fi-sidebar-item-button:hover *,
                        .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > a:hover * {
                            color: #111827 !important;
                        }

                        /* Inactive items hover style: Dark Mode override */
                        .dark .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > .fi-sidebar-item-button:hover,
                        .dark .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > a:hover {
                            background-color: rgba(255, 255, 255, 0.05) !important; /* Subtle dark gray hover overlay */
                            color: #ffffff !important;
                        }
                        .dark .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > .fi-sidebar-item-button:hover *,
                        .dark .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) > a:hover * {
                            color: #ffffff !important;
                        }

                        /* Active item styling: Solid primary background with white text and icon */
                        .fi-sidebar-item.fi-active > .fi-sidebar-item-button,
                        .fi-sidebar-item-active > .fi-sidebar-item-button,
                        .fi-sidebar-item-active > a {
                            background-color: rgb(var(--primary-600)) !important;
                            color: #ffffff !important;
                            font-weight: 500 !important;
                            box-shadow: 0 4px 6px -1px rgba(var(--primary-600), 0.2), 0 2px 4px -2px rgba(var(--primary-600), 0.2) !important;
                        }
                        
                        /* Active item hover style: Slightly darker primary, maintaining white text */
                        .fi-sidebar-item.fi-active > .fi-sidebar-item-button:hover,
                        .fi-sidebar-item-active > .fi-sidebar-item-button:hover,
                        .fi-sidebar-item-active > a:hover {
                            background-color: rgb(var(--primary-700)) !important; /* Darker Primary */
                            color: #ffffff !important;
                        }

                        /* FORCE all child elements of active item (like span label, icons) to inherit white color */
                        .fi-sidebar-item.fi-active > .fi-sidebar-item-button *:not(.fi-sidebar-item-icon):not(.fi-sidebar-item-icon *),
                        .fi-sidebar-item-active > .fi-sidebar-item-button *:not(.fi-sidebar-item-icon):not(.fi-sidebar-item-icon *),
                        .fi-sidebar-item-active > a *:not(.fi-sidebar-item-icon):not(.fi-sidebar-item-icon *) {
                            color: #ffffff !important;
                        }

                        /*
                         * Badge (số đếm bên phải menu) khi menu đang active:
                         * quy tắc "tô trắng mọi phần tử con" ở trên làm CHỮ trong badge thành trắng,
                         * trong khi NỀN badge vẫn sáng → số biến mất. Đổi nền badge sang trắng-mờ
                         * để số trắng vẫn đọc được trên nền primary.
                         */
                        .fi-sidebar-item.fi-active .fi-sidebar-item-badge,
                        .fi-sidebar-item-active .fi-sidebar-item-badge,
                        .fi-sidebar-item.fi-active .fi-sidebar-item-badge .fi-badge,
                        .fi-sidebar-item-active .fi-sidebar-item-badge .fi-badge {
                            background-color: rgba(255, 255, 255, 0.25) !important;
                            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.45) !important;
                            color: #ffffff !important;
                        }
                        .fi-sidebar-item.fi-active .fi-sidebar-item-badge *,
                        .fi-sidebar-item-active .fi-sidebar-item-badge * {
                            background-color: transparent !important;
                            color: #ffffff !important;
                        }
                        
                        /*
                         * Icon sidebar: Khung màu thương hiệu BlueFire cho từng phân hệ nghiệp vụ.
                         * Mặc định MỌI icon đều có khung màu nhạt mềm mại `#EBF3FF` + icon xanh `#1267E8`.
                         * Khi Active: Chuyển sang khung mờ trắng + Icon trắng tinh trên nền primary gradient.
                         */
                        .fi-sidebar-item-icon {
                            width: 1.5rem !important;
                            height: 1.5rem !important;
                            padding: 0.3125rem !important;
                            border-radius: 0.375rem !important;
                            flex-shrink: 0 !important;
                            transition: all 0.15s ease !important;
                            background-color: #EBF3FF !important;
                            color: #1267E8 !important;
                        }

                        /* 1. TỔNG QUAN & NGUYÊN LIỆU & KHO & CUNG ỨNG & VẬN HÀNH BẾP (Light Mode) */
                        .fi-sidebar-group:first-child .fi-sidebar-item:first-child .fi-sidebar-item-icon,
                        a[href$="/admin"] .fi-sidebar-item-icon,
                        a[href$="/admin/"] .fi-sidebar-item-icon,
                        a[href*="/admin/ingredients"] .fi-sidebar-item-icon,
                        a[href*="/admin/units"] .fi-sidebar-item-icon,
                        a[href*="/admin/ingredient-types"] .fi-sidebar-item-icon,
                        a[href*="/admin/menus"] .fi-sidebar-item-icon,
                        a[href*="/admin/lap-thuc-don-tuan"] .fi-sidebar-item-icon,
                        a[href*="/admin/employees"] .fi-sidebar-item-icon,
                        a[href*="/admin/users"] .fi-sidebar-item-icon,
                        a[href*="/admin/chat-nhom"] .fi-sidebar-item-icon {
                            background-color: #EBF3FF !important; color: #1267E8 !important;
                        }

                        a[href*="/admin/recipes"] .fi-sidebar-item-icon,
                        a[href*="/admin/recipe-types"] .fi-sidebar-item-icon,
                        a[href*="/admin/menu-audit-logs"] .fi-sidebar-item-icon {
                            background-color: #FFFBEB !important; color: #D97706 !important;
                        }

                        a[href*="/admin/stocks"] .fi-sidebar-item-icon,
                        a[href*="/admin/stock-transactions"] .fi-sidebar-item-icon,
                        a[href*="/admin/stock-transfers"] .fi-sidebar-item-icon,
                        a[href*="/admin/list-hang"] .fi-sidebar-item-icon,
                        a[href*="/admin/purchase-orders"] .fi-sidebar-item-icon,
                        a[href*="/admin/bao-cao"] .fi-sidebar-item-icon {
                            background-color: #ECFDF5 !important; color: #059669 !important;
                        }

                        a[href*="/admin/suppliers"] .fi-sidebar-item-icon {
                            background-color: #FFF7ED !important; color: #EA580C !important;
                        }

                        a[href*="/admin/food-safety-audits"] .fi-sidebar-item-icon,
                        a[href*="/admin/timekeepings"] .fi-sidebar-item-icon,
                        a[href*="/admin/shifts"] .fi-sidebar-item-icon,
                        a[href*="/admin/areas"] .fi-sidebar-item-icon,
                        a[href*="/admin/kitchens"] .fi-sidebar-item-icon,
                        a[href*="/admin/kitchen-types"] .fi-sidebar-item-icon {
                            background-color: #F0F9FF !important; color: #0284C7 !important;
                        }

                        a[href*="/admin/leave-overtimes"] .fi-sidebar-item-icon,
                        a[href*="/admin/leave-types"] .fi-sidebar-item-icon {
                            background-color: #FFF1F2 !important; color: #E11D48 !important;
                        }

                        a[href*="/admin/departments"] .fi-sidebar-item-icon,
                        a[href*="/admin/positions"] .fi-sidebar-item-icon,
                        a[href*="/shield/roles"] .fi-sidebar-item-icon,
                        a[href*="/roles"] .fi-sidebar-item-icon {
                            background-color: #F5F3FF !important; color: #7C3AED !important;
                        }

                        a[href*="/admin/system-settings"] .fi-sidebar-item-icon {
                            background-color: #F1F5F9 !important; color: #64748B !important;
                        }

                        /* 2. Menu ACTIVE: Đặt sau cùng với selector rộng để luôn ghi đè khi mục đang được chọn */
                        .fi-sidebar-item-active .fi-sidebar-item-icon,
                        .fi-sidebar-item.fi-active .fi-sidebar-item-icon,
                        .fi-sidebar-item-button-active .fi-sidebar-item-icon,
                        a[aria-current="page"] .fi-sidebar-item-icon,
                        a.fi-active .fi-sidebar-item-icon,
                        .fi-active .fi-sidebar-item-icon,
                        li:has(a[aria-current="page"]) .fi-sidebar-item-icon {
                            background-color: rgba(255, 255, 255, 0.22) !important;
                            color: #ffffff !important;
                        }

                        /* Hover nhẹ cho icon chưa active */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)):not(:has(a[aria-current="page"])) > .fi-sidebar-item-button:hover .fi-sidebar-item-icon,
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)):not(:has(a[aria-current="page"])) > a:hover .fi-sidebar-item-icon {
                            transform: scale(1.06) !important;
                        }

                        /* 3. Dark Mode Support */
                        .dark a[href*="/admin/recipes"] .fi-sidebar-item-icon,
                        .dark a[href*="/admin/recipe-types"] .fi-sidebar-item-icon {
                            background-color: rgba(217, 119, 6, 0.18) !important; color: #FBBF24 !important;
                        }
                        .dark a[href*="/admin/stocks"] .fi-sidebar-item-icon,
                        .dark a[href*="/admin/list-hang"] .fi-sidebar-item-icon,
                        .dark a[href*="/admin/purchase-orders"] .fi-sidebar-item-icon,
                        .dark a[href*="/admin/bao-cao"] .fi-sidebar-item-icon {
                            background-color: rgba(5, 150, 105, 0.18) !important; color: #34D399 !important;
                        }
                        .dark a[href$="/admin"] .fi-sidebar-item-icon,
                        .dark a[href*="/admin/ingredients"] .fi-sidebar-item-icon,
                        .dark a[href*="/admin/menus"] .fi-sidebar-item-icon,
                        .dark a[href*="/admin/employees"] .fi-sidebar-item-icon,
                        .dark a[href*="/admin/chat-nhom"] .fi-sidebar-item-icon,
                        .dark a[href*="/admin/users"] .fi-sidebar-item-icon {
                            background-color: rgba(18, 103, 232, 0.18) !important; color: #60A5FA !important;
                        }
                        .dark a[href*="/admin/suppliers"] .fi-sidebar-item-icon {
                            background-color: rgba(234, 88, 12, 0.18) !important; color: #FB923C !important;
                        }
                        .dark a[href*="/admin/food-safety-audits"] .fi-sidebar-item-icon,
                        .dark a[href*="/admin/timekeepings"] .fi-sidebar-item-icon,
                        .dark a[href*="/admin/shifts"] .fi-sidebar-item-icon,
                        .dark a[href*="/admin/areas"] .fi-sidebar-item-icon,
                        .dark a[href*="/admin/kitchens"] .fi-sidebar-item-icon {
                            background-color: rgba(2, 132, 199, 0.18) !important; color: #38BDF8 !important;
                        }
                        .dark a[href*="/admin/leave-overtimes"] .fi-sidebar-item-icon,
                        .dark a[href*="/admin/leave-types"] .fi-sidebar-item-icon {
                            background-color: rgba(225, 29, 72, 0.18) !important; color: #FB7185 !important;
                        }
                        .dark a[href*="/admin/departments"] .fi-sidebar-item-icon,
                        .dark a[href*="/admin/positions"] .fi-sidebar-item-icon,
                        .dark a[href*="/shield/roles"] .fi-sidebar-item-icon,
                        .dark a[href*="/roles"] .fi-sidebar-item-icon {
                            background-color: rgba(124, 58, 237, 0.18) !important; color: #A78BFA !important;
                        }

                        /* Giữ nguyên Active White trên Dark Mode */
                        .dark .fi-sidebar-item-active .fi-sidebar-item-icon,
                        .dark .fi-sidebar-item.fi-active .fi-sidebar-item-icon,
                        .dark a[aria-current="page"] .fi-sidebar-item-icon,
                        .dark .fi-active .fi-sidebar-item-icon {
                            background-color: rgba(255, 255, 255, 0.22) !important;
                            color: #ffffff !important;
                        }

                        /* Recipe create/edit form: match BA mockup spacing and cost table emphasis */
                        .fi-resource-recipes .fi-header-heading {
                            font-size: 1.875rem !important;
                            line-height: 2.25rem !important;
                            font-weight: 800 !important;
                            color: #111827 !important;
                        }
                        .dark .fi-resource-recipes .fi-header-heading {
                            color: #ffffff !important;
                        }
                        .fi-resource-recipes .fi-header-subheading {
                            color: #64748b !important;
                            font-size: 1rem !important;
                            font-weight: 500 !important;
                        }
                        .fi-resource-recipes .recipe-form-section,
                        .fi-resource-recipes .recipe-cost-section {
                            border: 1px solid #dbe3ee !important;
                            border-radius: 1rem !important;
                            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05) !important;
                        }
                        .dark .fi-resource-recipes .recipe-form-section,
                        .dark .fi-resource-recipes .recipe-cost-section {
                            border-color: #374151 !important;
                        }
                        .fi-resource-recipes .recipe-cost-section .fi-fo-repeater-item {
                            border: 0 !important;
                            box-shadow: none !important;
                            background: transparent !important;
                            display: grid !important;
                            grid-template-columns: 3.5rem minmax(0, 1fr) 2.5rem !important;
                            gap: 1rem !important;
                            align-items: center !important;
                            padding: 1rem !important;
                        }
                        .fi-resource-recipes .recipe-cost-section .fi-fo-repeater-item-header {
                            display: contents !important;
                        }
                        .fi-resource-recipes .recipe-cost-section .fi-fo-repeater-item-header > h4 {
                            grid-column: 1 !important;
                            grid-row: 1 !important;
                            align-self: start !important;
                            text-align: center !important;
                            line-height: 2.625rem !important;
                            white-space: nowrap !important;
                        }
                        .fi-resource-recipes .recipe-cost-section .recipe-stt-label {
                            display: block !important;
                            margin-bottom: 0.5rem !important;
                            line-height: 1.5rem !important;
                            font-weight: 600 !important;
                        }
                        .fi-resource-recipes .recipe-cost-section .fi-fo-repeater-item-header > ul {
                            grid-column: 3 !important;
                            grid-row: 1 !important;
                            align-self: start !important;
                            justify-self: center !important;
                            margin-inline-start: 0 !important;
                            margin-top: 2rem !important;
                        }
                        .fi-resource-recipes .recipe-cost-section .fi-fo-repeater-item-content {
                            grid-column: 2 !important;
                            grid-row: 1 !important;
                            padding: 0 !important;
                            min-width: 0 !important;
                        }
                        @media (max-width: 640px) {
                            .fi-resource-recipes .recipe-cost-section .fi-fo-repeater-item {
                                grid-template-columns: 2.75rem minmax(0, 1fr) 2.5rem !important;
                                gap: 0.5rem !important;
                                padding: 0.75rem !important;
                            }
                        }
                        .fi-resource-recipes .recipe-line-total {
                            display: flex !important;
                            align-items: center !important;
                            min-height: 2.625rem !important;
                            color: #ea580c !important;
                            font-weight: 800 !important;
                            padding-top: 0 !important;
                            white-space: nowrap !important;
                        }
                        .fi-resource-recipes .recipe-cost-section .fi-fo-field-wrp:has(.recipe-line-total) .fi-fo-field-wrp-label {
                            white-space: nowrap !important;
                        }
                        .fi-resource-recipes .recipe-total-cost {
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            min-height: 4rem !important;
                            border: 1px solid #fdba74 !important;
                            border-radius: 0.75rem !important;
                            background: linear-gradient(90deg, #fff7ed, #fef3c7) !important;
                            color: #9a3412 !important;
                            font-size: 1.125rem !important;
                            font-weight: 800 !important;
                        }
                        .dark .fi-resource-recipes .recipe-total-cost {
                            background: linear-gradient(90deg, rgba(120, 53, 4, 0.25), rgba(146, 64, 14, 0.25)) !important;
                            border-color: #7c2d12 !important;
                            color: #fdba74 !important;
                        }
                        .fi-resource-recipes .recipe-cost-note {
                            color: #64748b !important;
                            font-weight: 500 !important;
                        }
                    </style>
                ')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(fn () => __('catalog.groups.ingredients_inventory'))
                    ->collapsible(false),
                NavigationGroup::make()
                    ->label(fn () => __('catalog.groups.supply_inventory'))
                    ->collapsible(false),
                NavigationGroup::make()
                    ->label(fn () => __('catalog.groups.area_kitchen'))
                    ->collapsible(false),
                NavigationGroup::make()
                    ->label(fn () => __('catalog.groups.kitchen_operations'))
                    ->collapsible(false),
                NavigationGroup::make()
                    ->label(fn () => __('catalog.groups.hr'))
                    ->collapsible(false),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocale::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * Safely get a setting value with fallback for fresh installs.
     */
    private function getSetting(string $key, mixed $default = null): mixed
    {
        try {
            return Setting::get($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * Get a public URL for a setting that stores a file path.
     */
    private function getSettingFileUrl(string $key, string $fallbackUrl): string
    {
        $path = $this->getSetting($key);

        if ($path) {
            return Storage::disk('public')->url($path);
        }

        return $fallbackUrl;
    }
}
