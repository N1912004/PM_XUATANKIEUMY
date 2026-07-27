<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<style>
    :root {
        --po-bl: #1267E8;
        --po-bl-d: #1267E8;
        --po-bl-s: #E9F2F8;
        --po-bl-m: #A8CBE6;
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
        --po-pu-t: #5B21B6;
        --po-bg: #F8FAFC;
        --po-bd: #E2E8F0;
        --po-bd2: #F1F5F9;
        --po-tx: #0F172A;
        --po-su: #334155;
        --po-mu: #64748B;
        --po-fa: #94A3B8;
        --po-wh: #fff;
        --po-ms-draft-bg: #F1F5F9;
        --po-ms-draft-text: #475569;
        --po-ms-sent-bg: #EFF6FF;
        --po-ms-sent-text: #1E40AF;
        --po-ms-sent-border: #BFDBFE;
        --po-ms-locked-bg: #FEF3C7;
        --po-ms-locked-text: #78350F;
        --po-ms-locked-border: #FDE68A;
        --po-ms-locked-dot: #F59E0B;
        --po-sh: 0 1px 3px rgba(15,23,42,.05), 0 4px 16px rgba(15,23,42,.05);
        --po-sh2: 0 1px 2px rgba(15,23,42,.04);
        --po-r: 12px;
    }

    :root.dark {
        --po-bl-s: rgba(38, 125, 193, .18);
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
        --po-ms-draft-bg: #263449;
        --po-ms-draft-text: #CBD5E1;
        --po-ms-sent-bg: rgba(18, 103, 232, .18);
        --po-ms-sent-text: #93C5FD;
        --po-ms-sent-border: rgba(147, 197, 253, .45);
        --po-ms-locked-bg: rgba(245, 158, 11, .18);
        --po-ms-locked-text: #FCD34D;
        --po-ms-locked-border: rgba(252, 211, 77, .45);
        --po-ms-locked-dot: #FBBF24;
        --po-sh: 0 1px 2px rgba(0, 0, 0, .4);
    }

    .dark .emp-search {
        background: #0f172a;
    }

    .dark .emp-table thead tr,
    .dark .emp-table tbody tr:hover,
    .dark .emp-btn:hover {
        background: #172033 !important;
    }

    .dark .emp-bottom-bar {
        background: rgba(15, 23, 42, .85);
        border-color: rgba(51, 65, 85, .8);
    }

    .fi-main {
        background: var(--po-bg);
    }

    .emp-page {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: var(--po-tx);
        padding: 20px 24px 36px;
    }

    /* Header styling */
    .emp-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .emp-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--po-tx);
        letter-spacing: -.02em;
    }

    .emp-subtitle {
        font-size: 13px;
        color: var(--po-mu);
        margin-top: 0;
    }

    .emp-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .emp-btn {
        height: 38px;
        padding: 0 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: .13s;
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        color: var(--po-su);
    }

    .emp-btn:hover {
        background: var(--po-bg);
        border-color: var(--po-bd);
    }

    .emp-btn-primary {
        background: var(--po-bl) !important;
        border-color: var(--po-bl) !important;
        color: #fff !important;
    }

    .emp-btn-primary:hover {
        background: var(--po-bl-d) !important;
        border-color: var(--po-bl-d) !important;
    }

    /* KPIs stats strip */
    .mp-krow {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 16px;
    }

    .mp-kcard {
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        padding: 14px 16px;
        box-shadow: var(--po-sh2);
        display: flex;
        align-items: center;
        gap: 12px;
        transition: .13s;
    }

    .mp-kcard:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(15, 23, 42, .08);
    }

    .mp-kico {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        display: grid;
        place-items: center;
        font-size: 17px;
        flex-shrink: 0;
    }

    .mp-kval {
        font-size: 22px;
        font-weight: 800;
        color: var(--po-tx);
        letter-spacing: -.025em;
        line-height: 1.1;
    }

    .mp-klbl {
        font-size: 11px;
        font-weight: 600;
        color: var(--po-mu);
        margin-bottom: 3px;
    }

    /* Filter Bar */
    .mp-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .mp-sel {
        height: 38px;
        padding: 0 28px 0 11px;
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: 9px;
        font-size: 13px;
        color: var(--po-su);
        cursor: pointer;
        outline: none;
        min-width: 130px;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath d='M2 3.5l3 3 3-3' stroke='%2394A3B8' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
    }

    .mp-date-filter {
        height: 38px;
        padding: 0 11px;
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: 9px;
        font-size: 13px;
        color: var(--po-su);
        outline: none;
        min-width: 160px;
        box-shadow: none !important;
    }

    .tsp {
        flex: 1;
    }

    .att-rbtn {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid var(--po-bd);
        background: var(--po-wh);
        cursor: pointer;
        display: grid;
        place-items: center;
        color: var(--po-mu);
        font-size: 12.5px;
        transition: .11s;
    }

    .att-rbtn:hover {
        background: var(--po-bg);
        border-color: var(--po-bd);
    }

    /* List items */
    .mp-card-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .mp-item {
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        cursor: pointer;
        box-shadow: var(--po-sh2);
        transition: .14s;
    }

    .mp-item:hover {
        border-color: var(--po-bl-m);
        box-shadow: 0 4px 16px rgba(18, 103, 232, .10);
        transform: translateY(-1px);
    }

    .emp-bottom-bar {
        position: sticky;
        bottom: 0;
    }

    /* Popup Modal Styles */
    .popup-grp-btn {
        height: 28px;
        padding: 0 12px;
        border: 1px solid var(--po-bd);
        border-radius: 20px;
        background: var(--po-bg);
        font-size: 12px;
        font-weight: 600;
        color: var(--po-mu);
        cursor: pointer;
        transition: .13s;
        font-family: inherit;
        white-space: nowrap;
    }
    .popup-grp-btn:hover {
        border-color: var(--po-bl-m);
        color: var(--po-bl);
        background: var(--po-bl-s);
    }
    .popup-grp-btn.active {
        background: var(--po-bl);
        color: #fff;
        border-color: var(--po-bl);
        box-shadow: 0 2px 8px rgba(18, 103, 232, .22);
    }
    .popup-dish {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        border: 1.5px solid var(--po-bd);
        border-radius: 10px;
        margin-bottom: 7px;
        cursor: pointer;
        transition: .13s;
        background: var(--po-wh);
    }
    .popup-dish:hover {
        border-color: var(--po-bl-m);
        background: var(--po-bl-s);
    }
    .popup-dish.selected {
        border-color: var(--po-bl);
        background: var(--po-bl-s);
    }
    .popup-dish-ico {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        font-size: 16px;
        flex-shrink: 0;
        background: var(--po-bl-s);
        color: var(--po-bl);
    }
    .popup-dish-nm {
        font-size: 13px;
        font-weight: 600;
        color: var(--po-tx);
    }
    .popup-dish-meta {
        font-size: 11.5px;
        color: var(--po-mu);
        margin-top: 1px;
    }
    .popup-dish-cost {
        font-size: 12px;
        font-weight: 700;
        color: var(--po-gn);
        margin-left: auto;
        flex-shrink: 0;
        text-align: right;
    }
    .popup-dish-chk {
        width: 18px;
        height: 18px;
        border-radius: 5px;
        border: 2px solid var(--po-bd);
        display: grid;
        place-items: center;
        flex-shrink: 0;
        transition: .13s;
        background: var(--po-wh);
    }
    .popup-dish.selected .popup-dish-chk {
        background: var(--po-bl);
        border-color: var(--po-bl);
        color: #fff;
        font-size: 11px;
    }

    .mp-item-ico {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .mp-item-info {
        flex: 1;
        min-width: 0;
    }

    .mp-item-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--po-tx);
        margin-bottom: 3px;
    }

    .mp-item-sub {
        font-size: 12px;
        color: var(--po-mu);
    }

    .mp-item-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 6px;
        flex-wrap: wrap;
    }

    .mp-item-tag {
        font-size: 11.5px;
        color: var(--po-mu);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .mp-item-tag i {
        font-size: 11px;
        color: var(--po-fa);
    }

    .mp-item-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 7px;
        flex-shrink: 0;
    }

    /* Status Pill (Thực đơn) */
    .ms-draft {
        background: var(--po-ms-draft-bg);
        color: var(--po-ms-draft-text);
        border: 1px solid var(--po-bd);
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .ms-draft::before,
    .ms-sent::before,
    .ms-locked::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .ms-draft::before { background: var(--po-fa); }

    .ms-sent {
        background: var(--po-ms-sent-bg);
        color: var(--po-ms-sent-text);
        border: 1px solid var(--po-ms-sent-border);
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .ms-sent::before { background: var(--po-bl); }

    .ms-locked {
        background: var(--po-ms-locked-bg);
        color: var(--po-ms-locked-text);
        border: 1px solid var(--po-ms-locked-border);
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .ms-locked::before { background: var(--po-ms-locked-dot); }

    .ms-draft > i,
    .ms-sent > i,
    .ms-locked > i {
        display: none;
    }

    /* abt action button */
    .abt {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        border: 1px solid var(--po-bd);
        background: var(--po-wh);
        cursor: pointer;
        display: inline-grid;
        place-items: center;
        font-size: 12px;
        color: var(--po-mu);
        transition: .11s;
    }

    .abt:hover {
        background: var(--po-bl-s);
        color: var(--po-bl);
        border-color: var(--po-bl-m);
    }

    /* Bảng Grid Tuần */
    .grid-table th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        border: 1px solid #4387FA;
    }

    .grid-table td {
        border: 1px solid var(--po-bd);
    }

    /* Form Ca ăn (Thực đơn ngày) */
    .fc {
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        padding: 16px;
        box-shadow: var(--po-sh2);
    }

    .fch {
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1.5px solid var(--po-bd2);
        padding-bottom: 8px;
        margin-bottom: 14px;
    }

    .fci {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        display: grid;
        place-items: center;
        font-size: 12px;
    }

    .fct {
        font-size: 13.5px;
        font-weight: 800;
        color: var(--po-tx);
    }

    /* Form Fields */
    .field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .field label {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--po-su);
    }

    .ctrl {
        height: 36px;
        border-radius: 7px;
        border: 1px solid var(--po-bd);
        background: var(--po-wh);
        padding: 0 10px;
        font-size: 13px;
        color: var(--po-tx);
        outline: none;
        transition: .13s;
        width: 100%;
        box-shadow: none !important;
    }

    .ctrl:focus {
        border-color: var(--po-bl-m);
        background: var(--po-wh);
    }

    .dv-ca-badge {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        display: inline-block;
    }
    .dv-ca-b1 { background: #EFF6FF; color: #1e40af; }
    .dv-ca-b2 { background: #F0FDF4; color: #065F46; }
    .dv-ca-b3 { background: #FEF3C7; color: #78350F; }
    .dv-ca-b4 { background: #F5F3FF; color: #4C1D95; }

    .tcard {
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        box-shadow: var(--po-sh2);
    }

    @media (max-width: 900px) {
        .mp-krow {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        .emp-head {
            flex-direction: column;
            align-items: flex-start;
        }
        .emp-actions {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>
