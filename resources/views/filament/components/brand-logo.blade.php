@php
    $logoPath = \App\Models\Setting::get('site_logo');
    $displayName = $siteName ?? 'Bluefire Catering';
    $hasLogo = $logoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath);
@endphp

<div class="flex items-center gap-3 pt-1 pb-3">
    @if($hasLogo)
        <!-- Custom Logo Image -->
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) }}" 
             alt="" 
             style="height: 2.2rem; max-width: 80px; object-fit: contain;" 
             class="fi-logo rounded-lg">
    @else
        <!-- Default Red Laravel style gradient container -->
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-red-600 to-orange-500 flex items-center justify-center shadow-lg shadow-red-500/25">
            <!-- Isometric Laravel style Logo SVG -->
            <svg class="w-5.5 h-5.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
            </svg>
        </div>
    @endif

    <!-- Dynamic Site Name Text -->
    <span class="text-lg font-black tracking-tight text-gray-900 dark:text-white">
        {{ $displayName }}
    </span>
</div>

