@php
    $locale = app()->getLocale();
    $languages = [
        'vi' => [
            'name' => 'Tiếng Việt',
            'flag' => '🇻🇳',
            'short' => 'VI'
        ],
        'en' => [
            'name' => 'English',
            'flag' => '🇬🇧',
            'short' => 'EN'
        ],
    ];
    $currentLang = $languages[$locale] ?? $languages['vi'];
@endphp

<div x-data="{ open: false }" class="relative" @click.away="open = false" style="align-self: center; margin-right: 1rem;">
    <!-- Trigger Button -->
    <button @click="open = !open" 
            type="button"
            class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-850 hover:bg-gray-50 dark:hover:bg-gray-800 transition shadow-sm text-sm font-bold text-gray-750 dark:text-gray-200">
        <span class="text-base">{{ $currentLang['flag'] }}</span>
        <span>{{ $currentLang['short'] }}</span>
        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-40 rounded-xl bg-white dark:bg-gray-800 border border-gray-150 dark:border-gray-700 shadow-lg z-50 py-1 overflow-hidden"
         style="display: none;">
        @foreach($languages as $code => $lang)
            <a href="{{ route('lang.switch', $code) }}" 
               class="flex items-center gap-3 px-4 py-2 text-sm font-semibold text-gray-750 dark:text-gray-250 hover:bg-gray-50 dark:hover:bg-gray-750 transition {{ $locale === $code ? 'bg-blue-50/50 dark:bg-blue-950/20 text-blue-600 dark:text-blue-400' : '' }}">
                <span class="text-base">{{ $lang['flag'] }}</span>
                <span>{{ $lang['name'] }}</span>
            </a>
        @endforeach
    </div>
</div>
