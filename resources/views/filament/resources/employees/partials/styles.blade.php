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

    .emp-actions {
        display: flex;
        gap: 8px;
        align-items: center;
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

    .emp-btn-primary {
        background: var(--po-bl) !important;
        border-color: var(--po-bl) !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(18, 103, 232, 0.2);
    }

    .emp-btn-primary:hover {
        background: var(--po-bl-d) !important;
        border-color: var(--po-bl-d) !important;
    }

    /* KPIs Row */
    .py-krow {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 11px;
        margin-bottom: 16px;
    }

    .py-kcard {
        background: var(--po-wh);
        border: 1.5px solid var(--po-bd2);
        border-radius: 12px;
        padding: 14px 16px;
        box-shadow: var(--po-sh2);
        transition: .13s;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .py-kcard:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(15,23,42,.08);
    }

    .py-kico {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        display: grid;
        place-items: center;
        font-size: 17px;
        flex-shrink: 0;
    }

    .py-klbl {
        font-size: 11px;
        font-weight: 600;
        color: var(--po-mu);
        margin-bottom: 3px;
    }

    .py-kval {
        font-size: 22px;
        font-weight: 800;
        color: var(--po-tx);
        letter-spacing: -.025em;
        line-height: 1;
    }

    .py-knote {
        font-size: 10px;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 3px;
    }

    /* Filter Toolbar */
    .mp-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .mp-srch {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        background: var(--po-wh) !important;
        border: 1px solid var(--po-bd) !important;
        border-radius: 9px !important;
        padding: 0 12px !important;
        height: 38px !important;
        min-width: 220px !important;
        flex: 1 !important;
        max-width: 280px !important;
        transition: .13s !important;
    }

    .mp-srch:focus-within {
        border-color: var(--po-bl-m) !important;
    }

    .mp-srch i {
        color: var(--po-fa) !important;
        font-size: 13px !important;
        flex-shrink: 0 !important;
    }

    .mp-srch input {
        border: none !important;
        background: transparent !important;
        outline: none !important;
        font-size: 13px !important;
        color: var(--po-tx) !important;
        width: 100% !important;
        box-shadow: none !important;
    }

    .mp-sel {
        height: 38px !important;
        padding: 0 28px 0 11px !important;
        background-color: var(--po-wh) !important;
        border: 1px solid var(--po-bd) !important;
        border-radius: 9px !important;
        font-size: 13px !important;
        color: var(--po-su) !important;
        cursor: pointer !important;
        outline: none !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2394A3B8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E") !important;
        background-position: right 8px center !important;
        background-repeat: no-repeat !important;
        background-size: 1.15rem 1.15rem !important;
    }

    .mp-sel:hover {
        border-color: #CBD5E1 !important;
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

    .tsp {
        flex: 1;
    }

    /* Badges & Tables */
    .es-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .es-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }

    .es-working {
        background: #ECFDF5;
        color: #047857;
        border-color: #A7F3D0;
    }

    .es-working .es-dot {
        background: #10B981;
    }

    .es-leave {
        background: #FFF7ED;
        color: #C2410C;
        border-color: #FED7AA;
    }

    .es-leave .es-dot {
        background: #F97316;
    }

    .es-resign {
        background: #FEF2F2;
        color: #B91C1C;
        border-color: #FCA5A5;
    }

    .es-resign .es-dot {
        background: #EF4444;
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

    /* Custom Form Layout Styles (Create/Edit) */
    .fstack {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .fc {
        background: var(--po-wh);
        border: 1px solid var(--po-bd2);
        border-radius: 12px;
        padding: 20px 24px;
        box-shadow: var(--po-sh2);
    }

    .fch {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        border-bottom: 1px solid #F8FAFC;
        padding-bottom: 12px;
    }

    .fci {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-size: 14px;
    }

    .fct {
        font-size: 15px;
        font-weight: 800;
        color: var(--po-tx);
    }

    .play {
        display: flex;
        gap: 24px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .pcol {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 140px;
        flex-shrink: 0;
    }

    .pring {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 2px dashed #CBD5E1;
        display: grid;
        place-items: center;
        position: relative;
        background: #F8FAFC;
    }

    .pcam {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--po-bl);
        color: #fff;
        display: grid;
        place-items: center;
        font-size: 11px;
        cursor: pointer;
        border: 2px solid #fff;
        transition: .13s;
    }

    .pcam:hover {
        background: var(--po-bl-d);
    }

    .phint {
        text-align: center;
        font-size: 11px;
        color: var(--po-mu);
        margin-top: 10px;
        line-height: 1.4;
    }

    .fg {
        display: grid;
        gap: 14px 18px;
        flex: 1;
        min-width: 280px;
    }

    .fg3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .fg4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .field.sp2 {
        grid-column: span 2;
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
        height: 38px !important;
        border: 1px solid var(--po-bd) !important;
        border-radius: 8px !important;
        padding: 0 12px !important;
        font-size: 13px !important;
        color: var(--po-tx) !important;
        outline: none !important;
        width: 100% !important;
        background-color: var(--po-wh) !important;
        transition: .13s !important;
        box-shadow: none !important;
    }

    .ctrl:focus {
        border-color: var(--po-bl-m) !important;
    }

    select.ctrl {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2394A3B8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
        background-position: right 8px center;
        background-repeat: no-repeat;
        background-size: 1.15rem 1.15rem;
        padding-right: 28px !important;
    }

    @media (max-width: 900px) {
        .fg3 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .fg4 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .py-krow {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 600px) {
        .fg3, .fg4 {
            grid-template-columns: 1fr;
        }
        .field.sp2 {
            grid-column: span 1;
        }
        .py-krow {
            grid-template-columns: 1fr;
        }
    }
</style>
