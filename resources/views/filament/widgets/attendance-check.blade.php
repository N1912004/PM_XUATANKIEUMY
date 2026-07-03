<style>
    .custom-checkin-btn {
        background-color: rgb(var(--primary-600)) !important;
        border-color: rgb(var(--primary-600)) !important;
    }
    .custom-checkin-btn:hover {
        background-color: rgb(var(--primary-700)) !important;
        border-color: rgb(var(--primary-700)) !important;
    }
    .custom-checkin-avatar-border {
        border-color: rgba(var(--primary-200), 1) !important;
        background-color: rgba(var(--primary-50), 0.5) !important;
        color: rgb(var(--primary-600)) !important;
    }
    .dark .custom-checkin-avatar-border {
        border-color: rgba(var(--primary-800), 0.5) !important;
        background-color: rgba(var(--primary-900), 0.2) !important;
        color: rgb(var(--primary-400)) !important;
    }
</style>

<div class="p-6 bg-white rounded-xl border border-gray-250 shadow-sm dark:bg-gray-900 dark:border-gray-800">
    <!-- Top Row: Profile & Meta -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-gray-100 dark:border-gray-800">
        <!-- Left: Profile Block -->
        <div class="flex items-center gap-4">
            <!-- Avatar with initials fallback -->
            <div class="relative w-14 h-14 rounded-full overflow-hidden border flex items-center justify-center font-extrabold text-base custom-checkin-avatar-border">
                @if($employee && $employee->avatar_url)
                    <img src="{{ $employee->avatar_url }}" class="w-full h-full object-cover">
                @elseif($employee)
                    {{ collect(explode(' ', $employee->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('') }}
                @else
                    --
                @endif
            </div>
            <div>
                <h4 class="text-base font-extrabold text-gray-900 dark:text-white leading-tight">
                    {{ $employee->name ?? 'Chưa liên kết nhân viên' }}
                </h4>
                <p class="text-xs text-gray-400 font-bold mt-0.5">{{ $employee->code ?? 'NV000' }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold mt-0.5">
                    {{ $employee->position ?? 'Nhân viên' }} · {{ $employee->department ?? 'Chưa rõ' }}
                </p>
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
                    <div class="text-gray-900 dark:text-white font-extrabold text-xs">{{ $todayDateFormatted }} ({{ $todayDayName }})</div>
                </div>
            </div>

            <!-- Shift item -->
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wider">Ca làm việc</div>
                    <div class="text-gray-900 dark:text-white font-extrabold text-xs">
                        {{ $timekeeping && $timekeeping->shift ? $timekeeping->shift->name : 'Chưa xếp ca' }}
                        {{ $timekeeping && $timekeeping->shift && $timekeeping->shift->time_range ? ' · ' . $timekeeping->shift->time_range : '' }}
                    </div>
                </div>
            </div>

            <!-- Status item -->
            <div>
                <div class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Trạng thái</div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold 
                    @if($timekeeping && $timekeeping->status === 'Đúng giờ') bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 border border-green-200 dark:border-green-800/30
                    @elseif($timekeeping && $timekeeping->status === 'Đi trễ') bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400 border border-amber-200 dark:border-amber-800/30
                    @elseif($timekeeping && $timekeeping->status === 'Tăng ca') bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400 border border-blue-200 dark:border-blue-800/30
                    @else bg-gray-50 text-gray-700 dark:bg-gray-950/20 dark:text-gray-400 border border-gray-200 dark:border-gray-850/30
                    @endif">
                    {{ $timekeeping->status ?? 'Chưa chấm công' }}
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
                <span class="text-3xl font-black {{ $timekeeping && $timekeeping->check_in ? 'text-emerald-600 dark:text-emerald-450' : 'text-gray-300 dark:text-gray-700' }} leading-tight">
                    {{ $timekeeping && $timekeeping->check_in ? \Carbon\Carbon::parse($timekeeping->check_in)->format('H:i') : '--:--' }}
                </span>
                <span class="text-[11px] text-gray-450 font-semibold mt-0.5">{{ $todayDateFormatted }}</span>
            </div>

            <!-- Vertical Divider -->
            <div class="hidden sm:block w-px h-12 bg-gray-150 dark:bg-gray-800"></div>

            <!-- Check-out Time Box -->
            <div class="flex flex-col min-w-[80px]">
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Check-out</span>
                <span class="text-3xl font-black {{ $timekeeping && $timekeeping->check_out ? 'text-emerald-600 dark:text-emerald-450' : 'text-gray-300 dark:text-gray-700' }} leading-tight">
                    {{ $timekeeping && $timekeeping->check_out ? \Carbon\Carbon::parse($timekeeping->check_out)->format('H:i') : '--:--' }}
                </span>
                <span class="text-[11px] text-gray-450 font-semibold mt-0.5">{{ $todayDateFormatted }}</span>
            </div>
        </div>

        <!-- Right: Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto flex-1 max-w-3xl">
            <!-- Check-in Button -->
            @if($timekeeping && $timekeeping->check_in)
                <div class="flex items-center justify-center gap-3 w-full px-5 py-3 rounded-xl border border-green-200 bg-green-50/50 dark:border-green-900/30 dark:bg-green-950/10">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-450" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div class="text-left">
                        <div class="text-sm font-extrabold text-green-800 dark:text-green-400">Check-in</div>
                        <div class="text-[11px] text-green-650 dark:text-green-550 font-bold">Đã check-in lúc {{ \Carbon\Carbon::parse($timekeeping->check_in)->format('H:i') }}</div>
                    </div>
                </div>
            @else
                <button wire:click="checkIn" 
                        class="flex items-center justify-center gap-3 w-full px-5 py-3 rounded-xl text-white font-bold transition shadow-sm custom-checkin-btn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h3a3 3 0 013 3v1"/>
                    </svg>
                    <div class="text-left">
                        <div class="text-sm font-extrabold">Nhấn Check-in</div>
                        <div class="text-[10px] opacity-90">Ghi nhận giờ bắt đầu làm</div>
                    </div>
                </button>
            @endif

            <!-- Check-out Button -->
            @if($timekeeping && $timekeeping->check_out)
                <div class="flex items-center justify-center gap-3 w-full px-5 py-3 rounded-xl border border-green-200 bg-green-50/50 dark:border-green-900/30 dark:bg-green-950/10">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-450" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div class="text-left">
                        <div class="text-sm font-extrabold text-green-800 dark:text-green-400">Check-out</div>
                        <div class="text-[11px] text-green-650 dark:text-green-550 font-bold">Đã check-out lúc {{ \Carbon\Carbon::parse($timekeeping->check_out)->format('H:i') }}</div>
                    </div>
                </div>
            @elseif($timekeeping && $timekeeping->check_in)
                <button wire:click="checkOut" 
                        class="flex items-center justify-center gap-3 w-full px-5 py-3 rounded-xl border border-orange-500 bg-orange-600 hover:bg-orange-700 text-white font-bold transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h3a3 3 0 013 3v1"/>
                    </svg>
                    <div class="text-left">
                        <div class="text-sm font-extrabold">Nhấn Check-out</div>
                        <div class="text-[10px] opacity-90">Ghi nhận giờ kết thúc làm</div>
                    </div>
                </button>
            @else
                <div class="flex items-center justify-center gap-3 w-full px-5 py-3 rounded-xl border border-gray-250 bg-gray-50/50 dark:border-gray-800/30 dark:bg-gray-900/10 text-gray-400 cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                    </svg>
                    <div class="text-left">
                        <div class="text-sm font-extrabold">Check-out</div>
                        <div class="text-[11px] font-bold">Chưa thể thực hiện</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Alert Box: Confirmation -->
    @if($timekeeping)
        <div class="mt-5 flex items-center gap-2.5 px-4 py-3 rounded-lg bg-green-50 text-green-800 dark:bg-green-950/20 dark:text-green-400 border border-green-200 dark:border-green-900/30 text-xs font-semibold">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-450 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>Thời gian đã được lưu vào hệ thống chấm công ngày {{ $todayDateFormatted }}.</span>
        </div>
    @endif
</div>
