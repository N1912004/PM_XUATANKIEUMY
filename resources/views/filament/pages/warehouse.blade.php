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
            margin-bottom: 1rem;
            width: 100%;
        }
        .search-wrapper {
            position: relative;
            flex: 1;
            max-width: 280px;
            display: flex;
            align-items: center;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            padding: 0 0.625rem;
            height: 34px;
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
        .form-field input, .form-field select {
            height: 38px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0 0.75rem;
            font-size: 0.8125rem;
            background: #ffffff;
            color: #0f172a;
            outline: none;
        }
        .dark .form-field input, .dark .form-field select {
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
            <button type="button" class="wh-action-btn" wire:click="setTab('in')">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-6l-7 7-7-7"/>
                </svg>
                <span>Tạo phiếu nhập</span>
            </button>
            <button type="button" class="wh-action-btn wh-action-btn-primary" wire:click="setTab('out')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 19l7-7 7 7"/>
                </svg>
                <span>Tạo phiếu xuất</span>
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
            <div class="tsp flex-1"></div>
            <div class="filter-controls">
                <div class="search-wrapper">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm nguyên liệu...">
                </div>
                <select class="type-select" wire:model.live="selectedType">
                    <option value="">Tất cả loại</option>
                    <option value="Động Vật">Động Vật</option>
                    <option value="Thực Vật">Thực Vật</option>
                    <option value="Thực Phẩm Khô">Thực Phẩm Khô</option>
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
                            <tr>
                                <td style="text-align: center;">{{ $index + 1 }}</td>
                                <td><span style="font-weight: 700;">{{ $item['ingredient']['code'] }}</span></td>
                                <td style="font-weight: 600; color: #0f172a;" class="dark:text-white">{{ $item['ingredient']['name'] }}</td>
                                <td>{{ $item['ingredient']['type'] }}</td>
                                <td>{{ $item['ingredient']['supplier']['name'] ?? '—' }}</td>
                                <td style="text-align: right; font-weight: 750;">{{ $item['quantity'] }} {{ $item['ingredient']['unit'] }}</td>
                                <td style="text-align: right; color: #64748b;">{{ $item['min_quantity'] }} {{ $item['ingredient']['unit'] }}</td>
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

        @elseif($warehouseTab === 'check')
            <div>
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-500">Ngày kiểm:</span>
                        <input type="date" class="date-input" wire:model.live="checkDate">
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
                            @php $stocksData = \App\Models\Stock::with('ingredient')->get(); @endphp
                            @foreach($stocksData as $index => $item)
                                <tr>
                                    <td style="text-align: center;">{{ $index + 1 }}</td>
                                    <td style="font-weight: 700;">{{ $item->ingredient->name }}</td>
                                    <td>{{ $item->ingredient->unit }}</td>
                                    <td style="text-align: right; font-weight: 700;">{{ $item->quantity }}</td>
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
            <div>
                <form wire:submit.prevent="saveMovement('in')">
                    <div class="form-grid">
                        <div class="form-field">
                            <label>Nguyên liệu nhận</label>
                            <select wire:model="inIngredientId" required>
                                @foreach(\App\Models\Ingredient::all() as $ing)
                                    <option value="{{ $ing->id }}">{{ $ing->code }} - {{ $ing->name }} ({{ $ing->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label>Ngày nhập kho</label>
                            <input type="date" wire:model="inDate" required>
                        </div>
                        <div class="form-field">
                            <label>Số lượng</label>
                            <input type="number" step="0.01" min="0.01" wire:model="inQuantity" required>
                        </div>
                        <div class="form-field">
                            <label>Đơn giá tham chiếu (VND)</label>
                            <input type="number" step="1000" min="0" wire:model="inPrice" required>
                        </div>
                        <div class="form-field">
                            <label>Nguồn / Chứng từ</label>
                            <input type="text" wire:model="inRef" placeholder="VD: PO005 / NCC Hưng Thịnh">
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button type="submit" class="wh-action-btn wh-action-btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span>Lưu nhập kho</span>
                        </button>
                    </div>
                </form>
            </div>

        @elseif($warehouseTab === 'out')
            <div>
                <form wire:submit.prevent="saveMovement('out')">
                    <div class="form-grid">
                        <div class="form-field">
                            <label>Nguyên liệu xuất</label>
                            <select wire:model="outIngredientId" required>
                                @foreach(\App\Models\Ingredient::all() as $ing)
                                    <option value="{{ $ing->id }}">{{ $ing->code }} - {{ $ing->name }} ({{ $ing->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label>Ngày xuất kho</label>
                            <input type="date" wire:model="outDate" required>
                        </div>
                        <div class="form-field">
                            <label>Số lượng</label>
                            <input type="number" step="0.01" min="0.01" wire:model="outQuantity" required>
                        </div>
                        <div class="form-field">
                            <label>Lý do xuất</label>
                            <select wire:model="outReason" required>
                                <option value="Sản xuất theo list hàng">Sản xuất theo list hàng</option>
                                <option value="Hủy hao hụt">Hủy hao hụt</option>
                                <option value="Điều chuyển">Điều chuyển</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label>Ghi chú thêm</label>
                            <input type="text" wire:model="outRef" placeholder="VD: Ca trưa ngày 30/05">
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button type="submit" class="wh-action-btn wh-action-btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                            <span>Lưu xuất kho</span>
                        </button>
                    </div>
                </form>
            </div>

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
                                    @if($log['type'] === 'Nhập kho')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-950/20 dark:text-green-400">Nhập kho</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-950/20 dark:text-red-400">Xuất kho</span>
                                    @endif
                                </td>
                                <td style="font-weight: 700;">{{ $log['ingredient']['name'] ?? '—' }}</td>
                                <td style="text-align: right; font-weight: 750; color: {{ $log['type'] === 'Nhập kho' ? '#16a34a' : '#ef4444' }}">
                                    {{ $log['type'] === 'Nhập kho' ? '+' : '-' }}{{ $log['quantity'] }}
                                </td>
                                <td style="text-align: right; font-weight: 700;">{{ $log['after_quantity'] }}</td>
                                <td>{{ $log['note'] }}</td>
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
</x-filament-panels::page>
