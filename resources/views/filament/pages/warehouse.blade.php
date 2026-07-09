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
        .ico-blue { background: #eff6ff; color: #1e40af; }
        .ico-green { background: #f0fdf4; color: #065f46; }
        .ico-orange { background: #fff7ed; color: #ea580c; }
        .ico-purple { background: #f5f3ff; color: #7c3aed; }
        
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
            color: #64748b;
            font-weight: 600;
            margin-top: 0.125rem;
        }
        .dark .stat-lbl {
            color: #94a3b8;
        }
        .stat-note {
            font-size: 0.65rem;
            color: #94a3b8;
            margin-top: 0.25rem;
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
            background: #1e293b/40;
            color: #94a3b8;
            border-color: #334155;
        }
        .wh-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        .dark .wh-table td {
            border-color: #1e293b;
            color: #cbd5e1;
        }
        .wh-table tr:hover td {
            background: #f8fafc/50;
        }
        .dark .wh-table tr:hover td {
            background: #1e293b/20;
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
            background: #1e293b/60;
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
        }
        .dark .table-input {
            border-color: #334155;
            background: #1e293b;
            color: #ffffff;
        }
</style>
@endpush

<x-filament-panels::page>
    <div class="wh-header-container">
        <div>
            <h1 class="wh-title">Kho nguyên liệu</h1>
            <p class="wh-subtitle">Quản lý tồn kho hiện tại, nhập kho, xuất kho và kiểm tồn cuối ngày</p>
        </div>
        <div class="header-buttons">
            <button type="button" class="wh-action-btn" wire:click="openInTypeModal">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-6l-7 7-7-7"/>
                </svg>
                <span>Tạo phiếu nhập</span>
            </button>
            <button type="button" class="wh-action-btn wh-action-btn-primary" wire:click="openOutTypeModal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 19l7-7 7 7"/>
                </svg>
                <span>Tạo phiếu xuất</span>
            </button>
            <button type="button" class="wh-action-btn" wire:click="setTab('check')">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span>Kiểm tồn cuối ngày</span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    @php $stats = $this->getStats(); @endphp
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon ico-blue">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div class="stat-val">{{ $stats['items'] }}</div>
            <div class="stat-lbl">Mặt hàng tồn kho</div>
            <div class="stat-note">Từ danh sách nguyên liệu</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon ico-green">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="stat-val">{{ number_format($stats['value'], 0, ',', '.') }}đ</div>
            <div class="stat-lbl">Giá trị tồn</div>
            <div class="stat-note">Theo đơn giá tham chiếu</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon ico-orange">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="stat-val">{{ $stats['low'] }}</div>
            <div class="stat-lbl">Sắp hết hàng</div>
            <div class="stat-note">Dưới tồn tối thiểu</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon ico-purple">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div class="stat-val">{{ $stats['check'] }}</div>
            <div class="stat-lbl">Cần kiểm hôm nay</div>
            <div class="stat-note">Từ tồn kho hiện tại</div>
        </div>
    </div>

    <!-- Tab bar -->
    <div class="tabs-bar">
        <button class="tab-btn @if($warehouseTab === 'stock') active @endif" wire:click="setTab('stock')">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span>Tồn kho hiện tại</span>
        </button>
        <button class="tab-btn @if($warehouseTab === 'check') active @endif" wire:click="setTab('check')">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span>Tồn cuối ngày</span>
        </button>
        <button class="tab-btn @if($warehouseTab === 'in') active @endif" wire:click="setTab('in')">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-6l-7 7-7-7"/>
            </svg>
            <span>Nhập kho</span>
        </button>
        <button class="tab-btn @if($warehouseTab === 'out') active @endif" wire:click="setTab('out')">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 19l7-7 7 7"/>
            </svg>
            <span>Xuất kho</span>
        </button>
        <button class="tab-btn @if($warehouseTab === 'log') active @endif" wire:click="setTab('log')">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Nhật ký kho</span>
        </button>

        @if($warehouseTab === 'stock')
            <div class="filter-controls">
                <div class="search-wrapper">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm nguyên liệu...">
                </div>
                <select class="type-select" wire:model.live="selectedType">
                    <option value="">Tất cả loại</option>
                    <option value="Động vật">Động vật</option>
                    <option value="Thực vật">Thực vật</option>
                    <option value="Thực phẩm khô">Thực phẩm khô</option>
                    <option value="Gia vị">Gia vị</option>
                </select>
            </div>
        @endif
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
                            <th>Mã NL</th>
                            <th>Nguyên liệu</th>
                            <th>Loại</th>
                            <th>NCC</th>
                            <th style="text-align: right;">Tồn hiện tại</th>
                            <th style="text-align: right;">Tối thiểu</th>
                            <th style="text-align: right;">Đơn giá</th>
                            <th style="text-align: right;">Giá trị</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocksData as $index => $item)
                            <tr wire:click="openLedger({{ $item['ingredient']['id'] }})" style="cursor: pointer;" title="Click để xem Thẻ kho">
                                <td style="text-align: center;">{{ ($stocksData->currentPage() - 1) * $stocksData->perPage() + $index + 1 }}</td>
                                <td><span style="font-weight: 700;">{{ $item['ingredient']['code'] }}</span></td>
                                <td style="font-weight: 700; color: #0f172a;" class="dark:text-white">
                                    <div>{{ $item['ingredient']['name'] }}</div>
                                    <div style="font-size: 10px; color: #94a3b8; font-weight: 500; margin-top: 1px;">Click để xem Thẻ kho</div>
                                </td>
                                <td>{{ $item['ingredient']['type'] }}</td>
                                <td>{{ $item['ingredient']['supplier']['name'] ?? '—' }}</td>
                                <td style="text-align: right; font-weight: 750;">{{ number_format($item['quantity'], 2, ',', '.') }} {{ $item['ingredient']['unit'] }}</td>
                                <td style="text-align: right; color: #64748b;">{{ number_format($item['min_quantity'], 2, ',', '.') }} {{ $item['ingredient']['unit'] }}</td>
                                <td style="text-align: right;">{{ number_format($item['unit_price'], 0, ',', '.') }}đ</td>
                                <td style="text-align: right; font-weight: 700; color: rgb(var(--primary-600));" class="dark:text-primary-400">{{ number_format($item['quantity'] * $item['unit_price'], 0, ',', '.') }}đ</td>
                                <td>
                                    @if($item['quantity'] == 0)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-950/20 dark:text-red-400">Hết hàng</span>
                                    @elseif($item['quantity'] <= $item['min_quantity'])
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/20 dark:text-amber-400">Sắp hết</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-950/20 dark:text-green-400">Đủ hàng</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align: center; color: #94a3b8; padding: 20px; font-style: italic;">
                                    Không tìm thấy dữ liệu tồn kho phù hợp.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($stocksData->hasPages() || $stocksData->total() > 10)
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:12px 4px 2px; font-size:12px; color:#64748b;" class="dark:text-gray-400">
                    <div>
                        Hiển thị {{ $stocksData->firstItem() ?? 0 }}-{{ $stocksData->lastItem() ?? 0 }} trên {{ $stocksData->total() }} mặt hàng tồn kho
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <select wire:model.live="perPage" style="height:30px; border:1px solid #cbd5e1; border-radius:6px; padding:0 8px; font-size:12px; background:transparent;" class="dark:border-gray-700 dark:bg-gray-800">
                            <option value="10">10 / trang</option>
                            <option value="20">20 / trang</option>
                            <option value="50">50 / trang</option>
                        </select>

                        @if($stocksData->hasPages())
                            <nav role="navigation" aria-label="Pagination Navigation" style="display:flex; align-items:center; gap:4px;">
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
                        <span class="text-xs font-bold text-gray-500">Ngày kiểm:</span>
                        <input type="date" class="date-input" wire:model.live="checkDate" style="height:34px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 8px; font-size:12px;">
                    </div>
                    <button type="button" class="wh-action-btn wh-action-btn-primary" wire:click="saveEndDay">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        <span>Lưu tồn cuối ngày</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="wh-table">
                        <thead>
                            <tr>
                                <th style="width: 36px; text-align: center;">#</th>
                                <th>Nguyên liệu</th>
                                <th>Đơn vị</th>
                                <th style="text-align: right;">Tồn hệ thống</th>
                                <th style="text-align: center; width: 180px;">Tồn cuối ngày thực tế</th>
                                <th style="text-align: right;">Chênh lệch</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $kitchenId = auth()->user()?->currentKitchenId();
                                $stocksData = \App\Models\Stock::with('ingredient')
                                    ->when($kitchenId, fn($q) => $q->where('kitchen_id', $kitchenId))
                                    ->get(); 
                            @endphp
                            @foreach($stocksData as $index => $item)
                                <tr>
                                    <td style="text-align: center;">{{ $index + 1 }}</td>
                                    <td>
                                        <div style="font-weight: 700;">{{ $item->ingredient->name }}</div>
                                        <div style="font-size:10px; color:#64748b;">{{ $item->ingredient->code }} · {{ $item->ingredient->type }}</div>
                                    </td>
                                    <td>{{ $item->ingredient->unit }}</td>
                                    <td style="text-align: right; font-weight: 700;">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                                    <td style="text-align: center;">
                                        <input type="number" 
                                               step="0.01" 
                                               wire:model.live="actualQuantities.{{ $item->id }}" 
                                               class="w-28 text-center border border-gray-300 rounded px-2 py-1 text-xs dark:bg-gray-800 dark:border-gray-700">
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: {{ ($actualQuantities[$item->id] ?? $item->quantity) - $item->quantity < 0 ? '#ef4444' : (($actualQuantities[$item->id] ?? $item->quantity) - $item->quantity > 0 ? '#16a34a' : 'inherit') }}">
                                        @php $diff = ($actualQuantities[$item->id] ?? $item->quantity) - $item->quantity; @endphp
                                        {{ $diff > 0 ? '+' . $diff : $diff }}
                                    </td>
                                    <td>
                                        <input type="text" 
                                               wire:model.live="checkNotes.{{ $item->id }}" 
                                               placeholder="Lý do chênh lệch..."
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
                    <h3 style="font-size: 1rem; font-weight: 700; color:#0f172a;" class="dark:text-white">Luồng nhập kho</h3>
                    <p style="font-size: 0.78rem; color:#64748b; margin-top:0.25rem; margin-bottom:1.5rem;">Nhập theo Đơn đặt hàng (PO) hoặc Nhập mua ngoài trực tiếp.</p>
                    <button type="button" class="wh-action-btn wh-action-btn-primary" style="margin: 0 auto;" wire:click="openInTypeModal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tạo phiếu nhập</span>
                    </button>
                    <div style="font-size:0.75rem; color:#94a3b8; margin-top:1rem;">Nhấn Tạo phiếu nhập để bắt đầu.</div>
                </div>
            @elseif($inMode === 'po')
                <!-- PO Inbound Workspace (Ảnh 5) -->
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="form-field" style="width: 320px;">
                                <label style="font-size:0.7rem;">Mã Đơn đặt hàng đang chờ giao</label>
                                <select wire:model.live="selectedPOId" style="height: 34px;">
                                    <option value="">-- Chọn đơn hàng PO --</option>
                                    @foreach($this->getPendingPOs() as $po)
                                        <option value="{{ $po->id }}">{{ $po->code }} - {{ $po->supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="button" class="wh-action-btn wh-action-btn-primary" wire:click="confirmInboundPO">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Xác nhận nhập</span>
                        </button>
                    </div>

                    @if(empty($poItemsData))
                        <div style="text-align: center; color: #94a3b8; padding: 2rem; font-style: italic;">
                            Không có sản phẩm nào cần nhập kho trong PO được chọn.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="wh-table">
                                <thead>
                                    <tr>
                                        <th>Nguyên liệu</th>
                                        <th style="text-align: right;">SL dự kiến</th>
                                        <th style="text-align: center; width:160px;">SL thực nhập</th>
                                        <th style="text-align: right; width:180px;">Đơn giá</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($poItemsData as $index => $item)
                                        <tr>
                                            <td style="font-weight: 700; color: #0f172a;" class="dark:text-white">{{ $item['name'] }}</td>
                                            <td style="text-align: right; font-weight:600;">{{ number_format($item['quantity_ordered'], 2, ',', '.') }} {{ $item['unit'] }}</td>
                                            <td style="text-align: center;">
                                                <input type="number" step="0.01" wire:model="poItemsData.{{ $index }}.quantity_received" class="table-input" style="width: 110px;">
                                            </td>
                                            <td style="text-align: right;">
                                                <input type="number" step="1000" wire:model="poItemsData.{{ $index }}.unit_price" class="table-input" style="width: 140px; text-align: right;">
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
                            <h3 style="font-weight: 800; font-size:0.9rem;" class="dark:text-white">Nhập mua ngoài / Nhập kho trực tiếp</h3>
                            <p style="font-size:0.75rem; color:#64748b;">Nhập trực tiếp từ thị trường không qua đơn hàng PO.</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" class="wh-action-btn" wire:click="addDirectRow">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Thêm mặt hàng</span>
                            </button>
                            <button type="button" class="wh-action-btn wh-action-btn-primary" wire:click="confirmDirectInbound">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Lưu phiếu nhập</span>
                            </button>
                        </div>
                    </div>

                    <div class="form-field mb-4" style="max-width:320px;">
                        <label style="color:#ef4444; font-weight:700;">Hóa đơn chứng từ đính kèm (Bắt buộc) *</label>
                        <input type="file" wire:model="directInvoiceFile" class="ctrl" style="height:36px; padding:4px;">
                        <div wire:loading wire:target="directInvoiceFile" class="text-xs text-gray-500 mt-1">Đang tải tệp lên...</div>
                        @error('directInvoiceFile') <span class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div class="overflow-x-auto">
                        <table class="wh-table">
                            <thead>
                                <tr>
                                    <th>Nguyên liệu</th>
                                    <th style="text-align: center; width: 160px;">Số lượng</th>
                                    <th style="text-align: right; width: 180px;">Đơn giá</th>
                                    <th style="width: 60px; text-align: center;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($directItemsData as $index => $item)
                                    <tr>
                                        <td>
                                            <select wire:model="directItemsData.{{ $index }}.ingredient_id" style="height:34px; width:100%; border:1px solid #cbd5e1; border-radius:6px;">
                                                <option value="">-- Chọn nguyên liệu --</option>
                                                @foreach($this->getIngredientsList() as $ing)
                                                    <option value="{{ $ing->id }}">{{ $ing->code }} - {{ $ing->name }} ({{ $ing->unit }})</option>
                                                @endforeach
                                            </select>
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
                    <h3 style="font-size: 1rem; font-weight: 700; color:#0f172a;" class="dark:text-white">Luồng xuất kho</h3>
                    <p style="font-size: 0.78rem; color:#64748b; margin-top:0.25rem; margin-bottom:1.5rem;">Xuất sản xuất hàng ngày hoặc Điều chuyển đi chi nhánh/bếp khác.</p>
                    <button type="button" class="wh-action-btn wh-action-btn-primary" style="margin: 0 auto;" wire:click="openOutTypeModal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tạo phiếu xuất</span>
                    </button>
                    <div style="font-size:0.75rem; color:#94a3b8; margin-top:1rem;">Nhấn Tạo phiếu xuất để bắt đầu.</div>
                </div>
            @elseif($outMode === 'production')
                <!-- Production Outbound Workspace -->
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4 flex-wrap gap-2">
                        <div class="flex items-center gap-3">
                            <div class="form-field" style="width: 160px;">
                                <label style="font-size:0.7rem;">Ngày xuất</label>
                                <input type="date" wire:model.live="prodDate" style="height:34px;">
                            </div>
                            <div class="form-field" style="width: 140px;">
                                <label style="font-size:0.7rem;">Ca làm việc</label>
                                <select wire:model.live="prodShiftId" style="height:34px;">
                                    @foreach(\App\Models\Shift::all() as $sh)
                                        <option value="{{ $sh->id }}">{{ $sh->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="button" class="wh-action-btn wh-action-btn-primary" wire:click="confirmProductionOut">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Xác nhận xuất</span>
                        </button>
                    </div>
                    <div style="font-size:11px; color:#64748b; margin-bottom:12px;">Hệ thống tự động tổng hợp định lượng từ thực đơn tuần đã chốt.</div>

                    @if(empty($prodItemsData))
                        <div style="text-align: center; color: #94a3b8; padding: 2rem; font-style: italic;">
                            Không có nguyên liệu sản xuất nào được lên lịch cho ngày và ca đã chọn.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="wh-table">
                                <thead>
                                    <tr>
                                        <th>Nguyên liệu</th>
                                        <th style="text-align: right;">Tồn kho hiện tại</th>
                                        <th style="text-align: right;">Số lượng yêu cầu</th>
                                        <th style="text-align: center; width: 180px;">Số lượng thực xuất</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prodItemsData as $index => $item)
                                        <tr>
                                            <td style="font-weight: 700; color: #0f172a;" class="dark:text-white">{{ $item['name'] }}</td>
                                            <td style="text-align: right; font-weight:700;">{{ number_format($item['available_qty'], 2, ',', '.') }} {{ $item['unit'] }}</td>
                                            <td style="text-align: right; color:#64748b;">{{ number_format($item['quantity_expected'], 2, ',', '.') }} {{ $item['unit'] }}</td>
                                            <td style="text-align: center;">
                                                <input type="number" step="0.01" wire:model="prodItemsData.{{ $index }}.quantity_actual" class="table-input" style="width: 120px;">
                                                @if(($prodItemsData[$index]['quantity_actual'] ?? 0) > $item['available_qty'])
                                                    <div style="color:#ef4444; font-size:10px; font-weight:700; margin-top:2px;">Vượt tồn kho hiện tại</div>
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
                                <label style="font-size:0.7rem;">Kho/Bếp nhận</label>
                                <select wire:model="destKitchenId" style="height:34px;">
                                    <option value="">-- Chọn bếp nhận --</option>
                                    @foreach($this->getTransferKitchens() as $kit)
                                        <option value="{{ $kit->id }}">{{ $kit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" class="wh-action-btn" wire:click="addTransferRow">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Thêm mặt hàng</span>
                            </button>
                            <button type="button" class="wh-action-btn wh-action-btn-primary" wire:click="confirmTransferOut">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                <span>Tạo phiếu điều chuyển</span>
                            </button>
                        </div>
                    </div>

                    <div class="form-field mb-4">
                        <label>Ghi chú điều chuyển</label>
                        <input type="text" wire:model="transferNote" placeholder="VD: Điều chuyển khẩn cấp ca trưa..." style="height:36px;">
                    </div>

                    <div class="overflow-x-auto mb-6">
                        <table class="wh-table">
                            <thead>
                                <tr>
                                    <th>Mặt hàng</th>
                                    <th style="text-align: right; width: 180px;">Tồn hiện tại</th>
                                    <th style="text-align: center; width: 180px;">Số lượng chuyển</th>
                                    <th style="width: 60px; text-align: center;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transferItemsData as $index => $item)
                                    <tr>
                                        <td>
                                            <select wire:model.live="transferItemsData.{{ $index }}.ingredient_id" style="height:34px; width:100%; border:1px solid #cbd5e1; border-radius:6px;">
                                                <option value="">-- Chọn nguyên liệu --</option>
                                                @foreach($this->getIngredientsList() as $ing)
                                                    <option value="{{ $ing->id }}">{{ $ing->code }} - {{ $ing->name }} ({{ $ing->unit }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td style="text-align: right; font-weight:700;">
                                            {{ number_format($item['available_qty'], 2, ',', '.') }}
                                        </td>
                                        <td style="text-align: center;">
                                            <input type="number" step="0.01" wire:model="transferItemsData.{{ $index }}.quantity" class="table-input" style="width: 120px;">
                                            @if(($transferItemsData[$index]['quantity'] ?? 0) > $item['available_qty'])
                                                <div style="color:#ef4444; font-size:10px; font-weight:700; margin-top:2px;">Vượt tồn kho hiện tại</div>
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
                        <div style="font-weight: 800; margin-bottom: 0.5rem; font-size: 0.9rem;" class="dark:text-white">Phiếu điều chuyển gần đây</div>
                        <div class="overflow-x-auto">
                            <table class="wh-table">
                                <thead>
                                    <tr>
                                        <th>Mã phiếu</th>
                                        <th>Bếp xuất</th>
                                        <th>Bếp nhận</th>
                                        <th>Thời gian</th>
                                        <th>Trạng thái</th>
                                        <th style="text-align: center;">Hành động</th>
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
                                                @if($tf->status === 'Hoàn thành')
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800">Đã nhận</span>
                                                @elseif($tf->status === 'Hủy')
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">Đã hủy</span>
                                                @else
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Đang chuyển</span>
                                                @endif
                                            </td>
                                            <td style="text-align: center;">
                                                @if($tf->status === 'Đang chuyển' && auth()->user()?->currentKitchenId() === $tf->dest_kitchen_id)
                                                    <button type="button" class="wh-action-btn wh-action-btn-primary" style="height:28px; font-size:10px; padding:0 8px; margin:0 auto;" wire:click="confirmTransferReceive({{ $tf->id }})">
                                                        Xác nhận nhận hàng
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
            <div class="overflow-x-auto">
                <table class="wh-table">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Loại giao dịch</th>
                            <th>Nguyên liệu</th>
                            <th style="text-align: right;">Số lượng</th>
                            <th style="text-align: right;">Tồn sau</th>
                            <th>Chứng từ / Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logData as $log)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($log['created_at'])->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if(in_array($log['type'], ['Nhập kho', 'Nhập kho ngoài', 'Nhập chuyển kho']))
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-950/20 dark:text-green-400">
                                            {{ $log['type'] }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-950/20 dark:text-red-400">
                                            {{ $log['type'] }}
                                        </span>
                                    @endif
                                </td>
                                <td style="font-weight: 700;">{{ $log['ingredient']['name'] ?? '—' }}</td>
                                <td style="text-align: right; font-weight: 750; color: {{ in_array($log['type'], ['Nhập kho', 'Nhập kho ngoài', 'Nhập chuyển kho']) ? '#16a34a' : '#ef4444' }}">
                                    {{ in_array($log['type'], ['Nhập kho', 'Nhập kho ngoài', 'Nhập chuyển kho']) ? '+' : '-' }}{{ number_format(abs($log['quantity']), 2, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 700;">{{ number_format($log['after_quantity'], 2, ',', '.') }}</td>
                                <td>
                                    @if($log['voucher_code'])
                                        <span style="font-family: monospace; font-weight: 700; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size:11px;" class="dark:bg-gray-800">{{ $log['voucher_code'] }}</span> — 
                                    @endif
                                    {{ $log['note'] }}
                                    @if($log['attachment_url'])
                                        <a href="{{ Storage::disk('public')->url($log['attachment_url']) }}" target="_blank" class="ml-2 text-primary-600 hover:underline font-bold text-xs">
                                            [Xem chứng từ]
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: #94a3b8; padding: 20px; font-style: italic;">
                                    Chưa có giao dịch kho nào được ghi nhận.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- FLOW MODALS -->
    @if($showInModal)
        <div class="flow-modal-overlay">
            <div class="flow-modal-container">
                <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700 mb-4">
                    <div>
                        <h3 class="font-extrabold text-base text-gray-900 dark:text-white">Chọn loại phiếu nhập</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kho tạo phiếu nhập theo PO hoặc mua ngoài.</p>
                    </div>
                    <button type="button" class="text-gray-400 hover:text-gray-500" wire:click="closeModals">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="grid gap-3">
                    <button type="button" class="flow-modal-choice" wire:click="startInbound('po')">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-950/20 dark:text-blue-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 dark:text-white text-sm">Nhập theo Đơn đặt hàng (PO)</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Chọn PO đang chờ giao, tự động điền mặt hàng và số lượng dự kiến.</div>
                        </div>
                    </button>
                    <button type="button" class="flow-modal-choice" wire:click="startInbound('direct')">
                        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg dark:bg-emerald-950/20 dark:text-emerald-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 dark:text-white text-sm">Nhập mua ngoài / Nhập kho trực tiếp</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Tự thêm mặt hàng, số lượng, đơn giá và đính kèm hóa đơn.</div>
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
                        <h3 class="font-extrabold text-base text-gray-900 dark:text-white">Chọn loại phiếu xuất</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kho tạo phiếu xuất sản xuất hoặc điều chuyển.</p>
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
                            <div class="font-bold text-gray-900 dark:text-white text-sm">Xuất kho sản xuất</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Chọn ngày và ca, tự động tải danh sách nguyên liệu cần thiết.</div>
                        </div>
                    </button>
                    <button type="button" class="flow-modal-choice" wire:click="startOutbound('transfer')">
                        <div class="p-2 bg-purple-50 text-purple-600 rounded-lg dark:bg-purple-950/20 dark:text-purple-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 dark:text-white text-sm">Xuất điều chuyển / Chuyển kho</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Chọn Bếp nhận, thêm nhiều mặt hàng và theo dõi trạng thái vận chuyển.</div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- LEDGER (THẺ KHO) DRAWER MODAL -->
    @if($selectedLedgerIngId)
        @php $ledgerIng = \App\Models\Ingredient::find($selectedLedgerIngId); @endphp
        <div class="flow-modal-overlay">
            <div class="flow-modal-container" style="width: min(720px, 94vw);">
                <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700 mb-4">
                    <div>
                        <h3 class="font-extrabold text-base text-gray-900 dark:text-white">Thẻ kho - {{ $ledgerIng->code }} - {{ $ledgerIng->name }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Lịch sử biến động nhập xuất nguyên liệu của bếp.</p>
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
                                <th>Thời gian</th>
                                <th>Mã phiếu</th>
                                <th>Loại giao dịch</th>
                                <th style="text-align: right;">Số lượng</th>
                                <th style="text-align: right;">Tồn sau</th>
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
                                        @if(in_array($log['type'], ['Nhập kho', 'Nhập kho ngoài', 'Nhập chuyển kho']))
                                            <span class="px-2 rounded bg-green-50 text-green-700 font-semibold text-[10px]">{{ $log['type'] }}</span>
                                        @else
                                            <span class="px-2 rounded bg-red-50 text-red-700 font-semibold text-[10px]">{{ $log['type'] }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: {{ in_array($log['type'], ['Nhập kho', 'Nhập kho ngoài', 'Nhập chuyển kho']) ? '#16a34a' : '#ef4444' }}">
                                        {{ in_array($log['type'], ['Nhập kho', 'Nhập kho ngoài', 'Nhập chuyển kho']) ? '+' : '-' }}{{ number_format(abs($log['quantity']), 2, ',', '.') }}
                                    </td>
                                    <td style="text-align: right; font-weight: 700;">{{ number_format($log['after_quantity'], 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #94a3b8; padding: 20px; font-style: italic;">
                                        Không tìm thấy lịch sử giao dịch nào.
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
