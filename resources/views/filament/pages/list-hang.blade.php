@push('styles')
<style>
    :root {
        --bl: #1267E8;
        --bl-d: #0C50BB;
        --bl-s: #EBF3FF;
        --bl-m: #BFDBFE;
        --gn: #059669;
        --gn-s: #ECFDF5;
        --gn-t: #065F46;
        --or: #EA580C;
        --or-s: #FFF7ED;
        --or-t: #9A3412;
        --pu: #7C3AED;
        --pu-s: #F5F3FF;
        --rd: #DC2626;
        --rd-s: #FEF2F2;
        --rd-t: #991B1B;
        --am: #D97706;
        --am-s: #FFFBEB;
        --sk: #0284C7;
        --sk-s: #F0F9FF;
        --bd: #E2E8F0;
        --bd2: #F1F5F9;
        --tx: #0F172A;
        --mu: #64748B;
        --fa: #94A3B8;
        --wh: #FFFFFF;
        --r: 12px;
        --sh2: 0 2px 4px rgba(15,23,42,.02);
    }

    :root.dark {
        --bl-s: rgba(18, 103, 232, .18);
        --gn-s: rgba(5, 150, 105, .18);
        --or-s: rgba(234, 88, 12, .18);
        --pu-s: rgba(124, 58, 237, .18);
        --rd-s: rgba(220, 38, 38, .18);
        --gn-t: #34D399;
        --rd-t: #F87171;
        --bg: #0b1120;
        --wh: #1e293b;
        --tx: #f1f5f9;
        --su: #cbd5e1;
        --mu: #94a3b8;
        --fa: #64748b;
        --bd: #334155;
        --bd2: #263449;
        --sh: 0 1px 2px rgba(0, 0, 0, .4);
    }

    /* ══ LIST HÀNG NGÀY ══ */
    .lhn-root {
        flex: 1;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--bd) transparent;
    }

    /* date picker bar */
    .lhn-datebar {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: var(--r);
        padding: 13px 18px;
        box-shadow: var(--sh2);
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .lhn-date-inp {
        height: 40px;
        border: 1.5px solid var(--bl-m);
        border-radius: 9px;
        padding: 0 12px;
        font-size: 14px;
        font-weight: 700;
        color: var(--bl);
        outline: none;
        background: var(--bl-s);
        cursor: pointer;
        transition: .13s;
    }
    .dark .lhn-date-inp {
        background: #1e293b;
        color: #60a5fa;
        border-color: #3b82f6;
    }
    .dark input[type="date"] {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #ffffff !important;
    }
    .lhn-date-inp:focus {
        box-shadow: 0 0 0 3px rgba(18,103,232,.1);
    }
    .lhn-nav-btn {
        width: 34px;
        height: 34px;
        border: 1px solid var(--bd);
        border-radius: 8px;
        background: var(--wh);
        cursor: pointer;
        display: grid;
        place-items: center;
        font-size: 13px;
        color: var(--mu);
        transition: .13s;
        flex-shrink: 0;
    }
    .dark .lhn-nav-btn {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
    }
    .lhn-nav-btn:hover {
        background: var(--bl-s);
        color: var(--bl);
        border-color: var(--bl-m);
    }
    .lhn-today-btn {
        height: 34px;
        padding: 0 14px;
        border: 1px solid var(--bl);
        border-radius: 8px;
        background: var(--bl-s);
        color: var(--bl);
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        transition: .13s;
        font-family: inherit;
        flex-shrink: 0;
    }
    .dark .lhn-today-btn {
        background: #1e3a8a;
        color: #3b82f6;
        border-color: #3b82f6;
    }
    .lhn-today-btn:hover {
        background: var(--bl);
        color: #fff;
    }

    /* summary chips */
    .lhn-chips {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .lhn-chip {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: 10px;
        padding: 10px 14px;
        box-shadow: var(--sh2);
        flex: 1;
        min-width: 160px;
        transition: .13s;
    }
    .dark .lhn-chip {
        background: #0f172a;
        border-color: #1e293b;
    }
    .lhn-chip:hover {
        border-color: var(--bl-m);
        transform: translateY(-1px);
    }
    .lhn-chip-ico {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    .lhn-chip-val {
        font-size: 18px;
        font-weight: 800;
        color: var(--tx);
        line-height: 1;
    }
    .dark .lhn-chip-val {
        color: #fff;
    }
    .lhn-chip-lbl {
        font-size: 11px;
        color: var(--mu);
        margin-top: 2px;
    }

    /* ca section */
    .lhn-ca-card {
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: var(--r);
        box-shadow: var(--sh2);
        overflow: hidden;
        margin-bottom: 14px;
    }
    .dark .lhn-ca-card {
        background: #0f172a;
        border-color: #1e293b;
    }
    .lhn-ca-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid var(--bd);
        transition: .12s;
        user-select: none;
    }
    .dark .lhn-ca-head {
        border-color: #1e293b;
    }
    .lhn-ca-head:hover {
        background: #FAFBFC;
    }
    .dark .lhn-ca-head:hover {
        background: rgba(30, 41, 59, 0.4);
    }
    .lhn-ca-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .lhn-ca-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 800;
    }
    .lhn-ca-meta {
        font-size: 12.5px;
        color: var(--mu);
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .lhn-ca-body {
        padding: 14px 16px;
    }

    /* mon row */
    .lhn-mon-row {
        border: 1px solid var(--bd);
        border-radius: 10px;
        margin-bottom: 10px;
        overflow: hidden;
    }
    .dark .lhn-mon-row {
        border-color: #1e293b;
    }
    .lhn-mon-row:last-child {
        margin-bottom: 0;
    }
    .lhn-mon-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background: #F8FAFC;
        cursor: pointer;
        transition: .12s;
        border-bottom: 1px solid transparent;
    }
    .dark .lhn-mon-head {
        background: rgba(30, 41, 59, 0.3);
    }
    .lhn-mon-head.open {
        border-bottom-color: var(--bd);
        background: var(--bl-s);
    }
    .dark .lhn-mon-head.open {
        border-bottom-color: #1e293b;
        background: rgba(30, 58, 138, 0.3);
    }
    .lhn-mon-head:hover {
        background: var(--bl-s);
    }
    .dark .lhn-mon-head:hover {
        background: rgba(30, 58, 138, 0.2);
    }
    .lhn-mon-left {
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .lhn-mon-ico {
        font-size: 18px;
        flex-shrink: 0;
    }
    .lhn-mon-name {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--tx);
    }
    .dark .lhn-mon-name {
        color: #fff;
    }
    .lhn-mon-type {
        font-size: 11.5px;
        color: var(--mu);
        background: #F1F5F9;
        border-radius: 6px;
        padding: 2px 8px;
        margin-left: 2px;
    }
    .dark .lhn-mon-type {
        background: #1e293b;
        color: #94a3b8;
    }
    .lhn-mon-right {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 12.5px;
        color: var(--mu);
    }
    .lhn-mon-suat {
        font-weight: 700;
        color: var(--bl);
        background: var(--bl-s);
        border-radius: 6px;
        padding: 2px 9px;
    }
    .dark .lhn-mon-suat {
        background: rgba(30, 58, 138, 0.3);
        color: #60a5fa;
    }
    .lhn-expand-ico {
        font-size: 11px;
        color: var(--mu);
        transition: transform .2s;
    }

    /* ingredient table */
    .lhn-ing-table {
        width: 100%;
        border-collapse: collapse;
    }
    .lhn-ing-table th {
        padding: 8px 12px;
        text-align: left;
        font-size: 10.5px;
        font-weight: 700;
        color: var(--fa);
        text-transform: uppercase;
        letter-spacing: .07em;
        background: #F8FAFC;
        border-bottom: 1px solid var(--bd);
        white-space: nowrap;
    }
    .dark .lhn-ing-table th {
        background: rgba(30, 41, 59, 0.5);
        border-color: #1e293b;
        color: #94a3b8;
    }
    .lhn-ing-table td {
        padding: 9px 12px;
        font-size: 12.5px;
        border-bottom: 1px solid var(--bd2);
        vertical-align: middle;
        color: var(--tx);
    }
    .dark .lhn-ing-table td {
        border-color: #1e293b;
        color: #cbd5e1;
    }
    .lhn-ing-table tr:last-child td {
        border-bottom: none;
    }
    .lhn-ing-table tbody tr:hover {
        background: #FAFCFF;
    }
    .dark .lhn-ing-table tbody tr:hover {
        background: rgba(30, 41, 59, 0.1);
    }
    .lhn-ing-num {
        font-weight: 600;
        color: var(--mu);
        text-align: center;
        width: 36px;
    }
    .lhn-kg {
        font-weight: 700;
        color: var(--tx);
    }
    .dark .lhn-kg {
        color: #fff;
    }
    .lhn-total-row td {
        background: linear-gradient(135deg, #EFF6FF, #DBEAFE);
        font-weight: 800;
        font-size: 13px;
    }
    .dark .lhn-total-row td {
        background: linear-gradient(135deg, rgba(30, 58, 138, 0.3), rgba(30, 58, 138, 0.1));
    }

    /* grand total bar */
    .lhn-grand {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(135deg, #1474FF, #0059DD);
        border-radius: var(--r);
        padding: 16px 22px;
        color: #fff;
        box-shadow: 0 6px 20px rgba(18,103,232,.3);
    }
    .lhn-grand-lbl {
        font-size: 14px;
        font-weight: 700;
        opacity: .9;
    }
    .lhn-grand-val {
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -.02em;
    }

    /* step wizard */
    .oh-steps {
        display: flex;
        align-items: center;
        gap: 0;
        margin-bottom: 20px;
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: var(--r);
        padding: 14px 20px;
        box-shadow: var(--sh2);
    }
    .dark .oh-steps {
        background: #0f172a;
        border-color: #1e293b;
    }
    .oh-step {
        display: flex;
        align-items: center;
        gap: 9px;
        flex: 1;
    }
    .oh-step-num {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-size: 12px;
        font-weight: 800;
        flex-shrink: 0;
        transition: .2s;
    }
    .oh-step-active .oh-step-num {
        background: var(--bl);
        color: #fff;
        box-shadow: 0 3px 8px rgba(18,103,232,.3);
    }
    .oh-step-done .oh-step-num {
        background: var(--gn);
        color: #fff;
    }
    .oh-step-pending .oh-step-num {
        background: #F1F5F9;
        color: var(--mu);
        border: 1.5px solid var(--bd);
    }
    .dark .oh-step-pending .oh-step-num {
        background: #1e293b;
        color: #64748b;
        border-color: #334155;
    }
    .oh-step-lbl {
        font-size: 12.5px;
        font-weight: 700;
    }
    .oh-step-active .oh-step-lbl { color: var(--bl); }
    .oh-step-done .oh-step-lbl { color: var(--gn); }
    .oh-step-pending .oh-step-lbl { color: var(--fa); }
    .oh-step-line {
        flex: 1;
        height: 2px;
        background: var(--bd);
        margin: 0 8px;
        transition: .2s;
    }
    .dark .oh-step-line {
        background: #1e293b;
    }
    .oh-step-line.done {
        background: var(--gn);
    }

    /* NL group cards */
    .oh-group-card {
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: var(--r);
        box-shadow: var(--sh2);
        overflow: hidden;
        margin-bottom: 14px;
    }
    .dark .oh-group-card {
        background: #0f172a;
        border-color: #1e293b;
    }
    .oh-group-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-bottom: 1px solid var(--bd2);
    }
    .dark .oh-group-head {
        border-color: #1e293b;
    }
    .oh-group-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--tx);
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .dark .oh-group-title {
        color: #fff;
    }
    .oh-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .oh-table {
        width: 100%;
        min-width: 72rem;
        border-collapse: collapse;
    }
    .oh-table th {
        padding: 9px 12px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: var(--fa);
        text-transform: uppercase;
        letter-spacing: .06em;
        background: #F8FAFC;
        border-bottom: 1px solid var(--bd);
        white-space: nowrap;
    }
    .dark .oh-table th {
        background: rgba(30, 41, 59, 0.4);
        border-color: #1e293b;
        color: #94a3b8;
    }
    .oh-table td {
        padding: 10px 12px;
        font-size: 12.5px;
        border-bottom: 1px solid var(--bd2);
        vertical-align: middle;
        color: var(--tx);
    }
    .dark .oh-table td {
        border-color: #1e293b;
        color: #cbd5e1;
    }
    .oh-table tbody tr:hover {
        background: #FAFCFF;
    }
    .dark .oh-table tbody tr:hover {
        background: rgba(30, 41, 59, 0.1);
    }
    .oh-ncc-sel {
        height: 32px;
        padding: 0 24px 0 9px;
        background: var(--wh);
        border: 1.5px solid var(--bd);
        border-radius: 7px;
        font-size: 12px;
        color: var(--tx);
        cursor: pointer;
        outline: none;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath d='M2 3.5l3 3 3-3' stroke='%2394A3B8' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 7px center !important;
        transition: .13s;
        min-width: 130px;
    }
    .dark .oh-ncc-sel {
        background: #1e293b;
        border-color: #334155;
        color: #fff;
    }

    /* summary sidebar */
    .oh-sum {
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: var(--r);
        padding: 16px;
        box-shadow: var(--sh2);
        margin-bottom: 12px;
    }
    .dark .oh-sum {
        background: #0f172a;
        border-color: #1e293b;
    }
    .oh-sum-ttl {
        font-size: 13px;
        font-weight: 700;
        color: var(--tx);
        margin-bottom: 10px;
        padding-bottom: 9px;
        border-bottom: 1px solid var(--bd2);
    }
    .dark .oh-sum-ttl {
        color: #fff;
        border-color: #1e293b;
    }
    .oh-sum-row {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        border-bottom: 1px solid var(--bd2);
        font-size: 12px;
    }
    .dark .oh-sum-row {
        border-color: #1e293b;
    }
    .oh-sum-row:last-child {
        border-bottom: none;
    }
    .oh-sum-k { color: var(--mu); }
    .oh-sum-v { font-weight: 600; color: var(--tx); }
    .dark .oh-sum-v { color: #fff; }
    .oh-ncc-chip {
        display: flex;
        align-items: center;
        gap: 7px;
        background: #F8FAFC;
        border: 1px solid var(--bd);
        border-radius: 9px;
        padding: 8px 10px;
        margin-bottom: 7px;
    }
    .dark .oh-ncc-chip {
        background: rgba(30, 41, 59, 0.3);
        border-color: #1e293b;
    }
    .oh-ncc-chip-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .oh-ncc-chip-name {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--tx);
    }
    .dark .oh-ncc-chip-name { color: #fff; }
    .oh-ncc-chip-cnt { font-size: 11.5px; color: var(--mu); }
    .oh-ncc-chip-val {
        margin-left: auto;
        font-size: 12.5px;
        font-weight: 700;
        color: var(--bl);
    }
    
    .loai-badge {
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }
    .loai-thit { background: #FEF2F2; color: #DC2626; }
    .loai-uot { background: #EFF6FF; color: #1e40af; }
    .loai-kho { background: #FEF3C7; color: #78350F; }
</style>
@endpush

<x-filament-panels::page>
    @if($mode === 'list')
        {{-- In nhanh: chỉ hiện vùng danh sách (#lh-print-area), ẩn sidebar/khung Filament --}}
        <style>
            @media print {
                body * { visibility: hidden !important; }
                #lh-print-area, #lh-print-area * { visibility: visible !important; }
                #lh-print-area { position: absolute; top: 0; left: 0; width: 100%; background: #fff; padding: 8mm; }
                #lh-print-area .lhn-datebar, #lh-print-area button { display: none !important; }
            }
        </style>
        <!-- LIST HÀNG VIEW (MẪU ẢNH 1 & 2) -->
        <div class="lhn-root" id="lh-print-area">
            <!-- Header bar -->
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
                <div>
                    <h1 style="font-size:20px;font-weight:800;margin:0 0 4px" class="dark:text-white">List hàng — {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h1>
                    <p style="font-size:12.5px;color:#64748B;margin:0">Danh sách nguyên liệu cần chuẩn bị theo ngày và ca</p>
                </div>
                <div style="display:flex;gap:8px">
                    <button type="button" wire:click="exportList" class="wh-action-btn" style="height:36px;">
                        <i class="fa-solid fa-file-excel" style="color:#059669"></i>Xuất Excel
                    </button>
                    <button type="button" onclick="window.print()" class="wh-action-btn" style="height:36px;">
                        <i class="fa-solid fa-print"></i>In danh sách
                    </button>
                    <button type="button" wire:click="goOrderCreate" class="wh-action-btn wh-action-btn-primary" style="height:36px;">
                        <i class="fa-solid fa-cart-plus"></i>Tạo đơn đặt hàng
                    </button>
                </div>
            </div>

            <!-- Date and shift filter bar -->
            <div class="lhn-datebar">
                <button type="button" class="lhn-nav-btn" wire:click="changeDay(-1)">&#8249;</button>
                <input type="date" class="lhn-date-inp" wire:model.live="date">
                <button type="button" class="lhn-nav-btn" wire:click="changeDay(1)">&#8250;</button>
                <button type="button" class="lhn-today-btn" wire:click="goToday">Hôm nay</button>

                <!-- Week view sync -->
                <div style="display:flex;align-items:center;gap:6px;font-size:12.5px;color:var(--mu);padding:0 4px">
                    <span style="font-weight:600">Tuần:</span>
                    <input type="date" wire:model.live="weekFrom" style="border:1px solid var(--bd);border-radius:6px;padding:3px 8px;font-size:12px;outline:none" class="dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                    <span>–</span>
                    <input type="date" wire:model.live="weekTo" style="border:1px solid var(--bd);border-radius:6px;padding:3px 8px;font-size:12px;outline:none" class="dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                </div>

                <!-- Shift checkboxes styled as badges -->
                <div style="display:flex;align-items:center;gap:5px;font-size:12px;background:var(--bg);border:1px solid var(--bd);border-radius:8px;padding:4px 10px;" class="dark:bg-gray-800 dark:border-gray-700">
                    <span style="font-weight:600;color:var(--mu)">Ca:</span>
                    @foreach($this->getShiftsList() as $index => $sh)
                        @php 
                            $bg = ['#EFF6FF', '#F0FDF4', '#FEF3C7', '#F5F3FF'][$index % 4];
                            $color = ['#1e40af', '#065F46', '#78350F', '#4C1D95'][$index % 4];
                            $border = ['#BFDBFE', '#A7F3D0', '#FDE68A', '#DDD6FE'][$index % 4];
                            $isChecked = in_array($sh->id, $selectedShifts);
                        @endphp
                        <label style="display:flex;align-items:center;gap:4px;background:{{ $bg }};color:{{ $color }};border:1px solid {{ $border }};border-radius:20px;padding:2px 8px;font-size:11.5px;font-weight:600;cursor:pointer; @if(!$isChecked) opacity: 0.45; @endif">
                            <input type="checkbox" value="{{ $sh->id }}" wire:model.live="selectedShifts" style="width:12px;height:12px; border-radius:3px;"> {{ $sh->name }}
                        </label>
                    @endforeach
                </div>

                <div style="margin-left:auto;display:flex;align-items:center;gap:8px">
                    <span style="font-size:13.5px;font-weight:700;color:var(--tx)" class="dark:text-white">
                        {{ strtoupper(\Carbon\Carbon::parse($date)->locale('vi')->dayName) }} – {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-150 text-green-700 border border-green-200">Trong kỳ</span>
                </div>
            </div>

            <!-- Stats Row -->
            @php $stats = $this->getStats(); @endphp
            <div class="lhn-chips">
                <div class="lhn-chip">
                    <div class="lhn-chip-ico" style="background:#FEF2F2; color:#DC2626;"><i class="fa-solid fa-calendar-day"></i></div>
                    <div><div class="lhn-chip-val">{{ $stats['shifts'] }}</div><div class="lhn-chip-lbl">Ca phục vụ</div></div>
                </div>
                <div class="lhn-chip">
                    <div class="lhn-chip-ico" style="background:#EBF3FF; color:#1267E8;"><i class="fa-solid fa-users"></i></div>
                    <div><div class="lhn-chip-val">{{ number_format($stats['portions']) }}</div><div class="lhn-chip-lbl">Tổng suất ăn</div></div>
                </div>
                <div class="lhn-chip">
                    <div class="lhn-chip-ico" style="background:#FFF7ED; color:#EA580C;"><i class="fa-solid fa-bowl-food"></i></div>
                    <div><div class="lhn-chip-val">{{ $stats['dishes'] }}</div><div class="lhn-chip-lbl">Món cần nấu</div></div>
                </div>
                <div class="lhn-chip">
                    <div class="lhn-chip-ico" style="background:#ECFDF5; color:#059669;"><i class="fa-solid fa-leaf"></i></div>
                    <div><div class="lhn-chip-val">{{ $stats['ingredients'] }}</div><div class="lhn-chip-lbl">Loại nguyên liệu</div></div>
                </div>
            </div>

            <!-- Main Grouped List per Shift -->
            @php $groupedData = $this->getGroupedData(); @endphp
            @forelse($groupedData as $shiftData)
                <div class="lhn-ca-card" x-data="{ open: true }">
                    <div class="lhn-ca-head" @click="open = !open">
                        <div class="lhn-ca-title">
                            <span class="lhn-ca-badge bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200">{{ $shiftData['name'] }}</span>
                            <span class="text-xs text-gray-500 font-semibold">({{ $shiftData['time_range'] }})</span>
                        </div>
                        <div class="lhn-ca-meta">
                            <span class="font-bold text-gray-900 dark:text-white">{{ number_format($shiftData['total_portions']) }} suất</span>
                            <span class="text-gray-400">·</span>
                            <span class="text-gray-600 dark:text-gray-300">{{ $shiftData['total_dishes'] }} món</span>
                            <i class="fa-solid fa-chevron-down text-gray-400 transition" :class="open ? 'transform rotate-180' : ''"></i>
                        </div>
                    </div>

                    <div class="lhn-ca-body" x-show="open" x-collapse>
                        @foreach($shiftData['dishes'] as $dish)
                            <div class="lhn-mon-row" x-data="{ expanded: false }">
                                <div class="lhn-mon-head" :class="expanded ? 'open' : ''" @click="expanded = !expanded">
                                    <div class="lhn-mon-left">
                                        <span class="lhn-mon-ico">🍲</span>
                                        <span class="lhn-mon-name">{{ $dish['name'] }}</span>
                                        <span class="lhn-mon-type">{{ $dish['type'] }}</span>
                                    </div>
                                    <div class="lhn-mon-right">
                                        <span class="lhn-mon-suat">{{ number_format($dish['portions']) }} suất</span>
                                        <span class="text-gray-400">·</span>
                                        <span>{{ count($dish['ingredients']) }} NL</span>
                                        <i class="fa-solid fa-chevron-down lhn-expand-ico transition" :class="expanded ? 'transform rotate-180' : ''"></i>
                                    </div>
                                </div>

                                <!-- Expanded ingredients details (Ảnh 2) -->
                                <div x-show="expanded" x-collapse class="border-t border-gray-150 bg-gray-50/10 dark:bg-gray-900/50">
                                    <table class="lhn-ing-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px; text-align:center;">#</th>
                                                <th>Nguyên liệu</th>
                                                <th style="text-align: right; width: 140px;">Số suất</th>
                                                <th style="text-align: right; width: 140px;">ĐL (g)</th>
                                                <th style="text-align: right; width: 180px;">Số KG</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dish['ingredients'] as $iIdx => $ing)
                                                <tr>
                                                    <td style="text-align: center; color: #94a3b8;">{{ $iIdx + 1 }}</td>
                                                    <td style="font-weight: 700;" class="dark:text-white">
                                                        <div>{{ $ing['name'] }}</div>
                                                        <div style="font-size:10px; color:#94a3b8; font-weight:500;">{{ $ing['code'] }}</div>
                                                    </td>
                                                    <td style="text-align: right;">{{ number_format($dish['portions']) }}</td>
                                                    <td style="text-align: right; color:#64748b;">{{ number_format($ing['quantity_per_portion'] * 1000, 0, ',', '.') }} g</td>
                                                    <td style="text-align: right; font-weight: 750;" class="lhn-kg">{{ number_format($ing['quantity'], 3, ',', '.') }} kg</td>
                                                </tr>
                                            @endforeach
                                            <tr class="lhn-total-row">
                                                <td colspan="4" style="text-align: right; padding: 10px 12px; font-weight: 800; color:#1e40af;">Tổng {{ $dish['name'] }}:</td>
                                                <td style="text-align: right; padding: 10px 12px; font-weight: 850; color:#1e40af;">{{ number_format($dish['portions']) }} suất</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="lhn-empty">
                    <i class="fa-solid fa-clipboard-question"></i>
                    <h3>Chưa lập thực đơn</h3>
                    <p>Không tìm thấy thực đơn nào được lập cho ngày và ca đã chọn.</p>
                </div>
            @endforelse
        </div>
    @elseif($mode === 'create_po')
        <!-- TẠO ĐƠN ĐẶT HÀNG WIZARD (MẪU ẢNH 3) -->
        <div style="display: flex; gap: 1rem; overflow: hidden; height: calc(100vh - 160px);">
            <!-- Left Workspace -->
            <div style="flex: 1; overflow-y: auto; padding-right: 8px;">
                <!-- Header -->
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h1 style="font-size:20px; font-weight:800; margin:0;" class="dark:text-white">Tạo đơn đặt hàng</h1>
                        <p style="font-size: 12px; color: #64748b; margin-top:2px;">Tổng hợp nguyên liệu từ list hàng &rarr; phân NCC &rarr; tạo đơn</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="wh-action-btn" wire:click="goBackToList">
                            <i class="fa-solid fa-arrow-left"></i>Quay lại
                        </button>
                        <button type="button" class="wh-action-btn wh-action-btn-primary" wire:click="createOrders">
                            <i class="fa-solid fa-paper-plane"></i>Tạo & gửi đơn
                        </button>
                    </div>
                </div>

                <!-- Step progress wizard -->
                <div class="oh-steps">
                    <div class="oh-step oh-step-done">
                        <div class="oh-step-num"><i class="fa-solid fa-check" style="font-size:11px"></i></div>
                        <div>
                            <div class="oh-step-lbl">Chọn List hàng</div>
                            <div style="font-size:11px; color:#64748b;">
                                {{ \Carbon\Carbon::parse($poSourceFrom)->format('d/m') }} - {{ \Carbon\Carbon::parse($poSourceTo)->format('d/m') }}
                            </div>
                        </div>
                    </div>
                    <div class="oh-step-line done"></div>
                    <div class="oh-step oh-step-active">
                        <div class="oh-step-num">2</div>
                        <div>
                            <div class="oh-step-lbl">Phân NCC & xác nhận</div>
                            <div style="font-size:11px; color:#64748b;">Gán nhà cung cấp</div>
                        </div>
                    </div>
                    <div class="oh-step-line"></div>
                    <div class="oh-step oh-step-pending">
                        <div class="oh-step-num">3</div>
                        <div>
                            <div class="oh-step-lbl">Tạo đơn</div>
                            <div style="font-size:11px; color:#64748b;">Xuất & gửi NCC</div>
                        </div>
                    </div>
                </div>

                <!-- Date source selectors -->
                <div style="background:var(--wh); border:1px solid var(--bd); border-radius:var(--r); padding:14px 18px; box-shadow:var(--sh2); margin-bottom:14px; display:flex; align-items:flex-end; gap:14px; flex-wrap:wrap;" class="dark:bg-gray-900 dark:border-gray-800">
                    <div class="form-field" style="min-width:160px">
                        <label>Ngày đặt hàng</label>
                        <input type="date" wire:model.live="poDate" min="{{ today()->toDateString() }}" max="{{ today()->addDays(2)->toDateString() }}">
                    </div>
                    <div class="form-field" style="min-width:160px">
                        <label>Nguồn từ ngày</label>
                        <input type="date" wire:model.live="poSourceFrom">
                    </div>
                    <div class="form-field" style="min-width:160px">
                        <label>Nguồn đến ngày</label>
                        <input type="date" wire:model.live="poSourceTo">
                    </div>
                    <div class="form-field" style="min-width:240px">
                        <label>Ca lấy nguyên liệu</label>
                        <div style="display:flex; gap:6px; flex-wrap:wrap; background:var(--bg); border:1px solid var(--bd); border-radius:8px; padding:7px 9px; min-height:38px;" class="dark:bg-gray-800 dark:border-gray-700">
                            @foreach($this->getShiftsList() as $sh)
                                <label style="font-size:11px; font-weight:700; color:var(--bl); display:flex; align-items:center; gap:4px; cursor:pointer;">
                                    <input type="checkbox" value="{{ $sh->id }}" wire:model.live="poSelectedShifts" style="border-radius:3px;">
                                    <span>{{ $sh->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Main grouped tables -->
                @if(empty($poItems))
                    <div class="lhn-empty" style="background:var(--wh); border:1px solid var(--bd); border-radius:12px;">
                        <i class="fa-solid fa-basket-shopping" style="font-size:36px; color:#94a3b8; opacity:0.5;"></i>
                        <h3 style="font-size:15px; font-weight:700; color:var(--tx); margin:6px 0;">Chưa có nguyên liệu để đặt</h3>
                        <p style="font-size:12px; color:#94a3b8;">Hãy lập & chốt thực đơn tuần trước, sau đó tạo đơn đặt hàng.</p>
                    </div>
                @else
                    @php 
                        $categories = [
                            'thit' => ['label' => 'Thịt & Thủy hải sản', 'icon' => '🍖', 'class' => 'loai-thit'],
                            'uot' => ['label' => 'Rau củ quả & Nông sản', 'icon' => '🥬', 'class' => 'loai-uot'],
                            'kho' => ['label' => 'Hàng khô & Gia vị', 'icon' => '🧂', 'class' => 'loai-kho']
                        ];
                        $suppliers = $this->getActiveSuppliers();
                    @endphp

                    @foreach($categories as $key => $cat)
                        @php 
                            $catItems = collect($poItems)->where('loai', $key);
                        @endphp
                        @if($catItems->isNotEmpty())
                            <div class="oh-group-card">
                                <div class="oh-group-head">
                                    <div class="oh-group-title">
                                        <span class="loai-badge {{ $cat['class'] }}">{{ $cat['icon'] }} {{ $cat['label'] }}</span>
                                        <span style="font-size:12px; color:#64748b; font-weight:400;">{{ $catItems->count() }} nguyên liệu</span>
                                    </div>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <span style="font-size:12px; color:#64748b;">Gán nhanh NCC:</span>
                                        <select class="oh-ncc-sel" onchange="@this.bulkAssignSupplier('{{ $key }}', this.value)" style="height:28px; padding-top:2px;">
                                            <option value="">-- Chọn NCC --</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="oh-table-wrap">
                                <table class="oh-table">
                                    <thead>
                                        <tr>
                                            <th style="width:36px; text-align:center;">#</th>
                                            <th style="width:50px; text-align:center;">Đặt</th>
                                            <th>Tên nguyên liệu</th>
                                            <th>Thuộc món</th>
                                            <th style="text-align:right;">Số suất</th>
                                            <th style="text-align:right;">SL hệ thống</th>
                                            <th style="text-align:right; width:110px;">SL đặt tay</th>
                                            <th style="text-align:right;">Đơn giá</th>
                                            <th style="text-align:right; width:130px;">Thành tiền</th>
                                            <th style="width:140px;">Nhà cung cấp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($poItems as $index => $item)
                                            @if($item['loai'] === $key)
                                                <tr>
                                                    <td style="text-align:center; color:#94a3b8;">{{ $index + 1 }}</td>
                                                    <td style="text-align:center;">
                                                        <input type="checkbox" wire:model.live="poItems.{{ $index }}.checked">
                                                    </td>
                                                    <td style="font-weight:700;">
                                                        <div class="dark:text-white">{{ $item['name'] }}</div>
                                                        @if(!empty($item['ordered_info']['total']))
                                                            <div style="margin-top:4px; display:flex; gap:4px; flex-wrap:wrap;">
                                                                @foreach($item['ordered_info']['codes'] as $orderCode)
                                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold whitespace-nowrap bg-amber-100 text-amber-800 border border-amber-250">Đã đặt · {{ $orderCode }}</span>
                                                                @endforeach
                                                                @if($item['ordered_info']['total'] > count($item['ordered_info']['codes']))
                                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold whitespace-nowrap bg-slate-100 text-slate-600 border border-slate-200" title="Tổng {{ $item['ordered_info']['total'] }} đơn đã đặt cho nguyên liệu này trong ngày">+{{ $item['ordered_info']['total'] - count($item['ordered_info']['codes']) }} đơn khác</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td style="font-size:11px; color:#64748b;">
                                                        {{ implode(', ', $item['dishes']) }}
                                                    </td>
                                                    <td style="text-align:right;">{{ number_format($item['total_suat']) }}</td>
                                                    <td style="text-align:right; font-weight:600;">{{ number_format($item['total_kg'], 3, ',', '.') }} {{ $item['unit'] }}</td>
                                                    <td style="text-align:center;">
                                                        <input type="number" step="0.001" wire:model.live="poItems.{{ $index }}.quantity_manual" class="table-input" style="height:28px;">
                                                    </td>
                                                    <td style="text-align:right; color:#64748b;">{{ number_format($item['reference_price'], 0, ',', '.') }} đ/{{ $item['unit'] }}</td>
                                                    <td style="text-align:right; font-weight:700; color:#ea580c;">
                                                        @if($item['checked'])
                                                            {{ number_format($item['quantity_manual'] * $item['reference_price'], 0, ',', '.') }} đ
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <select wire:model.live="poItems.{{ $index }}.supplier_id" style="height:28px; font-size:11px; padding:2px; border-radius:6px; border-color:#cbd5e1; width:100%;">
                                                            @foreach($suppliers as $supplier)
                                                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach

                                        <!-- Group Total Row -->
                                        @php 
                                            $groupTotal = collect($poItems)
                                                ->where('loai', $key)
                                                ->filter(fn($it) => $it['checked'])
                                                ->sum(fn($it) => $it['quantity_manual'] * $it['reference_price']);
                                        @endphp
                                        <tr style="background:#F8FAFC" class="dark:bg-gray-800/40">
                                            <td colspan="8" style="text-align:right; font-weight:700; color:#64748b;">Tổng nhóm {{ $cat['label'] }}:</td>
                                            <td colspan="3" style="font-weight:800; color:#1267E8; font-size:13px;">{{ number_format($groupTotal, 0, ',', '.') }} đ</td>
                                        </tr>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    <!-- Grand Total Bar -->
                    @php 
                        $grandTotal = collect($poItems)
                            ->filter(fn($it) => $it['checked'])
                            ->sum(fn($it) => $it['quantity_manual'] * $it['reference_price']);
                        
                        $checkedItemsCount = collect($poItems)->filter(fn($it) => $it['checked'])->count();
                    @endphp
                    <div class="lhn-grand" style="margin-top:4px">
                        <div>
                            <div class="lhn-grand-lbl">TỔNG ĐƠN ĐẶT HÀNG – ĐẶT HÀNG {{ \Carbon\Carbon::parse($poDate)->format('d/m/Y') }}</div>
                            <div style="font-size:11px; opacity:.85; margin-top:3px">
                                {{ collect($poItems)->filter(fn($it) => $it['checked'])->pluck('supplier_id')->unique()->count() }} NCC · {{ $checkedItemsCount }}/{{ count($poItems) }} nguyên liệu
                            </div>
                        </div>
                        <div class="lhn-grand-val">{{ number_format($grandTotal, 0, ',', '.') }} đ</div>
                    </div>
                @endif
            </div>

            <!-- Right Sidebar Column -->
            <div style="width: 280px; flex-shrink: 0;">
                <!-- Summary Card -->
                <div class="oh-sum">
                    <div class="oh-sum-ttl">Tóm tắt đơn hàng</div>
                    @php 
                        $totalOrderedKg = collect($poItems)->filter(fn($it) => $it['checked'])->sum('quantity_manual');
                        $suppliersCount = collect($poItems)->filter(fn($it) => $it['checked'])->pluck('supplier_id')->unique()->count();
                        $grandVal = collect($poItems)->filter(fn($it) => $it['checked'])->sum(fn($it) => $it['quantity_manual'] * $it['reference_price']);
                    @endphp
                    <div class="oh-sum-row"><span class="oh-sum-k">Phạm vi</span><span class="oh-sum-v">Ngày</span></div>
                    <div class="oh-sum-row"><span class="oh-sum-k">Ngày đặt</span><span class="oh-sum-v">{{ \Carbon\Carbon::parse($poDate)->format('d/m/Y') }}</span></div>
                    <div class="oh-sum-row"><span class="oh-sum-k">Nguồn list</span><span class="oh-sum-v">{{ \Carbon\Carbon::parse($poSourceFrom)->format('d/m') }} - {{ \Carbon\Carbon::parse($poSourceTo)->format('d/m') }}</span></div>
                    <div class="oh-sum-row"><span class="oh-sum-k">Ca</span><span class="oh-sum-v">{{ count($poSelectedShifts) }} ca</span></div>
                    <div class="oh-sum-row"><span class="oh-sum-k">Số NCC</span><span class="oh-sum-v">{{ $suppliersCount }} NCC</span></div>
                    <div class="oh-sum-row"><span class="oh-sum-k">Tổng NL</span><span class="oh-sum-v">{{ collect($poItems)->filter(fn($it) => $it['checked'])->count() }}/{{ count($poItems) }} loại</span></div>
                    <div class="oh-sum-row"><span class="oh-sum-k">Tổng giá trị</span><span class="oh-sum-v" style="color:var(--bl); font-weight:800; font-size:13.5px;">{{ number_format($grandVal, 0, ',', '.') }} đ</span></div>
                </div>

                <!-- NCC Distribution Card -->
                <div class="oh-sum">
                    <div class="oh-sum-ttl">Phân bổ theo NCC</div>
                    @php
                        $byNcc = collect($poItems)->filter(fn($it) => $it['checked'])->groupBy('supplier_id');
                    @endphp
                    
                    @forelse($byNcc as $supId => $items)
                        @php 
                            $ncc = $this->getAllSuppliers()->get($supId);
                            $total = $items->sum(fn($it) => $it['quantity_manual'] * $it['reference_price']);
                        @endphp
                        @if($ncc)
                            <div class="oh-ncc-chip">
                                <div class="oh-ncc-chip-dot" style="background:#1267E8;"></div>
                                <div>
                                    <div class="oh-ncc-chip-name">{{ $ncc->code ?: 'NCC' }}</div>
                                    <div class="oh-ncc-chip-cnt" style="font-size:9.5px;">{{ $items->count() }} mặt hàng</div>
                                </div>
                                <div class="oh-ncc-chip-val">{{ number_format($total, 0, ',', '.') }}d</div>
                            </div>
                        @endif
                    @empty
                        <div style="font-size:11px; color:#94a3b8; font-style:italic;">Chưa có phân bổ.</div>
                    @endforelse

                </div>

                <!-- Info Box -->
                <div style="background:#EBF3FF; color:#1e40af; border: 1px solid #BFDBFE; border-radius:12px; padding:12px; font-size:11.5px; line-height:1.4;">
                    <div style="font-weight:800; display:flex; align-items:center; gap:4px; margin-bottom:6px;">
                        <i class="fa-solid fa-circle-info"></i> Lưu ý
                    </div>
                    <ul style="list-style-type: disc; padding-left: 14px; display:grid; gap:4px;">
                        <li>Mỗi NCC sẽ nhận đơn riêng.</li>
                        <li>Có thể bỏ trống nguyên liệu không đặt.</li>
                        <li>SL đặt tay được ưu tiên khi tạo phiếu.</li>
                    </ul>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
