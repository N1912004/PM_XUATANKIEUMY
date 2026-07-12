@push('styles')
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
        --po-bg: #F8FAFC;
        --po-bd: #E2E8F0;
        --po-bd2: #F1F5F9;
        --po-tx: #0F172A;
        --po-mu: #64748B;
        --po-fa: #94A3B8;
        --po-wh: #FFFFFF;
        --po-sh: 0 1px 3px rgba(15, 23, 42, .03), 0 1px 2px rgba(15, 23, 42, .06);
        --po-sh2: 0 4px 6px -1px rgba(15, 23, 42, .03), 0 2px 4px -2px rgba(15, 23, 42, .04);
        --po-r: .75rem;
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

    .dark .po-search {
        background: #0f172a;
    }

    .dark .po-table thead tr,
    .dark .po-table tbody tr:hover,
    .dark .po-btn:hover {
        background: #172033 !important;
    }

    .dark .po-info {
        background: rgba(18, 103, 232, .12);
        border-color: rgba(18, 103, 232, .35);
        color: var(--po-su);
    }

    .dark .po-bottom-bar {
        background: rgba(15, 23, 42, .85);
        border-color: rgba(51, 65, 85, .8);
    }

    .fi-main {
        background: var(--po-bg);
    }

    .po-page,
    .po-page * {
        box-sizing: border-box;
        font-family: 'Inter', system-ui, sans-serif;
    }

    .po-page {
        color: var(--po-tx);
        padding: 1.5rem 1.75rem 2rem !important;
        background: var(--po-bg);
        min-height: 100vh;
        overflow-x: hidden;
    }

    .po-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .po-title {
        font-size: 1.62rem !important;
        font-weight: 800 !important;
        color: var(--po-tx) !important;
        letter-spacing: -.02em;
        line-height: 1.35 !important;
        margin: 0 !important;
    }

    .po-subtitle {
        color: var(--po-mu);
        font-size: .88rem;
        margin-top: .25rem;
    }

    .po-actions {
        display: flex;
        align-items: center;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .po-btn {
        height: 2.5rem;
        padding: 0 1.15rem;
        border-radius: .65rem;
        font-size: .88rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        cursor: pointer;
        transition: .15s ease;
        border: 1.5px solid var(--po-bd);
        background: var(--po-wh);
        color: var(--po-tx);
        text-decoration: none;
    }

    .po-btn:hover {
        border-color: var(--po-bl-m);
        background: var(--po-bl-s);
        color: var(--po-bl);
    }

    .po-btn svg {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
    }

    .po-btn-primary {
        background: var(--po-bl) !important;
        border-color: var(--po-bl) !important;
        color: #fff !important;
        box-shadow: 0 4px 10px rgba(18, 103, 232, .18);
    }

    .po-btn-primary:hover {
        background: var(--po-bl-d) !important;
        border-color: var(--po-bl-d) !important;
        color: #fff !important;
        box-shadow: 0 4px 14px rgba(18, 103, 232, .28);
    }

    /* KPIs Grid */
    .po-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.1rem;
        margin-bottom: 1.5rem;
    }

    .po-kpi {
        background: var(--po-wh);
        border: 1.5px solid var(--po-bd2);
        border-radius: var(--po-r);
        padding: 1.25rem 1.4rem;
        box-shadow: var(--po-sh2);
        display: flex;
        align-items: center;
        gap: 1.1rem;
        position: relative;
    }

    .po-kpi-icon {
        width: 3.1rem;
        height: 3.1rem;
        border-radius: .7rem;
        display: grid;
        place-items: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    .po-kpi-icon svg {
        width: 1.4rem;
        height: 1.4rem;
    }

    .po-ico-blue { background: #EBF3FF; color: var(--po-bl); }
    .po-ico-orange { background: var(--po-or-s); color: var(--po-or); }
    .po-ico-green { background: var(--po-gn-s); color: var(--po-gn); }
    .po-ico-purple { background: #F0FDF4; color: #15803D; }

    .po-kpi-info {
        display: flex;
        flex-direction: column;
    }

    .po-kpi-label {
        font-size: .78rem;
        font-weight: 700;
        color: var(--po-mu);
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: .2rem;
    }

    .po-kpi-value {
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--po-tx);
        line-height: 1.2;
    }

    /* Filter Bar */
    .po-filter-bar {
        background: var(--po-wh);
        border: 1.5px solid var(--po-bd2);
        border-radius: var(--po-r);
        padding: .85rem 1rem;
        box-shadow: var(--po-sh2);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .po-search {
        position: relative;
        flex: 1;
        min-width: 10rem;
    }

    .po-search svg {
        position: absolute;
        left: .9rem;
        top: 50%;
        transform: translateY(-50%);
        width: .95rem;
        height: .95rem;
        color: var(--po-fa);
    }

    .po-search input {
        width: 100%;
        height: 2.4rem;
        padding: 0 .9rem 0 2.3rem;
        border-radius: .65rem;
        border: 1.5px solid var(--po-bd);
        background: var(--po-bg);
        font-size: .88rem;
        outline: none;
        transition: .15s ease;
        color: var(--po-tx);
    }

    .po-search input:focus {
        border-color: var(--po-bl-m);
        background: var(--po-wh);
        box-shadow: 0 0 0 3px rgba(18, 103, 232, .06);
    }

    .po-select {
        height: 2.4rem;
        padding: 0 2.2rem 0 .9rem;
        border-radius: .65rem;
        border: 1.5px solid var(--po-bd);
        background: var(--po-bg);
        font-size: .88rem;
        font-weight: 600;
        color: var(--po-tx);
        outline: none;
        cursor: pointer;
        transition: .15s ease;
        min-width: auto;
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
        background-position: right 0.65rem center;
        background-repeat: no-repeat;
        background-size: 1.15rem 1.15rem;
    }

    .po-select:focus,
    .po-select:hover {
        border-color: var(--po-bl-m);
        background-color: var(--po-wh);
    }

    .po-reset-btn {
        width: 2.4rem;
        height: 2.4rem;
        border-radius: .65rem;
        border: 1.5px solid var(--po-bd);
        background: var(--po-wh);
        color: var(--po-mu);
        display: inline-grid;
        place-items: center;
        cursor: pointer;
        transition: .15s ease;
        flex-shrink: 0;
    }

    .po-reset-btn:hover {
        background: var(--po-bl-s);
        color: var(--po-bl);
        border-color: var(--po-bl-m);
    }

    .po-reset-btn svg {
        width: 1rem;
        height: 1rem;
    }

    /* List Items */
    .po-list {
        display: flex;
        flex-direction: column;
        gap: .75rem;
    }

    .po-item {
        background: var(--po-wh);
        border: 1.5px solid var(--po-bd2);
        border-radius: var(--po-r);
        padding: 1.1rem 1.45rem;
        box-shadow: var(--po-sh2);
        display: flex;
        align-items: center;
        gap: 1.1rem;
        transition: .15s ease;
        position: relative;
    }

    .po-item:hover {
        border-color: var(--po-bl-m);
        box-shadow: 0 6px 20px rgba(18, 103, 232, .06);
        transform: translateY(-1px);
    }

    .po-item-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: .65rem;
        display: grid;
        place-items: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .po-item-icon svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    .po-item-info {
        flex: 1;
        min-width: 0;
    }

    .po-item-title {
        font-size: .95rem;
        font-weight: 800;
        color: var(--po-tx);
        line-height: 1.4;
        display: flex;
        align-items: center;
        gap: .65rem;
        flex-wrap: wrap;
    }

    .po-item-sub {
        font-size: .8rem;
        font-weight: 700;
        color: var(--po-mu);
        margin-top: .15rem;
    }

    .po-item-meta {
        font-size: .78rem;
        color: var(--po-mu);
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: .35rem;
    }

    .po-item-meta span {
        display: flex;
        align-items: center;
        gap: .35rem;
    }

    .po-item-meta svg {
        width: .85rem;
        height: .85rem;
        color: var(--po-fa);
    }

    .po-item-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: .45rem;
        flex-shrink: 0;
    }

    .po-item-val {
        font-size: 1rem;
        font-weight: 850;
        color: var(--po-bl);
    }

    /* Badges & Tags */
    .os-draft {
        background: #F1F5F9;
        color: #475569;
        border: 1px solid var(--po-bd);
        border-radius: 999px;
        padding: .15rem .65rem;
        font-size: .72rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        text-transform: uppercase;
        letter-spacing: .02em;
    }

    .os-sent {
        background: #EFF6FF;
        color: #1E40AF;
        border: 1px solid #BFDBFE;
        border-radius: 999px;
        padding: .15rem .65rem;
        font-size: .72rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        text-transform: uppercase;
        letter-spacing: .02em;
    }

    .os-checking {
        background: #FFF7ED;
        color: #92400E;
        border: 1px solid #FED7AA;
        border-radius: 999px;
        padding: .15rem .65rem;
        font-size: .72rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        text-transform: uppercase;
        letter-spacing: .02em;
    }

    .os-done {
        background: #ECFDF5;
        color: #065F46;
        border: 1px solid #A7F3D0;
        border-radius: 999px;
        padding: .15rem .65rem;
        font-size: .72rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        text-transform: uppercase;
        letter-spacing: .02em;
    }

    .ot-tuan {
        background: #FEF3C7;
        color: #78350F;
        border-radius: 6px;
        padding: .1rem .45rem;
        font-size: .72rem;
        font-weight: 800;
    }

    .ot-ngay {
        background: #EFF6FF;
        color: #1E40AF;
        border-radius: 6px;
        padding: .1rem .45rem;
        font-size: .72rem;
        font-weight: 800;
    }

    /* Item actions row */
    .po-item-actions {
        display: flex;
        gap: .35rem;
    }

    .po-item-action {
        width: 1.85rem;
        height: 1.85rem;
        border-radius: .45rem;
        border: 1px solid var(--po-bd);
        background: var(--po-wh);
        color: var(--po-mu);
        display: inline-grid;
        place-items: center;
        cursor: pointer;
        transition: .12s ease;
        text-decoration: none;
    }

    .po-item-action:hover {
        background: var(--po-bl-s);
        color: var(--po-bl);
        border-color: var(--po-bl-m);
    }

    .po-item-action-danger:hover {
        background: var(--po-rd-s);
        color: var(--po-rd);
        border-color: #FECACA;
    }

    .po-item-action svg {
        width: .9rem;
        height: .9rem;
    }

    /* Footer Pagination */
    .po-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.45rem;
        color: var(--po-mu);
        font-size: .86rem;
        flex-wrap: wrap;
        background: var(--po-wh);
        border: 1.5px solid var(--po-bd2);
        border-radius: var(--po-r);
        box-shadow: var(--po-sh2);
        margin-top: 1.25rem;
    }

    .po-pagination {
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .po-pagination nav {
        display: flex;
        gap: .35rem;
    }

    .po-pagination nav > div:first-child {
        display: none;
    }

    .po-pagination nav span,
    .po-pagination nav a,
    .po-pagination nav button {
        min-width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .5rem;
        border: 1px solid var(--po-bd);
        background: var(--po-wh);
        color: var(--po-mu);
        font-weight: 800;
        font-size: .86rem;
        text-decoration: none;
        padding: 0 .55rem;
        cursor: pointer;
    }

    .po-pagination nav button:hover {
        background: var(--po-bl-s);
        color: var(--po-bl);
        border-color: var(--po-bl-m);
    }

    .po-pagination nav span[aria-disabled="true"] {
        cursor: not-allowed;
        opacity: .5;
    }

    .po-pagination nav span.po-page-dots {
        border: none;
        background: transparent;
        min-width: 1.25rem;
        padding: 0;
    }

    .po-pagination nav span[aria-current="page"] span {
        background: var(--po-bl);
        border-color: var(--po-bl);
        color: #fff;
    }

    /* Empty state */
    .po-empty {
        background: var(--po-wh);
        border: 1.5px solid var(--po-bd2);
        border-radius: var(--po-r);
        padding: 4rem 2rem;
        text-align: center;
        box-shadow: var(--po-sh2);
    }

    .po-empty-icon {
        width: 4rem;
        height: 4rem;
        border-radius: 50%;
        background: var(--po-bg);
        display: inline-grid;
        place-items: center;
        margin-bottom: 1.25rem;
        color: var(--po-fa);
    }

    .po-empty-icon svg {
        width: 1.75rem;
        height: 1.75rem;
    }

    .po-empty-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--po-tx);
        margin-bottom: .35rem;
    }

    .po-empty-sub {
        font-size: .88rem;
        color: var(--po-mu);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .po-kpis {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 760px) {
        .po-head,
        .po-filter-bar,
        .po-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .po-kpis {
            grid-template-columns: 1fr;
        }

        .po-search {
            min-width: 100%;
        }

        .po-select {
            width: 100%;
        }

        .po-item {
            flex-direction: column;
            align-items: flex-start;
            gap: .9rem;
            padding: 1.1rem;
        }

        .po-item-right {
            width: 100%;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--po-bd2);
            padding-top: .75rem;
            margin-top: .25rem;
        }

        .po-item-icon {
            display: none;
        }
    }

    /* ==========================================================================
       BLUEFIRE GROUP - ORDER VIEW CSS INTEGRATION
       ========================================================================== */
    .py-krow {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
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

    /* Filter Bar */
    .mp-bar {
        display: flex;
        align-items: center;
        gap: 8px;
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

    .mp-srch input::placeholder {
        color: var(--po-fa) !important;
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
        border-color: #CBD5E1;
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

    /* Order Item List */
    .oh-item {
        background: var(--po-wh);
        border: 1px solid var(--po-bd);
        border-radius: var(--po-r);
        padding: 16px 18px;
        box-shadow: var(--po-sh2);
        display: flex;
        align-items: center;
        gap: 14px;
        transition: .14s;
        margin-bottom: 10px;
        cursor: pointer;
        text-align: left;
    }

    .oh-item:hover {
        border-color: var(--po-bl-m);
        box-shadow: 0 4px 16px rgba(18, 103, 232, .10);
        transform: translateY(-1px);
    }

    .oh-item-ico {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .oh-item-info {
        flex: 1;
        min-width: 0;
    }

    .oh-item-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--po-tx);
        margin-bottom: 3px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .oh-item-meta {
        font-size: 12px;
        color: var(--po-mu);
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 5px;
    }

    .oh-item-meta span {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .oh-item-meta span i {
        color: var(--po-fa);
    }

    .oh-item-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 7px;
        flex-shrink: 0;
    }

    .oh-item-val {
        font-size: 15px;
        font-weight: 800;
        color: var(--po-bl);
    }

    /* Badges & Tags */
    .os-draft {
        background: #F1F5F9;
        color: #475569;
        border: 1px solid var(--po-bd);
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 11.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .os-sent {
        background: #EFF6FF;
        color: #1e40af;
        border: 1px solid #BFDBFE;
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 11.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .os-checking {
        background: #FFF7ED;
        color: #92400E;
        border: 1px solid #FED7AA;
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 11.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .os-done {
        background: #ECFDF5;
        color: #065F46;
        border: 1px solid #A7F3D0;
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 11.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .ot-kho {
        background: #FEF3C7;
        color: #78350F;
        border-radius: 6px;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: 700;
    }

    .ot-uot {
        background: #EFF6FF;
        color: #1e40af;
        border-radius: 6px;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: 700;
    }

    .ot-thit {
        background: #FEF2F2;
        color: #DC2626;
        border-radius: 6px;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: 700;
    }

    /* Actions button */
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

    /* Detail View Tabs & Table CSS */
    .oh-ncc-tabs {
        margin-bottom: 20px;
    }
    
    .oh-ncc-tab {
        transition: all 0.2s ease-in-out !important;
    }
    
    .oh-ncc-tab:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
    }

    .po-list-card table th {
        font-weight: 700 !important;
        font-size: 11px !important;
        color: var(--po-mu) !important;
        border-bottom: 1.5px solid var(--po-bd2) !important;
    }

    .po-list-card table td {
        vertical-align: middle !important;
        border-bottom: 1px solid var(--po-bd2) !important;
    }

    .po-list-card table tbody tr:hover {
        background-color: var(--po-bd2) !important;
    }
</style>
@endpush
