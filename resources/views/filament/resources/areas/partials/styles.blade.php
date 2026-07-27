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
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: var(--po-tx);
        background: transparent;
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

    /* KPI icon đồng nhất màu brand — màu chỉ để mã hóa trạng thái ở dòng dữ liệu. */
    .ki-b,
    .ki-g,
    .ki-o,
    .ki-p { background: #E9F2F8; color: var(--po-bl); }

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

    /* Tabs */
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
        transition: background .15s, color .15s, border-color .15s;
        outline: none !important;
    }

    .area-tab:not(.active):hover {
        background: var(--po-bl-s);
        color: var(--po-bl);
        border-color: var(--po-bl-m);
    }

    .area-tab.active {
        background: var(--po-bl);
        color: #fff;
        border-color: var(--po-bl);
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
        background: var(--po-bl);
    }

    /* Card tables */
    .tcard {
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        box-shadow: var(--po-sh2);
        overflow: hidden;
    }

    .tbar {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 14px;
        border-bottom: 1px solid var(--po-bd2);
        flex-wrap: wrap;
    }

    .tsbox {
        display: flex;
        align-items: center;
        gap: 7px;
        background: var(--po-bg);
        border: 1px solid var(--po-bd);
        border-radius: 8px;
        padding: 0 10px;
        height: 34px;
        transition: .13s;
    }

    .tsbox:focus-within {
        border-color: #93C5FD;
        background: #fff;
    }

    .tsbox i {
        color: var(--po-fa);
        font-size: 12px;
        flex-shrink: 0;
    }

    .tsbox input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 12.5px;
        color: var(--po-tx);
        width: 100%;
        box-shadow: none !important;
        padding: 0;
    }

    .tsp {
        flex: 1;
    }

    .fbtn {
        display: flex;
        align-items: center;
        gap: 5px;
        height: 34px;
        padding: 0 12px;
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        color: var(--po-su);
        cursor: pointer;
        transition: .13s;
    }

    .fbtn:hover {
        background: var(--po-bg);
        border-color: #CBD5E1;
    }

    .lv-sel {
        height: 34px;
        padding: 0 24px 0 9px;
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: 8px;
        font-size: 12px;
        color: var(--po-su);
        cursor: pointer;
        outline: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath d='M2 3.5l3 3 3-3' stroke='%2394A3B8' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 7px center;
    }

    /* Table styles */
    .tw {
        overflow-x: auto;
    }

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

    .emp-row:hover {
        background-color: var(--po-bd2) !important;
    }

    /* ============================================================
       Form kiểu Filament — đồng bộ với form trang Nguyên liệu
       (Section có tiêu đề + lưới 2 cột + input ring/shadow)
       ============================================================ */
    .ff-section {
        background: var(--po-wh);
        border-radius: var(--po-r);
        box-shadow: 0 0 0 1px rgba(15, 23, 42, .05), 0 1px 3px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    :root.dark .ff-section {
        box-shadow: 0 0 0 1px rgba(255, 255, 255, .06), 0 1px 3px rgba(0, 0, 0, .35);
    }

    .ff-section-head {
        padding: 16px 20px 0;
    }

    .ff-section-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--po-tx);
        letter-spacing: -.01em;
    }

    .ff-section-sub {
        font-size: 12.5px;
        color: var(--po-mu);
        margin-top: 3px;
    }

    .ff-section-body {
        padding: 18px 20px 20px;
        display: grid;
        gap: 18px 20px;
    }

    .ff-grid-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .ff-col-full {
        grid-column: 1 / -1;
    }

    .ff-actions {
        grid-column: 1 / -1;
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        align-items: center;
        border-top: 1px solid var(--po-bd2);
        padding-top: 16px;
    }

    @media (max-width: 640px) {
        .ff-grid-2 {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    /* Modal thêm/sửa */
    .ff-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .5);
        backdrop-filter: blur(2px);
        z-index: 60;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 40px 16px;
        overflow-y: auto;
    }

    .ff-modal {
        background: var(--po-wh);
        border-radius: 14px;
        width: 100%;
        max-width: 560px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .3);
        overflow: hidden;
        margin: auto;
    }

    .ff-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--po-bd2);
    }

    .ff-modal-close {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid var(--po-bd);
        background: var(--po-wh);
        color: var(--po-mu);
        cursor: pointer;
        display: inline-grid;
        place-items: center;
        font-size: 14px;
        flex-shrink: 0;
        transition: .13s;
    }

    .ff-modal-close:hover {
        background: var(--po-bg);
        color: var(--po-tx);
    }

    /* Form Fields */
    .field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .field label {
        font-size: 13px;
        font-weight: 600;
        color: var(--po-tx);
    }

    .field label .req {
        color: var(--po-rd);
        margin-left: 2px;
    }

    .ctrl {
        height: 40px;
        border-radius: 8px;
        border: none;
        background: var(--po-wh);
        padding: 0 12px;
        font-size: 13.5px;
        color: var(--po-tx);
        outline: none;
        transition: box-shadow .13s;
        width: 100%;
        box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .1), 0 1px 2px rgba(15, 23, 42, .05) !important;
    }

    :root.dark .ctrl {
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .12), 0 1px 2px rgba(0, 0, 0, .25) !important;
    }

    .ctrl::placeholder {
        color: var(--po-fa);
    }

    .ctrl:focus {
        box-shadow: inset 0 0 0 1px var(--po-bl), 0 0 0 2px var(--po-bl-m) !important;
    }

    /* Select kiểu Filament: mũi tên tuỳ biến */
    select.ctrl {
        appearance: none;
        -webkit-appearance: none;
        padding-right: 34px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath d='M2 3.5l3 3 3-3' stroke='%2394A3B8' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
    }

    textarea.ctrl {
        height: auto;
        padding: 9px 12px;
        line-height: 1.5;
    }

    /* Status badges dot rounded */
    .st-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .st-pill::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
        display: inline-block;
    }

    .st-ok {
        background: var(--po-gn-s);
        color: var(--po-gn-t);
        border-color: #A7F3D0;
    }

    .st-ok::before {
        background: var(--po-gn);
    }

    .st-late {
        background: var(--po-or-s);
        color: var(--po-or-t);
        border-color: #FED7AA;
    }

    .st-late::before {
        background: var(--po-or);
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
