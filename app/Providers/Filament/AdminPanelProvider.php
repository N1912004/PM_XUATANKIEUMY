<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\EditProfile;
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
            ->login()
            ->profile(EditProfile::class)
            ->sidebarCollapsibleOnDesktop()
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
                fn () => new HtmlString('
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-200 mr-3" style="align-self: center;">
                        '.e(filament()->auth()->user()?->name ?? auth()->user()?->name ?? 'Admin').'
                    </span>
                ')
            )
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('Cài đặt')
                    ->icon('heroicon-o-user')
                    ->url("javascript:Livewire.dispatch('open-profile-modal')"),
            ])
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render("@livewire('edit-profile-modal')"),
            )
            // Nạp Laravel Echo (Reverb) vào panel để chat nhóm nhận tin nhắn realtime.
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render("@vite('resources/js/app.js')"),
            )
            ->colors([
                'primary' => $this->getSetting('primary_color', '#2563eb'),
            ])
            ->font('IBM Plex Sans')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => new HtmlString('
                    <style>
                        /* Base font styling for all sidebar elements to match */
                        .fi-sidebar, 
                        .fi-sidebar * {
                            font-family: \'IBM Plex Sans\', sans-serif !important;
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
                            color: #111827 !important; /* Slate-900 */
                            font-weight: 800 !important; /* Extra Bold */
                            font-size: 0.78rem !important;
                            letter-spacing: 0.05em !important;
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
                            font-size: 0.92rem !important;
                            letter-spacing: -0.01em !important;
                            transition: all 0.2s ease !important;
                        }
                        
                        /* Inactive menu item text color and font weight (excluding icons) */
                        .fi-sidebar-item:not(.fi-active):not(.fi-sidebar-item-active) .fi-sidebar-item-label {
                            color: #1f2937 !important; /* Slate-800 */
                            font-weight: 600 !important;
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
                            font-weight: 600 !important;
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
                        .fi-sidebar-item.fi-active > .fi-sidebar-item-button *,
                        .fi-sidebar-item-active > .fi-sidebar-item-button *,
                        .fi-sidebar-item-active > a * {
                            color: #ffffff !important;
                        }
                        
                        /* Inactive menu items: Grouped and harmonized color themes by domain */
                        /* 1. Tổng quan & Giao tiếp (Vibrant Sky Blue) */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href$="/admin"] .fi-sidebar-item-icon,
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/chat-nhom"] .fi-sidebar-item-icon,
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/areas"] .fi-sidebar-item-icon { color: #0ea5e9 !important; }

                        /* 2. Vận hành Bếp & Thực đơn (Vibrant Orange) */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/recipes"] .fi-sidebar-item-icon,
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/menus"] .fi-sidebar-item-icon,
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/bao-cao"] .fi-sidebar-item-icon { color: #f97316 !important; }

                        /* 3. Cung ứng & Kho hàng (Vibrant Emerald) */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/stocks"] .fi-sidebar-item-icon,
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/list-hang"] .fi-sidebar-item-icon,
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/ingredients"] .fi-sidebar-item-icon,
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/suppliers"] .fi-sidebar-item-icon,
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/purchase-orders"] .fi-sidebar-item-icon { color: #10b981 !important; }

                        /* 4. An toàn Vệ sinh thực phẩm (Vibrant Indigo) */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/food-safety-audits"] .fi-sidebar-item-icon { color: #6366f1 !important; }

                        /* 5. Nhân sự & Chấm công (Vibrant Violet) */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/employees"] .fi-sidebar-item-icon,
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/timekeepings"] .fi-sidebar-item-icon,
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/leave-overtimes"] .fi-sidebar-item-icon { color: #8b5cf6 !important; }
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
                    ->label('TỔNG QUAN'),
                NavigationGroup::make()
                    ->label('VẬN HÀNH BẾP'),
                NavigationGroup::make()
                    ->label('CUNG ỨNG & KHO'),
                NavigationGroup::make()
                    ->label('NHÂN SỰ'),
                NavigationGroup::make()
                    ->label('CHAT NHÓM'),
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
