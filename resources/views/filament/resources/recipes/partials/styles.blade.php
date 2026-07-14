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
        --su: #334155;
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

    /* ══ NGÂN HÀNG THỰC ĐƠN ══ */
    .mn-root {
        flex: 1;
        display: flex;
        overflow: hidden;
    }
    .mn-left {
        flex: 1;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--bd) transparent;
        min-width: 0;
    }
    .mn-left::-webkit-scrollbar {
        width: 4px;
    }
    .mn-left::-webkit-scrollbar-thumb {
        background: var(--bd);
        border-radius: 4px;
    }

    /* filter bar */
    .mn-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .mn-srch {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: 8px;
        padding: 0 12px;
        height: 36px;
        min-width: 220px;
        flex: 1;
        max-width: 280px;
        transition: .13s;
    }
    .mn-srch:focus-within {
        border-color: #93C5FD;
        background: var(--wh);
    }
    .mn-srch i {
        color: var(--fa);
        font-size: 12px;
        flex-shrink: 0;
    }
    .mn-srch input {
        border: none !important;
        background: transparent !important;
        outline: none !important;
        font-size: 13px !important;
        color: var(--tx) !important;
        width: 100% !important;
        box-shadow: none !important;
    }
    .mn-srch input::placeholder {
        color: var(--fa);
    }
    .mn-sel {
        height: 36px !important;
        padding: 0 28px 0 11px !important;
        background: var(--wh) !important;
        border: 1px solid var(--bd) !important;
        border-radius: 8px !important;
        font-size: 13px !important;
        color: var(--su) !important;
        cursor: pointer !important;
        outline: none !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath d='M2 3.5l3 3 3-3' stroke='%2394A3B8' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 8px center !important;
        transition: .13s;
    }
    .mn-sel:hover {
        border-color: #CBD5E1;
    }
    .mn-fbtn {
        display: flex;
        align-items: center;
        gap: 6px;
        height: 36px;
        padding: 0 13px;
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--su);
        cursor: pointer;
        transition: .13s;
    }
    .mn-fbtn:hover {
        background: var(--bd2);
        border-color: #CBD5E1;
    }
    .mn-clr {
        font-size: 13px;
        font-weight: 600;
        color: var(--bl);
        cursor: pointer;
        padding: 0 4px;
    }
    .mn-clr:hover {
        text-decoration: underline;
    }

    /* info banner */
    .mn-info {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #EFF6FF;
        border: 1px solid var(--bl-m);
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 12px;
        color: #1e40af;
        margin-bottom: 10px;
    }
    .dark .mn-info {
        background: rgba(30, 58, 138, 0.2);
        color: #93c5fd;
        border-color: rgba(59, 130, 246, 0.4);
    }
    .mn-info i {
        font-size: 13px;
        color: var(--bl);
        flex-shrink: 0;
    }

    /* main table */
    .mn-card {
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: var(--r);
        box-shadow: var(--sh2);
        overflow: hidden;
    }
    .mn-table {
        width: 100%;
        border-collapse: collapse;
    }
    .mn-table thead tr {
        background: var(--bd2);
    }
    .mn-table th {
        padding: 10px 12px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: var(--fa);
        text-transform: uppercase;
        letter-spacing: .07em;
        white-space: nowrap;
        border-bottom: 1px solid var(--bd);
    }
    .mn-table th:first-child {
        padding-left: 16px;
        width: 36px;
    }
    .mn-table td {
        padding: 11px 12px;
        vertical-align: middle;
        white-space: nowrap;
        border-bottom: 1px solid var(--bd2);
        color: var(--tx);
    }
    .mn-table td:first-child {
        padding-left: 16px;
    }
    .mn-table tbody tr:last-child td {
        border-bottom: none;
    }
    .mn-table tbody tr:hover {
        background: var(--bd2);
    }
    .mn-table tbody tr.mn-row-sel {
        background: var(--bl-s);
    }

    .mn-expand-btn {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        border: 1px solid var(--bd);
        background: var(--wh);
        cursor: pointer;
        display: grid;
        place-items: center;
        font-size: 10px;
        color: var(--mu);
        transition: .13s;
        flex-shrink: 0;
    }
    .mn-expand-btn:hover {
        background: var(--bl-s);
        color: var(--bl);
        border-color: var(--bl-m);
    }
    .mn-expand-btn.open {
        background: var(--bl-s);
        color: var(--bl);
        border-color: var(--bl-m);
        transform: rotate(90deg);
    }
    .mn-code {
        font-size: 11.5px;
        font-weight: 600;
        color: var(--mu);
        font-variant-numeric: tabular-nums;
    }
    .mn-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--tx);
    }
    .mn-group-pill {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 9px;
        border-radius: 20px;
        display: inline-block;
    }
    .mg-man {
        background: #FFF7ED;
        color: #92400E;
    }
    .dark .mg-man {
        background: rgba(254, 215, 170, 0.15);
        color: #fdba74;
    }
    .mg-xao {
        background: #ECFDF5;
        color: #065F46;
    }
    .dark .mg-xao {
        background: rgba(167, 243, 208, 0.15);
        color: #86efac;
    }
    .mg-canh {
        background: #EFF6FF;
        color: #1e40af;
    }
    .dark .mg-canh {
        background: rgba(191, 219, 254, 0.15);
        color: #93c5fd;
    }
    .mg-chien {
        background: #F5F3FF;
        color: #4C1D95;
    }
    .dark .mg-chien {
        background: rgba(221, 214, 254, 0.15);
        color: #c084fc;
    }
    
    .mn-price {
        font-size: 13px;
        font-weight: 600;
        color: var(--tx);
    }
    .mn-cost {
        font-size: 13px;
        font-weight: 700;
        color: var(--bl);
    }
    .mn-kg {
        font-size: 12.5px;
        color: var(--su);
    }
    .mn-num {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--tx);
    }
    .mn-date {
        font-size: 11.5px;
        color: var(--mu);
    }

    /* status pills menu */
    .ms-active {
        background: #ECFDF5;
        color: #065F46;
        border: 1px solid #A7F3D0;
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 11.5px;
        font-weight: 700;
        display: inline-block;
    }
    .dark .ms-active {
        background: rgba(6, 95, 70, 0.2);
        color: #34d399;
        border-color: rgba(52, 211, 153, 0.4);
    }
    .ms-review {
        background: #FFF7ED;
        color: #92400E;
        border: 1px solid #FED7AA;
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 11.5px;
        font-weight: 700;
        display: inline-block;
    }
    .dark .ms-review {
        background: rgba(146, 64, 14, 0.2);
        color: #fb923c;
        border-color: rgba(251, 146, 60, 0.4);
    }
    .ms-inactive {
        background: #F1F5F9;
        color: #475569;
        border: 1px solid var(--bd);
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 11.5px;
        font-weight: 700;
        display: inline-block;
    }
    .dark .ms-inactive {
        background: rgba(71, 85, 105, 0.2);
        color: #94a3b8;
        border-color: rgba(148, 163, 184, 0.4);
    }

    /* expanded ingredient sub-table */
    .mn-expand-row td {
        padding: 0 !important;
        border-bottom: 1px solid var(--bd2);
    }
    .mn-sub {
        background: var(--bd2);
        padding: 14px 16px 14px 50px;
    }
    .mn-sub-inner {
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }
    .mn-sub-table-wrap {
        flex: 1;
        min-width: 0;
    }
    .mn-sub-ttl {
        font-size: 12px;
        font-weight: 700;
        color: var(--su);
        margin-bottom: 8px;
    }
    .mn-sub-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: 8px;
        overflow: hidden;
    }
    .mn-sub-table th {
        padding: 7px 11px;
        font-size: 10.5px;
        font-weight: 700;
        color: var(--fa);
        text-transform: uppercase;
        letter-spacing: .06em;
        background: var(--bd2);
        border-bottom: 1px solid var(--bd);
    }
    .mn-sub-table td {
        padding: 8px 11px;
        font-size: 12.5px;
        border-bottom: 1px solid var(--bd2);
        color: var(--tx);
    }
    .mn-sub-table tr:last-child td {
        border-bottom: none;
    }
    .mn-sub-summary {
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: 9px;
        padding: 12px 14px;
        min-width: 210px;
        flex-shrink: 0;
        box-shadow: var(--sh2);
    }
    .mn-sub-sum-lbl {
        font-size: 11px;
        color: var(--mu);
        margin-bottom: 4px;
    }
    .mn-sub-sum-val {
        font-size: 14px;
        font-weight: 850;
        color: var(--tx);
        margin-bottom: 10px;
    }
    .mn-sub-cost-lbl {
        font-size: 11px;
        color: var(--mu);
        margin-bottom: 3px;
    }
    .mn-sub-cost-val {
        font-size: 18px;
        font-weight: 850;
        color: var(--bl);
    }

    /* Page layout */
    .ph {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    .ph-l {
        flex: 1;
        min-width: 250px;
    }
    .ph-l h1 {
        font-size: 1.5rem !important;
        font-weight: 800 !important;
        color: var(--tx) !important;
        line-height: 1.2 !important;
    }
    .ph-l p {
        font-size: 13px;
        color: var(--mu);
        margin-top: 3px;
    }
    .ph-r {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    .btn,
    .ph-r button,
    .ph-r a,
    .ph-r .fi-ac-action {
        white-space: nowrap !important;
        height: 38px !important;
        line-height: 38px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .btn {
        padding: 0 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        border: 1px solid var(--bd);
        background: var(--wh);
        color: var(--tx);
        text-decoration: none;
        transition: .15s ease;
    }
    .btn:hover {
        background: var(--bd2);
        border-color: #CBD5E1;
    }
    .btn-p {
        background: var(--bl) !important;
        border-color: var(--bl) !important;
        color: #fff !important;
    }
    .btn-p:hover {
        background: var(--bl-d) !important;
        border-color: var(--bl-d) !important;
    }

    /* Responsive cho mobile */
    @media (max-width: 640px) {
        .ph {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }
        .ph-r {
            width: 100%;
            justify-content: flex-start;
        }
        .btn, 
        .ph-r button, 
        .ph-r a, 
        .ph-r .fi-ac-action {
            flex: 1;
            min-width: 120px;
        }
    }

    /* pagination footer */
    .tf {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background: var(--wh);
        border-top: 1px solid var(--bd2);
        font-size: 13px;
        color: var(--mu);
        flex-wrap: wrap;
        gap: 12px;
    }
    .pgwrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pgsel {
        height: 28px !important;
        padding: 0 20px 0 8px !important;
        background: var(--wh) !important;
        border: 1px solid var(--bd) !important;
        border-radius: 6px !important;
        font-size: 12px !important;
        color: var(--tx) !important;
        cursor: pointer !important;
        outline: none !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 10 10'%3E%3Cpath d='M2 3.5l3 3 3-3' stroke='%2394A3B8' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 6px center !important;
    }
    .pgbs {
        display: flex;
        gap: 4px;
    }
    .pgb {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        border: 1px solid var(--bd);
        background: var(--wh);
        color: var(--mu);
        display: grid;
        place-items: center;
        cursor: pointer;
        font-weight: 600;
        font-size: 12px;
        transition: .12s;
    }
    .pgb:hover {
        background: var(--bl-s);
        color: var(--bl);
        border-color: var(--bl-m);
    }
    .pgb.cur {
        background: var(--bl);
        color: #fff;
        border-color: var(--bl);
    }
    .pgdot {
        width: 20px;
        height: 28px;
        display: grid;
        place-items: center;
        color: var(--fa);
    }
    
    .abt {
        width: 26px;
        height: 26px;
        border-radius: 6px;
        border: 1px solid var(--bd);
        background: var(--wh);
        cursor: pointer;
        display: inline-grid;
        place-items: center;
        font-size: 11px;
        color: var(--mu);
        transition: .11s;
        text-decoration: none;
    }
    .abt:hover {
        background: var(--bl-s);
        color: var(--bl);
        border-color: var(--bl-m);
    }
    .abt-danger:hover {
        background: var(--rd-s);
        color: var(--rd);
        border-color: #fecaca;
    }

    /* Custom Searchable Dropdown for List Recipes Page */
    .mn-filter-select {
        position: relative;
    }
    .mn-dropdown-panel {
        position: absolute;
        z-index: 50;
        top: calc(100% + 4px);
        left: 0;
        min-width: 220px;
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
        padding: 6px;
        max-height: 250px;
        overflow-y: auto;
    }
    .dark .mn-dropdown-panel {
        background: #1e293b;
        border-color: #334155;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
    }
    .mn-dropdown-search {
        width: 100%;
        border: 1px solid var(--bd);
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 13px;
        outline: none;
        margin-bottom: 6px;
        background: var(--bg);
        color: var(--tx);
    }
    .dark .mn-dropdown-search {
        border-color: #334155;
        background: #0f172a;
    }
    .mn-dropdown-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .mn-dropdown-item {
        width: 100%;
        text-align: left;
        padding: 6px 8px;
        border: none;
        background: transparent;
        font-size: 13px;
        color: var(--tx);
        cursor: pointer;
        border-radius: 4px;
        transition: background 0.1s;
    }
    .mn-dropdown-item:hover {
        background: var(--bd2);
    }
    .dark .mn-dropdown-item:hover {
        background: #334155;
    }
    .mn-dropdown-item.selected {
        background: var(--bl-s);
        color: var(--bl);
        font-weight: 700;
    }
    .dark .mn-dropdown-item.selected {
        background: rgba(18, 103, 232, 0.25);
        color: #38bdf8;
    }
    .mn-dropdown-empty {
        padding: 8px;
        color: var(--fa);
        font-size: 12px;
        text-align: center;
    }
</style>
@endpush
