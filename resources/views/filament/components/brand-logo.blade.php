@php
    $logoPath = \App\Models\Setting::get('site_logo');
    $displayName = $siteName ?? 'Bluefire Catering';
    $hasLogo = $logoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath);
@endphp

<div class="flex items-center justify-center w-full h-full py-1">
    @if($hasLogo)
        <!-- Custom Logo Image Only -->
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) }}" 
             alt="{{ $displayName }}" 
             style="max-height: 2.6rem; max-width: 140px; object-fit: contain;" 
             class="fi-logo rounded-lg transition-transform duration-200 hover:scale-105">
    @else
        <!-- Default Red Logo SVG Container if no image uploaded -->
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-red-600 to-orange-500 flex items-center justify-center shadow-lg shadow-red-500/25">
            <svg class="w-5.5 h-5.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
            </svg>
        </div>
    @endif
</div>
