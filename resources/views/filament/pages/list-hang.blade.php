<style>
    .custom-today-btn {
        background-color: rgba(var(--primary-500), 0.1) !important;
        color: rgb(var(--primary-600)) !important;
        border: 1px solid rgba(var(--primary-500), 0.2) !important;
    }
    .custom-today-btn:hover {
        background-color: rgba(var(--primary-500), 0.2) !important;
    }
    .dark .custom-today-btn {
        background-color: rgba(var(--primary-500), 0.2) !important;
        color: rgb(var(--primary-400)) !important;
        border-color: rgba(var(--primary-500), 0.3) !important;
    }
    .dark .custom-today-btn:hover {
        background-color: rgba(var(--primary-500), 0.3) !important;
    }
</style>

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Date and Shift Filters -->
        <div class="p-6 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-900 dark:border-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-6">
                <!-- Date Picker & navigation -->
                <div class="flex items-center gap-3">
                    <button wire:click="$set('date', '{{ \Carbon\Carbon::parse($date)->subDay()->toDateString() }}')"
                            class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400">
                        &larr;
                    </button>
                    <input type="date" wire:model.live="date" 
                           class="rounded-lg border-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white font-semibold">
                    <button wire:click="$set('date', '{{ \Carbon\Carbon::parse($date)->addDay()->toDateString() }}')"
                            class="p-2 rounded-lg border border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400">
                        &rarr;
                    </button>
                    <button wire:click="$set('date', '{{ now()->toDateString() }}')"
                            class="px-4 py-2 text-sm font-semibold rounded-lg custom-today-btn">
                        Hôm nay
                    </button>
                </div>

                <!-- Shift Selector checkboxes -->
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">Ca:</span>
                    <div class="flex items-center gap-3">
                        @foreach(\App\Models\Shift::all() as $shift)
                            <label class="inline-flex items-center px-3 py-1.5 rounded-full border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs font-semibold cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700">
                                <input type="checkbox" value="{{ $shift->id }}" wire:model.live="selectedShifts" 
                                       class="mr-2 rounded text-primary-600 focus:ring-primary-500 border-gray-300">
                                <span class="text-gray-850 dark:text-gray-200">{{ $shift->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Day Info -->
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase">
                        {{ \Carbon\Carbon::parse($date)->locale('vi')->dayName }} – {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 border border-green-100 dark:border-green-900/30">
                        Trong kỳ
                    </span>
                </div>
            </div>
        </div>

        @php
            $stats = $this->getStats();
            $groupedData = $this->getGroupedData();
        @endphp

        <!-- Stats Grid (ListHang.png Style) -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="p-3 mr-4 text-blue-500 bg-blue-50 rounded-lg dark:bg-blue-900/20 text-xl font-bold">
                    📅
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['shifts'] }}</p>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Ca phục vụ</p>
                </div>
            </div>

            <div class="flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="p-3 mr-4 text-green-500 bg-green-50 rounded-lg dark:bg-green-900/20 text-xl font-bold">
                    👥
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ number_format($stats['portions']) }}</p>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Tổng suất ăn</p>
                </div>
            </div>

            <div class="flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="p-3 mr-4 text-orange-500 bg-orange-50 rounded-lg dark:bg-orange-900/20 text-xl font-bold">
                    🍜
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['dishes'] }}</p>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Món cần nấu</p>
                </div>
            </div>

            <div class="flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="p-3 mr-4 text-purple-500 bg-purple-50 rounded-lg dark:bg-purple-900/20 text-xl font-bold">
                    🌿
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['ingredients'] }}</p>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Loại nguyên liệu</p>
                </div>
            </div>
        </div>

        <!-- Collapsible Content -->
        @if(empty($groupedData))
            <div class="p-12 text-center bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-900 dark:border-gray-800 text-gray-500">
                Không tìm thấy thực đơn nào được lập cho ngày và ca đã chọn.
            </div>
        @else
            <div class="space-y-6">
                @foreach($groupedData as $shiftData)
                    <div x-data="{ open: true }" class="bg-white rounded-xl border border-gray-150 shadow-sm dark:bg-gray-900 dark:border-gray-800 overflow-hidden">
                        <!-- Shift Header -->
                        <div @click="open = !open" 
                             class="flex justify-between items-center p-5 bg-gray-50 hover:bg-gray-100/70 dark:bg-gray-800/40 dark:hover:bg-gray-800/80 cursor-pointer border-b border-gray-100 dark:border-gray-800 transition">
                            <div class="flex items-center gap-3">
                                <span class="text-md font-bold text-primary-600 dark:text-primary-400 uppercase">{{ $shiftData['name'] }}</span>
                                <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">({{ $shiftData['time_range'] }})</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                                    {{ number_format($shiftData['total_portions']) }} suất
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                                <span>🍽️ {{ $shiftData['total_dishes'] }} món</span>
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                     :class="open ? 'transform rotate-180' : ''" 
                                     class="h-5 w-5 transition-transform duration-200" 
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <!-- Shift Content -->
                        <div x-show="open" x-transition class="p-5 space-y-4">
                            @foreach($shiftData['dishes'] as $dish)
                                <div x-data="{ expanded: false }" class="border border-gray-100 dark:border-gray-800 rounded-lg overflow-hidden">
                                    <!-- Dish Card Header -->
                                    <div @click="expanded = !expanded" 
                                         class="flex justify-between items-center p-4 bg-white dark:bg-gray-900 hover:bg-gray-50/50 dark:hover:bg-gray-800/20 cursor-pointer transition select-none">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $dish['name'] }}</span>
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-850 dark:text-gray-300">
                                                {{ $dish['type'] }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-4 text-sm">
                                            <span class="font-bold dark:text-primary-400" style="color: rgb(var(--primary-600));">{{ number_format($dish['portions']) }} suất</span>
                                            <span class="text-gray-400 dark:text-gray-500">{{ count($dish['ingredients']) }} NL</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" 
                                                 :class="expanded ? 'transform rotate-180' : ''" 
                                                 class="h-4 w-4 text-gray-400 transition-transform duration-150" 
                                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Dish Ingredients list (shown when expanded) -->
                                    <div x-show="expanded" x-transition class="border-t border-gray-100 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-900/50">
                                        <table class="w-full text-left border-collapse text-xs">
                                            <thead>
                                                <tr class="bg-gray-100/40 dark:bg-gray-800/30 text-gray-500 dark:text-gray-400 font-bold border-b border-gray-100 dark:border-gray-800">
                                                    <th class="p-3 w-12 text-center">STT</th>
                                                    <th class="p-3 w-28">Mã nguyên liệu</th>
                                                    <th class="p-3">Tên nguyên liệu</th>
                                                    <th class="p-3 text-right">Tổng định lượng</th>
                                                    <th class="p-3 w-20">Đơn vị</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                                                @foreach($dish['ingredients'] as $iIndex => $ing)
                                                    <tr class="hover:bg-gray-100/20 dark:hover:bg-gray-800/10">
                                                        <td class="p-3 text-center text-gray-400">{{ $iIndex + 1 }}</td>
                                                        <td class="p-3 font-mono font-semibold">{{ $ing['code'] }}</td>
                                                        <td class="p-3 font-semibold text-gray-900 dark:text-white">{{ $ing['name'] }}</td>
                                                        <td class="p-3 text-right font-bold text-primary-600 dark:text-primary-400">{{ number_format($ing['quantity'], 3) }}</td>
                                                        <td class="p-3">{{ $ing['unit'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
