<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\EditProfile;
use App\Models\Setting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
            ->brandName($siteName)
            ->brandLogo(fn () => view('filament.components.brand-logo', ['siteName' => $this->getSetting('site_name', 'Bluefire Catering')]))
            ->darkModeBrandLogo(fn () => view('filament.components.brand-logo', ['siteName' => $this->getSetting('site_name', 'Bluefire Catering')]))
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
                    ->icon('heroicon-o-user'),
            ])
            ->colors([
                'primary' => Color::Blue,
            ])
            ->font('Inter')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => new HtmlString('
                    <style>
                        /* Base font styling for all sidebar elements to match */
                        .fi-sidebar, 
                        .fi-sidebar * {
                            font-family: \'Inter\', system-ui, -apple-system, sans-serif !important;
                        }

                        /* Base inactive menu item styling - darker, crisp, and high contrast */
                        .fi-sidebar-item-button {
                            color: #374151 !important; /* Slate-700 */
                            font-size: 0.9rem !important;
                            font-weight: 550 !important;
                            letter-spacing: -0.01em !important;
                            transition: all 0.2s ease !important;
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

                        /* Inactive items: Dark Mode base style */
                        .dark .fi-sidebar-item-button {
                            color: #9ca3af !important; /* Gray-400 */
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

                        /* Active item styling: Solid blue background with white text and icon */
                        .fi-sidebar-item.fi-active > .fi-sidebar-item-button,
                        .fi-sidebar-item-active > .fi-sidebar-item-button,
                        .fi-sidebar-item-active > a {
                            background-color: #2563eb !important;
                            color: #ffffff !important;
                            font-weight: 600 !important;
                            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2), 0 2px 4px -2px rgba(37, 99, 235, 0.2) !important;
                        }
                        
                        /* Active item hover style: Slightly darker blue, maintaining white text */
                        .fi-sidebar-item.fi-active > .fi-sidebar-item-button:hover,
                        .fi-sidebar-item-active > .fi-sidebar-item-button:hover,
                        .fi-sidebar-item-active > a:hover {
                            background-color: #1d4ed8 !important; /* Darker Blue */
                            color: #ffffff !important;
                        }

                        /* FORCE all child elements of active item (like span label, icons) to inherit white color */
                        .fi-sidebar-item.fi-active > .fi-sidebar-item-button *,
                        .fi-sidebar-item-active > .fi-sidebar-item-button *,
                        .fi-sidebar-item-active > a * {
                            color: #ffffff !important;
                        }
                        
                        /* Inactive menu items: Distinct colors for icons matching design system */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href$="/admin"] .fi-sidebar-item-icon { color: #3b82f6 !important; } /* Xanh dương sáng */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/recipes"] .fi-sidebar-item-icon { color: #f97316 !important; } /* Cam */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/warehouse"] .fi-sidebar-item-icon { color: #10b981 !important; } /* Xanh lá */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/list-hang"] .fi-sidebar-item-icon { color: #10b981 !important; } /* Xanh lá */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/ingredients"] .fi-sidebar-item-icon { color: #10b981 !important; } /* Xanh lá */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/suppliers"] .fi-sidebar-item-icon { color: #d97706 !important; } /* Cam đậm */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/purchase-orders"] .fi-sidebar-item-icon { color: #10b981 !important; } /* Xanh lá */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/menus"] .fi-sidebar-item-icon { color: #10b981 !important; } /* Xanh lá */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/food-safety-audit"] .fi-sidebar-item-icon { color: #2563eb !important; } /* Xanh dương */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/bao-cao"] .fi-sidebar-item-icon { color: #10b981 !important; } /* Xanh lá */
                        
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/employees"] .fi-sidebar-item-icon { color: #2563eb !important; } /* Xanh dương */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/timekeepings"] .fi-sidebar-item-icon { color: #2563eb !important; } /* Xanh dương */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/leave-overtimes"] .fi-sidebar-item-icon { color: #eab308 !important; } /* Vàng */
                        
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/chat-nhom"] .fi-sidebar-item-icon { color: #2563eb !important; } /* Xanh dương */
                        .fi-sidebar-item:not(.fi-sidebar-item-active):not(:has(.fi-active)) a[href*="/areas"] .fi-sidebar-item-icon { color: #2563eb !important; } /* Xanh dương */
                    </style>
                ')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->navigationGroups([
                'TỔNG QUAN',
                'XUẤT ĂN',
                'NHÂN SỰ',
                'CHAT NHÓM',
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
