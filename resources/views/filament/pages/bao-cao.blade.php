@push('styles')
<style>
        .report-header-container {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .report-title {
            font-size: 1.25rem;
            font-weight: 800;
            margin: 0 0 0.25rem;
            color: #0f172a;
        }
        .dark .report-title {
            color: #ffffff;
        }
        .report-subtitle {
            font-size: 0.78rem;
            color: #64748b;
            margin: 0;
        }
        .dark .report-subtitle {
            color: #94a3b8;
        }
        .excel-btn {
            height: 36px;
            padding: 0 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
            cursor: pointer;
            font-size: 0.8125rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            color: #0f172a;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }
        .excel-btn:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }
        .dark .excel-btn {
            background: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }
        .dark .excel-btn:hover {
            background: #334155;
        }

        /* Filter bar */
        .filter-bar {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 13px 18px;
            box-shadow: 0 1px 2px rgba(15,23,42,0.04);
        }
        .dark .filter-bar {
            background: #0f172a;
            border-color: #1e293b;
        }
        .filter-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            color: #64748b;
        }
        .dark .filter-item {
            color: #94a3b8;
        }
        .date-input {
            height: 40px;
            border: 1.5px solid #bfdbfe;
            border-radius: 9px;
            padding: 0 12px;
            font-size: 14px;
            font-weight: 700;
            color: #1267e8;
            background: #ebf3ff;
            cursor: pointer;
            outline: none;
            transition: .13s;
        }
        .date-input:focus {
            box-shadow: 0 0 0 3px rgba(18,103,232,.1);
        }
        .dark .date-input {
            border-color: #1e3a8a;
            background: #172554;
            color: #93c5fd;
        }
        .week-btn {
            height: 34px;
            padding: 0 14px;
            background: rgba(var(--primary-500), 0.1);
            color: rgb(var(--primary-600));
            border: 1px solid rgb(var(--primary-600));
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            transition: .13s;
            flex-shrink: 0;
        }
        .week-btn:hover {
            background: rgb(var(--primary-600));
            color: #ffffff;
        }
        .dark .week-btn {
            background: rgba(var(--primary-500), 0.15);
            color: rgb(var(--primary-300));
            border-color: rgb(var(--primary-600));
        }
        .dark .week-btn:hover {
            background: rgb(var(--primary-600));
            color: #ffffff;
        }

        .shifts-group {
            display: flex;
            align-items: center;
            gap: 5px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 4px 10px;
            font-size: 12px;
        }
        .dark .shifts-group {
            background: #1e293b;
            border-color: #334155;
        }
        .shift-checkbox-label {
            display: flex;
            align-items: center;
            gap: 3px;
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            user-select: none;
        }
        .shift-ca1 { background: #eff6ff; color: #1e40af; border-color: #bfdbfe; }
        .shift-ca2 { background: #f0fdf4; color: #065f46; border-color: #a7f3d0; }
        .shift-ca3 { background: #fef3c7; color: #78350f; border-color: #fde68a; }
        .shift-ca4 { background: #f5f3ff; color: #4c1d95; border-color: #ddd6fe; }

        .search-container {
            position: relative;
            max-width: 280px;
            min-width: 220px;
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
            padding: 0 12px;
            height: 36px;
            flex: 1;
            transition: .13s;
        }
        .search-container:focus-within {
            border-color: #93c5fd;
        }
        .dark .search-container {
            border-color: #334155;
            background: #1e293b;
        }
        .search-container input {
            border: none;
            outline: none;
            background: transparent;
            font-size: 13px;
            width: 100%;
            color: #0f172a;
        }
        .dark .search-container input {
            color: #ffffff;
        }
        .search-icon {
            color: #94a3b8;
            font-size: 12px;
            flex-shrink: 0;
        }

        /* Chips / Stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 16px;
            margin-bottom: 14px;
        }
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        .stat-card {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            box-shadow: 0 1px 2px rgba(15,23,42,0.04);
            transition: .13s;
        }
        .stat-card:hover {
            border-color: #bfdbfe;
            transform: translateY(-1px);
        }
        .dark .stat-card {
            background: #0f172a;
            border-color: #1e293b;
        }
        .stat-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        .stat-val {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }
        .dark .stat-val {
            color: #ffffff;
        }
        .stat-lbl {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }
        .dark .stat-lbl {
            color: #94a3b8;
        }

        /* Day block */
        .day-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 14px;
            box-shadow: 0 1px 2px rgba(15,23,42,0.04);
        }
        .dark .day-card {
            background: #0f172a;
            border-color: #1e293b;
        }
        .day-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: linear-gradient(135deg,#eff6ff,#dbeafe);
            border-bottom: 1px solid #bfdbfe;
        }
        .dark .day-header {
            background: linear-gradient(135deg,#172554,#1e3a8a);
            border-color: #1e3a8a;
        }
        .day-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e40af;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .dark .day-title {
            color: #93c5fd;
        }
        .day-badge {
            font-size: 11.5px;
            background: #1267e8;
            color: #ffffff;
            border-radius: 6px;
            padding: 2px 9px;
            font-weight: 700;
            margin-left: 8px;
        }

        /* Ca sections */
        .shift-section {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
        }
        .shift-section:last-child {
            border-bottom: none;
        }
        .dark .shift-section {
            border-color: #1e293b;
        }
        .shift-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        .shift-badge {
            border-radius: 8px;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid;
        }
        .shift-count-badge {
            font-size: 12.5px;
            color: #64748b;
            font-weight: 400;
        }
        .dark .shift-count-badge {
            color: #94a3b8;
        }

        /* Dish block */
        .dish-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 10px;
            overflow: hidden;
            background: #ffffff;
        }
        .dark .dish-card {
            border-color: #1e293b;
            background: #1e293b/20;
        }
        .dish-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.625rem 0.875rem;
            background: #f8fafc;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .dark .dish-head {
            background: #1e293b/60;
        }
        .dish-title-block {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dish-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 0.875rem;
        }
        .dark .dish-name {
            color: #ffffff;
        }
        .dish-type-badge {
            font-size: 0.68rem;
            color: #64748b;
            background: #f1f5f9;
            border-radius: 6px;
            padding: 0.125rem 0.5rem;
            font-weight: 600;
        }
        .dark .dish-type-badge {
            color: #cbd5e1;
            background: #334155;
        }
        .dish-portions-block {
            display: flex;
            gap: 0.375rem;
        }
        .dish-portions-badge {
            font-size: 11.5px;
            font-weight: 700;
            color: #1267e8;
            background: #ebf3ff;
            border-radius: 6px;
            padding: 2px 9px;
            border: none;
        }
        .dark .dish-portions-badge {
            color: #60a5fa;
            background: #1e3a8a/30;
            border-color: #1e3a8a;
        }
        .dish-portions-phan {
            font-size: 11.5px;
            font-weight: 700;
            color: #059669;
            background: #ecfdf5;
            border-radius: 6px;
            padding: 2px 9px;
            border: none;
        }
        .dark .dish-portions-phan {
            color: #4ade80;
            background: #14532d/30;
            border-color: #14532d;
        }

        /* Ingredients table */
        .ing-table {
            width: 100%;
            border-collapse: collapse;
        }
        .ing-table th {
            padding: 8px 12px;
            text-align: left;
            font-size: 10.5px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .dark .ing-table th {
            background: #1e293b;
            color: #94a3b8;
            border-color: #334155;
        }
        .ing-table td {
            padding: 9px 12px;
            font-size: 12.5px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }
        .dark .ing-table td {
            border-color: #1e293b;
            color: #cbd5e1;
        }
        .ing-table tr:last-child td {
            border-bottom: none;
        }
        .ing-table tbody tr:hover {
            background: #fafcff;
        }
        .dark .ing-table tbody tr:hover {
            background: #1e293b;
        }
        .ing-num {
            width: 36px;
            text-align: center;
            color: #64748b;
            font-weight: 600;
        }
        .ing-code-badge {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            background: none;
            border: none;
            padding: 0;
        }
        .dark .ing-code-badge {
            color: #94a3b8;
        }
        .ing-name {
            font-weight: 500;
            color: #0f172a;
        }
        .dark .ing-name {
            color: #ffffff;
        }
        .ing-kg-val {
            font-weight: 700;
            color: #0f172a;
        }
        .dark .ing-kg-val {
            color: #ffffff;
        }
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            text-align: center;
            color: #94a3b8;
        }
        .dark .empty-state {
            color: #94a3b8;
        }
        .empty-state h3 {
            font-size: 16px;
            font-weight: 700;
            color: #334155;
            margin-top: 14px;
            margin-bottom: 6px;
        }
        .dark .empty-state h3 {
            color: #cbd5e1;
        }
        .empty-state p {
            font-size: 13px;
            color: #64748b;
        }
</style>
@endpush

<x-filament-panels::page>
    <div class="report-header-container">
        <div>
            <h1 class="report-title">Báo cáo – Xuất ăn</h1>
            <p class="report-subtitle">Tổng hợp món ăn & nguyên liệu theo khoảng ngày và ca phục vụ</p>
        </div>
        <div>
            <button type="button" class="excel-btn">
                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
                <span>Xuất Excel</span>
            </button>
        </div>
    </div>

    <!-- Filter bar -->
    <div class="filter-bar">
        <div class="filter-item">
            <span class="font-bold">Từ ngày:</span>
            <input type="date" class="date-input" wire:model.live="fromDate">
        </div>
        <div class="filter-item">
            <span class="font-bold">Đến ngày:</span>
            <input type="date" class="date-input" wire:model.live="toDate">
        </div>
        <button type="button" class="week-btn" wire:click="setThisWeek">Theo tuần thực đơn</button>

        <div class="shifts-group">
            <span class="font-semibold text-gray-500 mr-1" style="font-size: 0.72rem;">Ca:</span>
            
            @php $allShifts = \App\Models\Shift::all(); @endphp
            @foreach($allShifts as $index => $shift)
                @php 
                    $classes = ['shift-ca1', 'shift-ca2', 'shift-ca3', 'shift-ca4'];
                    $class = $classes[$index % count($classes)];
                @endphp
                <label class="shift-checkbox-label {{ $class }}">
                    <input type="checkbox" 
                           value="{{ $shift->id }}" 
                           wire:click="toggleShift({{ $shift->id }})"
                           @if(in_array($shift->id, $selectedShifts)) checked @endif
                           style="width: 12px; height: 12px; border-radius: 3px; border-color: currentColor;">
                    <span>{{ strtoupper($shift->name) }}</span>
                </label>
            @endforeach
        </div>

        <div class="search-container">
            <svg class="search-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm món / nguyên liệu...">
        </div>
    </div>

    <!-- Stats Cards -->
    @php $stats = $this->getStats(); @endphp
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #eff6ff; color: #1e40af;">📅</div>
            <div>
                <div class="stat-val">{{ $stats['days'] }}</div>
                <div class="stat-lbl">Ngày có thực đơn</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff7ed; color: #ea580c;">🍽</div>
            <div>
                <div class="stat-val">{{ $stats['dishes'] }}</div>
                <div class="stat-lbl">Lượt món</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #f5f3ff; color: #7c3aed;">🌿</div>
            <div>
                <div class="stat-val">{{ $stats['ingredients'] }}</div>
                <div class="stat-lbl">Dòng nguyên liệu</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #ecfdf5; color: #059669;">👥</div>
            <div>
                <div class="stat-val">{{ number_format($stats['suat'], 0, ',', '.') }}</div>
                <div class="stat-lbl">Tổng suất</div>
            </div>
        </div>
    </div>

    <!-- Main Report Content -->
    @php $groupedData = $this->getGroupedData(); @endphp
    @if(empty($groupedData))
        <div class="empty-state">
            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <h3>Không có dữ liệu trong khoảng đã chọn</h3>
            <p class="text-xs mt-1 text-gray-500">Hãy chọn lại khoảng ngày, ca, hoặc thử tìm kiếm cụ từ khác.</p>
        </div>
    @else
        @foreach($groupedData as $day)
            <div class="day-card">
                <div class="day-header">
                    <div class="day-title">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ strtoupper($day['day_of_week']) }} – {{ $day['date_formatted'] }}</span>
                        <span class="day-badge">{{ count($day['shifts']) }} ca</span>
                    </div>
                </div>

                @foreach($day['shifts'] as $index => $shift)
                    @php 
                        $badgeColors = [
                            ['bg' => '#eff6ff', 'text' => '#1e40af', 'border' => '#bfdbfe'],
                            ['bg' => '#f0fdf4', 'text' => '#065f46', 'border' => '#a7f3d0'],
                            ['bg' => '#fef3c7', 'text' => '#78350f', 'border' => '#fde68a'],
                        ];
                        $color = $badgeColors[$index % count($badgeColors)];
                    @endphp
                    <div class="shift-section">
                        <div class="shift-header">
                            <span class="shift-badge" style="background: {{ $color['bg'] }}; color: {{ $color['text'] }}; border-color: {{ $color['border'] }};">
                                {{ strtoupper($shift['name']) }}
                            </span>
                            <span class="shift-count-badge">{{ count($shift['dishes']) }} món</span>
                        </div>

                        @foreach($shift['dishes'] as $dish)
                            <div class="dish-card">
                                <div class="dish-head">
                                    <div class="dish-title-block">
                                        <svg class="w-4.5 h-4.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        <span class="dish-name">{{ $dish['name'] }}</span>
                                        <span class="dish-type-badge">{{ $dish['type'] }}</span>
                                    </div>
                                    <div class="dish-portions-block">
                                        <span class="dish-portions-badge">{{ $dish['suat'] }} suất</span>
                                        <span class="dish-portions-phan">{{ $dish['phan'] }} phần</span>
                                    </div>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="ing-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 36px; text-align: center;">#</th>
                                                <th>Mã NL</th>
                                                <th>Tên nguyên liệu</th>
                                                <th style="text-align: center;">ĐL (g/suất)</th>
                                                <th style="text-align: center;">Số suất</th>
                                                <th style="text-align: center;">Số phần</th>
                                                <th style="text-align: right;">Tổng KG</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($dish['ingredients'] as $iIndex => $ing)
                                                <tr>
                                                    <td class="ing-num">{{ $iIndex + 1 }}</td>
                                                    <td><span class="ing-code-badge">{{ $ing['code'] }}</span></td>
                                                    <td class="ing-name">{{ $ing['name'] }}</td>
                                                    <td style="text-align: center;">{{ number_format($ing['dl_g'], 0) }}</td>
                                                    <td style="text-align: center;">{{ $ing['suat'] }}</td>
                                                    <td style="text-align: center; font-weight: 700; color: #059669;">{{ $ing['phan'] }}</td>
                                                    <td class="ing-kg-val" style="text-align: right;">
                                                        @if($ing['unit'] === 'Trái' || $ing['unit'] === 'Quả')
                                                            {{ number_format($ing['quantity'], 0) }} {{ $ing['unit'] }}
                                                        @else
                                                            {{ number_format($ing['quantity'] / 1000, 2, ',', '.') }} kg
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 12px; font-style: italic;">
                                                        Món tự nhập – chưa khai báo nguyên liệu trong ngân hàng
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif
</x-filament-panels::page>
