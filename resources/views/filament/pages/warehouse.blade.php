@push('styles')
<style>
        .wh-header-container {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .wh-title {
            font-size: 1.25rem;
            font-weight: 800;
            margin: 0 0 0.25rem;
            color: #0f172a;
        }
        .dark .wh-title {
            color: #ffffff;
        }
        .wh-subtitle {
            font-size: 0.78rem;
            color: #64748b;
            margin: 0;
        }
        .dark .wh-subtitle {
            color: #94a3b8;
        }
        .header-buttons {
            display: flex;
            gap: 0.5rem;
        }

        /* Fix responsive cho Header Actions của Filament khi thu nhỏ màn hình */
        .fi-header {
            flex-wrap: wrap !important;
            gap: 1rem !important;
        }
        .fi-header-actions {
            flex-wrap: wrap !important;
            gap: 0.5rem !important;
        }
        @media (max-width: 1023px) {
            .fi-header {
                flex-direction: column !important;
                align-items: flex-start !important;
            }
            .fi-header-actions {
                width: 100% !important;
                justify-content: flex-start !important;
            }
        }
        .wh-action-btn {
            height: 38px;
            padding: 0 0.875rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            cursor: pointer;
            font-size: 0.8125rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            color: #334155;
            transition: all 0.2s;
        }
        .wh-action-btn:hover {
            background: #f8fafc;
        }
        .dark .wh-action-btn {
            border-color: #334155;
            background: #1e293b;
            color: #cbd5e1;
        }
        .dark .wh-action-btn:hover {
            background: #334155;
        }
        .wh-action-btn-primary {
            background: rgb(var(--primary-600)) !important;
            color: #ffffff !important;
            border-color: rgb(var(--primary-600)) !important;
        }
        .wh-action-btn-primary:hover {
            background: rgb(var(--primary-700)) !important;
            border-color: rgb(var(--primary-700)) !important;
        }

        /* Stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            position: relative;
        }
        .dark .stat-card {
            background: #0f172a;
            border-color: #1e293b;
        }
        .stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            margin-bottom: 0.5rem;
        }
        /* KPI icon đồng nhất màu brand — màu chỉ dùng mã hóa trạng thái ở dòng dữ liệu
           (badge "Sắp hết" amber, chênh lệch xanh/đỏ), không tô ngẫu nhiên trên thẻ thống kê. */
        .ico-blue,
        .ico-green,
        .ico-orange,
        .ico-purple { background: rgb(var(--primary-50)); color: rgb(var(--primary-600)); }
        :root.dark .ico-blue,
        :root.dark .ico-green,
        :root.dark .ico-orange,
        :root.dark .ico-purple { background: rgb(var(--primary-950) / .2); color: rgb(var(--primary-400)); }
        
        .stat-val {
            font-size: 1.375rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }
        .dark .stat-val {
            color: #ffffff;
        }
        .stat-lbl {
            font-size: 0.75rem;
            color: #475569;
            font-weight: 600;
            margin-top: 0.125rem;
        }
        .dark .stat-lbl {
            color: #cbd5e1;
        }
        .stat-note {
            font-size: 0.65rem;
            color: #64748b;
            margin-top: 0.25rem;
        }
        .dark .stat-note {
            color: #94a3b8;
        }

        /* Tabs bar */
        .tabs-bar {
            display: flex;
            align-items: center;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            padding: 0.375rem;
            margin-bottom: 1.25rem;
            flex-wrap: wrap;
            gap: 0.375rem;
        }
        .dark .tabs-bar {
            border-color: #1e293b;
            background: #0f172a;
        }
        .tab-btn {
            height: 34px;
            padding: 0 0.875rem;
            border-radius: 8px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            border: none;
            background: transparent;
        }
        .tab-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .dark .tab-btn:hover {
            background: #1e293b;
            color: #ffffff;
        }
        .tab-btn.active {
            background: rgba(var(--primary-500), 0.1) !important;
            color: rgb(var(--primary-600)) !important;
        }
        .dark .tab-btn.active {
            background: rgba(var(--primary-500), 0.15) !important;
            color: rgb(var(--primary-400)) !important;
        }

        .filter-controls {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            margin-left: auto;
        }
        .search-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            padding: 0 0.625rem;
            height: 34px;
            width: 240px;
        }
        .dark .search-wrapper {
            border-color: #334155;
            background: #1e293b;
        }
        .search-wrapper input {
            border: none;
            outline: none;
            background: transparent;
            font-size: 0.78rem;
            width: 100%;
            padding-left: 0.375rem;
            color: #0f172a;
        }
        .dark .search-wrapper input {
            color: #ffffff;
        }
        .type-select {
            height: 34px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.78rem;
            padding: 0 2rem 0 0.75rem;
            background-color: #ffffff;
            color: #334155;
            outline: none;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.25em 1.25em;
            transition: all 0.2s;
        }
        .search-wrapper:focus-within {
            border-color: rgb(var(--primary-500)) !important;
            box-shadow: 0 0 0 2px rgba(var(--primary-500), 0.2);
        }
        .type-select:focus {
            border-color: rgb(var(--primary-500)) !important;
            box-shadow: 0 0 0 2px rgba(var(--primary-500), 0.2);
        }
        .dark .type-select {
            border-color: #334155;
            background-color: #1e293b;
            color: #cbd5e1;
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
        }

        /* Tables & form layout */
        .wh-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            padding: 1.25rem;
        }
        .dark .wh-card {
            background: #0f172a;
            border-color: #1e293b;
        }
        .wh-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem;
        }
        .wh-table th {
            padding: 0.75rem 1rem;
            text-align: left;
            font-size: 0.68rem;
            font-weight: 850;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .dark .wh-table th {
            background: rgba(30, 41, 59, 0.4);
            color: #94a3b8;
            border-color: #334155;
        }
        .wh-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-variant-numeric: tabular-nums;
        }
        .dark .wh-table td {
            border-color: #1e293b;
            color: #cbd5e1;
        }
        .wh-table tr:hover td {
            background: rgba(248, 250, 252, 0.5);
        }
        .dark .wh-table tr:hover td {
            background: rgba(30, 41, 59, 0.2);
        }

        /* Premium micro-interactions & styles */
        .wh-table tr:hover .wh-ledger-icon {
            opacity: 1 !important;
        }
        .wh-table tbody {
            transition: opacity 0.15s ease-in-out;
        }
        .wh-table[wire:loading] tbody {
            opacity: 0.6;
        }
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }
        .overflow-x-auto::-webkit-scrollbar-track {
            background: transparent;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .dark .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #334155;
        }
        .dark .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        .form-field {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }
        .form-field label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #475569;
        }
        .dark .form-field label {
            color: #cbd5e1;
        }
        .form-field input, .form-field select, .form-field textarea {
            height: 38px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0 0.75rem;
            font-size: 0.8125rem;
            background: #ffffff;
            color: #0f172a;
            outline: none;
        }
        .dark .form-field input, .dark .form-field select, .dark .form-field textarea {
            border-color: #334155;
            background: #1e293b;
            color: #ffffff;
        }
        .date-input {
            background-color: #ffffff;
            color: #0f172a;
        }
        .dark .date-input,
        .dark input[type="date"] {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #ffffff !important;
        }
        .form-field textarea {
            height: auto;
            padding: 0.5rem 0.75rem;
        }

        .btn-icon-danger {
            color: #ef4444;
            transition: color 0.2s;
        }
        .btn-icon-danger:hover {
            color: #b91c1c;
        }

        /* Flow Modal */
        .flow-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            z-index: 99;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }
        .flow-modal-container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: min(540px, 92vw);
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
        }
        .dark .flow-modal-container {
            background: #1e293b;
            border-color: #334155;
        }
        .flow-modal-choice {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            text-align: left;
            width: 100%;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            gap: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .flow-modal-choice:hover {
            background: #f8fafc;
            border-color: rgb(var(--primary-500));
            transform: translateY(-1px);
        }
        .dark .flow-modal-choice {
            background: #0f172a;
            border-color: #334155;
        }
        .dark .flow-modal-choice:hover {
            background: rgba(30, 41, 59, 0.6);
            border-color: rgb(var(--primary-400));
        }

        /* Custom inputs for tables */
        .table-input {
            width: 100%;
            height: 32px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 0 0.5rem;
            font-size: 0.8125rem;
            text-align: center;
            background: #ffffff;
            color: #0f172a;
            outline: none;
            transition: all 0.2s;
        }
        .table-input:focus {
            border-color: rgb(var(--primary-500)) !important;
            box-shadow: 0 0 0 2px rgba(var(--primary-500), 0.2);
        }
        .dark .table-input {
            border-color: #334155;
            background: #1e293b;
            color: #ffffff;
        }
        
        .table-select {
            height: 32px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.78rem;
            padding: 0 0.5rem;
            background-color: #ffffff;
            color: #334155;
            outline: none;
            transition: all 0.2s;
        }
        .table-select:focus {
            border-color: rgb(var(--primary-500)) !important;
            box-shadow: 0 0 0 2px rgba(var(--primary-500), 0.2);
        }
        .dark .table-select {
            border-color: #334155;
            background-color: #1e293b;
            color: #cbd5e1;
        }

        .ctrl {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            font-size: 0.8125rem;
            color: #334155;
            outline: none;
            transition: all 0.2s;
        }
        .ctrl:focus {
            border-color: rgb(var(--primary-500)) !important;
            box-shadow: 0 0 0 2px rgba(var(--primary-500), 0.2);
        }
        .dark .ctrl {
            border-color: #334155;
            background: #1e293b;
            color: #cbd5e1;
        }
</style>
@endpush

<x-filament-panels::page>

    <!-- Stats Cards -->
    @php $stats = $this->getStats(); @endphp
    <div class="stats-grid">

    {{-- Nếu là quản trị viên/toàn quyền, cho phép chọn bếp linh hoạt --}}
    @if(auth()->user()?->hasRole(['super_admin', 'Quản trị viên']))
        <div class="col-span-full mb-1.5 flex items-center gap-2.5 rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
            <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">{{ __('warehouse.filters.selected_kitchen') }}</span>
            <select wire:model.live="selectedKitchenId" class="rounded-md border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:[color-scheme:dark]">
                <option value="all">{{ __('warehouse.filters.all_kitchens') }}</option>
                @foreach(\App\Models\Kitchen::orderBy('name')->get() as $kit)
                    <option value="{{ $kit->id }}">{{ $kit->name }}</option>
                @endforeach
            </select>
        </div>
    @elseif(! $this->operatingKitchenId())
        <div style="grid-column: 1 / -1; display:flex; gap:10px; align-items:flex-start; padding:12px 16px; margin-bottom: 6px; border-radius:8px; border:1px solid #f59e0b; background:#fffbeb; color:#92400e">
            <i class="fa-solid fa-triangle-exclamation" style="margin-top:2px"></i>
            <div>
                <div style="font-weight:700">{{ __('warehouse.notifications.no_kitchen_title') }}</div>
                <div style="font-size:12.5px">{{ __('warehouse.notifications.no_kitchen_body') }}</div>
            </div>
        </div>
    @endif
        <div class="stat-card">
            <div class="stat-icon ico-blue">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div class="stat-val">{{ $stats['items'] }}</div>
            <div class="stat-lbl">{{ __('warehouse.stats.stock_items') }}</div>
            <div class="stat-note">{{ __('warehouse.stats.from_ingredients') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon ico-green">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="stat-val">{{ number_format($stats['value'], 0, ',', '.') }}{{ __('warehouse.common.currency') }}</div>
            <div class="stat-lbl">{{ __('warehouse.stats.stock_value') }}</div>
            <div class="stat-note">{{ __('warehouse.stats.by_reference_price') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon ico-orange">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="stat-val">{{ $stats['low'] }}</div>
            <div class="stat-lbl">{{ __('warehouse.stats.low_stock') }}</div>
            <div class="stat-note">{{ __('warehouse.stats.below_minimum') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon ico-purple">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div class="stat-val">{{ $stats['check'] }}</div>
            <div class="stat-lbl">{{ __('warehouse.stats.need_check_today') }}</div>
            <div class="stat-note">{{ __('warehouse.stats.from_current_stock') }}</div>
        </div>
    </div>

    <!-- Tab bar -->
    <div class="tabs-bar">
        <button class="tab-btn @if($warehouseTab === 'stock') active @endif" wire:click="setTab('stock')">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span>{{ __('warehouse.tabs.stock') }}</span>
        </button>
        <button class="tab-btn @if($warehouseTab === 'check') active @endif" wire:click="setTab('check')">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span>{{ __('warehouse.tabs.check') }}</span>
        </button>
        <button class="tab-btn @if($warehouseTab === 'in') active @endif" wire:click="setTab('in')">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-6l-7 7-7-7"/>
            </svg>
            <span>{{ __('warehouse.tabs.inbound') }}</span>
        </button>
        <button class="tab-btn @if($warehouseTab === 'out') active @endif" wire:click="setTab('out')">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 19l7-7 7 7"/>
            </svg>
            <span>{{ __('warehouse.tabs.outbound') }}</span>
        </button>
        <button class="tab-btn @if($warehouseTab === 'log') active @endif" wire:click="setTab('log')">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ __('warehouse.tabs.log') }}</span>
        </button>

        <div class="filter-controls">
            <div class="search-wrapper">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('warehouse.placeholders.search_ingredient') }}">
            </div>
            <div style="min-width:170px">
                @include('filament.components.search-select', [
                    'name' => 'selectedType',
                    'live' => true,
                    'placeholder' => __('warehouse.filters.all_types'),
                    'emptyLabel' => __('warehouse.filters.all_types'),
                    'options' => collect($this->getIngredientTypeOptions())->map(fn ($t) => ['value' => $t, 'label' => $t])->all(),
                ])
            </div>
            <div style="min-width:170px">
                @include('filament.components.search-select', [
                    'name' => 'selectedSort',
                    'live' => true,
                    'placeholder' => __('warehouse.filters.latest_updated'),
                    'emptyLabel' => __('warehouse.filters.latest_updated'),
                    'options' => [
                        ['value' => 'latest', 'label' => __('warehouse.filters.latest_updated')],
                        ['value' => 'oldest', 'label' => __('warehouse.filters.oldest_updated')],
                    ],
                ])
            </div>
        </div>
    </div>

    <!-- PANELS CONTENT -->
    <div class="wh-card">
        @if($warehouseTab === 'stock')
            @php $stocksData = $this->getWarehouseData(); @endphp
            <div class="overflow-x-auto">
                <table class="wh-table">
                    <thead>
                        <tr>
                            <th style="width: 36px; text-align: center;">#</th>
                            <th>{{ __('warehouse.table.ingredient_code_short') }}</th>
                            <th>{{ __('warehouse.table.ingredient') }}</th>
                            <th>{{ __('warehouse.table.type') }}</th>
                            <th>{{ __('warehouse.table.supplier') }}</th>
                            <th style="text-align: right;">{{ __('warehouse.table.current_stock') }}</th>
                            <th style="text-align: right;">{{ __('warehouse.table.minimum') }}</th>
                            <th style="text-align: right;">{{ __('warehouse.table.unit_price') }}</th>
                            <th style="text-align: right;">{{ __('warehouse.table.value') }}</th>
                            <th style="text-align: center;">{{ __('warehouse.table.last_updated') }}</th>
                            <th>{{ __('warehouse.table.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocksData as $index => $item)
                            <tr wire:click="openLedger({{ $item['ingredient']['id'] }})" style="cursor: pointer;" title="{{ __('warehouse.tooltips.open_ledger') }}">
                                <td style="text-align: center;">{{ ($stocksData->currentPage() - 1) * $stocksData->perPage() + $index + 1 }}</td>
                                <td><span style="font-weight: 700;">{{ $item['ingredient']['code'] }}</span></td>
                                <td style="font-weight: 700; color: #0f172a;" class="dark:text-white">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span>{{ $item['ingredient']['name'] }}</span>
                                        <svg class="w-3.5 h-3.5 text-gray-400 opacity-0 wh-ledger-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transition: opacity 0.15s; flex-shrink: 0;" title="{{ __('warehouse.tooltips.open_ledger') }}">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                    </div>
                                </td>
                                <td>{{ $item['ingredient']['type'] }}</td>
                                <td>{{ str_starts_with($item['ingredient']['supplier']['name'] ?? '', 'test_') ? __('warehouse.common.test_supplier') : ($item['ingredient']['supplier']['name'] ?? '—') }}</td>
                                <td style="text-align: right; font-weight: 750;">
                                    {{ number_format($item['quantity'], 2, ',', '.') }}<span style="font-size: 11px; font-weight: 500; color: #94a3b8; margin-left: 2px;">{{ $item['ingredient']['unit'] }}</span>
                                </td>
                                <td style="text-align: right; color: #64748b;">
                                    {{ number_format($item['min_quantity'], 2, ',', '.') }}<span style="font-size: 11px; font-weight: 500; color: #94a3b8; margin-left: 2px;">{{ $item['ingredient']['unit'] }}</span>
                                </td>
                                <td style="text-align: right;">
                                    {{ number_format($item['unit_price'], 0, ',', '.') }}<span style="font-size: 10px; font-weight: 500; color: #94a3b8; margin-left: 1px;">{{ __('warehouse.common.currency') }}</span>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: rgb(var(--primary-600));" class="dark:text-primary-400">
                                    {{ number_format($item['quantity'] * $item['unit_price'], 0, ',', '.') }}<span style="font-size: 10px; font-weight: 500; color: #94a3b8; margin-left: 1px;">{{ __('warehouse.common.currency') }}</span>
                                </td>
                                <td style="text-align: center; font-size: 11px; color: #64748b; font-variant-numeric: tabular-nums;">
                                    {{ $item['updated_at']?->format('H:i d/m/Y') ?? '—' }}
                                </td>
                                <td>
                                    @if($item['quantity'] == 0)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-950/20 dark:text-red-400">
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-red-500"></span>
                                            </span>
                                            {{ __('warehouse.status.out_of_stock') }}
                                        </span>
                                    @elseif($item['quantity'] <= $item['min_quantity'])
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/20 dark:text-amber-400">
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-amber-500"></span>
                                            </span>
                                            {{ __('warehouse.status.low_stock') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-950/20 dark:text-green-400">
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
                                            </span>
                                            {{ __('warehouse.status.enough_stock') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" style="text-align: center; padding: 3rem 1.5rem;">
                                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem;">
                                        <div style="width: 48px; height: 48px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;" class="dark:bg-slate-800 dark:color-slate-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                        </div>
                                        <div style="font-weight: 700; color: #334155; font-size: 0.875rem;" class="dark:text-slate-300">{{ __('warehouse.empty.no_ingredients') }}</div>
                                        <div style="color: #64748b; font-size: 0.78rem; max-width: 280px; margin: 0 auto;" class="dark:text-slate-400">{{ __('warehouse.empty.adjust_filters') }}</div>
                                        @if($search !== '' || $selectedType !== '')
                                            <button type="button" wire:click="$set('search', ''); $set('selectedType', '')" style="margin-top: 0.25rem; font-size: 0.78rem; font-weight: 600; color: rgb(var(--primary-600)); background: transparent; border: none; cursor: pointer; text-decoration: underline;" class="hover:text-primary-500">
                                                {{ __('warehouse.actions.clear_filters') }}
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($stocksData->hasPages() || $stocksData->total() > 10)
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:12px 4px 2px; font-size:12px; color:#64748b;" class="dark:text-gray-400">
                    <div>
                        {{ __('warehouse.pagination.showing', [
                            'from' => $stocksData->firstItem() ?? 0,
                            'to' => $stocksData->lastItem() ?? 0,
                            'total' => $stocksData->total(),
                        ]) }}
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <select wire:model.live="perPage" style="height:30px; border:1px solid #cbd5e1; border-radius:6px; padding:0 8px; font-size:12px; background:transparent;" class="dark:border-gray-700 dark:bg-gray-800">
                            <option value="10">{{ __('warehouse.pagination.per_page', ['count' => 10]) }}</option>
                            <option value="20">{{ __('warehouse.pagination.per_page', ['count' => 20]) }}</option>
                            <option value="50">{{ __('warehouse.pagination.per_page', ['count' => 50]) }}</option>
                        </select>

                        @if($stocksData->hasPages())
                            <nav role="navigation" aria-label="{{ __('warehouse.pagination.navigation') }}" style="display:flex; align-items:center; gap:4px;">
                                {{-- Trang trước --}}
                                @if ($stocksData->onFirstPage())
                                    <span aria-disabled="true" style="opacity:.4; padding:4px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="15 18 9 12 15 6"/></svg>
                                    </span>
                                @else
                                    <button type="button" wire:click="previousPage" rel="prev" style="display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; border:1px solid #cbd5e1; border-radius:6px; background:transparent; cursor:pointer;" class="dark:border-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="15 18 9 12 15 6"/></svg>
                                    </button>
                                @endif

                                {{-- Số trang (dạng cửa sổ: 1 … n-1 n n+1 … cuối) --}}
                                @php
                                    $whCurrentPage = $stocksData->currentPage();
                                    $whLastPage = $stocksData->lastPage();
                                    $whPageWindow = collect([1, $whCurrentPage - 1, $whCurrentPage, $whCurrentPage + 1, $whLastPage])
                                        ->filter(fn ($p) => $p >= 1 && $p <= $whLastPage)
                                        ->unique()
                                        ->sort()
                                        ->values();
                                @endphp
                                @foreach ($whPageWindow as $i => $page)
                                    @if ($i > 0 && $page - $whPageWindow[$i - 1] > 1)
                                        <span aria-hidden="true" style="padding:0 4px">…</span>
                                    @endif
                                    @if ($page == $whCurrentPage)
                                        <span aria-current="page" style="display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; border-radius:6px; background:rgb(var(--primary-600)); color:#fff; font-weight:700;">{{ $page }}</span>
                                    @else
                                        <button type="button" wire:click="gotoPage({{ $page }})" style="display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; border:1px solid #cbd5e1; border-radius:6px; background:transparent; cursor:pointer;" class="dark:border-gray-700">{{ $page }}</button>
                                    @endif
                                @endforeach

                                {{-- Trang sau --}}
                                @if ($stocksData->hasMorePages())
                                    <button type="button" wire:click="nextPage" rel="next" style="display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; border:1px solid #cbd5e1; border-radius:6px; background:transparent; cursor:pointer;" class="dark:border-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="9 18 15 12 9 6"/></svg>
                                    </button>
                                @else
                                    <span aria-disabled="true" style="opacity:.4; padding:4px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="9 18 15 12 9 6"/></svg>
                                    </span>
                                @endif
                            </nav>
                        @endif
                    </div>
                </div>
            @endif

        @elseif($warehouseTab === 'check')
            <div>
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-500">{{ __('warehouse.form.check_date') }}:</span>
                        <input type="date" class="date-input" wire:model.live="checkDate" style="height:34px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 8px; font-size:12px;">
                    </div>
                    <button type="button" class="wh-action-btn wh-action-btn-primary" wire:click="saveEndDay">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        <span>{{ __('warehouse.actions.save_end_day') }}</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="wh-table">
                        <thead>
                            <tr>
                                <th style="width: 36px; text-align: center;">#</th>
                                <th>{{ __('warehouse.table.ingredient') }}</th>
                                <th>{{ __('warehouse.table.unit') }}</th>
                                <th style="text-align: right;">{{ __('warehouse.table.system_stock') }}</th>
                                <th style="text-align: center; width: 180px;">{{ __('warehouse.table.actual_end_day_stock') }}</th>
                                <th style="text-align: right;">{{ __('warehouse.table.difference') }}</th>
                                <th>{{ __('warehouse.table.note') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $stocksData = $this->getCheckStocks();
                                $systemQty = $this->getSystemQuantities($checkDate);
                                $openingQty = $this->getOpeningQuantities();
                            @endphp
                            @foreach($stocksData as $index => $item)
                                @php
                                    $sysQty = $systemQty[$item->id] ?? 0;
                                    $diff = ($actualQuantities[$item->id] ?? $sysQty) - $sysQty;
                                @endphp
                                <tr>
                                    <td style="text-align: center;">{{ $index + 1 }}</td>
                                    <td>
                                        <div style="font-weight: 700;">{{ $item->ingredient->name }}</div>
                                        <div style="font-size:10px; color:#64748b;">{{ $item->ingredient->code }} · {{ $item->ingredient->type }}</div>
                                    </td>
                                    <td>{{ $item->ingredient->unit }}</td>
                                    <td style="text-align: right; font-weight: 700;">{{ number_format($sysQty, 2, ',', '.') }}</td>
                                    <td style="text-align: center;">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               wire:model.blur="actualQuantities.{{ $item->id }}"
                                               class="w-28 text-center border border-gray-300 rounded px-2 py-1 text-xs dark:bg-gray-800 dark:border-gray-700">
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: {{ $diff < 0 ? '#ef4444' : ($diff > 0 ? '#16a34a' : 'inherit') }}">
                                        {{ $diff > 0 ? '+' . $diff : $diff }}
                                    </td>
                                    <td>
                                        <input type="text" 
                                               wire:model.blur="checkNotes.{{ $item->id }}" 
                                               placeholder="{{ __('warehouse.placeholders.difference_reason') }}"
                                               class="w-full border border-gray-300 rounded px-2 py-1 text-xs dark:bg-gray-800 dark:border-gray-700">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @elseif($warehouseTab === 'in')
            @if(is_null($inMode))
                <!-- Initial Workspace for Inbound (Ảnh 3) -->
                <div style="text-align: center; padding: 3rem 1.5rem;">
                    <div style="display:inline-flex; align-items:center; justify-content:center; width:54px; height:54px; border-radius:50%; background:#eff6ff; color:#1e40af; margin-bottom:1rem;">
                        <i class="fa-solid fa-boxes-packing" style="font-size:24px"></i>
                    </div>
                    <h3 style="font-size: 1rem; font-weight: 700; color:#0f172a;" class="dark:text-white">{{ __('warehouse.inbound.title') }}</h3>
                    <p style="font-size: 0.78rem; color:#64748b; margin-top:0.25rem; margin-bottom:1.5rem;">{{ __('warehouse.inbound.description') }}</p>
                    <button type="button" class="wh-action-btn wh-action-btn-primary" style="margin: 0 auto;" wire:click="openInTypeModal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('warehouse.actions.create_in') }}</span>
                    </button>
                    <div style="font-size:0.75rem; color:#94a3b8; margin-top:1rem;">{{ __('warehouse.inbound.start_hint') }}</div>
                </div>
            @elseif($inMode === 'po')
                <!-- PO Inbound Workspace (Ảnh 5) -->
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="form-field" style="width: 320px;">
                                <label style="font-size:0.7rem;">{{ __('warehouse.form.pending_po_code') }}</label>
                                @include('filament.components.search-select', [
                                    'name' => 'selectedPOId',
                                    'live' => true,
                                    'placeholder' => __('warehouse.placeholders.select_po'),
                                    'options' => collect($this->getPendingPOs())->map(fn ($po) => [
                                        'value' => $po->id,
                                        'label' => $po->code,
                                        'sub' => $po->supplier?->name,
                                    ])->all(),
                                ])
                            </div>
                        </div>
                        <button type="button" class="wh-action-btn wh-action-btn-primary" wire:click="confirmInboundPO">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('warehouse.actions.confirm_inbound') }}</span>
                        </button>
                    </div>

                    @if(empty($poItemsData))
                        <div style="text-align: center; color: #94a3b8; padding: 2rem; font-style: italic;">
                            {{ __('warehouse.empty.no_po_items') }}
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="wh-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('warehouse.table.ingredient') }}</th>
                                        <th style="text-align: right;">{{ __('warehouse.table.expected_qty') }}</th>
                                        <th style="text-align: center; width:140px;">{{ __('warehouse.table.received_qty') }}</th>
                                        <th style="text-align: right; width:110px;">{{ __('warehouse.table.difference') }}</th>
                                        <th style="text-align: right; width:140px;">{{ __('warehouse.table.po_locked_price') }}</th>
                                        <th style="width:200px;">{{ __('warehouse.table.difference_reason') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($poItemsData as $index => $item)
                                        @php
                                            // Chênh lệch = thực nhận − đặt; thừa xanh, thiếu đỏ (quy định kiểm hàng BA)
                                            $diff = (float) ($item['quantity_received'] ?? 0) - (float) $item['quantity_ordered'];
                                        @endphp
                                        <tr>
                                            <td style="font-weight: 700; color: #0f172a;" class="dark:text-white">{{ $item['name'] }}</td>
                                            <td style="text-align: right; font-weight:600;">{{ number_format($item['quantity_ordered'], 2, ',', '.') }} {{ $item['unit'] }}</td>
                                            <td style="text-align: center;">
                                                <input type="number" step="0.01" wire:model.live.debounce.400ms="poItemsData.{{ $index }}.quantity_received" class="table-input" style="width: 110px;">
                                            </td>
                                            <td style="text-align: right; font-weight:750; color: {{ $diff > 0 ? '#16a34a' : ($diff < 0 ? '#ef4444' : 'inherit') }}">
                                                {{ $diff == 0 ? '—' : ($diff > 0 ? '+' : '').number_format($diff, 2, ',', '.') }}
                                            </td>
                                            <td style="text-align: right; font-weight:600;">
                                                {{ number_format($item['unit_price'], 0, ',', '.') }}{{ __('warehouse.common.currency') }}
                                            </td>
                                            <td>
                                                @if($diff != 0)
                                                    <input type="text" wire:model="poItemsData.{{ $index }}.receive_note" class="table-input" style="width: 100%;" placeholder="{{ __('warehouse.placeholders.required_when_different') }}">
                                                @else
                                                    <span style="color:#94a3b8; font-size:11px;">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @elseif($inMode === 'direct')
                <!-- Direct Inbound Workspace -->
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                        <div>
                            <h3 style="font-weight: 800; font-size:0.9rem;" class="dark:text-white">{{ __('warehouse.inbound.direct_title') }}</h3>
                            <p style="font-size:0.75rem; color:#64748b;">{{ __('warehouse.inbound.direct_description') }}</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" class="wh-action-btn" wire:click="addDirectRow">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>{{ __('warehouse.actions.add_item') }}</span>
                            </button>
                            <button type="button" class="wh-action-btn wh-action-btn-primary" wire:click="confirmDirectInbound">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>{{ __('warehouse.actions.save_inbound') }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="form-field mb-4" style="max-width:320px;">
                        <label style="color: #374151; font-weight: 600; font-size: 0.8125rem;" class="dark:text-slate-300">
                            {{ __('warehouse.form.invoice_attachment') }}
                            <span style="color: #ef4444; font-weight: 700;">{{ __('warehouse.form.required_mark') }}</span>
                        </label>
                        <input type="file" wire:model="directInvoiceFile" class="ctrl" style="width: 100%; font-size: 0.78rem; padding: 5px 8px; cursor: pointer;">
                        <div wire:loading wire:target="directInvoiceFile" class="text-xs text-gray-500 mt-1">{{ __('warehouse.form.uploading') }}</div>
                        @error('directInvoiceFile') <span class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div class="overflow-x-auto">
                        <table class="wh-table">
                            <thead>
                                <tr>
                                    <th>{{ __('warehouse.table.ingredient') }}</th>
                                    <th style="text-align: center; width: 160px;">{{ __('warehouse.table.quantity') }}</th>
                                    <th style="text-align: right; width: 180px;">{{ __('warehouse.table.unit_price') }}</th>
                                    <th style="width: 60px; text-align: center;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($directItemsData as $index => $item)
                                    <tr>
                                        <td>
                                            @include('filament.components.search-select', [
                                                'name' => 'directItemsData.'.$index.'.ingredient_id',
                                                'live' => false,
                                                'placeholder' => __('warehouse.placeholders.select_ingredient'),
                                                'options' => collect($this->getIngredientsList())->map(fn ($ing) => [
                                                    'value' => $ing->id,
                                                    'label' => $ing->code.' - '.$ing->name,
                                                    'sub' => $ing->unit,
                                                ])->all(),
                                            ])
                                        </td>
                                        <td style="text-align: center;">
                                            <input type="number" step="0.01" wire:model="directItemsData.{{ $index }}.quantity" class="table-input" style="width:110px;">
                                        </td>
                                        <td style="text-align: right;">
                                            <input type="number" step="1000" wire:model="directItemsData.{{ $index }}.unit_price" class="table-input" style="width:140px; text-align:right;">
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn-icon-danger" wire:click="removeDirectRow({{ $index }})">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        @elseif($warehouseTab === 'out')
            @if(is_null($outMode))
                <!-- Initial Workspace for Outbound -->
                <div style="text-align: center; padding: 3rem 1.5rem;">
                    <div style="display:inline-flex; align-items:center; justify-content:center; width:54px; height:54px; border-radius:50%; background:#fff7ed; color:#ea580c; margin-bottom:1rem;">
                        <i class="fa-solid fa-truck-ramp-box" style="font-size:24px"></i>
                    </div>
                    <h3 style="font-size: 1rem; font-weight: 700; color:#0f172a;" class="dark:text-white">{{ __('warehouse.outbound.title') }}</h3>
                    <p style="font-size: 0.78rem; color:#64748b; margin-top:0.25rem; margin-bottom:1.5rem;">{{ __('warehouse.outbound.description') }}</p>
                    <button type="button" class="wh-action-btn wh-action-btn-primary" style="margin: 0 auto;" wire:click="openOutTypeModal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('warehouse.actions.create_out') }}</span>
                    </button>
                    <div style="font-size:0.75rem; color:#94a3b8; margin-top:1rem;">{{ __('warehouse.outbound.start_hint') }}</div>
                </div>
            @elseif($outMode === 'production')
                <!-- Production Outbound Workspace -->
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4 flex-wrap gap-2">
                        <div class="flex items-center gap-3">
                            <div class="form-field" style="width: 160px;">
                                <label style="font-size:0.7rem;">{{ __('warehouse.form.outbound_date') }}</label>
                                <input type="date" wire:model.live="prodDate" style="height:34px;">
                            </div>
                            <div class="form-field" style="width: 140px;">
                                <label style="font-size:0.7rem;">{{ __('warehouse.form.shift') }}</label>
                                @include('filament.components.search-select', [
                                    'name' => 'prodShiftId',
                                    'live' => true,
                                    'nullable' => false,
                                    'placeholder' => __('warehouse.form.shift'),
                                    'options' => collect($this->getShiftsList())->map(fn ($sh) => ['value' => $sh->id, 'label' => $sh->name])->all(),
                                ])
                            </div>
                        </div>
                        <button type="button" class="wh-action-btn wh-action-btn-primary" wire:click="confirmProductionOut">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('warehouse.actions.confirm_outbound') }}</span>
                        </button>
                    </div>
                    <div style="font-size:11px; color:#64748b; margin-bottom:12px;">{{ __('warehouse.outbound.production_hint') }}</div>

                    @if(empty($prodItemsData))
                        <div style="text-align: center; color: #94a3b8; padding: 2rem; font-style: italic;">
                            {{ __('warehouse.empty.no_production_items') }}
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="wh-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('warehouse.table.ingredient') }}</th>
                                        <th style="text-align: right;">{{ __('warehouse.table.current_stock') }}</th>
                                        <th style="text-align: right;">{{ __('warehouse.table.required_qty') }}</th>
                                        <th style="text-align: center; width: 180px;">{{ __('warehouse.table.actual_out_qty') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prodItemsData as $index => $item)
                                        <tr>
                                            <td style="font-weight: 700; color: #0f172a;" class="dark:text-white">{{ $item['name'] }}</td>
                                            {{-- payload từ client có thể thiếu key → không được để vỡ trang (500) --}}
                                            <td style="text-align: right; font-weight:700;">{{ number_format($item['available_qty'] ?? 0, 2, ',', '.') }} {{ $item['unit'] ?? '' }}</td>
                                            <td style="text-align: right; color:#64748b;">{{ number_format($item['quantity_expected'], 2, ',', '.') }} {{ $item['unit'] }}</td>
                                            <td style="text-align: center;">
                                                <input type="number" step="0.01" wire:model="prodItemsData.{{ $index }}.quantity_actual" class="table-input" style="width: 120px;">
                                                @if(($prodItemsData[$index]['quantity_actual'] ?? 0) > ($item['available_qty'] ?? 0))
                                                    <div style="color:#ef4444; font-size:10px; font-weight:700; margin-top:2px;">{{ __('warehouse.validation.over_current_stock') }}</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @elseif($outMode === 'transfer')
                <!-- Transfer Outbound Workspace -->
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="form-field" style="width: 260px;">
                                <label style="font-size: 0.78rem; font-weight: 600; color: #475569;" class="dark:text-slate-300">{{ __('warehouse.form.destination_kitchen') }}</label>
                                @include('filament.components.search-select', [
                                    'name' => 'destKitchenId',
                                    'placeholder' => __('warehouse.placeholders.select_destination_kitchen'),
                                    'options' => collect($this->getTransferKitchens())->map(fn ($kit) => ['value' => $kit->id, 'label' => $kit->name])->all(),
                                ])
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" class="wh-action-btn" wire:click="addTransferRow">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>{{ __('warehouse.actions.add_item') }}</span>
                            </button>
                            <button type="button" class="wh-action-btn wh-action-btn-primary" wire:click="confirmTransferOut">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                <span>{{ __('warehouse.actions.create_transfer') }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="form-field mb-4">
                        <label style="font-size: 0.78rem; font-weight: 600; color: #475569;" class="dark:text-slate-300">{{ __('warehouse.form.transfer_note') }}</label>
                        <input type="text" wire:model="transferNote" class="ctrl" placeholder="{{ __('warehouse.placeholders.transfer_note') }}" style="width: 100%; font-size: 0.78rem; padding: 5px 8px;">
                    </div>

                    <div class="overflow-x-auto mb-6">
                        <table class="wh-table">
                            <thead>
                                <tr>
                                    <th>{{ __('warehouse.table.item') }}</th>
                                    <th style="text-align: right; width: 180px;">{{ __('warehouse.table.current_stock') }}</th>
                                    <th style="text-align: center; width: 180px;">{{ __('warehouse.table.transfer_qty') }}</th>
                                    <th style="width: 60px; text-align: center;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transferItemsData as $index => $item)
                                    <tr>
                                        <td>
                                            @include('filament.components.search-select', [
                                                'name' => 'transferItemsData.'.$index.'.ingredient_id',
                                                'live' => true,
                                                'placeholder' => __('warehouse.placeholders.select_ingredient'),
                                                'options' => collect($this->getIngredientsList())->map(fn ($ing) => [
                                                    'value' => $ing->id,
                                                    'label' => $ing->code.' - '.$ing->name,
                                                    'sub' => $ing->unit,
                                                ])->all(),
                                            ])
                                        </td>
                                        <td style="text-align: right; font-weight:700;">
                                            {{ number_format($item['available_qty'] ?? 0, 2, ',', '.') }}<span style="font-size: 11px; font-weight: 500; color: #94a3b8; margin-left: 2px;">{{ $item['unit'] ?? '' }}</span>
                                        </td>
                                        <td style="text-align: center;">
                                            <input type="number" step="0.01" wire:model="transferItemsData.{{ $index }}.quantity" class="table-input" style="width: 120px;">
                                            @if(($transferItemsData[$index]['quantity'] ?? 0) > ($item['available_qty'] ?? 0))
                                                <div style="color:#ef4444; font-size:10px; font-weight:700; margin-top:2px;">{{ __('warehouse.validation.over_current_stock') }}</div>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn-icon-danger" wire:click="removeTransferRow({{ $index }})">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Phiếu điều chuyển gần đây -->
                    @php $recentTransfers = $this->getRecentTransfers(); @endphp
                    @if(!empty($recentTransfers))
                        <div style="font-weight: 800; margin-bottom: 0.5rem; font-size: 0.9rem;" class="dark:text-white">{{ __('warehouse.transfer.recent') }}</div>
                        <div class="overflow-x-auto">
                            <table class="wh-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('warehouse.table.voucher_code') }}</th>
                                        <th>{{ __('warehouse.table.source_kitchen') }}</th>
                                        <th>{{ __('warehouse.table.destination_kitchen') }}</th>
                                        <th>{{ __('warehouse.table.time') }}</th>
                                        <th>{{ __('warehouse.table.status') }}</th>
                                        <th style="text-align: center;">{{ __('warehouse.table.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransfers as $tf)
                                        <tr>
                                            <td><span style="font-weight: 700;">{{ $tf->code }}</span></td>
                                            <td>{{ $tf->sourceKitchen->name }}</td>
                                            <td>{{ $tf->destKitchen->name }}</td>
                                            <td>{{ $tf->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($tf->status === \App\Models\StockTransfer::STATUS_DONE)
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-950/20 dark:text-green-400">
                                                        <span class="relative flex h-1.5 w-1.5">
                                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
                                                        </span>
                                                        {{ $this->transferStatusLabel($tf->status) }}
                                                    </span>
                                                @elseif($tf->status === \App\Models\StockTransfer::STATUS_CANCELLED)
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-950/20 dark:text-red-400">
                                                        <span class="relative flex h-1.5 w-1.5">
                                                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-red-500"></span>
                                                        </span>
                                                        {{ $this->transferStatusLabel($tf->status) }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/20 dark:text-amber-400">
                                                        <span class="relative flex h-1.5 w-1.5">
                                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-amber-500"></span>
                                                        </span>
                                                        {{ $this->transferStatusLabel($tf->status) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td style="text-align: center;">
                                                @if($tf->status === \App\Models\StockTransfer::STATUS_IN_TRANSIT && auth()->user()?->currentKitchenId() === $tf->dest_kitchen_id)
                                                    <button type="button" class="wh-action-btn wh-action-btn-primary" style="height:28px; font-size:10px; padding:0 8px; margin:0 auto;" wire:click="confirmTransferReceive({{ $tf->id }})">
                                                        {{ __('warehouse.actions.confirm_receive') }}
                                                    </button>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif

        @elseif($warehouseTab === 'log')
            @php $logData = $this->getLogData(); @endphp
            <!-- Bộ lọc nhật ký: loại giao dịch + nguyên liệu -->
            <div style="display:flex; gap:10px; margin-bottom:12px; flex-wrap:wrap;">
                @php
                    $logTypes = collect([
                        'inbound', 'external_inbound', 'outbound', 'transfer_out', 'transfer_in', 'stock_check',
                    ])->map(fn ($k) => [
                        'value' => __('warehouse.transaction_types.'.$k),
                        'label' => __('warehouse.transaction_type_labels.'.$k),
                    ])->all();
                @endphp
                <div style="min-width:200px">
                    @include('filament.components.search-select', [
                        'name' => 'logTypeFilter',
                        'live' => true,
                        'placeholder' => __('warehouse.filters.all_transaction_types'),
                        'emptyLabel' => __('warehouse.filters.all_transaction_types'),
                        'options' => $logTypes,
                    ])
                </div>
                <div style="min-width:220px">
                    @include('filament.components.search-select', [
                        'name' => 'logIngredientFilter',
                        'live' => true,
                        'placeholder' => __('warehouse.filters.all_ingredients'),
                        'emptyLabel' => __('warehouse.filters.all_ingredients'),
                        'options' => collect($this->getIngredientsList())->map(fn ($ing) => [
                            'value' => $ing->id,
                            'label' => $ing->name,
                            'sub' => $ing->code,
                        ])->all(),
                    ])
                </div>
                {{-- Lọc theo khoảng thời gian để đối soát đúng kỳ --}}
                <input type="date" wire:model.live="logFromDate" class="table-input" style="height:34px" title="{{ __('warehouse.filters.from_date') }}">
                <input type="date" wire:model.live="logToDate" class="table-input" style="height:34px" title="{{ __('warehouse.filters.to_date') }}">
            </div>
            <div class="overflow-x-auto">
                <table class="wh-table">
                    <thead>
                        <tr>
                            <th>{{ __('warehouse.table.time') }}</th>
                            <th>{{ __('warehouse.table.transaction_type') }}</th>
                            <th>{{ __('warehouse.table.ingredient') }}</th>
                            <th style="text-align: right;">{{ __('warehouse.table.quantity') }}</th>
                            <th style="text-align: right;">{{ __('warehouse.table.after_stock') }}</th>
                            <th>{{ __('warehouse.table.voucher_note') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logData as $log)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($log['created_at'])->format('d/m/Y H:i') }}</td>
                                <td>
                                    @php
                                        // 'Kiểm kê' lưu quantity CÓ DẤU (+ thừa / − thiếu); các loại khác dấu theo nhóm nhập/xuất
                                        $isInflow = $log['type'] === __('warehouse.transaction_types.stock_check')
                                            ? $log['quantity'] >= 0
                                            : in_array($log['type'], [
                                                __('warehouse.transaction_types.inbound'),
                                                __('warehouse.transaction_types.external_inbound'),
                                                __('warehouse.transaction_types.transfer_in'),
                                            ]);
                                    @endphp
                                    @if($log['type'] === __('warehouse.transaction_types.stock_check'))
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/20 dark:text-amber-400">
                                            {{ $this->transactionTypeLabel($log['type']) }}
                                        </span>
                                    @elseif($isInflow)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-950/20 dark:text-green-400">
                                            {{ $this->transactionTypeLabel($log['type']) }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-950/20 dark:text-red-400">
                                            {{ $this->transactionTypeLabel($log['type']) }}
                                        </span>
                                    @endif
                                </td>
                                <td style="font-weight: 700;">{{ $log['ingredient']['name'] ?? '—' }}</td>
                                <td style="text-align: right; font-weight: 750; color: {{ $isInflow ? '#16a34a' : '#ef4444' }}">
                                    {{ $isInflow ? '+' : '-' }}{{ number_format(abs($log['quantity']), 2, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 700;">{{ number_format($log['after_quantity'], 2, ',', '.') }}</td>
                                <td>
                                    @if($log['voucher_code'])
                                        <span style="font-family: monospace; font-weight: 700; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size:11px;" class="dark:bg-gray-800">{{ $log['voucher_code'] }}</span> — 
                                    @endif
                                    {{ $log['note'] }}
                                    @if($log['attachment_url'])
                                        <a href="{{ Storage::disk('public')->url($log['attachment_url']) }}" target="_blank" class="ml-2 text-primary-600 hover:underline font-bold text-xs">
                                            {{ __('warehouse.actions.view_attachment') }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: #94a3b8; padding: 20px; font-style: italic;">
                                    {{ __('warehouse.empty.no_stock_transactions') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @php $logTotal = $this->getLogTotal(); @endphp
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:10px; font-size:12px; color:#64748b">
                <span>{{ __('warehouse.log.showing', ['shown' => count($logData), 'total' => $logTotal]) }}</span>
                @if(count($logData) < $logTotal)
                    <button type="button" wire:click="loadMoreLog" class="wh-action-btn">{{ __('warehouse.log.load_more') }}</button>
                @endif
            </div>
        @endif
    </div>

    <!-- FLOW MODALS -->
    @if($showInModal)
        <div class="flow-modal-overlay">
            <div class="flow-modal-container">
                <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700 mb-4">
                    <div>
                        <h3 class="font-extrabold text-base text-gray-900 dark:text-white">{{ __('warehouse.modal.select_inbound_type') }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('warehouse.modal.inbound_type_desc') }}</p>
                    </div>
                    <button type="button" class="text-gray-400 hover:text-gray-500" wire:click="closeModals">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="grid gap-3">
                    <button type="button" class="flow-modal-choice" wire:click="startInbound('po')">
                        <div class="p-2 bg-primary-50 text-primary-600 rounded-lg dark:bg-primary-950/20 dark:text-primary-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 dark:text-white text-sm">{{ __('warehouse.modal.inbound_po') }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('warehouse.modal.inbound_po_desc') }}</div>
                        </div>
                    </button>
                    <button type="button" class="flow-modal-choice" wire:click="startInbound('direct')">
                        <div class="p-2 bg-primary-50 text-primary-600 rounded-lg dark:bg-primary-950/20 dark:text-primary-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 dark:text-white text-sm">{{ __('warehouse.modal.inbound_direct') }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('warehouse.modal.inbound_direct_desc') }}</div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($showOutModal)
        <div class="flow-modal-overlay">
            <div class="flow-modal-container">
                <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700 mb-4">
                    <div>
                        <h3 class="font-extrabold text-base text-gray-900 dark:text-white">{{ __('warehouse.modal.select_outbound_type') }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('warehouse.modal.outbound_type_desc') }}</p>
                    </div>
                    <button type="button" class="text-gray-400 hover:text-gray-500" wire:click="closeModals">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="grid gap-3">
                    <button type="button" class="flow-modal-choice" wire:click="startOutbound('production')">
                        <div class="p-2 bg-orange-50 text-orange-600 rounded-lg dark:bg-orange-950/20 dark:text-orange-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 dark:text-white text-sm">{{ __('warehouse.modal.production_outbound') }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('warehouse.modal.production_outbound_desc') }}</div>
                        </div>
                    </button>
                    <button type="button" class="flow-modal-choice" wire:click="startOutbound('transfer')">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-950/20 dark:text-blue-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 dark:text-white text-sm">{{ __('warehouse.modal.transfer_outbound') }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('warehouse.modal.transfer_outbound_desc') }}</div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- LEDGER (THẺ KHO) DRAWER MODAL -->
    @if($selectedLedgerIngId)
        @php $ledgerIng = $this->getLedgerIngredient(); @endphp
        <div class="flow-modal-overlay">
            <div class="flow-modal-container" style="width: min(720px, 94vw);">
                <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700 mb-4">
                    <div>
                        <h3 class="font-extrabold text-base text-gray-900 dark:text-white">{{ __('warehouse.ledger.title', ['code' => $ledgerIng->code, 'name' => $ledgerIng->name]) }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('warehouse.ledger.description') }}</p>
                    </div>
                    <button type="button" class="text-gray-400 hover:text-gray-500" wire:click="closeLedger">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="overflow-y-auto" style="max-height: 400px;">
                    <table class="wh-table">
                        <thead>
                            <tr>
                                <th>{{ __('warehouse.table.time') }}</th>
                                <th>{{ __('warehouse.table.voucher_code') }}</th>
                                <th>{{ __('warehouse.table.transaction_type') }}</th>
                                <th style="text-align: right;">{{ __('warehouse.table.quantity') }}</th>
                                <th style="text-align: right;">{{ __('warehouse.table.after_stock') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ledgerTransactions as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log['created_at'])->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($log['voucher_code'])
                                            <span style="font-family: monospace; font-weight: 700; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size:10px;" class="dark:bg-gray-800">{{ $log['voucher_code'] }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $isInflow = $log['type'] === __('warehouse.transaction_types.stock_check')
                                                ? $log['quantity'] >= 0
                                                : in_array($log['type'], [
                                                    __('warehouse.transaction_types.inbound'),
                                                    __('warehouse.transaction_types.external_inbound'),
                                                    __('warehouse.transaction_types.transfer_in'),
                                                ]);
                                        @endphp
                                        @if($log['type'] === __('warehouse.transaction_types.stock_check'))
                                            <span class="px-2 rounded bg-amber-50 text-amber-700 font-semibold text-[10px]">{{ $this->transactionTypeLabel($log['type']) }}</span>
                                        @elseif($isInflow)
                                            <span class="px-2 rounded bg-green-50 text-green-700 font-semibold text-[10px]">{{ $this->transactionTypeLabel($log['type']) }}</span>
                                        @else
                                            <span class="px-2 rounded bg-red-50 text-red-700 font-semibold text-[10px]">{{ $this->transactionTypeLabel($log['type']) }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: {{ $isInflow ? '#16a34a' : '#ef4444' }}">
                                        {{ $isInflow ? '+' : '-' }}{{ number_format(abs($log['quantity']), 2, ',', '.') }}
                                    </td>
                                    <td style="text-align: right; font-weight: 700;">{{ number_format($log['after_quantity'], 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #94a3b8; padding: 20px; font-style: italic;">
                                        {{ __('warehouse.empty.no_ledger_transactions') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
