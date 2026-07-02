<div class="p-6 bg-white rounded-xl border border-gray-250 shadow-sm dark:bg-gray-900 dark:border-gray-800">
    <!-- Top Row: Profile & Meta -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-gray-100 dark:border-gray-800">
        <!-- Left: Profile Block -->
        <div class="flex items-center gap-4">
            <!-- Avatar with initials fallback -->
            <div class="relative w-14 h-14 rounded-full overflow-hidden border border-blue-200 dark:border-blue-800 flex items-center justify-center bg-blue-50 text-blue-600 font-extrabold text-base">
                VA
            </div>
            <div>
                <h4 class="text-base font-extrabold text-gray-900 dark:text-white leading-tight">Nguyễn Van An</h4>
                <p class="text-xs text-gray-400 font-bold mt-0.5">NV001</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold mt-0.5">Nhân viên · Nhân sự</p>
            </div>
        </div>

        <!-- Right: Meta details -->
        <div class="flex flex-wrap items-center gap-x-8 gap-y-4 text-xs font-semibold text-gray-500 dark:text-gray-400">
            <!-- Date item -->
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wider">Hôm nay</div>
                    <div class="text-gray-900 dark:text-white font-extrabold text-xs">15/05/2026 (Thứ Sáu)</div>
                </div>
            </div>

            <!-- Shift item -->
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wider">Ca làm việc</div>
                    <div class="text-gray-900 dark:text-white font-extrabold text-xs">Ca sáng · 07:00 – 16:00</div>
                </div>
            </div>

            <!-- Status item -->
            <div>
                <div class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Trạng thái</div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 border border-green-200 dark:border-green-800/30">
                    Đang làm việc
                </span>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Times & Buttons -->
    <div class="flex flex-col lg:flex-row items-center justify-between gap-6 pt-6">
        <!-- Left: Times display -->
        <div class="flex items-center gap-8 w-full lg:w-auto">
            <!-- Check-in Time Box -->
            <div class="flex flex-col min-w-[80px]">
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Check-in</span>
                <span class="text-3xl font-black text-emerald-600 dark:text-emerald-450 leading-tight">07:01</span>
                <span class="text-[11px] text-gray-450 font-semibold mt-0.5">15/05/2026</span>
            </div>

            <!-- Vertical Divider -->
            <div class="hidden sm:block w-px h-12 bg-gray-150 dark:bg-gray-800"></div>

            <!-- Check-out Time Box -->
            <div class="flex flex-col min-w-[80px]">
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Check-out</span>
                <span class="text-3xl font-black text-emerald-600 dark:text-emerald-450 leading-tight">16:05</span>
                <span class="text-[11px] text-gray-450 font-semibold mt-0.5">15/05/2026</span>
            </div>
        </div>

        <!-- Right: Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto flex-1 max-w-3xl">
            <!-- Check-in Button -->
            <button class="flex items-center justify-center gap-3 w-full px-5 py-3 rounded-xl border border-green-200 bg-green-50/50 dark:border-green-900/30 dark:bg-green-950/10 cursor-default">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-450" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="text-left">
                    <div class="text-sm font-extrabold text-green-800 dark:text-green-400">Check-in</div>
                    <div class="text-[11px] text-green-600 dark:text-green-550 font-bold">Đã thực hiện lúc 07:01</div>
                </div>
            </button>

            <!-- Check-out Button -->
            <button class="flex items-center justify-center gap-3 w-full px-5 py-3 rounded-xl border border-blue-200 bg-blue-50/50 dark:border-blue-900/30 dark:bg-blue-950/10 cursor-default">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="text-left">
                    <div class="text-sm font-extrabold text-blue-800 dark:text-blue-400">Check-out</div>
                    <div class="text-[11px] text-blue-650 dark:text-blue-500 font-bold">Đã thực hiện lúc 16:05</div>
                </div>
            </button>
        </div>
    </div>

    <!-- Alert Box: Confirmation -->
    <div class="mt-5 flex items-center gap-2.5 px-4 py-3 rounded-lg bg-green-50 text-green-800 dark:bg-green-950/20 dark:text-green-400 border border-green-200 dark:border-green-900/30 text-xs font-semibold">
        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-450 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span>Thời gian đã được lưu vào bảng chấm công.</span>
    </div>
</div>
