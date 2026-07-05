<style>
    .sidebar-footer-settings-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 10px 16px;
        border-radius: 8px;
        background-color: rgb(var(--primary-600)) !important;
        color: #ffffff !important;
        text-decoration: none !important;
        font-size: 13px;
        font-weight: bold;
        box-shadow: 0 4px 6px -1px rgba(var(--primary-600), 0.15) !important;
        transition: background-color 0.15s ease, box-shadow 0.15s ease;
    }
    .sidebar-footer-settings-btn:hover {
        background-color: rgb(var(--primary-700)) !important;
    }
</style>

<div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800" style="background: transparent;">
    <a href="{{ url('/admin/system-settings') }}" class="sidebar-footer-settings-btn">
        <svg class="w-4 h-4" 
             style="color: #ffffff !important; flex-shrink: 0;"
             fill="none" 
             stroke="currentColor" 
             stroke-width="2.5" 
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span style="color: #ffffff !important;">{{ __('Cài đặt hệ thống') }}</span>
    </a>
</div>
