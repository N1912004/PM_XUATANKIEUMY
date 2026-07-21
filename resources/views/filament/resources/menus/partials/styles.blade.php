<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<style>
    :root {
        --po-bl: #267DC1;
        --po-bl-d: #1F669E;
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
        font-family: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: var(--po-tx);
    }

    /* Header styling */
    .emp-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
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
        margin-top: 2px;
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
    }

    .mp-kico {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .mp-kval {
        font-size: 22px;
        font-weight: 800;
        color: var(--po-tx);
        line-height: 1.1;
    }

    .mp-klbl {
        font-size: 11px;
        font-weight: 600;
        color: var(--po-mu);
        margin-bottom: 2px;
    }

    /* Filter Bar */
    .mp-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        padding: 8px 12px;
        box-shadow: var(--po-sh2);
        flex-wrap: wrap;
    }

    .mp-srch {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--po-bg);
        border: 1px solid var(--po-bd);
        border-radius: 8px;
        padding: 0 10px;
        height: 34px;
        flex: 1;
        min-width: 200px;
    }

    .mp-srch i {
        color: var(--po-fa);
        font-size: 12px;
    }

    .mp-srch input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 12.5px;
        color: var(--po-tx);
        width: 100%;
        box-shadow: none !important;
        padding: 0;
    }

    .mp-sel {
        height: 34px;
        padding: 0 24px 0 10px;
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: 8px;
        font-size: 12.5px;
        color: var(--po-su);
        cursor: pointer;
        outline: none;
        min-width: 130px;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath d='M2 3.5l3 3 3-3' stroke='%2394A3B8' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
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

    /* Locked Warning Panel */
    .mp-locked-panel {
        background: var(--po-or-s);
        border: 1px solid var(--po-or);
        border-radius: var(--po-r);
        padding: 14px 16px;
    }

    .mp-locked-panel-hd {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 12px;
        flex-wrap: wrap;
        gap: 8px;
    }

    .mp-locked-panel-title {
        font-size: 14px;
        font-weight: 800;
        color: var(--po-or);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .mp-locked-panel-sub {
        font-size: 11.5px;
        color: var(--po-or);
        margin-top: 3px;
    }

    .mp-locked-tools {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .dv-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .dv-field label {
        font-size: 11px;
        font-weight: 700;
        color: var(--po-or);
    }

    .dv-sel {
        height: 34px;
        border-radius: 7px;
        border: 1px solid var(--po-or);
        background: var(--po-wh);
        padding: 0 10px;
        font-size: 12.5px;
        color: var(--po-or);
        outline: none;
    }

    .btn {
        height: 34px;
        padding: 0 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        color: var(--po-su);
    }

    .btn-p {
        background: var(--po-bl) !important;
        border-color: var(--po-bl) !important;
        color: #fff !important;
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
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        cursor: pointer;
        box-shadow: var(--po-sh2);
        transition: .12s;
    }

    .mp-item:hover {
        border-color: var(--po-bl-m);
        background: var(--po-bg);
    }

    .mp-item-ico {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .mp-item-info {
        flex: 1;
    }

    .mp-item-title {
        font-size: 13.5px;
        font-weight: 800;
        color: var(--po-tx);
    }

    .mp-item-sub {
        font-size: 11.5px;
        color: var(--po-mu);
        margin-top: 3px;
    }

    .mp-item-meta {
        display: flex;
        gap: 12px;
        margin-top: 6px;
        flex-wrap: wrap;
    }

    .mp-item-tag {
        font-size: 11px;
        color: var(--po-fa);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .mp-item-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 6px;
    }

    /* Status Pill (Thực đơn) */
    .ms-draft {
        background: var(--po-bd2);
        color: var(--po-su);
        border: 1px solid var(--po-bd);
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 12px;
    }

    .ms-sent {
        background: var(--po-bl-s);
        color: var(--po-bl);
        border: 1px solid var(--po-bl-m);
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 12px;
    }

    .ms-locked {
        background: var(--po-or-s);
        color: var(--po-or);
        border: 1px solid var(--po-or);
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 12px;
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
