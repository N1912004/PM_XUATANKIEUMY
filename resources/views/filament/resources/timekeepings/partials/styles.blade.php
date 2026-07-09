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
        --po-r: 14px;
    }

    .emp-page {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: var(--po-tx);
        background: transparent;
    }

    /* Header Styling */
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

    .emp-btn {
        height: 38px;
        padding: 0 16px;
        border-radius: 9px;
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

    /* Personal check-in card */
    .ci-card {
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        padding: 18px 20px;
        box-shadow: var(--po-sh2);
        margin-bottom: 14px;
    }

    .ci-top {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--po-bd2);
        flex-wrap: wrap;
    }

    .ci-user {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 200px;
        margin-right: auto;
    }

    .ci-av {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid var(--po-bl-m);
        flex-shrink: 0;
    }

    .ci-av img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .ci-name {
        font-size: 15px;
        font-weight: 800;
        color: var(--po-tx);
    }

    .ci-id {
        font-size: 11.5px;
        color: var(--po-mu);
        margin-top: 1px;
        font-weight: 600;
    }

    .ci-role {
        font-size: 11.5px;
        color: var(--po-fa);
        margin-top: 1px;
        font-weight: 500;
    }

    .ci-meta {
        display: flex;
        align-items: center;
        gap: 22px;
        flex-wrap: wrap;
    }

    .ci-meta-item {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .ci-meta-ico {
        font-size: 14px;
        color: var(--po-bl);
    }

    .ci-meta-lbl {
        font-size: 10.5px;
        color: var(--po-mu);
        margin-bottom: 1px;
        font-weight: 600;
    }

    .ci-meta-val {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--po-tx);
    }

    .ci-status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        background: var(--po-gn-s);
        color: var(--po-gn-t);
        border: 1px solid #A7F3D0;
        display: inline-block;
    }

    .ci-status-badge.late {
        background: var(--po-or-s);
        color: var(--po-or-t);
        border-color: #FED7AA;
    }

    .ci-bottom {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .ci-time-box {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 80px;
    }

    .ci-time-lbl {
        font-size: 11px;
        font-weight: 600;
        color: var(--po-mu);
    }

    .ci-time-val {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -.02em;
    }

    .ci-time-val.in {
        color: var(--po-gn);
    }

    .ci-time-val.out {
        color: var(--po-gn);
    }

    .ci-time-date {
        font-size: 10.5px;
        color: var(--po-fa);
    }

    .ci-divider {
        width: 1px;
        height: 48px;
        background: var(--po-bd2);
        flex-shrink: 0;
    }

    .ci-btn-wrap {
        flex: 1;
        display: flex;
        gap: 10px;
        min-width: 300px;
    }

    .ci-btn {
        flex: 1;
        height: 60px;
        border-radius: 11px;
        border: 2px solid var(--po-bl);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
        transition: .14s ease;
        font-weight: 700;
        font-size: 14px;
        background: #fff;
    }

    .ci-btn.done-in {
        background: var(--po-gn-s) !important;
        border-color: #A7F3D0 !important;
        color: var(--po-gn-t) !important;
        cursor: default !important;
    }

    .ci-btn.done-out {
        background: var(--po-bl-s) !important;
        border-color: var(--po-bl-m) !important;
        color: var(--po-bl) !important;
        cursor: default !important;
    }

    .ci-btn.active-in {
        background: linear-gradient(135deg, #059669, #10B981) !important;
        color: #fff !important;
        border-color: transparent !important;
        box-shadow: 0 6px 16px rgba(5, 150, 105, 0.28);
    }

    .ci-btn.active-in:hover {
        box-shadow: 0 8px 20px rgba(5, 150, 105, 0.38);
        transform: translateY(-1px);
    }

    .ci-btn.active-out {
        background: linear-gradient(135deg, #1474FF, #0059DD) !important;
        color: #fff !important;
        border-color: transparent !important;
        box-shadow: 0 6px 16px rgba(18, 103, 232, 0.28);
    }

    .ci-btn.active-out:hover {
        box-shadow: 0 8px 20px rgba(18, 103, 232, 0.38);
        transform: translateY(-1px);
    }

    .ci-btn-ico {
        font-size: 20px;
    }

    .ci-btn-sub {
        font-size: 11px;
        font-weight: 500;
        margin-top: 2px;
        opacity: .8;
    }

    .ci-confirm {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 12px;
        color: var(--po-gn-t);
        background: var(--po-gn-s);
        border: 1px solid #A7F3D0;
        border-radius: 8px;
        padding: 7px 12px;
    }

    /* Attendance Table Card */
    .att-card {
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        box-shadow: var(--po-sh2);
        overflow: hidden;
    }

    .att-toolbar {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        padding: 12px 14px;
        border-bottom: 1px solid var(--po-bd2);
        flex-wrap: wrap;
    }

    .att-srch {
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

    .att-srch:focus-within {
        border-color: #93C5FD;
        background: #fff;
    }

    .att-srch i {
        color: var(--po-fa);
        font-size: 12px;
        flex-shrink: 0;
    }

    .att-srch input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 12.5px;
        color: var(--po-tx);
        width: 100%;
        box-shadow: none !important;
    }

    .att-date {
        height: 34px;
        border: 1px solid var(--po-bd);
        border-radius: 8px;
        background: var(--po-wh);
        padding: 0 10px;
        font-size: 12.5px;
        color: var(--po-tx);
        outline: none;
        transition: .13s;
        cursor: pointer;
    }

    .att-date:focus {
        border-color: #93C5FD;
    }

    .att-sel {
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

    .att-sel:hover {
        border-color: #CBD5E1;
    }

    .att-sel:focus {
        border-color: #93C5FD;
    }

    .att-sp {
        flex: 1;
    }

    .att-fbtn {
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

    .att-fbtn:hover {
        background: var(--po-bg);
        border-color: #CBD5E1;
    }

    .att-fdot {
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

    .att-rbtn {
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

    .att-rbtn:hover {
        background: var(--po-bg);
        color: var(--po-bl);
        border-color: var(--po-bl-m);
    }

    /* Table styles */
    .att-tw {
        overflow-x: auto;
    }

    .att-ca {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--po-su);
    }

    .att-ca-ico {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        display: grid;
        place-items: center;
        font-size: 10px;
        flex-shrink: 0;
    }

    .att-ca-morning {
        background: #FFF7ED;
        color: #D97706;
    }

    .att-ca-night {
        background: #EFF6FF;
        color: #1D4ED8;
    }

    .att-time {
        font-size: 13px;
        font-weight: 700;
    }

    .att-time.in {
        color: var(--po-gn);
    }

    .att-time.late {
        color: var(--po-or);
    }

    .att-time.empty {
        color: var(--po-fa);
    }

    .att-time.out {
        color: var(--po-gn);
    }

    .att-hrs {
        font-size: 12.5px;
        color: var(--po-su);
        font-weight: 600;
    }

    .att-ot {
        font-size: 12.5px;
        font-weight: 600;
    }

    .att-ot.has {
        color: var(--po-bl);
    }

    .att-ot.none {
        color: var(--po-fa);
    }

    /* status badges */
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

    .st-ot {
        background: var(--po-bl-s);
        color: var(--po-bl);
        border-color: var(--po-bl-m);
    }

    .st-ot::before {
        background: var(--po-bl);
    }

    .st-leave {
        background: var(--po-pu-s);
        color: var(--po-pu);
        border-color: #DDD6FE;
    }

    .st-leave::before {
        background: var(--po-pu);
    }

    .st-absent {
        background: var(--po-rd-s);
        color: var(--po-rd-t);
        border-color: #FCA5A5;
    }

    .st-absent::before {
        background: var(--po-rd);
    }

    /* Actions Button */
    .abt {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        border: 1px solid var(--po-bd);
        background: var(--po-wh);
        cursor: pointer;
        display: grid;
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

    /* Row Hover */
    .emp-row:hover {
        background-color: #F8FAFC !important;
    }

    @media (max-width: 900px) {
        .ci-top {
            flex-direction: column;
            align-items: flex-start;
        }
        .ci-meta {
            margin-top: 12px;
            gap: 12px;
        }
        .ci-bottom {
            flex-direction: column;
            align-items: flex-start;
        }
        .ci-divider {
            width: 100%;
            height: 1px;
        }
        .ci-btn-wrap {
            width: 100%;
        }
    }
</style>
