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
        --po-pu-t: #5B21B6;
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

    .emp-page {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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

    /* Tabs list view */
    .lv-tabs {
        display: flex;
        gap: 24px;
        border-bottom: 1px solid var(--po-bd);
        margin-bottom: 16px;
    }

    .lv-tab {
        padding: 8px 4px 12px;
        font-size: 14px;
        font-weight: 700;
        color: var(--po-mu);
        border-bottom: 2px solid transparent;
        cursor: pointer;
        transition: .13s;
        background: none;
        border-top: none;
        border-left: none;
        border-right: none;
        outline: none !important;
    }

    .lv-tab:hover {
        color: var(--po-tx);
    }

    .lv-tab.active {
        color: var(--po-bl);
        border-bottom-color: var(--po-bl);
    }

    /* Cards */
    .tcard {
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        box-shadow: var(--po-sh2);
        overflow: hidden;
    }

    /* Toolbar filter */
    .lv-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 14px;
        border-bottom: 1px solid var(--po-bd2);
        flex-wrap: wrap;
    }

    .lv-srch {
        display: flex;
        align-items: center;
        gap: 7px;
        background: var(--po-bg);
        border: 1px solid var(--po-bd);
        border-radius: 8px;
        padding: 0 10px;
        height: 34px;
        min-width: 180px;
        transition: .13s;
    }

    .lv-srch:focus-within {
        border-color: #93C5FD;
        background: #fff;
    }

    .lv-srch i {
        color: var(--po-fa);
        font-size: 12px;
        order: -1;
    }

    .lv-srch input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 12.5px;
        color: var(--po-tx);
        width: 100%;
        box-shadow: none !important;
        padding: 0;
    }

    .lv-date {
        height: 34px;
        display: flex;
        align-items: center;
        gap: 6px;
        border: 1px solid var(--po-bd);
        border-radius: 8px;
        padding: 0 10px;
        background: var(--po-wh);
    }

    .lv-sel {
        height: 34px;
        padding: 0 26px 0 9px;
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
        transition: .13s;
    }

    .lv-sel:hover {
        border-color: #CBD5E1;
    }

    .lv-sel:focus {
        border-color: #93C5FD;
    }

    .lv-sp {
        flex: 1;
    }

    .lv-fbtn {
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

    .lv-fbtn:hover {
        background: var(--po-bg);
        border-color: #CBD5E1;
    }

    .lv-fdot {
        background: var(--po-bl);
        color: #fff;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-size: 8px;
        font-weight: 800;
    }

    .lv-rbtn {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid var(--po-bd);
        background: var(--po-wh);
        cursor: pointer;
        display: grid;
        place-items: center;
        font-size: 13px;
        color: var(--po-mu);
        transition: .13s;
    }

    .lv-rbtn:hover {
        background: var(--po-bg);
        color: var(--po-bl);
        border-color: var(--po-bl-m);
    }

    /* Badges */
    .req-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .req-annual {
        background: var(--po-pu-s);
        color: var(--po-pu-t);
    }

    .req-sick {
        background: var(--po-rd-s);
        color: var(--po-rd-t);
    }

    .req-unpaid {
        background: var(--po-or-s);
        color: var(--po-or-t);
    }

    .req-ot {
        background: var(--po-bl-s);
        color: var(--po-bl);
    }

    .req-other {
        background: #F1F5F9;
        color: #475569;
    }

    /* Table elements styling */
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
        background-color: #F8FAFC !important;
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

    .st-absent {
        background: var(--po-rd-s);
        color: var(--po-rd-t);
        border-color: #FCA5A5;
    }

    .st-absent::before {
        background: var(--po-rd);
    }

    /* Form Styles 2 Columns */
    .lf-tabs {
        display: flex;
        gap: 8px;
        border-bottom: 1.5px solid var(--po-bd2);
        padding-bottom: 2px;
    }

    .lf-tab {
        padding: 10px 18px;
        font-size: 13.5px;
        font-weight: 700;
        color: var(--po-mu);
        background: #fff;
        border: 1px solid var(--po-bd);
        border-radius: 8px 8px 0 0;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-bottom: none;
        transition: .13s;
    }

    .lf-tab.active {
        background: var(--po-wh);
        color: var(--po-bl);
        border-color: var(--po-bd) var(--po-bd) transparent var(--po-bd);
        position: relative;
        font-weight: 800;
    }

    .lf-tab.active::after {
        content: "";
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: #fff;
    }

    .lf-layout {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 16px;
        align-items: start;
    }

    /* Right summary cards */
    .lf-sum, .lf-quota, .lf-notice {
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        padding: 16px;
        box-shadow: var(--po-sh2);
    }

    .lf-sum-ttl, .lf-quota-ttl, .lf-notice-ttl {
        font-size: 13.5px;
        font-weight: 800;
        color: var(--po-tx);
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--po-bd2);
    }

    .lf-sum-row, .lf-quota-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px dashed var(--po-bd2);
        font-size: 12px;
    }

    .lf-sum-k, .lf-quota-k {
        color: var(--po-mu);
        font-weight: 500;
    }

    .lf-sum-v, .lf-quota-v {
        color: var(--po-tx);
        font-weight: 600;
        text-align: right;
    }

    .lf-rule {
        list-style: none;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
        font-size: 11.5px;
        color: var(--po-mu);
        line-height: 1.4;
    }

    /* Left card container form */
    .fc {
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        padding: 16px 20px 20px;
        box-shadow: var(--po-sh2);
    }

    .fch {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--po-bd2);
    }

    .fci {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .fct {
        font-size: 15px;
        font-weight: 800;
        color: var(--po-tx);
    }

    .fg {
        display: grid;
        gap: 14px;
    }

    .fg2 {
        grid-template-columns: repeat(2, 1fr);
    }

    .fg3 {
        grid-template-columns: repeat(3, 1fr);
    }

    .field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .field label {
        font-size: 12px;
        font-weight: 700;
        color: var(--po-su);
    }

    .field label .req {
        color: var(--po-rd);
        margin-left: 2px;
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
        border-color: #93C5FD;
        background: #fff;
    }

    textarea.ctrl {
        height: auto;
        padding: 8px 10px;
    }

    /* Upload zone */
    .lf-upload {
        border: 2.2px dashed var(--po-bl-m);
        border-radius: 9px;
        padding: 16px;
        text-align: center;
        background: var(--po-bg);
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        transition: .13s;
    }

    .lf-upload:hover {
        background: var(--po-bl-s);
        border-color: var(--po-bl);
    }

    .lf-upload-ico {
        font-size: 20px;
        color: var(--po-bl);
    }

    .lf-upload span {
        font-size: 12.5px;
        color: var(--po-su);
    }

    .lf-upload small {
        font-size: 11px;
        color: var(--po-fa);
    }

    @media (max-width: 900px) {
        .lf-layout {
            grid-template-columns: 1fr;
        }
        .fg2, .fg3 {
            grid-template-columns: 1fr;
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
