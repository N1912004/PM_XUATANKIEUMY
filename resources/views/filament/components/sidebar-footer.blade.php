<div class="border-t border-gray-100 dark:border-gray-800">
    <a href="{{ url('/admin/system-settings') }}" class="sidebar-footer-settings-btn" title="{{ __('Cài đặt hệ thống') }}">
        <svg class="sidebar-footer-settings-icon"
             fill="none" 
             stroke="currentColor" 
             stroke-width="2.5" 
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span>{{ __('Cài đặt hệ thống') }}</span>
    </a>
</div>

<!-- Ép trình duyệt tải trước Font Awesome ngay khi load app để tránh lỗi ô vuông khi chuyển trang SPA (Livewire navigate) -->
<div style="position: absolute; left: -9999px; top: -9999px; opacity: 0; width: 0; height: 0; overflow: hidden;" aria-hidden="true">
    <i class="fa-solid fa-clock"></i>
    <i class="fa-brands fa-font-awesome"></i>
</div>
