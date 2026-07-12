<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
        border-color: #CBD5E1;
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

    .emp-btn-danger {
        background: var(--po-rd-s) !important;
        border-color: #FCA5A5 !important;
        color: var(--po-rd) !important;
    }

    .emp-btn-danger:hover {
        background: #FEE2E2 !important;
    }

    /* KPIs stats strip */
    .krow {
        display: grid;
        gap: 12px;
    }

    .kcard {
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        padding: 16px;
        box-shadow: var(--po-sh2);
    }

    .ktop {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
    }

    .kico {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        font-size: 14px;
    }

    .ki-b { background: var(--po-bl-s); color: var(--po-bl); }
    .ki-g { background: var(--po-gn-s); color: var(--po-gn); }
    .ki-o { background: var(--po-or-s); color: var(--po-or); }
    .ki-p { background: var(--po-pu-s); color: var(--po-pu); }

    .kval {
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -.03em;
        line-height: 1.1;
        color: var(--po-tx);
    }

    .klbl {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--po-su);
        margin-top: 4px;
    }

    .knote {
        font-size: 10.5px;
        color: var(--po-fa);
        margin-top: 2px;
    }

    /* Top Filters Card */
    .filter-card {
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        padding: 14px 18px;
        box-shadow: var(--po-sh2);
        display: flex;
        align-items: flex-end;
        gap: 14px;
        flex-wrap: wrap;
    }

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
        height: 38px;
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

    /* Steps Tabs */
    .area-tabs {
        display: flex;
        gap: 6px;
        border-bottom: 1.5px solid var(--po-bd2);
        padding-bottom: 2px;
        margin-bottom: 16px;
    }

    .area-tab {
        padding: 10px 18px;
        font-size: 13.5px;
        font-weight: 700;
        color: var(--po-mu);
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: 8px 8px 0 0;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-bottom: none;
        transition: .13s;
        outline: none !important;
    }

    .area-tab.active {
        background: var(--po-wh);
        color: var(--po-bl);
        border-color: var(--po-bd) var(--po-bd) transparent var(--po-bd);
        position: relative;
        font-weight: 800;
    }

    .area-tab.active::after {
        content: "";
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--po-wh);
    }

    /* Table styles BYT style */
    .tcard {
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        box-shadow: var(--po-sh2);
        overflow: hidden;
    }

    .tbar {
        background: var(--po-bd2);
        padding: 14px 18px;
        border-bottom: 1px solid var(--po-bd2);
        text-align: center;
    }

    .byt-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
    }

    .byt-table th, .byt-table td {
        border: 1px solid var(--po-bd);
        padding: 10px;
        color: var(--po-tx);
    }

    .byt-table th {
        background: var(--po-bd2);
        font-weight: 800;
        text-align: center;
        vertical-align: middle;
        text-transform: uppercase;
        font-size: 11.5px;
    }

    .byt-table td {
        background: var(--po-wh);
    }

    .emp-row:hover td {
        background-color: var(--po-bd2) !important;
    }

    .text-center { text-align: center !important; }
    .text-right { text-align: right !important; }
    .font-bold { font-weight: 700 !important; }
    .font-mono { font-family: monospace !important; }

    /* Group title row step 1 */
    .byt-group-title {
        background: var(--po-bd2) !important;
        font-weight: 800;
        color: var(--po-su);
        font-size: 13px;
        text-align: left;
    }

    .byt-group-desc {
        background: var(--po-bd2) !important;
        font-size: 11px;
        color: var(--po-mu);
        font-style: italic;
        text-align: left;
    }

    /* Action buttons in tables */
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

    /* Footer Sign section step 1 */
    .byt-sign-title {
        font-weight: 700;
        background: var(--po-wh) !important;
        border-top: 1.5px solid var(--po-fa);
    }

    .byt-sign-text {
        font-style: italic;
        color: var(--po-mu);
        background: var(--po-wh) !important;
    }

    @media (max-width: 900px) {
        .krow {
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
