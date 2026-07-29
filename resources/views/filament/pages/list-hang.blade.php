@push('styles')
<style>
    :root {
        --bl: #1267E8;
        --bl-d: #1267E8;
        --bl-s: #E9F2F8;
        --bl-m: #A8CBE6;
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
        --mu: #64748B;
        --fa: #94A3B8;
        --wh: #FFFFFF;
        --bg: #F8FAFC; /* Thiếu ở light nên khối 'Ca lấy nguyên liệu' bị mất nền xám */
        --r: 12px;
        --sh2: 0 2px 4px rgba(15,23,42,.02);
    }

    :root.dark {
        --bl-s: rgba(38, 125, 193, .18);
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

    /* ══ LIST HÀNG NGÀY ══ */
    .fi-page:has(.lhn-root) {
        padding: 22px 28px 36px !important;
    }
    .lhn-root {
        flex: 1;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--bd) transparent;
    }
    .fi-page:has(.lhn-root) > section > .fi-header { display: none !important; }

    @media (max-width: 767px) {
        .fi-page:has(.lhn-root) { padding: 16px !important; }
    }

    .lhn-page-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    .lhn-page-head h1 {
        margin: 0 0 4px;
        color: var(--tx);
        font-size: 20px;
        font-weight: 800;
        letter-spacing: -.02em;
    }
    .lhn-page-actions {
        display: flex;
        gap: 8px;
    }
    .lhn-head-btn {
        height: 36px;
        padding: 0 14px;
        border: 1px solid var(--bd);
        border-radius: 8px;
        background: var(--wh);
        color: var(--tx);
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        box-shadow: var(--sh2);
        transition: .14s ease;
        white-space: nowrap;
    }
    .lhn-head-btn:hover {
        background: #F8FAFC;
        transform: translateY(-1px);
    }
    .lhn-head-btn-primary {
        border-color: transparent;
        background: linear-gradient(135deg, #1474FF, #0059DD);
        color: #fff;
        box-shadow: 0 6px 16px rgba(18, 103, 232, .28);
    }
    .lhn-head-btn-primary:hover {
        background: linear-gradient(135deg, #105FCC, #004FC4);
    }
    :root.dark .lhn-head-btn {
        background: #0f172a;
        border-color: #334155;
        color: #f1f5f9;
    }
    :root.dark .lhn-head-btn-primary {
        background: linear-gradient(135deg, #1474FF, #0059DD);
        border-color: transparent;
        color: #fff;
    }

    /* date picker bar */
    .lhn-datebar {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: var(--r);
        padding: 13px 18px;
        box-shadow: var(--sh2);
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .lhn-date-inp {
        height: 40px;
        border: 1.5px solid var(--bl-m);
        border-radius: 9px;
        padding: 0 12px;
        font-size: 14px;
        font-weight: 700;
        color: var(--bl);
        outline: none;
        background: var(--bl-s);
        cursor: pointer;
        transition: .13s;
    }
    .dark .lhn-date-inp {
        background: #1e293b;
        color: #60a5fa;
        border-color: #3b82f6;
    }
    .dark input[type="date"] {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #ffffff !important;
    }
    .lhn-date-inp:focus {
        box-shadow: 0 0 0 3px rgba(38, 125, 193,.1);
    }
    .lhn-nav-btn {
        width: 34px;
        height: 34px;
        border: 1px solid var(--bd);
        border-radius: 8px;
        background: var(--wh);
        cursor: pointer;
        display: grid;
        place-items: center;
        font-size: 13px;
        color: var(--mu);
        transition: .13s;
        flex-shrink: 0;
    }
    .dark .lhn-nav-btn {
        background: #1e293b;
        border-color: #334155;
        color: var(--fa);
    }
    .lhn-nav-btn:hover {
        background: var(--bl-s);
        color: var(--bl);
        border-color: var(--bl-m);
    }
    .lhn-today-btn {
        height: 34px;
        padding: 0 14px;
        border: 1px solid var(--bl);
        border-radius: 8px;
        background: var(--bl-s);
        color: var(--bl);
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        transition: .13s;
        font-family: inherit;
        flex-shrink: 0;
    }
    .dark .lhn-today-btn {
        background: #1e3a8a;
        color: #3b82f6;
        border-color: #3b82f6;
    }
    .lhn-today-btn:hover {
        background: var(--bl);
        color: #fff;
    }

    /* summary chips */
    .lhn-chips {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .lhn-chip {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: 10px;
        padding: 10px 14px;
        box-shadow: var(--sh2);
        flex: 1;
        min-width: 160px;
        transition: .13s;
    }
    .dark .lhn-chip {
        background: #0f172a;
        border-color: #1e293b;
    }
    .lhn-chip:hover {
        border-color: var(--bl-m);
        transform: translateY(-1px);
    }
    .lhn-chip-ico {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    .lhn-chip-val {
        font-size: 18px;
        font-weight: 800;
        color: var(--tx);
        line-height: 1;
    }
    .dark .lhn-chip-val {
        color: #fff;
    }
    .lhn-chip-lbl {
        font-size: 11px;
        color: var(--mu);
        margin-top: 2px;
    }

    /* ca section */
    .lhn-ca-card {
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: var(--r);
        box-shadow: var(--sh2);
        overflow: hidden;
        margin-bottom: 14px;
    }
    .dark .lhn-ca-card {
        background: #0f172a;
        border-color: #1e293b;
    }
    .lhn-ca-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid var(--bd);
        transition: .12s;
        user-select: none;
    }
    .dark .lhn-ca-head {
        border-color: #1e293b;
    }
    .lhn-ca-head:hover {
        background: #FAFBFC;
    }
    .dark .lhn-ca-head:hover {
        background: rgba(30, 41, 59, 0.4);
    }
    .lhn-ca-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .lhn-ca-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 800;
    }
    .lhn-ca-meta {
        font-size: 12.5px;
        color: var(--mu);
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .lhn-ca-body {
        padding: 14px 16px;
    }

    /* mon row */
    .lhn-mon-row {
        border: 1px solid var(--bd);
        border-radius: 10px;
        margin-bottom: 10px;
        overflow: hidden;
    }
    .dark .lhn-mon-row {
        border-color: #1e293b;
    }
    .lhn-mon-row:last-child {
        margin-bottom: 0;
    }
    .lhn-mon-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background: #F8FAFC;
        cursor: pointer;
        transition: .12s;
        border-bottom: 1px solid transparent;
    }
    .dark .lhn-mon-head {
        background: rgba(30, 41, 59, 0.3);
    }
    .lhn-mon-head.open {
        border-bottom-color: var(--bd);
        background: var(--bl-s);
    }
    .dark .lhn-mon-head.open {
        border-bottom-color: #1e293b;
        background: rgba(30, 58, 138, 0.3);
    }
    .lhn-mon-head:hover {
        background: var(--bl-s);
    }
    .dark .lhn-mon-head:hover {
        background: rgba(30, 58, 138, 0.2);
    }
    .lhn-mon-left {
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .lhn-mon-ico {
        font-size: 18px;
        flex-shrink: 0;
    }
    .lhn-mon-name {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--tx);
    }
    .dark .lhn-mon-name {
        color: #fff;
    }
    .lhn-mon-type {
        font-size: 11.5px;
        color: var(--mu);
        background: #F1F5F9;
        border-radius: 6px;
        padding: 2px 8px;
        margin-left: 2px;
    }
    .dark .lhn-mon-type {
        background: #1e293b;
        color: var(--fa);
    }
    .lhn-mon-right {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 12.5px;
        color: var(--mu);
    }
    .lhn-mon-suat {
        font-weight: 700;
        color: var(--bl);
        background: var(--bl-s);
        border-radius: 6px;
        padding: 2px 9px;
    }
    .dark .lhn-mon-suat {
        background: rgba(30, 58, 138, 0.3);
        color: #60a5fa;
    }
    .lhn-expand-ico {
        font-size: 11px;
        color: var(--mu);
        transition: transform .2s;
    }

    /* ingredient table */
    .lhn-ing-table {
        width: 100%;
        border-collapse: collapse;
    }
    .lhn-ing-table th {
        padding: 8px 12px;
        text-align: left;
        font-size: 10.5px;
        font-weight: 700;
        color: var(--fa);
        text-transform: uppercase;
        letter-spacing: .07em;
        background: #F8FAFC;
        border-bottom: 1px solid var(--bd);
        white-space: nowrap;
    }
    .dark .lhn-ing-table th {
        background: rgba(30, 41, 59, 0.5);
        border-color: #1e293b;
        color: var(--fa);
    }
    .lhn-ing-table td {
        padding: 9px 12px;
        font-size: 12.5px;
        border-bottom: 1px solid var(--bd2);
        vertical-align: middle;
        color: var(--tx);
    }
    .dark .lhn-ing-table td {
        border-color: #1e293b;
        color: var(--su, #cbd5e1);
    }
    .lhn-ing-table tr:last-child td {
        border-bottom: none;
    }
    .lhn-ing-table tbody tr:hover {
        background: #FAFCFF;
    }
    .dark .lhn-ing-table tbody tr:hover {
        background: rgba(30, 41, 59, 0.1);
    }
    .lhn-ing-num {
        font-weight: 600;
        color: var(--mu);
        text-align: center;
        width: 36px;
    }
    .lhn-kg {
        font-weight: 700;
        color: var(--tx);
    }
    .dark .lhn-kg {
        color: #fff;
    }
    .lhn-total-row td {
        background: linear-gradient(135deg, #EFF6FF, #DBEAFE);
        font-weight: 800;
        font-size: 13px;
    }
    .dark .lhn-total-row td {
        background: linear-gradient(135deg, rgba(30, 58, 138, 0.3), rgba(30, 58, 138, 0.1));
    }

    /* grand total bar */
    .lhn-grand {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(135deg, #1474FF, #0059DD);
        border-radius: var(--r);
        padding: 16px 22px;
        color: #fff;
        box-shadow: 0 6px 20px rgba(38, 125, 193,.3);
    }
    .lhn-grand-lbl {
        font-size: 14px;
        font-weight: 700;
        opacity: .9;
    }
    .lhn-grand-val {
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -.02em;
    }

    /* Nút hành động + ô nhập của wizard tạo đơn.
       Trước đây dùng chung class .wh-action-btn/.form-field nhưng CSS của chúng chỉ
       nằm trong warehouse.blade.php, không load ở trang này nên nút mất hẳn khung. */
    .lhn-act-btn {
        height: 38px;
        padding: 0 16px;
        border: 1px solid var(--bd);
        border-radius: 9px;
        background: var(--wh);
        color: var(--tx);
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
        white-space: nowrap;
        transition: background .16s ease, border-color .16s ease, box-shadow .16s ease;
    }
    .lhn-act-btn:hover:not(:disabled) {
        background: var(--bd2);
    }
    .lhn-act-btn:disabled {
        opacity: .6;
        cursor: not-allowed;
    }
    .lhn-act-btn-primary {
        background: var(--bl);
        border-color: var(--bl);
        color: #fff;
        box-shadow: 0 4px 12px rgba(18, 103, 232, .28);
    }
    .lhn-act-btn-primary:hover:not(:disabled) {
        background: #0F58CC;
        border-color: #0F58CC;
    }
    .dark .lhn-act-btn {
        background: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }
    .dark .lhn-act-btn:hover:not(:disabled) {
        background: #334155;
    }
    .dark .lhn-act-btn-primary {
        background: var(--bl);
        border-color: var(--bl);
        color: #fff;
    }

    .form-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-field label {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
    }
    .dark .form-field label {
        color: #cbd5e1;
    }
    /* :not(...) để khỏi thổi checkbox 'Ca lấy nguyên liệu' lên cỡ ô nhập */
    .form-field input:not([type="checkbox"]):not([type="radio"]),
    .form-field select,
    .form-field textarea {
        height: 38px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 0 12px;
        font-size: 13px;
        background: #fff;
        color: var(--tx);
        outline: none;
    }
    .form-field input[type="checkbox"],
    .form-field input[type="radio"] {
        width: 14px;
        height: 14px;
        padding: 0;
        flex: 0 0 auto;
    }
    .form-field input:not([type="checkbox"]):not([type="radio"]):focus,
    .form-field select:focus {
        border-color: var(--bl);
        box-shadow: 0 0 0 3px rgba(18, 103, 232, .12);
    }
    .dark .form-field input,
    .dark .form-field select,
    .dark .form-field textarea {
        border-color: #334155;
        background: #1e293b;
        color: #fff;
    }

    /* step wizard */
    .oh-steps {
        display: flex;
        align-items: center;
        gap: 0;
        margin-bottom: 20px;
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: var(--r);
        padding: 14px 20px;
        box-shadow: var(--sh2);
    }
    .dark .oh-steps {
        background: #0f172a;
        border-color: #1e293b;
    }
    .oh-step {
        display: flex;
        align-items: center;
        gap: 9px;
        flex: 1;
    }
    .oh-step-num {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-size: 12px;
        font-weight: 800;
        flex-shrink: 0;
        transition: .2s;
    }
    .oh-step-active .oh-step-num {
        background: var(--bl);
        color: #fff;
        box-shadow: 0 3px 8px rgba(38, 125, 193,.3);
    }
    .oh-step-done .oh-step-num {
        background: var(--gn);
        color: #fff;
    }
    .oh-step-pending .oh-step-num {
        background: #F1F5F9;
        color: var(--mu);
        border: 1.5px solid var(--bd);
    }
    .dark .oh-step-pending .oh-step-num {
        background: #1e293b;
        color: var(--mu);
        border-color: #334155;
    }
    .oh-step-lbl {
        font-size: 12.5px;
        font-weight: 700;
    }
    .oh-step-active .oh-step-lbl { color: var(--bl); }
    .oh-step-done .oh-step-lbl { color: var(--gn); }
    .oh-step-pending .oh-step-lbl { color: var(--fa); }
    .oh-step-line {
        flex: 1;
        height: 2px;
        background: var(--bd);
        margin: 0 8px;
        transition: .2s;
    }
    .dark .oh-step-line {
        background: #1e293b;
    }
    .oh-step-line.done {
        background: var(--gn);
    }

    /* NL group cards */
    .oh-group-card {
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: var(--r);
        box-shadow: var(--sh2);
        overflow: hidden;
        margin-bottom: 14px;
    }
    .dark .oh-group-card {
        background: #0f172a;
        border-color: #1e293b;
    }
    .oh-group-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-bottom: 1px solid var(--bd2);
    }
    .dark .oh-group-head {
        border-color: #1e293b;
    }
    .oh-group-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--tx);
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .dark .oh-group-title {
        color: #fff;
    }
    .oh-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .oh-table {
        width: 100%;
        min-width: 72rem;
        border-collapse: collapse;
    }
    .oh-table th {
        padding: 9px 12px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: var(--fa);
        text-transform: uppercase;
        letter-spacing: .06em;
        background: #F8FAFC;
        border-bottom: 1px solid var(--bd);
        white-space: nowrap;
    }
    .dark .oh-table th {
        background: rgba(30, 41, 59, 0.4);
        border-color: #1e293b;
        color: var(--fa);
    }
    .oh-table td {
        padding: 10px 12px;
        font-size: 12.5px;
        border-bottom: 1px solid var(--bd2);
        vertical-align: middle;
        color: var(--tx);
    }
    .dark .oh-table td {
        border-color: #1e293b;
        color: var(--su, #cbd5e1);
    }
    .oh-table tbody tr:hover {
        background: #FAFCFF;
    }
    .dark .oh-table tbody tr:hover {
        background: rgba(30, 41, 59, 0.1);
    }
    .oh-ncc-sel {
        height: 32px;
        padding: 0 24px 0 9px;
        background: var(--wh);
        border: 1.5px solid var(--bd);
        border-radius: 7px;
        font-size: 12px;
        color: var(--tx);
        cursor: pointer;
        outline: none;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath d='M2 3.5l3 3 3-3' stroke='%2394A3B8' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 7px center !important;
        transition: .13s;
        min-width: 130px;
    }
    .dark .oh-ncc-sel {
        background: #1e293b;
        border-color: #334155;
        color: #fff;
    }

    /* summary sidebar */
    .oh-sum {
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: var(--r);
        padding: 16px;
        box-shadow: var(--sh2);
        margin-bottom: 12px;
    }
    .dark .oh-sum {
        background: #0f172a;
        border-color: #1e293b;
    }
    .oh-sum-ttl {
        font-size: 13px;
        font-weight: 700;
        color: var(--tx);
        margin-bottom: 10px;
        padding-bottom: 9px;
        border-bottom: 1px solid var(--bd2);
    }
    .dark .oh-sum-ttl {
        color: #fff;
        border-color: #1e293b;
    }
    .oh-sum-row {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        border-bottom: 1px solid var(--bd2);
        font-size: 12px;
    }
    .dark .oh-sum-row {
        border-color: #1e293b;
    }
    .oh-sum-row:last-child {
        border-bottom: none;
    }
    .oh-sum-k { color: var(--mu); }
    .oh-sum-v { font-weight: 600; color: var(--tx); }
    .dark .oh-sum-v { color: #fff; }
    .oh-ncc-chip {
        display: flex;
        align-items: center;
        gap: 7px;
        background: #F8FAFC;
        border: 1px solid var(--bd);
        border-radius: 9px;
        padding: 8px 10px;
        margin-bottom: 7px;
    }
    .dark .oh-ncc-chip {
        background: rgba(30, 41, 59, 0.3);
        border-color: #1e293b;
    }
    .oh-ncc-chip-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .oh-ncc-chip-name {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--tx);
    }
    .dark .oh-ncc-chip-name { color: #fff; }
    .oh-ncc-chip-cnt { font-size: 11.5px; color: var(--mu); }
    .oh-ncc-chip-val {
        margin-left: auto;
        font-size: 12.5px;
        font-weight: 700;
        color: var(--bl);
    }
    
    .loai-badge {
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }
    .loai-thit { background: var(--rd-s); color: var(--rd); }
    .loai-uot { background: var(--bl-s); color: var(--bl); }
    .loai-kho { background: var(--am-s); color: var(--am); }

    /* Nền sáng hardcode chói trên nền tối — hạ về nền mờ alpha + chữ sáng */
    :root.dark .loai-thit { background: rgba(220, 38, 38, .18); color: #F87171; }
    :root.dark .loai-uot { background: rgba(38, 125, 193, .18); color: #93c5fd; }
    :root.dark .loai-kho { background: rgba(217, 119, 6, .18); color: #fbbf24; }

    .lhn-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 50px 20px;
        text-align: center;
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: var(--r);
        box-shadow: var(--sh2);
        margin-top: 12px;
    }
    .lhn-empty-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--bg);
        border: 1px solid var(--bd);
        display: grid;
        place-items: center;
        margin-bottom: 12px;
    }
    .lhn-empty h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--tx);
        margin: 0 0 6px 0;
    }
    .lhn-empty p {
        font-size: 13px;
        color: var(--mu);
        margin: 0;
        max-width: 420px;
        line-height: 1.5;
    }
</style>
@endpush

<x-filament-panels::page>
    @if($mode === 'list')
        {{-- In nhanh: chỉ hiện vùng danh sách (#lh-print-area), ẩn sidebar/khung Filament --}}
        <style>
            @media print {
                body * { visibility: hidden !important; }
                #lh-print-area, #lh-print-area * { visibility: visible !important; }
                #lh-print-area { position: absolute; top: 0; left: 0; width: 100%; background: #fff; padding: 8mm; }
                #lh-print-area .lhn-datebar, #lh-print-area button { display: none !important; }
            }
        </style>
        <!-- LIST HÀNG VIEW (MẪU ẢNH 1 & 2) -->
        <div class="lhn-root w-full" id="lh-print-area">
            <!-- Header bar -->
            <div class="lhn-page-head">
                <div>
                    <h1>{{ __('list_hang.page_heading') }}</h1>
                    <p style="font-size:12.5px;color:var(--mu);margin:0">{{ __('list_hang.subtitle') }}</p>
                </div>
                <div class="lhn-page-actions">
                    <button type="button" wire:click="exportList" class="lhn-head-btn">
                        <i class="fa-solid fa-file-excel" style="color:var(--gn)"></i>
                        <span>{{ __('list_hang.actions.export') }}</span>
                    </button>
                    @if(\App\Filament\Resources\PurchaseOrderResource::canCreate())
                        <button type="button" wire:click="goOrderCreate" class="lhn-head-btn lhn-head-btn-primary">
                            <i class="fa-solid fa-cart-plus"></i>
                            <span>{{ __('list_hang.actions.create_po') }}</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Date and shift filter bar -->
            <div class="lhn-datebar">
                <button type="button" class="lhn-nav-btn" wire:click="changeDay(-1)">&#8249;</button>
                <input type="date" class="lhn-date-inp" wire:model.live="date">
                <button type="button" class="lhn-nav-btn" wire:click="changeDay(1)">&#8250;</button>
                <button type="button" class="lhn-today-btn" wire:click="goToday">{{ __('list_hang.actions.today') }}</button>

                <!-- Week view sync -->
                <div style="display:flex;align-items:center;gap:6px;font-size:12.5px;color:var(--mu);padding:0 4px">
                    <span style="font-weight:600">{{ __('list_hang.filters.week') }}:</span>
                    <input type="date" wire:model.live="weekFrom" style="border:1px solid var(--bd);border-radius:6px;padding:3px 8px;font-size:12px;outline:none" class="dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                    <span>–</span>
                    <input type="date" wire:model.live="weekTo" style="border:1px solid var(--bd);border-radius:6px;padding:3px 8px;font-size:12px;outline:none" class="dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                </div>

                <!-- Shift checkboxes styled as badges -->
                <div style="display:flex;align-items:center;gap:5px;font-size:12px;background:var(--bg);border:1px solid var(--bd);border-radius:8px;padding:4px 10px;" class="dark:bg-gray-800 dark:border-gray-700">
                    <span style="font-weight:600;color:var(--mu)">Ca:</span>
                    @foreach($this->getShiftsList() as $index => $sh)
                        @php 
                            $bg = ['#EFF6FF', '#F0FDF4', '#FEF3C7', '#F5F3FF'][$index % 4];
                            $color = ['#1e40af', '#065F46', '#78350F', '#4C1D95'][$index % 4];
                            $border = ['#A8CBE6', '#A7F3D0', '#FDE68A', '#DDD6FE'][$index % 4];
                            $isChecked = in_array($sh->id, $selectedShifts);
                        @endphp
                        <label style="display:flex;align-items:center;gap:4px;background:{{ $bg }};color:{{ $color }};border:1px solid {{ $border }};border-radius:20px;padding:2px 8px;font-size:11.5px;font-weight:600;cursor:pointer; @if(!$isChecked) opacity: 0.45; @endif">
                            <input type="checkbox" value="{{ $sh->id }}" wire:model.live="selectedShifts" style="width:12px;height:12px; border-radius:3px;"> {{ $sh->name }}
                        </label>
                    @endforeach
                </div>

                <div style="margin-left:auto;display:flex;align-items:center;gap:8px">
                    <span style="font-size:13.5px;font-weight:700;color:var(--tx)" class="dark:text-white">
                        @php
                            $dt = \Carbon\Carbon::parse($date)->locale(app()->getLocale());
                            $dowName = mb_strtoupper($dt->dayName);
                            $selDt = \Carbon\Carbon::parse($date);
                            $wFrom = $weekFrom ? \Carbon\Carbon::parse($weekFrom) : null;
                            $wTo = $weekTo ? \Carbon\Carbon::parse($weekTo) : null;
                            $inPeriod = ($wFrom && $wTo) ? ($selDt->gte($wFrom) && $selDt->lte($wTo)) : true;
                        @endphp
                        {{ $dowName }} – {{ $dt->format('d/m/Y') }}
                    </span>
                    @if ($inPeriod)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-800">{{ __('list_hang.filters.in_period') }}</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:border-amber-800">{{ __('list_hang.filters.out_of_period') }}</span>
                    @endif
                </div>
            </div>

            <!-- Stats Row -->
            @php $stats = $this->getStats(); @endphp
            <div class="lhn-chips">
                <div class="lhn-chip">
                    <div class="lhn-chip-ico" style="background:#FEF2F2; color:#DC2626;"><i class="fa-solid fa-calendar-day"></i></div>
                    <div><div class="lhn-chip-val">{{ $stats['shifts'] }}</div><div class="lhn-chip-lbl">{{ __('list_hang.stats.shifts') }}</div></div>
                </div>
                <div class="lhn-chip">
                    <div class="lhn-chip-ico" style="background:var(--bl-s); color:var(--bl);"><i class="fa-solid fa-users"></i></div>
                    <div><div class="lhn-chip-val">{{ number_format($stats['portions']) }}</div><div class="lhn-chip-lbl">{{ __('list_hang.stats.portions') }}</div></div>
                </div>
                <div class="lhn-chip">
                    <div class="lhn-chip-ico" style="background:#FFF7ED; color:#EA580C;"><i class="fa-solid fa-bowl-food"></i></div>
                    <div><div class="lhn-chip-val">{{ $stats['dishes'] }}</div><div class="lhn-chip-lbl">{{ __('list_hang.stats.dishes') }}</div></div>
                </div>
                <div class="lhn-chip">
                    <div class="lhn-chip-ico" style="background:#ECFDF5; color:#059669;"><i class="fa-solid fa-leaf"></i></div>
                    <div><div class="lhn-chip-val">{{ $stats['ingredients'] }}</div><div class="lhn-chip-lbl">{{ __('list_hang.stats.ingredients') }}</div></div>
                </div>
            </div>

            <!-- Main Grouped List per Shift -->
            @php
                $listPaginator = $this->getGroupedDataPaginator();
                $groupedData = $listPaginator->getCollection()
                    ->groupBy(fn (array $row): int => $row['shift']['id'])
                    ->map(function ($rows): array {
                        $shift = $rows->first()['shift'];
                        $shift['dishes'] = $rows->pluck('dish')->all();

                        return $shift;
                    })
                    ->values();
            @endphp
            @forelse($groupedData as $shiftData)
                <div class="lhn-ca-card" x-data="{ open: true }">
                    <div class="lhn-ca-head" @click="open = !open">
                        <div class="lhn-ca-title">
                            <span class="lhn-ca-badge bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200">{{ $shiftData['name'] }}</span>
                            <span class="text-xs text-gray-500 font-semibold">({{ $shiftData['time_range'] }})</span>
                        </div>
                        <div class="lhn-ca-meta">
                            <span class="font-bold text-gray-900 dark:text-white">{{ __('list_hang.counts.portions', ['count' => number_format($shiftData['total_portions'])]) }}</span>
                            <span class="text-gray-400">·</span>
                            <span class="text-gray-600 dark:text-gray-300">{{ __('list_hang.counts.dishes', ['count' => $shiftData['total_dishes']]) }}</span>
                            <i class="fa-solid fa-chevron-down text-gray-400 transition" :class="open ? 'transform rotate-180' : ''"></i>
                        </div>
                    </div>

                    <div class="lhn-ca-body" x-show="open" x-collapse>
                        @foreach($shiftData['dishes'] as $dish)
                            <div class="lhn-mon-row" x-data="{ expanded: false }">
                                <div class="lhn-mon-head" :class="expanded ? 'open' : ''" @click="expanded = !expanded">
                                    <div class="lhn-mon-left">
                                        <span class="lhn-mon-ico">🍲</span>
                                        <span class="lhn-mon-name">{{ $dish['name'] }}</span>
                                        <span class="lhn-mon-type">{{ $dish['type'] }}</span>
                                    </div>
                                    <div class="lhn-mon-right">
                                        <span class="lhn-mon-suat">{{ __('list_hang.counts.portions', ['count' => number_format($dish['portions'])]) }}</span>
                                        <span class="text-gray-400">·</span>
                                        <span>{{ count($dish['ingredients']) }} NL</span>
                                        <i class="fa-solid fa-chevron-down lhn-expand-ico transition" :class="expanded ? 'transform rotate-180' : ''"></i>
                                    </div>
                                </div>

                                <!-- Expanded ingredients details (Ảnh 2) -->
                                <div x-show="expanded" x-collapse class="border-t border-gray-150 bg-gray-50/10 dark:bg-gray-900/50">
                                    <table class="lhn-ing-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px; text-align:center;">#</th>
                                                <th>{{ __('list_hang.table.ingredient') }}</th>
                                                <th style="text-align: right; width: 140px;">{{ __('list_hang.table.portions') }}</th>
                                                <th style="text-align: right; width: 140px;">{{ __('list_hang.table.quantity_g') }}</th>
                                                <th style="text-align: right; width: 180px;">{{ __('list_hang.table.quantity_kg') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dish['ingredients'] as $iIdx => $ing)
                                                <tr>
                                                    <td style="text-align: center; color: var(--fa);">{{ $iIdx + 1 }}</td>
                                                    <td style="font-weight: 700;" class="dark:text-white">
                                                        <div>{{ $ing['name'] }}</div>
                                                        <div style="font-size:10px; color:var(--fa); font-weight:500;">{{ $ing['code'] }}</div>
                                                    </td>
                                                    <td style="text-align: right;">{{ number_format($dish['portions']) }}</td>
                                                    <td style="text-align: right; color:var(--mu);">{{ number_format($ing['quantity_per_portion'] * 1000, 0, ',', '.') }} g</td>
                                                    <td style="text-align: right; font-weight: 750;" class="lhn-kg">{{ number_format($ing['quantity'], 3, ',', '.') }} kg</td>
                                                </tr>
                                            @endforeach
                                            <tr class="lhn-total-row">
                                                <td colspan="4" style="text-align: right; padding: 10px 12px; font-weight: 800; color:var(--bl);">{{ __('list_hang.table.dish_total', ['dish' => $dish['name']]) }}:</td>
                                                <td style="text-align: right; padding: 10px 12px; font-weight: 850; color:var(--bl);">{{ __('list_hang.counts.portions', ['count' => number_format($dish['portions'])]) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="lhn-empty">
                    <div class="lhn-empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:28px;height:28px;color:var(--fa);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3>{{ __('list_hang.empty.no_menu') }}</h3>
                    <p>{{ __('list_hang.empty.no_menu_description') }}</p>
                </div>
            @endforelse

            @if($listPaginator->total() > 0)
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-top:14px; padding:14px 16px; border-top:1px solid var(--bd); font-size:12px; color:var(--mu);">
                    <div>
                        {{ __('list_hang.pagination.summary', [
                            'from' => $listPaginator->firstItem() ?? 0,
                            'to' => $listPaginator->lastItem() ?? 0,
                            'total' => $listPaginator->total(),
                        ]) }}
                    </div>
                    <div class="pgwrap">
                        <span>{{ __('common.pagination.per_page_label') }}</span>
                        <select wire:model.live="listPerPage" class="lv-per-page-select">
                            @foreach([5, 10, 20, 50] as $count)
                                <option value="{{ $count }}">{{ $count }}</option>
                            @endforeach
                        </select>

                        @if($listPaginator->total() > 0)
                            <nav role="navigation" aria-label="{{ __('list_hang.pagination.navigation') }}" style="display:flex; align-items:center; gap:4px;">
                                @if ($listPaginator->onFirstPage())
                                    <span aria-disabled="true" style="opacity:.4; padding:4px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="15 18 9 12 15 6"/></svg>
                                    </span>
                                @else
                                    <button type="button" wire:click="previousPage('listPage')" rel="prev" style="display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; border:1px solid var(--bd); border-radius:6px; background:transparent; cursor:pointer;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="15 18 9 12 15 6"/></svg>
                                    </button>
                                @endif

                                @php
                                    $listCurrentPage = $listPaginator->currentPage();
                                    $listLastPage = $listPaginator->lastPage();
                                    $listPageWindow = collect([1, $listCurrentPage - 1, $listCurrentPage, $listCurrentPage + 1, $listLastPage])
                                        ->filter(fn ($page) => $page >= 1 && $page <= $listLastPage)
                                        ->unique()
                                        ->sort()
                                        ->values();
                                @endphp
                                @foreach ($listPageWindow as $index => $page)
                                    @if ($index > 0 && $page - $listPageWindow[$index - 1] > 1)
                                        <span aria-hidden="true" style="padding:0 4px">…</span>
                                    @endif
                                    @if ($page === $listCurrentPage)
                                        <span aria-current="page" style="display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; border-radius:6px; background:var(--bl); color:#fff; font-weight:700;">{{ $page }}</span>
                                    @else
                                        <button type="button" wire:click="gotoPage({{ $page }}, 'listPage')" style="display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; border:1px solid var(--bd); border-radius:6px; background:transparent; color:var(--tx); cursor:pointer;">{{ $page }}</button>
                                    @endif
                                @endforeach

                                @if($listPaginator->hasMorePages())
                                    <button type="button" wire:click="nextPage('listPage')" rel="next" style="display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; border:1px solid var(--bd); border-radius:6px; background:transparent; cursor:pointer;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="9 18 15 12 9 6"/></svg>
                                    </button>
                                @else
                                    <span aria-disabled="true" style="opacity:.4; padding:4px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="9 18 15 12 9 6"/></svg>
                                    </span>
                                @endif
                            </nav>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @elseif($mode === 'create_po')
        <!-- TẠO ĐƠN ĐẶT HÀNG WIZARD (MẪU ẢNH 3) -->
        <div style="display: flex; gap: 1rem; overflow: hidden; height: calc(100vh - 160px);">
            <!-- Left Workspace -->
            <div style="flex: 1; overflow-y: auto; padding-right: 8px;">
                <!-- Header -->
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h1 style="font-size:20px; font-weight:800; margin:0;" class="dark:text-white">{{ __('list_hang.po.title') }}</h1>
                        <p style="font-size: 12px; color: var(--mu); margin-top:2px;">{{ __('list_hang.po.subtitle') }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="lhn-act-btn" wire:click="goBackToList">
                            <i class="fa-solid fa-arrow-left"></i>{{ __('list_hang.actions.back') }}
                        </button>
                        <button type="button" class="lhn-act-btn lhn-act-btn-primary" wire:click="createOrders" wire:loading.attr="disabled" wire:target="createOrders">
                            <i class="fa-solid fa-paper-plane" wire:loading.remove wire:target="createOrders"></i>
                            <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="createOrders"></i>
                            {{ __('list_hang.actions.create_send') }}
                        </button>
                    </div>
                </div>

                <!-- Step progress wizard -->
                <div class="oh-steps">
                    <div class="oh-step oh-step-done">
                        <div class="oh-step-num"><i class="fa-solid fa-check" style="font-size:11px"></i></div>
                        <div>
                            <div class="oh-step-lbl">{{ __('list_hang.po.steps.select_list') }}</div>
                            <div style="font-size:11px; color:var(--mu);">
                                {{ \Carbon\Carbon::parse($poSourceFrom)->format('d/m') }} - {{ \Carbon\Carbon::parse($poSourceTo)->format('d/m') }}
                            </div>
                        </div>
                    </div>
                    <div class="oh-step-line done"></div>
                    <div class="oh-step oh-step-active">
                        <div class="oh-step-num">2</div>
                        <div>
                            <div class="oh-step-lbl">{{ __('list_hang.po.steps.assign_supplier') }}</div>
                            <div style="font-size:11px; color:var(--mu);">{{ __('list_hang.po.steps.assign_supplier_description') }}</div>
                        </div>
                    </div>
                    <div class="oh-step-line"></div>
                    <div class="oh-step oh-step-pending">
                        <div class="oh-step-num">3</div>
                        <div>
                            <div class="oh-step-lbl">{{ __('list_hang.po.steps.create') }}</div>
                            <div style="font-size:11px; color:var(--mu);">{{ __('list_hang.po.steps.create_description') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Date source selectors -->
                <div style="background:var(--wh); border:1px solid var(--bd); border-radius:var(--r); padding:14px 18px; box-shadow:var(--sh2); margin-bottom:14px; display:flex; align-items:flex-end; gap:14px; flex-wrap:wrap;" class="dark:bg-gray-900 dark:border-gray-800">
                    <div class="form-field" style="min-width:160px">
                        <label>{{ __('list_hang.po.fields.order_date') }}</label>
                        <input type="date" wire:model.live="poDate" min="{{ today()->toDateString() }}" max="{{ today()->addDays(2)->toDateString() }}">
                    </div>
                    <div class="form-field" style="min-width:160px">
                        <label>{{ __('list_hang.po.fields.source_from') }}</label>
                        <input type="date" wire:model.live="poSourceFrom">
                    </div>
                    <div class="form-field" style="min-width:160px">
                        <label>{{ __('list_hang.po.fields.source_to') }}</label>
                        <input type="date" wire:model.live="poSourceTo">
                    </div>
                    <div class="form-field" style="min-width:240px">
                        <label>{{ __('list_hang.po.fields.shift') }}</label>
                        <div style="display:flex; gap:6px; flex-wrap:wrap; background:var(--bg); border:1px solid var(--bd); border-radius:8px; padding:7px 9px; min-height:38px;" class="dark:bg-gray-800 dark:border-gray-700">
                            @foreach($this->getShiftsList() as $sh)
                                <label style="font-size:11px; font-weight:700; color:var(--bl); display:flex; align-items:center; gap:4px; cursor:pointer;">
                                    <input type="checkbox" value="{{ $sh->id }}" wire:model.live="poSelectedShifts" style="border-radius:3px;">
                                    <span>{{ $sh->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Main grouped tables -->
                @if(empty($poItems))
                    <div class="lhn-empty">
                        <div class="lhn-empty-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:28px;height:28px;color:var(--fa);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <h3>{{ __('list_hang.empty.no_order_items') }}</h3>
                        <p>{{ __('list_hang.empty.no_order_items_description') }}</p>
                    </div>
                @else
                    @php 
                        $categories = [
                            'thit' => ['label' => __('list_hang.categories.meat'), 'icon' => '🍖', 'class' => 'loai-thit'],
                            'uot' => ['label' => __('list_hang.categories.produce'), 'icon' => '🥬', 'class' => 'loai-uot'],
                            'kho' => ['label' => __('list_hang.categories.dry'), 'icon' => '🧂', 'class' => 'loai-kho']
                        ];
                        $suppliers = $this->getActiveSuppliers();
                    @endphp

                    @foreach($categories as $key => $cat)
                        @php 
                            $catItems = collect($poItems)->where('loai', $key);
                        @endphp
                        @if($catItems->isNotEmpty())
                            <div class="oh-group-card">
                                <div class="oh-group-head">
                                    <div class="oh-group-title">
                                        <span class="loai-badge {{ $cat['class'] }}">{{ $cat['icon'] }} {{ $cat['label'] }}</span>
                                        <span style="font-size:12px; color:var(--mu); font-weight:400;">{{ __('list_hang.counts.ingredients', ['count' => $catItems->count()]) }}</span>
                                    </div>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <span style="font-size:12px; color:var(--mu);">{{ __('list_hang.po.quick_supplier') }}:</span>
                                        <select class="oh-ncc-sel" onchange="@this.bulkAssignSupplier('{{ $key }}', this.value)" style="height:28px; padding-top:2px;">
                                            <option value="">{{ __('list_hang.po.select_supplier') }}</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="oh-table-wrap">
                                <table class="oh-table">
                                    <thead>
                                        <tr>
                                            <th style="width:36px; text-align:center;">#</th>
                                            <th style="width:50px; text-align:center;">{{ __('list_hang.table.order') }}</th>
                                            <th>{{ __('list_hang.table.ingredient_name') }}</th>
                                            <th>{{ __('list_hang.table.dishes') }}</th>
                                            <th style="text-align:right;">{{ __('list_hang.table.portions') }}</th>
                                            <th style="text-align:right;">{{ __('list_hang.table.demand') }}</th>
                                            <th style="text-align:right;">{{ __('list_hang.table.stock') }}</th>
                                            <th style="text-align:right; width:110px;">{{ __('list_hang.table.manual_quantity') }}</th>
                                            <th style="text-align:right;">{{ __('list_hang.table.unit_price') }}</th>
                                            <th style="text-align:right; width:130px;">{{ __('list_hang.table.total') }}</th>
                                            <th style="width:140px;">{{ __('list_hang.table.supplier') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($poItems as $index => $item)
                                            @if($item['loai'] === $key)
                                                <tr>
                                                    <td style="text-align:center; color:var(--fa);">{{ $index + 1 }}</td>
                                                    <td style="text-align:center;">
                                                        <input type="checkbox" wire:model.live="poItems.{{ $index }}.checked">
                                                    </td>
                                                    <td style="font-weight:700;">
                                                        <div class="dark:text-white">{{ $item['name'] }}</div>
                                                        @if(!empty($item['ordered_info']['total']))
                                                            <div style="margin-top:4px; display:flex; gap:4px; flex-wrap:wrap;">
                                                                @foreach($item['ordered_info']['codes'] as $orderCode)
                                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold whitespace-nowrap bg-amber-100 text-amber-800 border border-amber-250">{{ __('list_hang.po.ordered_code', ['code' => $orderCode]) }}</span>
                                                                @endforeach
                                                                @if($item['ordered_info']['total'] > count($item['ordered_info']['codes']))
                                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold whitespace-nowrap bg-slate-100 text-slate-600 border border-slate-200" title="{{ __('list_hang.po.ordered_title', ['count' => $item['ordered_info']['total']]) }}">{{ __('list_hang.po.other_orders', ['count' => $item['ordered_info']['total'] - count($item['ordered_info']['codes'])]) }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td style="font-size:11px; color:var(--mu);">
                                                        {{ implode(', ', $item['dishes']) }}
                                                    </td>
                                                    <td style="text-align:right;">{{ number_format($item['total_suat']) }}</td>
                                                    <td style="text-align:right; font-weight:600;">{{ number_format($item['total_kg'], 3, ',', '.') }} {{ $item['unit'] }}</td>
                                                    {{-- Tồn kho hiện có: SL đề xuất đã tự trừ phần này để không đặt thừa --}}
                                                    <td style="text-align:right; color:{{ ($item['stock_qty'] ?? 0) > 0 ? 'var(--gn)' : 'var(--fa)' }}; font-weight:600;">
                                                        {{ number_format($item['stock_qty'] ?? 0, 3, ',', '.') }} {{ $item['unit'] }}
                                                    </td>
                                                    <td style="text-align:center;">
                                                        <input type="number" step="0.001" wire:model.blur="poItems.{{ $index }}.quantity_manual" class="table-input" style="height:28px;">
                                                    </td>
                                                    <td style="text-align:right; color:var(--mu);">{{ number_format($item['reference_price'], 0, ',', '.') }} {{ __('list_hang.currency') }}/{{ $item['unit'] }}</td>
                                                    <td style="text-align:right; font-weight:700; color:#ea580c;">
                                                        @if($item['checked'])
                                                            {{ number_format($item['quantity_manual'] * $item['reference_price'], 0, ',', '.') }} {{ __('list_hang.currency') }}
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <select wire:model.live="poItems.{{ $index }}.supplier_id" style="height:28px; font-size:11px; padding:2px; border-radius:6px; width:100%; border:{{ ($item['checked'] && empty($item['supplier_id'])) ? '1.5px solid #DC2626; background-color:#FEF2F2; color:#DC2626;' : '1px solid var(--su, #cbd5e1);' }}">
                                                            <option value="">{{ __('list_hang.po.select_supplier') }}</option>
                                                            @foreach($suppliers as $supplier)
                                                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @if($item['checked'] && empty($item['supplier_id']))
                                                            <div style="font-size:10px; font-weight:700; color:#DC2626; margin-top:2px; display:flex; align-items:center; gap:2px">
                                                                <i class="fa-solid fa-circle-exclamation"></i> Bắt buộc chọn NCC!
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach

                                        <!-- Group Total Row -->
                                        @php 
                                            $groupTotal = collect($poItems)
                                                ->where('loai', $key)
                                                ->filter(fn($it) => $it['checked'])
                                                ->sum(fn($it) => $it['quantity_manual'] * $it['reference_price']);
                                        @endphp
                                        <tr style="background:#F8FAFC" class="dark:bg-gray-800/40">
                                            <td colspan="9" style="text-align:right; font-weight:700; color:var(--mu);">{{ __('list_hang.po.group_total', ['group' => $cat['label']]) }}:</td>
                                            <td colspan="3" style="font-weight:800; color:var(--bl); font-size:13px;">{{ number_format($groupTotal, 0, ',', '.') }} {{ __('list_hang.currency') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    <!-- Grand Total Bar -->
                    @php 
                        $grandTotal = collect($poItems)
                            ->filter(fn($it) => $it['checked'])
                            ->sum(fn($it) => $it['quantity_manual'] * $it['reference_price']);
                        
                        $checkedItemsCount = collect($poItems)->filter(fn($it) => $it['checked'])->count();
                    @endphp
                    <div class="lhn-grand" style="margin-top:4px">
                        <div>
                            <div class="lhn-grand-lbl">{{ __('list_hang.po.grand_total', ['date' => \Carbon\Carbon::parse($poDate)->format('d/m/Y')]) }}</div>
                            <div style="font-size:11px; opacity:.85; margin-top:3px">
                                {{ __('list_hang.po.selection_summary', ['suppliers' => collect($poItems)->filter(fn($it) => $it['checked'])->pluck('supplier_id')->unique()->count(), 'selected' => $checkedItemsCount, 'total' => count($poItems)]) }}
                            </div>
                        </div>
                        <div class="lhn-grand-val">{{ number_format($grandTotal, 0, ',', '.') }} {{ __('list_hang.currency') }}</div>
                    </div>
                @endif
            </div>

            <!-- Right Sidebar Column -->
            <div style="width: 280px; flex-shrink: 0;">
                <!-- Summary Card -->
                <div class="oh-sum">
                    <div class="oh-sum-ttl">{{ __('list_hang.summary.title') }}</div>
                    @php 
                        $totalOrderedKg = collect($poItems)->filter(fn($it) => $it['checked'])->sum('quantity_manual');
                        $suppliersCount = collect($poItems)->filter(fn($it) => $it['checked'])->pluck('supplier_id')->unique()->count();
                        $grandVal = collect($poItems)->filter(fn($it) => $it['checked'])->sum(fn($it) => $it['quantity_manual'] * $it['reference_price']);
                    @endphp
                    <div class="oh-sum-row"><span class="oh-sum-k">{{ __('list_hang.summary.scope') }}</span><span class="oh-sum-v">{{ __('list_hang.summary.day') }}</span></div>
                    <div class="oh-sum-row"><span class="oh-sum-k">{{ __('list_hang.summary.order_date') }}</span><span class="oh-sum-v">{{ \Carbon\Carbon::parse($poDate)->format('d/m/Y') }}</span></div>
                    <div class="oh-sum-row"><span class="oh-sum-k">{{ __('list_hang.summary.source') }}</span><span class="oh-sum-v">{{ \Carbon\Carbon::parse($poSourceFrom)->format('d/m') }} - {{ \Carbon\Carbon::parse($poSourceTo)->format('d/m') }}</span></div>
                    <div class="oh-sum-row"><span class="oh-sum-k">Ca</span><span class="oh-sum-v">{{ count($poSelectedShifts) }} ca</span></div>
                    <div class="oh-sum-row"><span class="oh-sum-k">{{ __('list_hang.summary.suppliers') }}</span><span class="oh-sum-v">{{ $suppliersCount }} NCC</span></div>
                    <div class="oh-sum-row"><span class="oh-sum-k">{{ __('list_hang.summary.ingredients') }}</span><span class="oh-sum-v">{{ __('list_hang.summary.ingredient_types', ['selected' => collect($poItems)->filter(fn($it) => $it['checked'])->count(), 'total' => count($poItems)]) }}</span></div>
                    <div class="oh-sum-row"><span class="oh-sum-k">{{ __('list_hang.summary.value') }}</span><span class="oh-sum-v" style="color:var(--bl); font-weight:800; font-size:13.5px;">{{ number_format($grandVal, 0, ',', '.') }} {{ __('list_hang.currency') }}</span></div>
                </div>

                <!-- NCC Distribution Card -->
                <div class="oh-sum">
                    <div class="oh-sum-ttl">{{ __('list_hang.summary.by_supplier') }}</div>
                    @php
                        $byNcc = collect($poItems)->filter(fn($it) => $it['checked'])->groupBy('supplier_id');
                    @endphp
                    
                    @forelse($byNcc as $supId => $items)
                        @php 
                            $ncc = $this->getAllSuppliers()->get($supId);
                            $total = $items->sum(fn($it) => $it['quantity_manual'] * $it['reference_price']);
                        @endphp
                        @if($ncc)
                            <div class="oh-ncc-chip">
                                <div class="oh-ncc-chip-dot" style="background:var(--bl);"></div>
                                <div>
                                    <div class="oh-ncc-chip-name">{{ $ncc->code ?: 'NCC' }}</div>
                                    <div class="oh-ncc-chip-cnt" style="font-size:9.5px;">{{ __('list_hang.counts.items', ['count' => $items->count()]) }}</div>
                                </div>
                                <div class="oh-ncc-chip-val">{{ number_format($total, 0, ',', '.') }}d</div>
                            </div>
                        @endif
                    @empty
                        <div style="font-size:11px; color:var(--fa); font-style:italic;">{{ __('list_hang.empty.no_allocation') }}</div>
                    @endforelse

                </div>

                <!-- Info Box -->
                <div style="background:var(--bl-s); color:var(--bl); border: 1px solid #A8CBE6; border-radius:12px; padding:12px; font-size:11.5px; line-height:1.4;">
                    <div style="font-weight:800; display:flex; align-items:center; gap:4px; margin-bottom:6px;">
                        <i class="fa-solid fa-circle-info"></i> {{ __('list_hang.notes.title') }}
                    </div>
                    <ul style="list-style-type: disc; padding-left: 14px; display:grid; gap:4px;">
                        <li>{{ __('list_hang.notes.separate_orders') }}</li>
                        <li>{{ __('list_hang.notes.optional_items') }}</li>
                        <li>{{ __('list_hang.notes.manual_priority') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
