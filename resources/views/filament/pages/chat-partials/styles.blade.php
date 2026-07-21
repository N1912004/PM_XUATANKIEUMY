<style>
    :root {
        --bl: {{ \App\Models\Setting::get('primary_color', '#267DC1') }};
        --bl-d: {{ \App\Models\Setting::get('primary_color', '#267DC1') }};
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
        --pk: #E11D48;
        --pk-s: #FFF1F2;
        --bg: #F8FAFC;
        --wh: #fff;
        --tx: #0F172A;
        --su: #334155;
        --mu: #64748B;
        --fa: #94A3B8;
        --bd: #E2E8F0;
        --bd2: #F1F5F9;
        --sh: 0 1px 3px rgba(15,23,42,.05), 0 4px 16px rgba(15,23,42,.05);
        --sh2: 0 1px 2px rgba(15,23,42,.04);
        --r: 12px;
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

    .dark .cm {
        background: #0f172a !important;
    }

    .dark .mi-box:focus-within {
        background: var(--wh) !important;
        border-color: #3b82f6 !important;
    }

    .dark .mi-att:hover,
    .dark .cm-ico:hover {
        background: #334155 !important;
    }

    .chat-page {
        font-family: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: var(--tx);
        background: transparent;
        height: calc(100vh - 11rem);
        display: flex;
        flex-direction: column;
    }

    .chat-root {
        flex: 1;
        display: flex;
        overflow: hidden;
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: var(--r);
        box-shadow: var(--sh2);
    }

    /* LEFT PANEL */
    .cl {
        width: 320px;
        flex-shrink: 0;
        border-right: 1px solid var(--bd);
        display: flex;
        flex-direction: column;
        background: var(--wh);
        overflow: hidden;
    }

    .cl-head {
        padding: 16px 16px 10px;
        flex-shrink: 0;
    }

    .cl-head h1 {
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -.02em;
        margin-bottom: 4px;
        color: var(--tx);
    }

    .cl-head p {
        font-size: 11.5px;
        color: var(--mu);
        line-height: 1.5;
    }

    /* Group search */
    .grp-srch {
        padding: 10px 16px;
        border-bottom: 1px solid var(--bd2);
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .gsbox {
        flex: 1;
        height: 34px;
        border: 1px solid var(--po-bd);
        border-radius: 8px;
        background: var(--bg);
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0 10px;
        transition: .13s;
    }

    .gsbox:focus-within {
        border-color: #93C5FD;
        background: #wh;
    }

    .gsbox i {
        color: var(--fa);
        font-size: 12px;
        flex-shrink: 0;
    }

    .gsbox input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 12.5px;
        color: var(--tx);
        width: 100%;
        box-shadow: none !important;
        padding: 0;
    }

    .filt-ico {
        width: 34px;
        height: 34px;
        border: 1px solid var(--bd);
        border-radius: 8px;
        background: var(--wh);
        display: grid;
        place-items: center;
        cursor: pointer;
        font-size: 13px;
        color: var(--mu);
        flex-shrink: 0;
        transition: .12s;
    }

    .filt-ico:hover {
        background: var(--bg);
        color: var(--bl);
    }

    /* Group List */
    .grp-list {
        flex: 1;
        overflow-y: auto;
    }

    .grp-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        cursor: pointer;
        transition: .12s;
        border-bottom: 1px solid var(--bd2);
    }

    .grp-item:last-child {
        border-bottom: none;
    }

    .grp-item:hover {
        background: #F8FAFC;
    }

    .grp-item.sel {
        background: var(--bl-s);
    }

    .g-av {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .g-av-b { background: #E9F2F8; color: var(--bl); }
    .g-av-o { background: #FFF7ED; color: #EA580C; }
    .g-av-g { background: #ECFDF5; color: #059669; }
    .g-av-p { background: #F5F3FF; color: #7C3AED; }
    .g-av-k { background: #FFFBEB; color: #D97706; }

    .g-info {
        flex: 1;
        min-width: 0;
    }

    .g-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--tx);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .grp-item.sel .g-name {
        color: var(--bl);
    }

    .g-desc {
        font-size: 11px;
        color: var(--mu);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 1px;
    }

    .g-meta {
        font-size: 10px;
        color: var(--fa);
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .g-dot {
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: var(--fa);
    }

    .g-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
        flex-shrink: 0;
    }

    .g-badge {
        background: var(--bl);
        color: #fff;
        font-size: 9px;
        font-weight: 800;
        min-width: 18px;
        height: 18px;
        padding: 0 4px;
        border-radius: 9px;
        display: grid;
        place-items: center;
    }

    /* MIDDLE PANEL (CHAT AREA) */
    .cm {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #F8FAFC;
    }

    .cm-head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 16px;
        height: 56px;
        background: var(--wh);
        border-bottom: 1px solid var(--bd);
        flex-shrink: 0;
    }

    .cm-ginfo {
        flex: 1;
        min-width: 0;
    }

    .cm-gname {
        font-size: 14px;
        font-weight: 800;
        color: var(--tx);
    }

    .cm-gmeta {
        font-size: 11px;
        color: var(--mu);
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 1px;
    }

    .cm-onl {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--gn);
        display: inline-block;
    }

    .cm-ico {
        width: 30px;
        height: 30px;
        border: none;
        background: transparent;
        border-radius: 8px;
        cursor: pointer;
        display: grid;
        place-items: center;
        font-size: 13px;
        color: var(--mu);
        transition: .12s;
        flex-shrink: 0;
    }

    .cm-ico:hover {
        background: #F1F5F9;
        color: var(--bl);
    }

    /* Message List */
    .msgs {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .day-sep {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 14px 0;
        color: var(--fa);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .02em;
    }

    .day-sep::before, .day-sep::after {
        content: "";
        flex: 1;
        height: 1px;
        background: var(--bd2);
    }

    .msg-row {
        display: flex;
        gap: 10px;
        margin-bottom: 12px;
        align-items: flex-end;
    }

    .msg-row.me {
        flex-direction: row-reverse;
    }

    .msg-av {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        border: 1.5px solid var(--bd);
    }

    .msg-av img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .msg-body {
        max-width: 66%;
        display: flex;
        flex-direction: column;
        gap: 1px;
    }

    .msg-row.me .msg-body {
        align-items: flex-end;
    }

    .msg-sender {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--mu);
        margin-bottom: 4px;
    }

    .bubble {
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: 12px;
        border-bottom-left-radius: 3px;
        padding: 9px 13px;
        font-size: 13px;
        line-height: 1.55;
        color: var(--tx);
        box-shadow: var(--sh2);
    }

    .msg-row.me .bubble {
        background: linear-gradient(135deg, #1474FF, #0059DD);
        color: #fff;
        border-color: transparent;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 3px;
        box-shadow: 0 4px 12px rgba(38, 125, 193, 0.22);
    }

    .msg-time {
        font-size: 10px;
        color: var(--fa);
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .msg-row.me .msg-time {
        justify-content: flex-end;
    }

    .msg-tick {
        color: var(--bl-m);
    }

    .file-bbl {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: 9px;
        padding: 9px 12px;
        width: 280px;
        cursor: pointer;
        transition: .12s;
        margin-top: 4px;
        box-shadow: var(--sh2);
    }

    .file-bbl:hover {
        background: #F8FAFC;
        border-color: var(--bl-m);
    }

    .fic {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        font-size: 11px;
        font-weight: 800;
        flex-shrink: 0;
    }

    .f-pdf { background: #FEF2F2; color: #DC2626; }
    .f-xls { background: #ECFDF5; color: #059669; }
    .f-doc { background: #E9F2F8; color: var(--bl); }

    .fi-nm {
        font-size: 12px;
        font-weight: 700;
        color: var(--tx);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .fi-sz {
        font-size: 10.5px;
        color: var(--fa);
        margin-top: 1px;
    }

    .img-bbl {
        max-width: 280px;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 4px;
        cursor: pointer;
        border: 1px solid var(--bd);
        box-shadow: var(--sh2);
    }

    .img-bbl img {
        width: 100%;
        display: block;
        object-fit: cover;
    }

    /* Input panel */
    .msg-inp {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: var(--wh);
        border-top: 1px solid var(--bd);
        flex-shrink: 0;
    }

    .mi-att {
        width: 34px;
        height: 34px;
        border: none;
        background: transparent;
        cursor: pointer;
        display: grid;
        place-items: center;
        font-size: 16px;
        color: var(--mu);
        transition: .12s;
        border-radius: 8px;
        flex-shrink: 0;
    }

    .mi-att:hover {
        background: #F1F5F9;
        color: var(--bl);
    }

    .mi-box {
        flex: 1;
        height: 38px;
        border: 1.5px solid var(--bd);
        border-radius: 19px;
        background: var(--bg);
        display: flex;
        align-items: center;
        padding: 0 14px;
        gap: 8px;
        transition: .13s;
    }

    .mi-box:focus-within {
        border-color: #93C5FD;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(38, 125, 193, 0.08);
    }

    .mi-box input {
        flex: 1;
        border: none;
        background: transparent;
        outline: none;
        font-size: 13px;
        color: var(--tx);
        padding: 0;
        box-shadow: none !important;
    }

    .mi-box input::placeholder {
        color: var(--fa);
    }

    .mi-emoji {
        font-size: 18px;
        cursor: pointer;
        user-select: none;
        opacity: .6;
        transition: .12s;
    }

    .mi-emoji:hover {
        opacity: 1;
    }

    .mi-send {
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 50%;
        background: linear-gradient(135deg, #1474FF, #0059DD);
        color: #fff;
        display: grid;
        place-items: center;
        cursor: pointer;
        font-size: 14px;
        transition: .13s;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(38, 125, 193, 0.2);
    }

    .mi-send:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 12px rgba(38, 125, 193, 0.3);
    }

    @media (max-width: 768px) {
        .cl {
            display: none; /* Hide group list on small screens */
        }
    }
</style>
