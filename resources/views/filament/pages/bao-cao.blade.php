<div class="emp-page">
    
<style>
    :root {
        --po-bl: #1267E8;
        --po-bl-d: #0C50BB;
        --po-bl-s: #EBF3FF;
        --po-bl-m: #BFDBFE;
        --po-gn: #059669;
        --po-gn-s: #ECFDF5;
        --po-gn-t: #065F46;
        --po-or: #EA580C;
        --po-or-s: #FFF7ED;
        --po-or-t: #9A3412;
        --po-rd: #DC2626;
        --po-rd-s: #FEF2F2;
        --po-rd-t: #991B1B;
        --po-pu: #7C3AED;
        --po-pu-s: #F5F3FF;
        --po-bg: #F4F7FB;
        --po-bd: #E2E8F0;
        --po-bd2: #F1F5F9;
        --po-tx: #0F172A;
        --po-su: #334155;
        --po-mu: #64748B;
        --po-fa: #94A3B8;
        --po-wh: #fff;
        --po-sh: 0 1px 3px rgba(15,23,42,.05), 0 4px 16px rgba(15,23,42,.05);
        --po-sh2: 0 1px 2px rgba(15,23,42,.04);
        --po-r: 12px;
    }

    :root.dark {
        --po-bl-s: rgba(18, 103, 232, .18);
        --po-gn-s: rgba(5, 150, 105, .18);
        --po-or-s: rgba(234, 88, 12, .18);
        --po-pu-s: rgba(124, 58, 237, .18);
        --po-rd-s: rgba(220, 38, 38, .18);
        --po-gn-t: #34D399;
        --po-rd-t: #F87171;
        --po-bg: #0b1120;
        --po-wh: #1e293b;
        --po-tx: #f1f5f9;
        --po-su: #cbd5e1;
        --po-mu: #94a3b8;
        --po-fa: #64748b;
        --po-bd: #334155;
        --po-bd2: #263449;
        --po-sh: 0 1px 2px rgba(0, 0, 0, .4);
    }

    .emp-page {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: var(--po-tx);
    }

    .report-header-container {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 14px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .report-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--po-tx);
        letter-spacing: -.02em;
    }

    .report-subtitle {
        font-size: 13px;
        color: var(--po-mu);
        margin-top: 2px;
    }

    .excel-btn {
        height: 38px;
        padding: 0 16px;
        border: 1px solid var(--po-bd);
        border-radius: 8px;
        background: var(--po-wh);
        cursor: pointer;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--po-su);
        transition: .13s;
    }

    .excel-btn:hover {
        background: var(--po-bg);
        border-color: var(--po-bd);
    }

    /* Filter bar */
    .filter-bar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: 14px;
        padding: 12px 16px;
        box-shadow: var(--po-sh2);
    }

    .filter-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--po-mu);
    }

    .date-input {
        height: 36px;
        border: 1.5px solid var(--po-bl-m);
        border-radius: 8px;
        padding: 0 12px;
        font-size: 13px;
        font-weight: 700;
        background: var(--po-bl-s);
        color: var(--po-bl);
        cursor: pointer;
        outline: none;
        transition: .13s;
    }

    .date-input:focus {
        border-color: var(--po-bl);
    }

    .week-btn {
        height: 36px;
        padding: 0 14px;
        background: var(--po-bl-s);
        color: var(--po-bl);
        border: 1.5px solid var(--po-bl-m);
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        transition: .13s;
        flex-shrink: 0;
    }

    .week-btn:hover {
        background: var(--po-bl-d);
        color: #ffffff;
        border-color: var(--po-bl-d);
    }

    .shifts-group {
        display: flex;
        align-items: center;
        gap: 5px;
        background: var(--po-bd2);
        border: 1px solid var(--po-bd);
        border-radius: 8px;
        padding: 4px 10px;
        font-size: 12px;
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

    .shift-ca1 { background: var(--po-bl-s); color: var(--po-bl); border-color: var(--po-bl-m); }
    .shift-ca2 { background: var(--po-gn-s); color: var(--po-gn); border-color: var(--po-gn); }
    .shift-ca3 { background: var(--po-or-s); color: var(--po-or); border-color: var(--po-or); }
    .shift-ca4 { background: var(--po-pu-s); color: var(--po-pu); border-color: var(--po-pu); }

    .search-container {
        position: relative;
        max-width: 280px;
        min-width: 220px;
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid var(--po-bd);
        border-radius: 8px;
        background: var(--po-wh);
        padding: 0 12px;
        height: 36px;
        flex: 1;
        transition: .13s;
    }

    .search-container:focus-within {
        border-color: var(--po-bl-m);
    }

    .search-container input {
        border: none;
        outline: none;
        background: transparent;
        font-size: 13px;
        width: 100%;
        color: var(--po-tx);
        box-shadow: none !important;
        padding: 0;
    }

    .search-icon {
        color: var(--po-fa);
        font-size: 12px;
        flex-shrink: 0;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-top: 14px;
        margin-bottom: 14px;
    }

    .stat-card {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        padding: 14px 16px;
        box-shadow: var(--po-sh2);
        transition: .13s;
    }

    .stat-card:hover {
        border-color: var(--po-bl-m);
    }

    .stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .stat-val {
        font-size: 22px;
        font-weight: 800;
        color: var(--po-tx);
        line-height: 1.1;
    }

    .stat-lbl {
        font-size: 11px;
        color: var(--po-mu);
        margin-top: 2px;
    }

    /* Day block */
    .day-card {
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        overflow: hidden;
        margin-bottom: 14px;
        box-shadow: var(--po-sh2);
    }

    .day-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: linear-gradient(135deg, var(--po-bl-s), var(--po-bl-m));
        border-bottom: 1px solid var(--po-bl-m);
    }

    .day-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--po-bl);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .day-badge {
        font-size: 11px;
        background: var(--po-bl);
        color: #ffffff;
        border-radius: 6px;
        padding: 2px 8px;
        font-weight: 700;
        margin-left: 8px;
    }

    /* Ca sections */
    .shift-section {
        padding: 14px 16px;
        border-bottom: 1px solid var(--po-bd2);
    }

    .shift-section:last-child {
        border-bottom: none;
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
        color: var(--po-mu);
    }

    /* Dish block */
    .dish-card {
        border: 1px solid var(--po-bd);
        border-radius: 10px;
        margin-bottom: 10px;
        overflow: hidden;
        background: var(--po-wh);
    }

    .dish-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9px 13px;
        background: var(--po-bd2);
        flex-wrap: wrap;
        gap: 8px;
    }

    .dish-title-block {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dish-name {
        font-weight: 700;
        color: var(--po-tx);
        font-size: 13.5px;
    }

    .dish-type-badge {
        font-size: 11px;
        color: var(--po-mu);
        background: var(--po-bd2);
        border-radius: 6px;
        padding: 2px 8px;
        font-weight: 600;
    }

    .dish-portions-block {
        display: flex;
        gap: 6px;
    }

    .dish-portions-badge {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--po-bl);
        background: var(--po-bl-s);
        border-radius: 6px;
        padding: 2px 9px;
    }

    .dish-portions-phan {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--po-gn);
        background: var(--po-gn-s);
        border-radius: 6px;
        padding: 2px 9px;
    }

    /* Ingredients table */
    .ing-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
    }

    .ing-table th {
        padding: 8px 12px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: var(--po-mu);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: #f8fafc;
        border-bottom: 1px solid var(--po-bd);
        white-space: nowrap;
    }

    .ing-table td {
        padding: 9px 12px;
        border-bottom: 1px solid var(--po-bd2);
        color: var(--po-su);
        vertical-align: middle;
    }

    .ing-table tr:last-child td {
        border-bottom: none;
    }

    .ing-table tbody tr:hover {
        background: var(--po-bd2);
    }

    .ing-num {
        width: 36px;
        text-align: center;
        color: var(--po-mu);
        font-weight: 600;
    }

    .ing-code-badge {
        font-size: 12px;
        font-weight: 600;
        color: var(--po-mu);
    }

    .ing-name {
        font-weight: 500;
        color: var(--po-tx);
    }

    .ing-kg-val {
        font-weight: 700;
        color: var(--po-tx);
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        text-align: center;
        color: var(--po-fa);
        background: #fff;
        border: 1px solid var(--po-bd);
        border-radius: 14px;
    }

    .empty-state h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--po-su);
        margin-top: 14px;
        margin-bottom: 6px;
    }

    .empty-state p {
        font-size: 13px;
        color: var(--po-mu);
    }
