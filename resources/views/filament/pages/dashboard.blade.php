<x-filament-panels::page>
    <!-- Custom styling to override standard styles and provide a premium wow-factor -->
    <style>
        .custom-dashboard-container {
            font-family: 'IBM Plex Sans', sans-serif;
        }
        .premium-card {
            background: #ffffff;
            border: 1px solid rgba(243, 244, 246, 1);
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(156, 163, 175, 0.05), 0 2px 8px -1px rgba(156, 163, 175, 0.03);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .premium-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -4px rgba(156, 163, 175, 0.12), 0 4px 12px -2px rgba(156, 163, 175, 0.06);
            border-color: rgba(219, 234, 254, 1);
        }
        .action-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(243, 244, 246, 1);
        }
        .action-card:hover {
            transform: scale(1.03);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.1);
        }
        .icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            width: 46px;
            height: 46px;
            transition: all 0.3s ease;
        }
        .pulse-soft {
            animation: pulse-animation 2s infinite;
        }
        @keyframes pulse-animation {
            0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.2); }
            70% { box-shadow: 0 0 0 8px rgba(37, 99, 235, 0); }
            100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
        }

        /* Dark Mode Overrides for Premium Dashboard */
        .dark .premium-card {
            background: #1f2937 !important; /* Gray-800 */
            border-color: #374151 !important; /* Gray-700 */
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.3) !important;
        }
        .dark .premium-card:hover {
            border-color: #1e3a8a !important; /* Dark Blue-900 */
            box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.4) !important;
        }
        .dark .action-card {
            border-color: #374151 !important; /* Gray-700 */
            background: linear-gradient(to bottom, #1f2937, #111827) !important;
        }
        .dark .action-card:hover {
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2) !important;
        }
        .dark .icon-box.bg-blue-50 { background-color: rgba(38, 125, 193, 0.15) !important; }
        .dark .icon-box { border: 1px solid rgba(255, 255, 255, 0.05) !important; }
        
        /* Vá Dark Mode cho các phần tử Custom trong Dashboard (do không được Tailwind compile) */
        .dark .bg-gray-50 {
            background-color: rgba(31, 41, 55, 0.45) !important; /* gray-800/45 */
        }
        .dark .bg-gray-50:hover {
            background-color: rgba(30, 58, 138, 0.15) !important; /* blue-950/15 */
        }
        .dark .bg-blue-100 {
            background-color: rgba(30, 58, 138, 0.6) !important; /* blue-950/60 */
            color: #93c5fd !important; /* blue-300 */
        }
        .dark .text-gray-900,
        .dark .text-gray-950 {
            color: #ffffff !important;
        }
        .dark .bg-red-50\/20 {
            background-color: rgba(127, 29, 29, 0.15) !important; /* red-950/15 */
        }
        .dark .border-red-100 {
            border-color: rgba(127, 29, 29, 0.25) !important;
        }
        .dark .bg-red-100 {
            background-color: rgba(127, 29, 29, 0.45) !important;
            color: #fca5a5 !important;
        }
        .dark .action-card {
            background: linear-gradient(to bottom, #1e293b, #0f172a) !important;
        }
        .dark .action-card span {
            color: #e2e8f0 !important;
        }
        .dark .hover\:bg-gray-50:hover {
            background-color: rgba(255, 255, 255, 0.05) !important; /* màu tối nhẹ khi hover */
        }
        
        
        /* Premium Banner with Solid Fallback CSS Gradient */
        .premium-banner {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            background: linear-gradient(135deg, rgb(var(--primary-800)) 0%, rgb(var(--primary-600)) 50%, rgb(var(--primary-500)) 100%) !important;
            padding: 32px !important;
            color: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(var(--primary-600), 0.15) !important;
        }
        .premium-banner h2 {
            color: #ffffff !important;
            font-size: 1.875rem !important;
            font-weight: 800 !important;
            line-height: 1.25 !important;
        }
        .premium-banner p {
            color: #e0e7ff !important;
            font-size: 0.95rem !important;
            font-weight: 500 !important;
            opacity: 0.95 !important;
        }
        .premium-banner .badge-status {
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: #e0e7ff !important;
            backdrop-filter: blur(4px) !important;
            border-radius: 9999px !important;
            padding: 4px 12px !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
        }
        .premium-banner .date-box {
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
            backdrop-filter: blur(4px) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 12px !important;
            padding: 12px 20px !important;
            font-size: 0.875rem !important;
            font-weight: 700 !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }
        
        .custom-dashboard-link {
            color: rgb(var(--primary-600)) !important;
        }
        .custom-dashboard-link:hover {
            color: rgb(var(--primary-500)) !important;
        }
        .custom-dashboard-btn {
            background-color: rgb(var(--primary-600)) !important;
            box-shadow: 0 4px 6px -1px rgba(var(--primary-500), 0.2), 0 2px 4px -2px rgba(var(--primary-500), 0.2) !important;
        }
        .custom-dashboard-btn:hover {
            background-color: rgb(var(--primary-500)) !important;
        }

        .custom-stats-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.25rem;
        }
        @media (min-width: 640px) {
            .custom-stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .custom-stats-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .custom-actions-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .custom-actions-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    </style>

    <div class="custom-dashboard-container space-y-6">
        <!-- 1. Header Banner (Rich Gradient & Glassmorphism) -->
        <div class="premium-banner">
            <div class="absolute right-0 top-0 -mr-6 -mt-6 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute bottom-0 left-1/4 -mb-10 h-32 w-32 rounded-full bg-white/10 blur-xl"></div>
            <div class="absolute top-1/2 left-10 -mt-12 h-16 w-16 rounded-full bg-white/10 blur-lg"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-2">
                    <div class="badge-status inline-flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-green-400 animate-ping"></span>
                        {{ __('Hệ thống đã sẵn sàng') }}
                    </div>
                    <h2>
                        {{ __($greeting) }}, {{ auth()->user()->name }}
                    </h2>
                    <p>
                        {{ __('Báo cáo tổng hợp vận hành nhà ăn, quản lý kho hàng và đặt hàng nguyên liệu ngày hôm nay.') }}
                    </p>
                </div>
                <div class="date-box self-start md:self-auto">
                    <x-heroicon-m-calendar class="h-6 w-6 text-blue-200" />
                    <span>{{ $todayFormatted }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Operational Stats Widgets (Premium Styled Cards) -->
        <div class="custom-stats-grid">
            <!-- Portions Today -->
            <div class="premium-card p-6 border-l-4 border-l-blue-600">
                <div class="flex items-center justify-between">
                    <div class="icon-box bg-blue-50 dark:bg-blue-950/20 text-blue-600">
                        <x-heroicon-o-book-open class="h-6 w-6" />
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('Hôm nay') }}</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ number_format($totalPortionsToday) }}</h3>
                    <p class="mt-1 text-xs font-bold text-gray-500 uppercase tracking-wide">{{ __('Suất ăn dự kiến') }}</p>
                </div>
            </div>

            <!-- Ingredients -->
            <div class="premium-card p-6 border-l-4 border-l-blue-600">
                <div class="flex items-center justify-between">
                    <div class="icon-box bg-blue-50 dark:bg-blue-950/20 text-blue-600">
                        <x-heroicon-o-archive-box class="h-6 w-6" />
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('Nguyên liệu') }}</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ number_format($totalIngredients) }}</h3>
                    <p class="mt-1 text-xs font-bold text-gray-500 uppercase tracking-wide">{{ __('Đang hoạt động') }}</p>
                </div>
            </div>

            <!-- Pending POs -->
            <div class="premium-card p-6 border-l-4 border-l-blue-600">
                <div class="flex items-center justify-between">
                    <div class="icon-box bg-blue-50 dark:bg-blue-950/20 text-blue-600">
                        <x-heroicon-o-shopping-cart class="h-6 w-6" />
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('Đơn hàng') }}</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ number_format($pendingOrders) }}</h3>
                    <p class="mt-1 text-xs font-bold text-gray-500 uppercase tracking-wide">{{ __('Đang xử lý (PO)') }}</p>
                </div>
            </div>

            <!-- Active Employees -->
            <div class="premium-card p-6 border-l-4 border-l-blue-600">
                <div class="flex items-center justify-between">
                    <div class="icon-box bg-blue-50 dark:bg-blue-950/20 text-blue-600">
                        <x-heroicon-o-users class="h-6 w-6" />
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('Bếp ăn') }}</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ number_format($activeEmployees) }}</h3>
                    <p class="mt-1 text-xs font-bold text-gray-500 uppercase tracking-wide">{{ __('Nhân viên làm việc') }}</p>
                </div>
            </div>
        </div>

        <!-- 3. Quick Actions Section -->
        <div class="premium-card p-6">
            <h3 class="text-base font-extrabold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <x-heroicon-s-bolt class="h-5 w-5 text-blue-600" />
                {{ __('Lối tắt tác vụ nhanh') }}
            </h3>
            <div class="custom-actions-grid">
                <a href="{{ url('/admin/menus') }}" class="action-card flex flex-col items-center justify-center p-5 rounded-2xl bg-gradient-to-b from-gray-50 to-white hover:from-blue-50/50 hover:to-blue-50/10 group hover:border-blue-300 dark:from-gray-800/40 dark:to-gray-900/40 dark:hover:from-blue-950/20">
                    <div class="rounded-full bg-blue-100 dark:bg-blue-900/30 p-3 mb-3 group-hover:scale-110 transition-transform">
                        <x-heroicon-o-calendar class="h-6 w-6 text-blue-600" />
                    </div>
                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200 group-hover:text-blue-800 dark:group-hover:text-blue-400">{{ __('Lập thực đơn') }}</span>
                </a>

                <a href="{{ url('/admin/purchase-orders') }}" class="action-card flex flex-col items-center justify-center p-5 rounded-2xl bg-gradient-to-b from-gray-50 to-white hover:from-blue-50/50 hover:to-blue-50/10 group hover:border-blue-300 dark:from-gray-800/40 dark:to-gray-900/40 dark:hover:from-blue-950/20">
                    <div class="rounded-full bg-blue-100 dark:bg-blue-900/30 p-3 mb-3 group-hover:scale-110 transition-transform">
                        <x-heroicon-o-shopping-bag class="h-6 w-6 text-blue-600" />
                    </div>
                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200 group-hover:text-blue-800 dark:group-hover:text-blue-400">{{ __('Đặt hàng NCC') }}</span>
                </a>

                <a href="{{ url('/admin/food-safety-audits') }}" class="action-card flex flex-col items-center justify-center p-5 rounded-2xl bg-gradient-to-b from-gray-50 to-white hover:from-blue-50/50 hover:to-blue-50/10 group hover:border-blue-300 dark:from-gray-800/40 dark:to-gray-900/40 dark:hover:from-blue-950/20">
                    <div class="rounded-full bg-blue-100 dark:bg-blue-900/30 p-3 mb-3 group-hover:scale-110 transition-transform">
                        <x-heroicon-o-shield-check class="h-6 w-6 text-blue-600" />
                    </div>
                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200 group-hover:text-blue-800 dark:group-hover:text-blue-400">{{ __('Kiểm thực ATTP') }}</span>
                </a>

                <a href="{{ url('/admin/timekeepings') }}" class="action-card flex flex-col items-center justify-center p-5 rounded-2xl bg-gradient-to-b from-gray-50 to-white hover:from-blue-50/50 hover:to-blue-50/10 group hover:border-blue-300 dark:from-gray-800/40 dark:to-gray-900/40 dark:hover:from-blue-950/20">
                    <div class="rounded-full bg-blue-100 dark:bg-blue-900/30 p-3 mb-3 group-hover:scale-110 transition-transform">
                        <x-heroicon-o-clock class="h-6 w-6 text-blue-600" />
                    </div>
                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200 group-hover:text-blue-800 dark:group-hover:text-blue-400">{{ __('Chấm công NV') }}</span>
                </a>
            </div>
        </div>

        <!-- 4. Detailed Sections (Two Column Layout) -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Left Column -->
            <div class="space-y-6">
                <!-- Today's Menus -->
                <div class="premium-card p-6">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-book-open class="h-5 w-5 text-blue-600" />
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white">{{ __('Thực đơn ca hôm nay') }}</h3>
                        </div>
                        <a href="{{ url('/admin/menus') }}" class="text-xs font-extrabold transition-colors uppercase tracking-wider custom-dashboard-link">{{ __('Xem tất cả') }}</a>
                    </div>
                    
                    @if(count($todayMenus) > 0)
                        <div class="space-y-4">
                            @foreach($todayMenus as $menu)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800/40 transition-colors hover:bg-blue-50/30 dark:hover:bg-blue-950/10">
                                    <div class="flex items-center gap-3">
                                        <div class="rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-extrabold text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                                            {{ __($menu->shift->name ?? 'Ca') }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                                {{ $menu->recipe->name ?? __('Món ăn') }}
                                            </p>
                                            <p class="text-2xs text-gray-400 mt-0.5 font-medium">
                                                {{ __('Phân loại') }}: {{ __($menu->recipe->type ?? 'Chưa rõ') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-extrabold text-gray-900 dark:text-white">
                                            {{ number_format($menu->estimated_portions) }}
                                        </p>
                                        <p class="text-2xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Suất ăn') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            {{ $todayMenus->links() }}
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="rounded-full bg-gray-50 p-4 mb-3 dark:bg-gray-800">
                                <x-heroicon-o-face-frown class="h-8 w-8 text-gray-400" />
                            </div>
                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400">{{ __('Hôm nay chưa thiết lập thực đơn') }}</p>
                            <a href="{{ url('/admin/menus') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-bold text-white transition-all custom-dashboard-btn">
                                <x-heroicon-m-plus class="h-4 w-4" /> {{ __('Thiết lập thực đơn mới') }}
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Low Stock Warnings -->
                <div class="premium-card p-6">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-red-500" />
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white">{{ __('Cảnh báo tồn kho tối thiểu') }}</h3>
                        </div>
                        <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-2xs font-extrabold text-red-700 dark:bg-red-950/40 dark:text-red-400">
                            {{ number_format($lowStockCount) }} {{ __('nguyên liệu') }}
                        </span>
                    </div>
                    
                    @if(count($lowStockIngredients) > 0)
                        <div class="space-y-3 max-h-[350px] overflow-y-auto pr-1">
                            @foreach($lowStockIngredients as $stock)
                                <div class="flex items-center justify-between rounded-xl border border-red-100 bg-red-50/20 p-4 dark:border-red-900/20 dark:bg-red-950/10">
                                    <div>
                                        <p class="text-sm font-bold text-gray-950 dark:text-white">
                                            {{ $stock['ingredient']['name'] ?? __('Nguyên liệu') }}
                                        </p>
                                        <p class="text-2xs text-gray-500 mt-1 font-medium">
                                            {{ __('Định mức an toàn') }}: <span class="font-bold text-gray-700 dark:text-gray-300">{{ number_format($stock['min_quantity']) }} {{ __($stock['ingredient']['unit'] ?? '') }}</span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-red-100 dark:bg-red-950 px-3 py-1.5 text-xs font-bold text-red-700 dark:text-red-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500 animate-ping"></span>
                                            {{ __('Tồn') }}: {{ number_format($stock['quantity']) }} {{ __($stock['ingredient']['unit'] ?? '') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="rounded-full bg-green-50 p-4 mb-3 dark:bg-green-950/20">
                                <x-heroicon-o-check-circle class="h-8 w-8 text-green-500" />
                            </div>
                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400">{{ __('Kho hàng ở mức an toàn ổn định') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Supplier Document Expiry Warnings -->
                @if(count($expiringSupplierDocs) > 0)
                    <div class="premium-card p-6">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-document-check class="h-5 w-5 text-amber-500" />
                                <h3 class="text-base font-extrabold text-gray-900 dark:text-white">{{ __('Hồ sơ NCC sắp hết hạn / quá hạn') }}</h3>
                            </div>
                            <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-2xs font-extrabold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400">
                                {{ count($expiringSupplierDocs) }} {{ __('hồ sơ') }}
                            </span>
                        </div>
                        <div class="space-y-3 max-h-[280px] overflow-y-auto pr-1">
                            @foreach($expiringSupplierDocs as $doc)
                                <div class="flex items-center justify-between rounded-xl border p-3 {{ $doc['expired'] ? 'border-red-100 bg-red-50/20 dark:border-red-900/20 dark:bg-red-950/10' : 'border-amber-100 bg-amber-50/20 dark:border-amber-900/20 dark:bg-amber-950/10' }}">
                                    <div>
                                        <p class="text-sm font-bold text-gray-950 dark:text-white">{{ $doc['document'] }}</p>
                                        <p class="text-2xs text-gray-500 mt-0.5 font-medium">{{ $doc['supplier'] }}</p>
                                    </div>
                                    <span class="text-xs font-bold {{ $doc['expired'] ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }}">
                                        {{ $doc['expired'] ? __('Quá hạn') : __('Hết hạn') }} {{ $doc['expires_at'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Recent Purchase Orders -->
                <div class="premium-card p-6">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-document-text class="h-5 w-5 text-blue-600" />
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white">{{ __('Đơn hàng mới tạo (PO)') }}</h3>
                        </div>
                        <a href="{{ url('/admin/purchase-orders') }}" class="text-xs font-extrabold transition-colors uppercase tracking-wider custom-dashboard-link">{{ __('Xem tất cả') }}</a>
                    </div>
                    
                    @if(count($recentOrders) > 0)
                        <div class="space-y-4">
                            @foreach($recentOrders as $order)
                                <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="rounded-lg bg-gray-100 dark:bg-gray-800 p-2 font-mono text-xs font-bold text-gray-700 dark:text-gray-300">
                                            PO
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                                {{ $order['code'] }}
                                            </p>
                                            <p class="text-2xs text-gray-400 mt-0.5 font-medium">
                                                {{ __('Nhà cung cấp') }}: {{ $order['supplier']['name'] ?? __('Chưa rõ') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        @php
                                            $badgeColor = match($order['status']) {
                                                'draft' => 'gray',
                                                'sent' => 'info',
                                                'checking' => 'warning',
                                                'done' => 'success',
                                                default => 'gray'
                                            };
                                            $badgeText = match($order['status']) {
                                                'draft' => __('Bản nháp'),
                                                'sent' => __('Đã gửi NCC'),
                                                'checking' => __('Đang kiểm hàng'),
                                                'done' => __('Hoàn thành'),
                                                default => $order['status']
                                            };
                                        @endphp
                                        <x-filament::badge :color="$badgeColor" size="sm" class="rounded-lg px-2.5 py-1">
                                            {{ $badgeText }}
                                        </x-filament::badge>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="rounded-full bg-gray-50 p-4 mb-3 dark:bg-gray-800">
                                <x-heroicon-o-clipboard class="h-8 w-8 text-gray-400" />
                            </div>
                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400">{{ __('Chưa có đơn đặt hàng nào gần đây') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Today's Food Safety Audits -->
                <div class="premium-card p-6">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-check-badge class="h-5 w-5 text-blue-600" />
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white">{{ __('Nhật ký kiểm thực ATTP hôm nay') }}</h3>
                        </div>
                        <a href="{{ url('/admin/food-safety-audits') }}" class="text-xs font-extrabold transition-colors uppercase tracking-wider custom-dashboard-link">{{ __('Kiểm thực') }}</a>
                    </div>
                    
                    @if(count($todayAudits) > 0)
                        <div class="space-y-3">
                            @foreach($todayAudits as $audit)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800/40 hover:bg-green-50/20 transition-colors">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-gray-950 dark:text-white">
                                                {{ __($audit->stage) }}
                                            </span>
                                            <span class="text-2xs font-semibold text-gray-400 bg-gray-200/50 dark:bg-gray-700 px-1.5 py-0.5 rounded-md">
                                                {{ __($audit->shift->name ?? 'Ca') }}
                                            </span>
                                        </div>
                                        <p class="text-2xs text-gray-500 mt-1 font-medium">
                                            {{ __('Kiểm tra viên') }}: <span class="font-bold text-gray-700 dark:text-gray-300">{{ $audit->inspected_by }}</span>
                                        </p>
                                    </div>
                                    <div>
                                        <x-filament::badge :color="$audit->status === 'Đạt' ? 'success' : 'danger'" class="rounded-lg px-2.5 py-1">
                                            {{ __($audit->status) }}
                                        </x-filament::badge>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            {{ $todayAudits->links() }}
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="rounded-full bg-amber-50 p-4 mb-3 dark:bg-amber-950/20">
                                <x-heroicon-o-shield-exclamation class="h-8 w-8 text-amber-500" />
                            </div>
                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400">{{ __('Hôm nay chưa ghi nhận biên bản kiểm thực') }}</p>
                            <a href="{{ url('/admin/food-safety-audits') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-bold text-white transition-all custom-dashboard-btn">
                                <x-heroicon-m-plus class="h-4 w-4" /> {{ __('Bắt đầu kiểm thực 3 bước') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
