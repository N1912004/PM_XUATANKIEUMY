@php
    // 8 múi ảnh món ăn cho vòng tròn ISO — chọn ảnh khớp chủ đề mẫu (trái cây, hải sản,
    // rau xanh, củ quả, món nướng, món thịt, cá, đầu bếp); có màu nền dự phòng khi offline.
    $wheel = [
        ['photo-1490474418585-ba9bad8fd0ea', '#FDE68A'], // trái cây tươi
        ['photo-1467003909585-2f8a72700288', '#BFDBFE'], // cá hồi / hải sản
        ['photo-1540420773420-3366772f4999', '#BBF7D0'], // rau xanh
        ['photo-1512621776951-a57141f2eefd', '#FECACA'], // salad củ quả
        ['photo-1555939594-58d7cb561ad1',    '#FED7AA'], // món nướng xiên
        ['photo-1504674900247-0877df9cc836', '#FBCFE8'], // món thịt
        ['photo-1546069901-ba9599a7e63c',    '#DDD6FE'], // bowl healthy
        ['photo-1556910103-1c02745aae4d',    '#E2E8F0'], // đầu bếp chế biến
    ];
    // Toạ độ 8 múi 45°/múi (tâm 110,110 bán kính 110, bắt đầu từ đỉnh 12h)
    $pts = [[110,0],[187.78,32.22],[220,110],[187.78,187.78],[110,220],[32.22,187.78],[0,110],[32.22,32.22]];

    // ĐỌC MÀU CHỦ ĐẠO TỪ SETTINGS (Mặc định là xanh dương #1256C4 nếu chưa có cấu hình)
    $primaryColor = \App\Models\Setting::get('primary_color', '#1256C4');

    // Helper chuyển HEX sang RGB
    $hexToRgb = function ($hex) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return [$r, $g, $b];
    };

    // Helper làm tối màu (darken) cho hover
    $darkenColor = function ($hex, $percent = 12) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = substr($hex, 0, 1) . substr($hex, 0, 1) . substr($hex, 1, 1) . substr($hex, 1, 1) . substr($hex, 2, 1) . substr($hex, 2, 1);
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = max(0, min(255, (int)($r - ($r * $percent / 100))));
        $g = max(0, min(255, (int)($g - ($g * $percent / 100))));
        $b = max(0, min(255, (int)($b - ($b * $percent / 100))));

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    };

    // Helper làm sáng màu (lighten) cho dải băng
    $lightenColor = function ($hex, $percent = 10) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = substr($hex, 0, 1) . substr($hex, 0, 1) . substr($hex, 1, 1) . substr($hex, 1, 1) . substr($hex, 2, 1) . substr($hex, 2, 1);
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = max(0, min(255, (int)($r + ((255 - $r) * $percent / 100))));
        $g = max(0, min(255, (int)($g + ((255 - $g) * $percent / 100))));
        $b = max(0, min(255, (int)($b + ((255 - $b) * $percent / 100))));

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    };

    $rgb = $hexToRgb($primaryColor);
    $primaryColorRgb = implode(',', $rgb);
    $primaryColorHover = $darkenColor($primaryColor, 12);
    $primaryColorBand = $lightenColor($primaryColor, 10);
