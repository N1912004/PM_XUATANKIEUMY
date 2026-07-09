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
        gap: 18px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--po-bd2);
    }

    .ci-user {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 260px;
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

    .ci-av-fallback {
        display: grid;
        place-items: center;
        font-size: 18px;
        font-weight: 800;
    }

    .ci-name {
        color: var(--po-tx);
        font-size: 15px;
        font-weight: 800;
        line-height: 1.2;
    }

    .ci-id,
    .ci-role {
        color: var(--po-mu);
        font-size: 11.5px;
        font-weight: 600;
        margin-top: 2px;
    }

    .ci-role {
        color: var(--po-fa);
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
        gap: 8px;
    }

    .ci-meta-ico {
        color: var(--po-bl);
        font-size: 14px;
    }

    .ci-meta-lbl {
        color: var(--po-mu);
        font-size: 10.5px;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .ci-meta-val {
        color: var(--po-tx);
        font-size: 12.5px;
        font-weight: 800;
        white-space: nowrap;
    }

    .ci-meta-status {
        min-width: 120px;
    }

    .ci-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border: 1px solid transparent;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 800;
        white-space: nowrap;
    }

    .ci-status-badge::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .ci-status-badge.ok {
        background: var(--po-gn-s);
        color: var(--po-gn-t);
        border-color: #A7F3D0;
    }

    .ci-status-badge.late {
        background: var(--po-or-s);
        color: var(--po-or-t);
        border-color: #FED7AA;
    }

    .ci-status-badge.ot {
        background: var(--po-bl-s);
        color: var(--po-bl);
        border-color: var(--po-bl-m);
    }

    .ci-status-badge.leave {
        background: var(--po-pu-s);
        color: var(--po-pu);
        border-color: #DDD6FE;
    }

    .ci-status-badge.absent {
        background: var(--po-rd-s);
        color: var(--po-rd-t);
        border-color: #FCA5A5;
    }

    .ci-status-badge.neutral {
        background: #F1F5F9;
        color: var(--po-mu);
        border-color: #CBD5E1;
    }

    .ci-bottom {
        display: flex;
        align-items: center;
        gap: 16px;
        padding-top: 16px;
    }

    .ci-time-box {
        min-width: 86px;
    }

    .ci-time-lbl {
        color: var(--po-mu);
        font-size: 11px;
        font-weight: 700;
    }

    .ci-time-val {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -.02em;
        line-height: 1.1;
        margin-top: 3px;
    }

    .ci-time-val.in,
    .ci-time-val.out {
        color: var(--po-gn);
    }

    .ci-time-date {
        color: var(--po-fa);
        font-size: 10.5px;
        margin-top: 3px;
    }

    .ci-divider {
        width: 1px;
        height: 50px;
        background: var(--po-bd2);
    }

    .ci-actions {
        flex: 1;
        display: grid;
        grid-template-columns: repeat(2, minmax(220px, 1fr));
        gap: 10px;
    }

    .ci-btn {
        min-height: 58px;
        border-radius: 10px;
        border: 1.5px solid var(--po-bd);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 8px 14px;
        color: var(--po-su);
        background: #fff;
        font-size: 13px;
        font-weight: 800;
        transition: .14s ease;
    }

    .ci-btn span {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        line-height: 1.2;
    }

    .ci-btn small {
        margin-top: 3px;
        color: inherit;
        font-size: 11px;
        font-weight: 600;
        opacity: .75;
    }

    .ci-btn-ico {
        font-size: 18px;
    }

    .ci-btn.done-in {
        background: var(--po-gn-s);
        border-color: #A7F3D0;
        color: var(--po-gn-t);
    }

    .ci-btn.done-out {
        background: var(--po-bl-s);
        border-color: var(--po-bl-m);
        color: var(--po-bl);
    }

    .ci-btn.active-in {
        background: linear-gradient(135deg, #059669, #10B981);
        border-color: transparent;
        color: #fff;
        cursor: pointer;
        box-shadow: 0 6px 16px rgba(5, 150, 105, .24);
    }

    .ci-btn.active-out {
        background: linear-gradient(135deg, #1474FF, #0059DD);
        border-color: transparent;
        color: #fff;
        cursor: pointer;
        box-shadow: 0 6px 16px rgba(18, 103, 232, .24);
    }

    .ci-btn.active-in:hover,
    .ci-btn.active-out:hover {
        transform: translateY(-1px);
    }

    .ci-btn.disabled {
        color: var(--po-fa);
        cursor: not-allowed;
        background: #F8FAFC;
    }

    .ci-confirm {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-top: 12px;
        padding: 8px 12px;
        border: 1px solid #A7F3D0;
        border-radius: 8px;
        background: var(--po-gn-s);
        color: var(--po-gn-t);
        font-size: 12px;
        font-weight: 700;
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

    @media (max-width: 1100px) {
        .ci-top,
        .ci-bottom {
            align-items: flex-start;
            flex-direction: column;
        }

        .ci-user {
            min-width: 0;
        }

        .ci-actions {
            width: 100%;
        }

        .ci-divider {
            display: none;
        }
    }

    @media (max-width: 640px) {
        .ci-card {
            padding: 14px;
        }

        .ci-meta {
            align-items: flex-start;
            flex-direction: column;
            gap: 12px;
        }

        .ci-actions {
            grid-template-columns: 1fr;
        }

        .ci-btn {
            justify-content: flex-start;
        }
    }

</style>
