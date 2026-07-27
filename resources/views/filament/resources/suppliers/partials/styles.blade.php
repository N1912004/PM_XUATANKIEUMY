@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<style>
            :root {
                --sup-bl: #1267E8;
                --sup-bl-d: #1267E8;
                --sup-bl-s: #E9F2F8;
                --sup-bl-m: #A8CBE6;
                --sup-gn: #059669;
                --sup-gn-s: #ECFDF5;
                --sup-gn-t: #065F46;
                --sup-or: #EA580C;
                --sup-or-s: #FFF7ED;
                --sup-pu: #7C3AED;
                --sup-pu-s: #F5F3FF;
                --sup-rd: #DC2626;
                --sup-rd-s: #FEF2F2;
                --sup-rd-bd: #FECACA;
                --sup-bg: #F8FAFC;
                --sup-wh: #FFFFFF;
                --sup-tx: #0F172A;
                --sup-su: #334155;
                --sup-mu: #64748B;
                --sup-fa: #94A3B8;
                --sup-bd: #E2E8F0;
                --sup-bd2: #F1F5F9;
                --sup-sh: 0 1px 2px rgba(15, 23, 42, .04);
                --sup-r: 12px;
            }

            /* Dark mode: Filament thêm class .dark vào <html>. Đè lại palette để
               trang không còn là mảng sáng chói giữa app tối. */
            :root.dark {
                --sup-bl-s: rgba(38, 125, 193, .18);
                --sup-gn-s: rgba(5, 150, 105, .18);
                --sup-or-s: rgba(234, 88, 12, .18);
                --sup-pu-s: rgba(124, 58, 237, .18);
                --sup-rd-s: rgba(220, 38, 38, .18);
                --sup-rd-bd: rgba(248, 113, 113, .45);
                --sup-gn-t: #34D399;
                --sup-rd: #F87171;
                --sup-bg: #0b1120;
                --sup-wh: #1e293b;
                --sup-tx: #f1f5f9;
                --sup-su: #cbd5e1;
                --sup-mu: #94a3b8;
                --sup-fa: #64748b;
                --sup-bd: #334155;
                --sup-bd2: #263449;
                --sup-sh: 0 1px 2px rgba(0, 0, 0, .4);
            }

            /* Các bề mặt dùng màu sáng hardcode (không qua biến) — vá riêng cho dark. */
            .dark .sup-search {
                background: #0f172a;
            }

            .dark .sup-table thead tr,
            .dark .sup-table tbody tr:hover,
            .dark .sup-btn:hover {
                background: #172033 !important;
            }

            .dark .sup-info {
                background: rgba(38, 125, 193, .12);
                border-color: rgba(38, 125, 193, .35);
                color: var(--sup-su);
            }

            .dark .sup-bottom-bar {
                background: rgba(15, 23, 42, .85);
                border-color: rgba(51, 65, 85, .8);
            }

            .fi-main {
                background: var(--sup-bg);
            }

            .sup-page,
            .sup-page * {
                box-sizing: border-box;
                font-family: "Inter", system-ui, sans-serif;
            }

            .sup-page .fa,
            .sup-page .fa-solid,
            .sup-page .fa-regular,
            .sup-page .fa-brands,
            .sup-page .fas,
            .sup-page .far,
            .sup-page [class*="fa-"] {
                font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
            }

            .sup-page .fa-solid,
            .sup-page .fas {
                font-weight: 900 !important;
            }

            .sup-page {
                color: var(--sup-tx);
                padding: 22px 28px 36px !important;
                background: var(--sup-bg);
                min-height: 100vh;
            }

            .sup-head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1.125rem;
            }

            .sup-title {
                font-size: 20px !important;
                line-height: 1.25 !important;
                font-weight: 800 !important;
                letter-spacing: -.02em !important;
                margin: 0 0 4px !important;
                color: var(--sup-tx) !important;
            }

            .sup-subtitle {
                margin: 0;
                color: var(--sup-mu);
                font-size: 13px;
            }

            .sup-actions {
                display: flex;
                gap: .65rem;
                align-items: center;
                flex-wrap: wrap;
            }

            .sup-btn {
                height: 40px;
                border-radius: 9px;
                border: 1px solid var(--sup-bd);
                padding: 0 1rem;
                background: var(--sup-wh);
                color: var(--sup-tx);
                font-weight: 600;
                font-size: 13px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .45rem;
                cursor: pointer;
                transition: .14s ease;
                white-space: nowrap;
                box-shadow: var(--sup-sh);
                text-decoration: none;
            }

            .sup-btn:hover {
                background: #F8FAFC;
                transform: translateY(-1px);
            }

            .sup-btn-primary {
                background: linear-gradient(135deg, #1474FF, #0059DD);
                color: #fff;
                border-color: transparent;
                box-shadow: 0 6px 16px rgba(38, 125, 193, .28);
            }

            .sup-btn-primary:hover {
                background: linear-gradient(135deg, #105FCC, #004FC4);
                color: #fff;
            }

            .sup-btn-danger {
                color: var(--sup-rd);
                border-color: var(--sup-rd-bd);
            }

            .sup-kpis {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 12px;
                margin-bottom: 18px;
            }

            .sup-kpi,
            .sup-card {
                background: var(--sup-wh);
                border: 1px solid var(--sup-bd);
                border-radius: var(--sup-r);
                box-shadow: var(--sup-sh);
            }

            .sup-kpi {
                min-height: 0;
                padding: 16px;
            }

            .sup-kpi-icon,
            .sup-side-icon {
                display: inline-grid;
                place-items: center;
                border-radius: 10px;
                width: 38px;
                height: 38px;
                margin-bottom: 8px;
            }

            .sup-kpi-icon svg,
            .sup-side-icon svg,
            .sup-btn svg,
            .sup-search svg,
            .sup-small-btn svg,
            .sup-row-action svg {
                width: 1rem;
                height: 1rem;
            }

            .sup-ico-blue { background: var(--sup-bl-s); color: var(--sup-bl); }
            .sup-ico-orange { background: var(--sup-or-s); color: var(--sup-or); }
            .sup-ico-green { background: var(--sup-gn-s); color: var(--sup-gn); }
            .sup-ico-purple { background: var(--sup-pu-s); color: var(--sup-pu); }

            .sup-kpi-value {
                font-size: 28px;
                line-height: 1;
                font-weight: 800;
                letter-spacing: -.035em;
                color: var(--sup-tx);
                margin-bottom: 2px;
            }

            .sup-kpi-label {
                color: var(--sup-su);
                font-size: 12px;
                font-weight: 600;
                margin-bottom: 0;
            }

            .sup-kpi-note {
                color: var(--sup-fa);
                font-size: 11px;
                margin-top: 5px;
                display: flex;
                gap: .3rem;
                align-items: center;
            }

            .sup-table-card {
                /* Không cắt overflow ở đây, nếu không dropdown filter bị card che cụt.
                   Việc bo góc bảng chuyển xuống .sup-table-wrap. */
                overflow: visible;
            }

            .sup-toolbar {
                display: flex;
                align-items: end;
                gap: 9px;
                padding: 13px 16px;
                border-bottom: 1px solid var(--sup-bd2);
                flex-wrap: wrap;
            }

            .sup-search {
                height: 38px;
                min-width: 260px;
                max-width: 320px;
                border-radius: 8px;
                border: 1px solid var(--sup-bd);
                background: var(--sup-bg);
                display: flex;
                align-items: center;
                gap: .6rem;
                padding: 0 11px;
                color: var(--sup-fa);
            }

            .sup-search input {
                border: none;
                outline: none;
                background: transparent;
                color: var(--sup-tx);
                width: 100%;
                font-size: 13px;
            }

            .sup-search input::placeholder {
                color: #AAB7CA;
            }

            .sup-filter {
                display: flex;
                flex-direction: column;
                gap: 2px;
            }

            .sup-filter label {
                color: var(--sup-fa);
                font-size: 10px;
                font-weight: 600;
                letter-spacing: .04em;
            }

            .sup-select,
            .sup-input {
                height: 34px;
                border: 1px solid var(--sup-bd);
                border-radius: 7px;
                background: var(--sup-wh);
                color: var(--sup-su);
                padding: 0 .9rem;
                outline: none;
                font-size: 12.5px;
                width: 100%;
            }

            .sup-select {
                min-width: 9.5rem;
            }

            /* Combobox lọc "Loại TP cung cấp": vừa tìm kiếm vừa chọn */
            .sup-combo {
                position: relative;
            }

            .sup-combo-toggle {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .5rem;
                cursor: pointer;
                text-align: left;
            }

            .sup-combo-placeholder {
                color: var(--sup-fa);
            }

            .sup-combo-caret {
                width: 1rem;
                height: 1rem;
                flex: none;
                color: var(--sup-fa);
            }

            .sup-combo-panel {
                position: absolute;
                z-index: 30;
                top: calc(100% + .25rem);
                left: 0;
                right: 0;
                background: var(--sup-wh);
                border: 1px solid var(--sup-bd);
                border-radius: .65rem;
                box-shadow: 0 10px 25px -5px rgb(0 0 0 / .12);
                overflow: hidden;
            }

            .sup-combo-search {
                width: 100%;
                border: none;
                border-bottom: 1px solid var(--sup-bd);
                background: transparent;
                color: var(--sup-su);
                padding: .6rem .9rem;
                outline: none;
                font-size: .875rem;
            }

            .sup-combo-list {
                max-height: 13rem;
                overflow-y: auto;
                margin: 0;
                padding: .25rem;
                list-style: none;
            }

            .sup-combo-option {
                display: block;
                width: 100%;
                border: none;
                background: transparent;
                color: var(--sup-su);
                text-align: left;
                padding: .5rem .65rem;
                border-radius: .45rem;
                font-size: .875rem;
                cursor: pointer;
            }

            .sup-combo-option:hover {
                background: var(--sup-bg, rgb(0 0 0 / .04));
            }

            .sup-combo-option-active {
                font-weight: 700;
                background: rgb(245 158 11 / .12);
            }

            /* Hàng chọn nhiều: ô tick bên trái */
            .sup-combo-check {
                display: flex;
                align-items: center;
                gap: .55rem;
            }

            .sup-combo-box {
                flex: none;
                width: 1.05rem;
                height: 1.05rem;
                border: 1.5px solid var(--sup-bd);
                border-radius: .3rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #fff;
            }

            .sup-combo-box svg {
                width: .8rem;
                height: .8rem;
            }

            .sup-combo-box-on {
                background: rgb(245 158 11);
                border-color: rgb(245 158 11);
            }

            .sup-combo-clear {
                color: var(--sup-fa);
                font-size: .8rem;
                border-bottom: 1px solid var(--sup-bd);
                border-radius: 0;
                margin-bottom: .15rem;
            }

            .sup-combo-empty {
                padding: .6rem .65rem;
                color: var(--sup-fa);
                font-size: .82rem;
            }

            [x-cloak] {
                display: none !important;
            }

            .sup-spacer {
                flex: 1;
            }

            .sup-table-wrap {
                overflow-x: auto;
                border-bottom-left-radius: var(--sup-r);
                border-bottom-right-radius: var(--sup-r);
            }

            .sup-table {
                width: 100%;
                border-collapse: collapse;
                min-width: 62rem;
            }

            .sup-table thead tr {
                background: #F8FAFC;
            }

            /* Bảng chọn nguyên liệu cuộn trong khung — header phải dính lại (nền đặt trên TH,
               vì nền của TR không đi theo khi sticky). */
            .sup-table thead.sup-thead-sticky th {
                position: sticky;
                top: 0;
                z-index: 2;
                background: #F8FAFC;
            }
            .dark .sup-table thead.sup-thead-sticky th {
                background: #172033 !important;
            }

            .sup-table th {
                text-align: left !important;
                padding: 10px 12px !important;
                font-size: 11px !important;
                font-weight: 700 !important;
                letter-spacing: .07em !important;
                text-transform: uppercase !important;
                color: var(--sup-fa) !important;
                border-bottom: 1px solid var(--sup-bd) !important;
                white-space: nowrap !important;
                overflow: visible !important;
                text-overflow: clip !important;
            }

            .sup-table td {
                padding: 10px 12px !important;
                border-bottom: 1px solid var(--sup-bd2) !important;
                color: var(--sup-tx) !important;
                white-space: nowrap !important;
                vertical-align: middle !important;
                font-size: 13px !important;
                overflow: visible !important;
                text-overflow: clip !important;
            }

            .sup-table tbody tr:hover {
                background: #F8FAFC !important;
            }

            .sup-muted {
                color: var(--sup-mu) !important;
                font-weight: 600 !important;
            }

            .sup-name {
                font-weight: 600 !important;
                color: var(--sup-tx) !important;
            }

            .sup-link-num {
                font-weight: 700 !important;
                color: var(--sup-bl) !important;
                text-align: center !important;
            }

            /* Status Pills matching mockup 100% */
            .spill {
                display: inline-flex !important;
                align-items: center !important;
                gap: 5px !important;
                padding: 3px 10px !important;
                border-radius: 20px !important;
                font-size: 11.5px !important;
                font-weight: 600 !important;
                white-space: nowrap !important;
                line-height: 1.2 !important;
                height: 22px !important;
            }

            .spill::before {
                content: "" !important;
                width: 6px !important;
                height: 6px !important;
                border-radius: 50% !important;
                flex-shrink: 0 !important;
            }

            .s-ok {
                background: var(--sup-gn-s) !important;
                color: var(--sup-gn-t) !important;
            }

            .s-ok::before {
                background: var(--sup-gn) !important;
            }

            .s-qt {
                background: var(--sup-rd-s) !important;
                color: var(--sup-rd) !important;
            }

            .s-qt::before {
                background: var(--sup-rd) !important;
            }

            .sup-type-pill {
                display: inline-flex !important;
                align-items: center !important;
                gap: 6px !important;
                padding: 5px 14px !important;
                border-radius: 9999px !important;
                font-size: 13px !important;
                font-weight: 700 !important;
                background: #EFF6FF !important;
                color: #1D4ED8 !important;
                border: 1.5px solid #BFDBFE !important;
                box-shadow: 0 1px 2px rgba(29, 78, 216, 0.06) !important;
                line-height: 1.3 !important;
            }

            :root.dark .sup-type-pill,
            .dark .sup-type-pill {
                background: rgba(37, 99, 235, 0.22) !important;
                color: #93C5FD !important;
                border-color: rgba(147, 197, 253, 0.35) !important;
            }

            .sup-row-actions {
                display: flex;
                gap: .35rem;
                justify-content: flex-end;
            }

            .sup-row-action,
            .sup-small-btn {
                width: 2rem;
                height: 2rem;
                border-radius: .5rem;
                border: 1px solid var(--sup-bd);
                background: var(--sup-wh);
                color: var(--sup-mu);
                display: inline-grid;
                place-items: center;
                cursor: pointer;
                transition: .13s ease;
            }

            .sup-row-action:hover,
            .sup-small-btn:hover {
                background: var(--sup-bl-s);
                color: var(--sup-bl);
                border-color: var(--sup-bl-m);
            }

            .sup-row-danger {
                color: var(--sup-rd);
                border-color: #FECACA;
            }

            .sup-footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
                padding: 1rem 1.45rem;
                color: var(--sup-mu);
                font-size: .86rem;
                flex-wrap: wrap;
            }

            .sup-pagination {
                display: flex;
                align-items: center;
                gap: .5rem;
            }

            .sup-pagination nav {
                display: flex;
                gap: .35rem;
            }

            .sup-pagination nav > div:first-child {
                display: none;
            }

            .sup-pagination nav span,
            .sup-pagination nav a {
                min-width: 2rem;
                height: 2rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: .5rem;
                border: 1px solid var(--sup-bd);
                background: var(--sup-wh);
                color: var(--sup-mu);
                font-weight: 800;
                text-decoration: none;
                padding: 0 .55rem;
            }

            .sup-pagination nav span[aria-current="page"] span {
                background: var(--sup-bl);
                border-color: var(--sup-bl);
                color: #fff;
            }

            .sup-form-layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 20.5rem;
                gap: 1.1rem;
                align-items: start;
            }

            .sup-form-stack {
                display: grid;
                gap: 1.1rem;
                min-width: 0;
            }

            .sup-form-layout .sup-card {
                padding: 1.35rem 1.5rem;
                max-width: 100%;
                box-sizing: border-box;
                overflow: hidden;
            }

            .sup-card-title {
                font-size: 1rem;
                font-weight: 800;
                margin: 0 0 1rem;
                padding-bottom: .9rem;
                border-bottom: 1px solid var(--sup-bd2);
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .5rem;
                flex-wrap: wrap;
            }

            .sup-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1.05rem;
            }

            .sup-field {
                display: flex;
                flex-direction: column;
                gap: .5rem;
            }

            .sup-field-full {
                grid-column: 1 / -1;
            }

            .sup-label {
                font-size: .82rem;
                color: var(--sup-su);
                font-weight: 800;
            }

            .sup-required {
                color: #EF4444;
            }

            .sup-form-layout .sup-input,
            .sup-form-layout .sup-select {
                height: 3rem;
                border-radius: .7rem;
            }

            .sup-input::placeholder {
                color: #AAB7CA;
            }

            .sup-error {
                color: var(--sup-rd);
                font-size: .78rem;
                font-weight: 700;
            }

            .sup-ingredient-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: .9rem;
                flex-wrap: wrap;
            }

            .sup-help {
                color: var(--sup-mu);
                font-size: .78rem;
            }

            .sup-check {
                width: .95rem;
                height: .95rem;
                accent-color: var(--sup-bl);
            }

            .sup-cost {
                max-width: 10rem !important;
                margin-left: auto !important;
                text-align: right !important;
            }

            .sup-table .sup-input {
                height: 2.25rem !important;
                border-radius: .5rem !important;
                padding: 0 .75rem !important;
                font-size: .86rem !important;
                text-align: right !important;
                max-width: 140px !important;
                margin-left: auto !important;
                background: var(--sup-wh) !important;
                border: 1px solid var(--sup-bd) !important;
            }

            .sup-table .sup-input:disabled {
                background: var(--sup-bd2) !important;
                color: var(--sup-fa) !important;
                cursor: not-allowed !important;
                border-color: var(--sup-bd2) !important;
            }

            .sup-form-stack .sup-table {
                min-width: 100% !important;
            }

            .sup-info {
                margin-top: 1.1rem;
                background: #EAF3FF;
                border: 1px solid #A8CBE6;
                color: #334155;
                border-radius: .65rem;
                padding: .95rem 1rem;
                font-size: .86rem;
                line-height: 1.65;
            }

            .sup-info-title {
                color: var(--sup-bl);
                font-weight: 800;
                display: flex;
                gap: .45rem;
                align-items: center;
                margin-bottom: .25rem;
            }

            .sup-side {
                display: grid;
                gap: 1rem;
                min-width: 0;
            }

            .sup-toggle-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                font-weight: 800;
                color: var(--sup-su);
            }

            .sup-switch {
                position: relative;
                display: inline-flex;
                align-items: center;
                gap: .65rem;
                cursor: pointer;
                color: var(--sup-su);
            }

            .sup-switch input {
                position: absolute;
                opacity: 0;
            }

            .sup-switch-track {
                width: 3rem;
                height: 1.55rem;
                border-radius: 999px;
                background: #CBD5E1;
                position: relative;
                transition: .16s;
            }

            .sup-switch-track::after {
                content: "";
                position: absolute;
                top: .18rem;
                left: .18rem;
                width: 1.18rem;
                height: 1.18rem;
                background: #fff;
                border-radius: 50%;
                transition: .16s;
            }

            .sup-switch input:checked + .sup-switch-track {
                background: var(--sup-bl);
            }

            .sup-switch input:checked + .sup-switch-track::after {
                transform: translateX(1.45rem);
            }

            .sup-summary-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                border-bottom: 1px solid var(--sup-bd2);
                padding: .8rem 0;
                font-size: .9rem;
            }

            .sup-summary-row:last-child {
                border-bottom: 0;
            }

            .sup-summary-key {
                color: var(--sup-mu);
                display: flex;
                align-items: center;
                gap: .55rem;
            }

            .sup-summary-value {
                font-weight: 800;
                color: var(--sup-tx);
            }

            .sup-bottom-bar {
                position: sticky;
                bottom: 1.25rem;
                z-index: 10;
                margin: 2rem 0 0 0 !important;
                min-height: 4.25rem;
                background: rgba(255, 255, 255, 0.85);
                backdrop-filter: blur(12px) saturate(190%);
                -webkit-backdrop-filter: blur(12px) saturate(190%);
                border: 1px solid rgba(226, 232, 240, 0.8);
                border-radius: 1rem;
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: .75rem;
                padding: .8rem 1.5rem !important;
                box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.15), 
                            0 1px 3px rgba(15, 23, 42, 0.05);
                transition: all 0.3s ease;
            }

            .sup-breadcrumb {
                display: flex;
                align-items: center;
                gap: .55rem;
                font-size: .82rem;
                font-weight: 800;
                margin-bottom: .85rem;
            }

            .sup-breadcrumb a {
                color: var(--sup-bl);
                text-decoration: none;
            }

            @media (max-width: 1350px) {
                .sup-kpis {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .sup-form-layout {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 900px) {
                .sup-grid {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 760px) {
                .sup-page {
                    padding: .85rem .75rem 1.5rem !important;
                }

                .sup-card {
                    padding: 1rem .85rem;
                }

                .sup-card-title {
                    font-size: .95rem;
                    gap: .4rem;
                }

                .sup-ingredient-head {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: .35rem;
                }

                .sup-head,
                .sup-toolbar,
                .sup-footer {
                    align-items: stretch;
                    flex-direction: column;
                }

                .sup-kpis,
                .sup-grid {
                    grid-template-columns: 1fr;
                }

                .sup-search {
                    min-width: 100%;
                }

                .sup-actions {
                    width: 100%;
                }

                .sup-btn {
                    flex: 1;
                }

                .sup-bottom-bar {
                    position: static !important;
                    margin: 1.25rem 0 0 0 !important;
                    padding: 1rem .85rem !important;
                    flex-direction: column-reverse !important;
                    align-items: stretch !important;
                    gap: 0.5rem !important;
                    box-shadow: none !important;
                    border: 1px solid var(--sup-bd) !important;
                    background: var(--sup-wh) !important;
                    border-radius: .75rem !important;
                }

                .sup-bottom-bar .sup-btn {
                    width: 100% !important;
                    justify-content: center !important;
                    height: 2.75rem !important;
                }
            }
        </style>
@endpush