</style>

    <div class="report-header-container">
        <div>
            <h1 class="report-title">Báo cáo – Xuất ăn</h1>
            <p class="report-subtitle">Tổng hợp món ăn & nguyên liệu theo khoảng ngày và ca phục vụ</p>
        </div>
        <div>
            <button type="button" class="excel-btn" wire:click="exportExcel">
                <i class="fa-solid fa-file-excel" style="color:#059669; font-size:15px"></i>
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
            <span class="font-semibold text-gray-500 mr-1" style="font-size: 11px;">Ca:</span>
            
            @php $allShifts = $this->getAllShifts(); @endphp
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
                           style="width: 12px; height: 12px; border-radius: 3px; border-color: currentColor; cursor:pointer">
                    <span>{{ strtoupper($shift->name) }}</span>
                </label>
            @endforeach
        </div>

        <div class="search-container">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm món / nguyên liệu...">
        </div>
    </div>

    <!-- Stats Cards -->
    @php $stats = $this->getStats(); @endphp
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #eff6ff; color: #1e40af;"><i class="fa-regular fa-calendar"></i></div>
            <div>
                <div class="stat-val">{{ $stats['days'] }}</div>
                <div class="stat-lbl">Ngày có thực đơn</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff7ed; color: #ea580c;"><i class="fa-solid fa-utensils"></i></div>
            <div>
                <div class="stat-val">{{ $stats['dishes'] }}</div>
                <div class="stat-lbl">Lượt món</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #f5f3ff; color: #7c3aed;"><i class="fa-solid fa-seedling"></i></div>
            <div>
                <div class="stat-val">{{ $stats['ingredients'] }}</div>
                <div class="stat-lbl">Dòng nguyên liệu</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #ecfdf5; color: #059669;"><i class="fa-solid fa-users"></i></div>
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
            <i class="fa-solid fa-chart-column" style="font-size:32px; opacity:.3; margin-bottom:8px"></i>
            <h3>Không có dữ liệu trong khoảng đã chọn</h3>
            <p>Hãy chọn lại khoảng ngày, ca, hoặc thử tìm kiếm cụm từ khác.</p>
        </div>
    @else
        @foreach($groupedData as $dayIndex => $day)
            <div class="day-card" x-data="{ open: {{ $dayIndex === 0 ? 'true' : 'false' }} }">
                <div class="day-header" @click="open = !open" style="cursor:pointer; user-select:none">
                    <div class="day-title">
                        <i class="fa-regular fa-calendar-days"></i>
                        <span>{{ strtoupper($day['day_of_week']) }} – {{ $day['date_formatted'] }}</span>
                        <span class="day-badge">{{ count($day['shifts']) }} ca</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px">
                        <i class="fa-solid fa-chevron-down" x-show="!open" style="font-size:12px; color:#1e40af"></i>
                        <i class="fa-solid fa-chevron-up" x-show="open" style="font-size:12px; color:#1e40af"></i>
                    </div>
                </div>

                <div x-show="open" x-collapse>

                @foreach($day['shifts'] as $index => $shift)
                    @php 
                        $badgeColors = [
                            ['bg' => 'var(--po-bl-s)', 'text' => 'var(--po-bl)', 'border' => 'var(--po-bl-m)'],
                            ['bg' => 'var(--po-gn-s)', 'text' => 'var(--po-gn)', 'border' => 'var(--po-gn)'],
                            ['bg' => 'var(--po-or-s)', 'text' => 'var(--po-or)', 'border' => 'var(--po-or)'],
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
                            <div class="dish-card" x-data="{ open: false }">
                                <div class="dish-head" @click="open = !open" style="cursor:pointer; user-select:none">
                                    <div class="dish-title-block">
                                        <i class="fa-solid fa-bowl-food" style="color:var(--po-bl)"></i>
                                        <span class="dish-name">{{ $dish['name'] }}</span>
                                        <span class="dish-type-badge">{{ $dish['type'] }}</span>
                                    </div>
                                    <div style="display:flex; align-items:center; gap:8px">
                                        <div class="dish-portions-block">
                                            <span class="dish-portions-badge">{{ $dish['suat'] }} suất</span>
                                            <span class="dish-portions-phan">{{ $dish['phan'] }} phần</span>
                                        </div>
                                        <i class="fa-solid fa-chevron-down" x-show="!open" style="font-size:11px; color:var(--po-mu)"></i>
                                        <i class="fa-solid fa-chevron-up" x-show="open" style="font-size:11px; color:var(--po-mu)"></i>
                                    </div>
                                </div>

                                <div x-show="open" x-collapse class="overflow-x-auto" style="border-top:1px solid var(--po-bd2)">
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
                                                    <td style="text-align: center;">{{ number_format($ing['dl_g'] * 1000, 0) }}</td>
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
            </div>
        @endforeach
    @endif
</div>