@endphp
<div class="lg-page">
<style>
    :root {
        --lg-bl: {{ $primaryColor }};
        --lg-bl-d: {{ $primaryColorHover }};
        --lg-bl-band: {{ $primaryColorBand }};
        --lg-rd: #E11D48;
        --lg-ink: #16233B;
        --lg-mu: #5B6B84;
        --lg-line: #DCE4F0;
        --lg-bg: #EDF1F7;
        --lg-card: #FFFFFF;
    }

    .lg-page {
        min-height: 100vh; font-family: 'IBM Plex Sans', system-ui, sans-serif;
        background: var(--lg-bg); color: var(--lg-ink); position: relative; overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
    }
    /* Sóng trang trí mờ dưới nền như mẫu */
    .lg-page::before, .lg-page::after {
        content: ""; position: absolute; border-radius: 50%; pointer-events: none;
        border: 1.5px solid rgba({{ $primaryColorRgb }}, .08);
    }
    .lg-page::before { width: 900px; height: 900px; left: -350px; bottom: -560px; box-shadow: 0 0 0 46px rgba({{ $primaryColorRgb }},.05), 0 0 0 100px rgba({{ $primaryColorRgb }},.03); }
    .lg-page::after  { width: 520px; height: 520px; right: -180px; top: -300px; box-shadow: 0 0 0 40px rgba({{ $primaryColorRgb }},.04); }

    .lg-lang-fixed { position: absolute; top: 26px; right: 40px; z-index: 5; }
    .lg-lang-fixed .relative { position: relative; }
    .lg-lang-fixed button {
        display: flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 8px;
        border: 1px solid var(--lg-line); background: var(--lg-card); color: var(--lg-ink);
        font-weight: 700; font-size: 14px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: background .15s; outline: none;
    }
    .lg-lang-fixed button:hover { background: #f9fafb; }
    .lg-lang-fixed button svg { width: 16px; height: 16px; transition: transform 0.2s; }
    .lg-lang-fixed button .rotate-180 { transform: rotate(180deg); }
    .lg-lang-fixed .absolute {
        position: absolute; right: 0; margin-top: 8px; width: 160px; border-radius: 12px;
        background: var(--lg-card); border: 1px solid var(--lg-line); box-shadow: 0 10px 25px rgba(15, 35, 70, 0.15);
        z-index: 50; padding: 4px 0; overflow: hidden;
    }
    .lg-lang-fixed a {
        display: flex; align-items: center; gap: 12px; padding: 8px 16px; font-size: 14px;
        font-weight: 600; color: var(--lg-ink); text-decoration: none; transition: background .15s;
    }
    .lg-lang-fixed a:hover { background: #f9fafb; }
    .lg-lang-fixed a.bg-blue-50\/50 { background: rgba({{ $primaryColorRgb }}, 0.08) !important; color: var(--lg-bl) !important; }


    .lg-shell {
        position: relative; z-index: 1; max-width: 1460px; margin: 0 auto;
        min-height: 100vh; padding: 28px 48px 18px;
        display: grid; grid-template-columns: 1.3fr 520px; gap: 56px; align-items: center;
    }

    /* ── Cột trái: thương hiệu ── */
    .lg-left { display: flex; flex-direction: column; min-height: calc(100vh - 46px); padding: 4px 0 0; }
    .lg-lockup { display: flex; flex-direction: column; align-items: flex-start; gap: 6px; }
    .lg-lockup .nm { font-size: 30px; font-weight: 800; color: var(--lg-bl); letter-spacing: -.02em; line-height: 1; }
    .lg-lockup .tg { font-size: 11px; font-weight: 700; color: var(--lg-rd); letter-spacing: .34em; }

    .lg-hero { margin-top: 40px; }
    .lg-hero h1 {
        font-size: clamp(23px, 2.3vw, 30px); font-weight: 800; line-height: 1.35;
        letter-spacing: .01em; margin: 0; max-width: 34ch; text-wrap: balance;
    }
    .lg-hero .rule { width: 46px; height: 4px; background: var(--lg-bl); border-radius: 2px; margin: 16px 0; }
    .lg-hero .en { font-style: italic; color: var(--lg-mu); font-size: 16.5px; max-width: 36ch; margin: 0; line-height: 1.6; }

    .lg-stage { position: relative; margin-top: 8px; min-height: 430px; display: flex; align-items: center; }
    .lg-band {
        position: absolute; left: -48px; top: 50%; transform: translateY(-14%); z-index: 0;
        background: var(--lg-bl-band); color: #fff; padding: 30px 220px 30px 48px;
        font-size: clamp(28px, 2.7vw, 40px); font-weight: 900; letter-spacing: .01em;
        line-height: 1.22; font-style: italic; text-transform: uppercase; white-space: nowrap;
        box-shadow: 0 14px 34px rgba({{ $primaryColorRgb }}, .25);
    }
    .lg-wheel {
        position: relative; z-index: 1; width: min(400px, 58vw); margin-left: clamp(190px, 30vw, 400px);
        filter: drop-shadow(0 18px 40px rgba(15, 35, 70, .2));
    }
    .lg-wheel svg { display: block; width: 100%; height: auto; }
    .lg-wheel-core {
        position: absolute; inset: 50%; width: 43%; height: 43%; transform: translate(-50%, -50%);
        background: #fff; border-radius: 50%; display: flex; flex-direction: column;
        align-items: center; justify-content: center; gap: 2px; text-align: center;
        box-shadow: 0 0 0 5px rgba(255,255,255,.92);
    }
    .lg-wheel-core .nm { font-size: 17px; font-weight: 800; color: var(--lg-bl); line-height: 1.05; margin-top: 3px; }
    .lg-wheel-core .tg { font-size: 6.5px; font-weight: 700; color: var(--lg-rd); letter-spacing: .3em; }
    .lg-wheel-core .iso { font-size: 11px; font-weight: 800; color: var(--lg-ink); margin-top: 3px; }

    /* 4 giá trị cốt lõi — icon OUTLINE như mẫu, không nền hộp */
    .lg-values { margin-top: auto; padding-top: 30px; display: flex; align-items: stretch; }
    .lg-val { flex: 1; text-align: center; padding: 0 14px; }
    .lg-val + .lg-val { border-left: 1px solid var(--lg-line); }
    .lg-val .ic { height: 44px; display: grid; place-items: center; margin-bottom: 8px; color: var(--lg-bl); }
    .lg-val .ic svg { width: 38px; height: 38px; stroke: currentColor; fill: none; stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round; }
    .lg-val .t { font-size: 12.5px; font-weight: 800; color: var(--lg-bl); letter-spacing: .05em; }
    .lg-val .s { font-size: 11.5px; color: var(--lg-mu); margin-top: 3px; line-height: 1.45; }

    .lg-foot {
        margin-top: 24px; padding-top: 15px; border-top: 1px solid var(--lg-line);
        display: flex; justify-content: center; gap: 30px; flex-wrap: wrap;
        font-size: 12px; color: var(--lg-mu);
    }
    .lg-foot span { position: relative; }
    .lg-foot span + span::before { content: "|"; position: absolute; left: -18px; color: var(--lg-line); }

    /* ── Cột phải: card đăng nhập ── */
    .lg-card {
        background: var(--lg-card); border-radius: 22px; padding: 42px 44px 38px;
        box-shadow: 0 2px 6px rgba(15, 35, 70, .05), 0 28px 60px rgba(15, 35, 70, .12);
    }
    .lg-card-logo { display: flex; flex-direction: column; align-items: center; gap: 5px; margin-bottom: 18px; }
    .lg-card-logo .nm { font-size: 25px; font-weight: 800; color: var(--lg-bl); line-height: 1.05; }
    .lg-card-logo .tg { font-size: 9.5px; font-weight: 700; color: var(--lg-rd); letter-spacing: .32em; }

    .lg-title { text-align: center; font-size: 24px; font-weight: 800; margin: 4px 0 0; letter-spacing: -.01em; }
    .lg-title-rule { width: 44px; height: 3.5px; background: var(--lg-bl); border-radius: 2px; margin: 12px auto 14px; }
    .lg-sub { text-align: center; color: var(--lg-mu); font-size: 14px; line-height: 1.6; margin: 0 auto 26px; max-width: 34ch; }

    .lg-field { margin-bottom: 18px; }
    .lg-label { display: block; font-size: 14px; font-weight: 700; margin-bottom: 8px; color: var(--lg-ink); }
    .lg-inputwrap { position: relative; }
    .lg-inputwrap > .fa-solid {
        position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
        color: #9AA7BC; font-size: 15px; pointer-events: none;
    }
    .lg-input {
        width: 100%; height: 52px; border: 1.5px solid var(--lg-line); border-radius: 12px;
        padding: 0 46px 0 44px; font-size: 14.5px; color: var(--lg-ink); background: #fff;
        outline: none; transition: border-color .15s, box-shadow .15s;
    }
    .lg-input::placeholder { color: #A6B1C4; }
    .lg-input:focus { border-color: var(--lg-bl); box-shadow: 0 0 0 4px rgba({{ $primaryColorRgb }}, .12); }
    .lg-eye {
        position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
        width: 38px; height: 38px; border: none; background: transparent; cursor: pointer;
        color: #9AA7BC; font-size: 15px; border-radius: 9px;
    }
    .lg-eye:hover { color: var(--lg-bl); background: rgba({{ $primaryColorRgb }},.06); }
    .lg-err { display: block; margin-top: 7px; font-size: 12.5px; color: var(--lg-rd); font-weight: 600; }

    .lg-row { display: flex; align-items: center; justify-content: space-between; margin: 4px 0 22px; }
    .lg-remember { display: inline-flex; align-items: center; gap: 9px; font-size: 14px; cursor: pointer; user-select: none; }
    .lg-remember input { width: 18px; height: 18px; accent-color: var(--lg-bl); cursor: pointer; }
    .lg-forgot { font-size: 14px; color: var(--lg-ink); text-decoration: none; font-weight: 500; }
    .lg-forgot:hover { color: var(--lg-bl); text-decoration: underline; }

    /* Selector độ ưu tiên cao (.lg-page button.lg-submit[type]) + !important để THẮNG rule global
       của panel `button[type="submit"]:not(.fi-btn-color-gray)` ép mọi nút submit theo primary_color
       trong Cài đặt (đang là xanh lá) — nút đăng nhập phải giữ XANH DƯƠNG theo mẫu thiết kế */
    .lg-page button.lg-submit[type="submit"],
    .lg-page .lg-submit {
        width: 100%; height: 54px; border: none; border-radius: 12px; cursor: pointer;
        background: var(--lg-bl) !important; color: #fff !important; font-size: 15.5px; font-weight: 800 !important;
        letter-spacing: .1em; text-transform: uppercase;
        box-shadow: 0 12px 26px rgba({{ $primaryColorRgb }}, .32) !important; transition: background .15s, transform .1s;
        font-family: inherit;
    }
    .lg-page button.lg-submit[type="submit"]:hover,
    .lg-page .lg-submit:hover { background: var(--lg-bl-d) !important; box-shadow: 0 12px 26px rgba({{ $primaryColorRgb }}, .32) !important; }
    .lg-page .lg-submit:active { transform: translateY(1px); }
    .lg-page .lg-submit:focus-visible { outline: 3px solid rgba({{ $primaryColorRgb }},.4); outline-offset: 2px; }
    .lg-page .lg-submit[disabled] { opacity: .75; cursor: wait; }

    /* ── Responsive ── */
    @media (max-width: 1180px) {
        .lg-shell { grid-template-columns: 1fr 460px; gap: 32px; padding: 24px 28px; }
        .lg-band { padding-right: 130px; }
    }
    @media (max-width: 960px) {
        .lg-lang-fixed { top: 16px; right: 16px; }
        .lg-shell { grid-template-columns: 1fr; max-width: 640px; padding: 64px 18px 40px; }
        .lg-left { min-height: 0; order: 2; }
        .lg-card { order: 1; padding: 30px 22px; }
        .lg-hero h1 { max-width: none; }
        .lg-stage { min-height: 0; display: block; }
        .lg-band { position: static; transform: none; margin: 22px -18px; padding: 18px; white-space: normal; font-size: 24px; box-shadow: none; }
        .lg-wheel { margin: 18px auto 0; }
        .lg-values { flex-wrap: wrap; gap: 16px 0; }
        .lg-val { flex: 1 1 45%; }
        .lg-val:nth-child(3) { border-left: none; }
    }
    @media (prefers-reduced-motion: reduce) {
        .lg-page .lg-submit, .lg-input { transition: none; }
    }
</style>

    {{-- Chọn ngôn ngữ — góc phải trên cùng của trang như mẫu --}}
    <div class="lg-lang-fixed">
        @include('filament.components.language-switcher')
    </div>

    <div class="lg-shell">

        {{-- ══════════ CỘT TRÁI: THƯƠNG HIỆU ══════════ --}}
        <div class="lg-left">
            <div class="lg-lockup">
                @include('filament.pages.auth.partials.bluefire-logo', ['height' => 46])
                <div>
                    <div class="nm">BlueFire</div>
                    <div class="tg">TASTE&nbsp;BEAUTY</div>
                </div>
            </div>

            <div class="lg-hero">
                <h1>{{ __('login.commit_title') }}</h1>
                <div class="rule"></div>
                <p class="en">{{ __('login.commit_subtitle') }}</p>
            </div>

            <div class="lg-stage">
                <div class="lg-band">{!! __('login.band_text') !!}</div>

                {{-- Vòng tròn 8 múi ảnh món ăn + tâm ISO --}}
                <div class="lg-wheel">
                    <svg viewBox="0 0 220 220" role="img" aria-label="Bánh xe hình ảnh món ăn — chứng nhận ISO 22000:2018">
                        <defs>
                            @foreach($wheel as $i => [$photo, $fallback])
                                @php $p1 = $pts[$i]; $p2 = $pts[($i + 1) % 8]; @endphp
                                <clipPath id="wedge{{ $i }}">
                                    <path d="M110,110 L{{ $p1[0] }},{{ $p1[1] }} A110,110 0 0 1 {{ $p2[0] }},{{ $p2[1] }} Z"/>
                                </clipPath>
                            @endforeach
                        </defs>
                        @foreach($wheel as $i => [$photo, $fallback])
                            @php $p1 = $pts[$i]; $p2 = $pts[($i + 1) % 8]; @endphp
                            {{-- màu nền dự phòng khi ảnh chưa tải / offline --}}
                            <path d="M110,110 L{{ $p1[0] }},{{ $p1[1] }} A110,110 0 0 1 {{ $p2[0] }},{{ $p2[1] }} Z" fill="{{ $fallback }}"/>
                            <image clip-path="url(#wedge{{ $i }})"
                                   href="https://images.unsplash.com/{{ $photo }}?auto=format&fit=crop&w=440&q=60"
                                   x="-10" y="-10" width="240" height="240" preserveAspectRatio="xMidYMid slice"/>
                            {{-- vách trắng ngăn múi --}}
                            <path d="M110,110 L{{ $p1[0] }},{{ $p1[1] }}" stroke="#fff" stroke-width="3"/>
                        @endforeach
                        <circle cx="110" cy="110" r="108.5" fill="none" stroke="#fff" stroke-width="3"/>
                    </svg>
                    <div class="lg-wheel-core">
                        @include('filament.pages.auth.partials.bluefire-logo', ['height' => 30])
                        <div class="nm">BlueFire</div>
                        <div class="tg">TASTE BEAUTY</div>
                        <div class="iso">ISO 22000:2018</div>
                    </div>
                </div>
            </div>

            {{-- 4 giá trị cốt lõi — icon outline như mẫu --}}
            <div class="lg-values">
                <div class="lg-val">
                    <div class="ic">
                        <svg viewBox="0 0 24 24"><path d="M12 3l7 2.6v5.2c0 4.6-3 8.3-7 9.7-4-1.4-7-5.1-7-9.7V5.6L12 3z"/><path d="M9 12l2.2 2.2L15.5 9.8"/></svg>
                    </div>
                    <div class="t">{{ __('login.values.safety.title') }}</div>
                    <div class="s">{{ __('login.values.safety.desc') }}</div>
                </div>
                <div class="lg-val">
                    <div class="ic">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="9" r="5.4"/><path d="M10 6.8l1.4 1.4L14.2 5.4"/><path d="M8.6 13.5L7 21l5-2.6L17 21l-1.6-7.5"/></svg>
                    </div>
                    <div class="t">{{ __('login.values.quality.title') }}</div>
                    <div class="s">{{ __('login.values.quality.desc') }}</div>
                </div>
                <div class="lg-val">
                    <div class="ic">
                        <svg viewBox="0 0 24 24"><circle cx="9" cy="8.5" r="3"/><path d="M3.5 19c.6-3 2.9-4.6 5.5-4.6s4.9 1.6 5.5 4.6"/><circle cx="16.6" cy="9.5" r="2.4"/><path d="M15.4 14.7c2.5.1 4.4 1.5 5.1 4.3"/></svg>
                    </div>
                    <div class="t">{{ __('login.values.professional.title') }}</div>
                    <div class="s">{{ __('login.values.professional.desc') }}</div>
                </div>
                <div class="lg-val">
                    <div class="ic">
                        <svg viewBox="0 0 24 24"><path d="M2.5 8.5L7 6l5 2.5L16.9 6l4.6 2.5"/><path d="M12 8.5l-3.4 3.3a1.6 1.6 0 002.2 2.3L12 13l2.6 2.5a1.7 1.7 0 002.4-2.4L13.5 9.6"/><path d="M2.5 8.5v6.7L7 17.7M21.5 8.5v6.7L17 17.7"/></svg>
                    </div>
                    <div class="t">{{ __('login.values.companion.title') }}</div>
                    <div class="s">{{ __('login.values.companion.desc') }}</div>
                </div>
            </div>

            <div class="lg-foot">
                <span>{{ __('login.footer.copyright') }}</span>
                <span>{{ __('login.footer.version') }}</span>
                <span>{{ __('login.footer.website') }}</span>
            </div>
        </div>

        {{-- ══════════ CỘT PHẢI: CARD ĐĂNG NHẬP ══════════ --}}
        <div class="lg-card">
            <div class="lg-card-logo">
                @include('filament.pages.auth.partials.bluefire-logo', ['height' => 44])
                <div class="nm">BlueFire</div>
                <div class="tg">TASTE&nbsp;BEAUTY</div>
            </div>

            <h2 class="lg-title">{{ __('login.card.title') }}</h2>
            <div class="lg-title-rule"></div>
            <p class="lg-sub">{{ __('login.card.subtitle') }}</p>

            {{-- Submit qua authenticate() của Filament — giữ nguyên rate limit / remember / redirect --}}
            <form wire:submit="authenticate" novalidate>
                <div class="lg-field">
                    <label class="lg-label" for="lg-email">{{ __('login.card.username') }}</label>
                    <div class="lg-inputwrap">
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                        <input id="lg-email" type="email" class="lg-input"
                               placeholder="{{ __('login.card.username_placeholder') }}"
                               wire:model="data.email"
                               autocomplete="email" required autofocus>
                    </div>
                    @error('data.email') <span class="lg-err">{{ $message }}</span> @enderror
                </div>

                <div class="lg-field" x-data="{ show: false }">
                    <label class="lg-label" for="lg-password">{{ __('login.card.password') }}</label>
                    <div class="lg-inputwrap">
                        <i class="fa-solid fa-lock" aria-hidden="true"></i>
                        <input id="lg-password" class="lg-input"
                               placeholder="{{ __('login.card.password_placeholder') }}"
                               wire:model="data.password"
                               autocomplete="current-password" required
                               x-bind:type="show ? 'text' : 'password'">
                        <button type="button" class="lg-eye" x-on:click="show = !show"
                                x-bind:aria-label="show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'">
                            <i class="fa-solid" x-bind:class="show ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('data.password') <span class="lg-err">{{ $message }}</span> @enderror
                </div>

                <div class="lg-row">
                    <label class="lg-remember">
                        <input type="checkbox" wire:model="data.remember" checked>
                        <span>{{ __('login.card.remember') }}</span>
                    </label>
                    @if (filament()->hasPasswordReset())
                        <a class="lg-forgot" href="{{ filament()->getRequestPasswordResetUrl() }}">{{ __('login.card.forgot') }}</a>
                    @else
                        <a class="lg-forgot" href="#" onclick="return false" title="{{ __('login.card.forgot_helper') }}">{{ __('login.card.forgot') }}</a>
                    @endif
                </div>

                <button type="submit" class="lg-submit" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ __('login.card.submit') }}</span>
                    <span wire:loading>{{ __('login.card.submitting') }}</span>
                </button>

            </form>
        </div>
    </div>
</div>
