@php
    $siteName = \App\Models\Setting::get('site_name', 'Bluefire Catering');
    $logoPath = \App\Models\Setting::get('site_logo');
    $logoUrl = $logoPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) : null;

    // 8 múi ảnh món ăn cho vòng tròn ISO (ảnh minh họa Unsplash — có màu nền dự phòng khi offline)
    $wheel = [
        ['photo-1490474418585-ba9bad8fd0ea', '#FDE68A'], // trái cây
        ['photo-1572443490709-e57452e86fe8', '#BFDBFE'], // hải sản
        ['photo-1512621776951-a57141f2eefd', '#BBF7D0'], // salad rau
        ['photo-1498837167922-ddd27525d352', '#FECACA'], // củ quả
        ['photo-1504674900247-0877df9cc836', '#FED7AA'], // món thịt
        ['photo-1467003909585-2f8a72700288', '#FBCFE8'], // cá hồi
        ['photo-1546069901-ba9599a7e63c',    '#DDD6FE'], // bowl healthy
        ['photo-1577219491135-ce391730fb2c', '#E2E8F0'], // đầu bếp
    ];
    // Toạ độ 8 múi 45°/múi (tâm 110,110 bán kính 110, bắt đầu từ đỉnh 12h)
    $pts = [[110,0],[187.78,32.22],[220,110],[187.78,187.78],[110,220],[32.22,187.78],[0,110],[32.22,32.22]];
@endphp

<style>
    :root {
        --lg-bl: #1256C4;
        --lg-bl-d: #0C3F94;
        --lg-bl-band: #1E63D0;
        --lg-rd: #E11D48;
        --lg-ink: #16233B;
        --lg-mu: #5B6B84;
        --lg-line: #DCE4F0;
        --lg-bg: #EDF1F7;
        --lg-card: #FFFFFF;
    }
    .dark {
        --lg-ink: #16233B;
        --lg-mu: #5B6B84;
    }

    .lg-page {
        min-height: 100vh; font-family: 'IBM Plex Sans', system-ui, sans-serif;
        background: var(--lg-bg); color: var(--lg-ink); position: relative; overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
    }
    /* Sóng trang trí mờ dưới nền như mẫu */
    .lg-page::before, .lg-page::after {
        content: ""; position: absolute; border-radius: 50%; pointer-events: none;
        border: 1.5px solid rgba(18, 86, 196, .08);
    }
    .lg-page::before { width: 900px; height: 900px; left: -350px; bottom: -560px; box-shadow: 0 0 0 46px rgba(18,86,196,.05), 0 0 0 100px rgba(18,86,196,.03); }
    .lg-page::after  { width: 520px; height: 520px; right: -180px; top: -300px; box-shadow: 0 0 0 40px rgba(18,86,196,.04); }

    .lg-shell {
        position: relative; z-index: 1; max-width: 1440px; margin: 0 auto;
        min-height: 100vh; padding: 28px 44px 20px;
        display: grid; grid-template-columns: 1.25fr 520px; gap: 48px; align-items: center;
    }

    /* ── Cột trái: thương hiệu ── */
    .lg-left { display: flex; flex-direction: column; min-height: calc(100vh - 48px); padding: 6px 0 0; }
    .lg-lockup { display: flex; align-items: center; gap: 12px; }
    .lg-lockup img { height: 52px; width: auto; object-fit: contain; }
    .lg-lockup-txt .nm { font-size: 30px; font-weight: 800; color: var(--lg-bl); letter-spacing: -.02em; line-height: 1; }
    .lg-lockup-txt .tg { font-size: 11px; font-weight: 700; color: var(--lg-rd); letter-spacing: .34em; margin-top: 3px; }

    .lg-hero { margin-top: 46px; position: relative; }
    .lg-hero h1 {
        font-size: clamp(22px, 2.4vw, 31px); font-weight: 800; line-height: 1.3;
        letter-spacing: .01em; margin: 0; max-width: 21ch; text-wrap: balance;
    }
    .lg-hero .rule { width: 46px; height: 4px; background: var(--lg-bl); border-radius: 2px; margin: 16px 0; }
    .lg-hero .en { font-style: italic; color: var(--lg-mu); font-size: 16.5px; max-width: 34ch; margin: 0; }

    .lg-stage { position: relative; margin-top: 34px; min-height: 380px; }
    .lg-band {
        position: absolute; left: -44px; top: 118px; z-index: 0;
        background: var(--lg-bl-band); color: #fff; padding: 26px 190px 26px 44px;
        border-radius: 0 6px 6px 0;
        font-size: clamp(26px, 2.6vw, 37px); font-weight: 900; letter-spacing: .01em;
        line-height: 1.22; font-style: italic; text-transform: uppercase; white-space: nowrap;
        box-shadow: 0 14px 34px rgba(18, 86, 196, .28);
    }
    .lg-wheel {
        position: relative; z-index: 1; width: min(340px, 62vw); margin-left: clamp(150px, 26vw, 330px);
        filter: drop-shadow(0 18px 40px rgba(15, 35, 70, .22));
    }
    .lg-wheel svg { display: block; width: 100%; height: auto; }
    .lg-wheel-core {
        position: absolute; inset: 50%; width: 41%; height: 41%; transform: translate(-50%, -50%);
        background: #fff; border-radius: 50%; display: flex; flex-direction: column;
        align-items: center; justify-content: center; gap: 3px; text-align: center;
        box-shadow: 0 0 0 5px rgba(255,255,255,.9);
    }
    .lg-wheel-core img { height: 34px; width: auto; object-fit: contain; }
    .lg-wheel-core .nm { font-size: 15px; font-weight: 800; color: var(--lg-bl); line-height: 1; }
    .lg-wheel-core .tg { font-size: 6.5px; font-weight: 700; color: var(--lg-rd); letter-spacing: .3em; }
    .lg-wheel-core .iso { font-size: 10.5px; font-weight: 800; color: var(--lg-ink); margin-top: 2px; }

    .lg-values { margin-top: auto; padding-top: 34px; display: flex; align-items: stretch; }
    .lg-val { flex: 1; text-align: center; padding: 0 14px; }
    .lg-val + .lg-val { border-left: 1px solid var(--lg-line); }
    .lg-val .ic {
        width: 46px; height: 46px; margin: 0 auto 9px; border-radius: 12px;
        display: grid; place-items: center; color: var(--lg-bl); font-size: 21px;
        background: rgba(18, 86, 196, .07);
    }
    .lg-val .t { font-size: 12.5px; font-weight: 800; color: var(--lg-bl); letter-spacing: .04em; }
    .lg-val .s { font-size: 11.5px; color: var(--lg-mu); margin-top: 3px; line-height: 1.45; }

    .lg-foot {
        margin-top: 26px; padding-top: 16px; border-top: 1px solid var(--lg-line);
        display: flex; justify-content: center; gap: 26px; flex-wrap: wrap;
        font-size: 12px; color: var(--lg-mu);
    }
    .lg-foot span { position: relative; }
    .lg-foot span + span::before { content: "|"; position: absolute; left: -16px; color: var(--lg-line); }

    /* ── Cột phải: card đăng nhập ── */
    .lg-cardwrap { display: flex; flex-direction: column; gap: 14px; }
    .lg-lang { align-self: flex-end; }
    .lg-card {
        background: var(--lg-card); border-radius: 22px; padding: 42px 44px 38px;
        box-shadow: 0 2px 6px rgba(15, 35, 70, .05), 0 28px 60px rgba(15, 35, 70, .12);
    }
    .lg-card-logo { display: flex; flex-direction: column; align-items: center; gap: 4px; margin-bottom: 20px; }
    .lg-card-logo img { height: 46px; width: auto; object-fit: contain; }
    .lg-card-logo .nm { font-size: 24px; font-weight: 800; color: var(--lg-bl); line-height: 1.05; }
    .lg-card-logo .tg { font-size: 9.5px; font-weight: 700; color: var(--lg-rd); letter-spacing: .32em; }

    .lg-title { text-align: center; font-size: 24px; font-weight: 800; margin: 4px 0 0; letter-spacing: -.01em; }
    .lg-title-rule { width: 44px; height: 3.5px; background: var(--lg-bl); border-radius: 2px; margin: 12px auto 14px; }
    .lg-sub { text-align: center; color: var(--lg-mu); font-size: 14px; line-height: 1.6; margin: 0 auto 26px; max-width: 34ch; }

    .lg-field { margin-bottom: 18px; }
    .lg-label { display: block; font-size: 14px; font-weight: 700; margin-bottom: 8px; color: var(--lg-ink); }
    .lg-inputwrap { position: relative; }
    .lg-inputwrap .fa-solid {
        position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
        color: #9AA7BC; font-size: 15px; pointer-events: none;
    }
    .lg-input {
        width: 100%; height: 52px; border: 1.5px solid var(--lg-line); border-radius: 12px;
        padding: 0 46px 0 44px; font-size: 14.5px; color: var(--lg-ink); background: #fff;
        outline: none; transition: border-color .15s, box-shadow .15s;
    }
    .lg-input::placeholder { color: #A6B1C4; }
    .lg-input:focus { border-color: var(--lg-bl); box-shadow: 0 0 0 4px rgba(18, 86, 196, .12); }
    .lg-eye {
        position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
        width: 38px; height: 38px; border: none; background: transparent; cursor: pointer;
        color: #9AA7BC; font-size: 15px; border-radius: 9px;
    }
    .lg-eye:hover { color: var(--lg-bl); background: rgba(18,86,196,.06); }
    .lg-err { display: block; margin-top: 7px; font-size: 12.5px; color: var(--lg-rd); font-weight: 600; }

    .lg-row { display: flex; align-items: center; justify-content: space-between; margin: 4px 0 22px; }
    .lg-remember { display: inline-flex; align-items: center; gap: 9px; font-size: 14px; cursor: pointer; user-select: none; }
    .lg-remember input {
        width: 18px; height: 18px; accent-color: var(--lg-bl); border-radius: 5px; cursor: pointer;
    }
    .lg-forgot { font-size: 14px; color: var(--lg-ink); text-decoration: none; font-weight: 500; }
    .lg-forgot:hover { color: var(--lg-bl); text-decoration: underline; }

    .lg-submit {
        width: 100%; height: 54px; border: none; border-radius: 12px; cursor: pointer;
        background: var(--lg-bl); color: #fff; font-size: 15.5px; font-weight: 800;
        letter-spacing: .08em; text-transform: uppercase;
        box-shadow: 0 12px 26px rgba(18, 86, 196, .32); transition: background .15s, transform .1s;
        font-family: inherit;
    }
    .lg-submit:hover { background: var(--lg-bl-d); }
    .lg-submit:active { transform: translateY(1px); }
    .lg-submit:focus-visible { outline: 3px solid rgba(18,86,196,.4); outline-offset: 2px; }
    .lg-submit[disabled] { opacity: .75; cursor: wait; }

    /* ── Responsive ── */
    @media (max-width: 1180px) {
        .lg-shell { grid-template-columns: 1fr 460px; gap: 32px; padding: 24px 28px; }
        .lg-band { padding-right: 120px; }
    }
    @media (max-width: 960px) {
        .lg-shell { grid-template-columns: 1fr; max-width: 640px; padding: 20px 18px 40px; }
        .lg-left { min-height: 0; order: 2; }
        .lg-cardwrap { order: 1; }
        .lg-hero h1 { max-width: none; }
        .lg-band { position: static; margin: 22px -18px; border-radius: 0; padding: 18px 18px; white-space: normal; font-size: 24px; box-shadow: none; }
        .lg-wheel { margin: 18px auto 0; }
        .lg-stage { min-height: 0; }
        .lg-values { flex-wrap: wrap; gap: 16px 0; }
        .lg-val { flex: 1 1 45%; }
        .lg-val:nth-child(3) { border-left: none; }
        .lg-card { padding: 30px 22px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .lg-submit, .lg-input { transition: none; }
    }
</style>

<div class="lg-page">
    <div class="lg-shell">

        {{-- ══════════ CỘT TRÁI: THƯƠNG HIỆU ══════════ --}}
        <div class="lg-left">
            <div class="lg-lockup">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $siteName }}">
                @endif
                <div class="lg-lockup-txt">
                    <div class="nm">BlueFire</div>
                    <div class="tg">TASTE&nbsp;BEAUTY</div>
                </div>
            </div>

            <div class="lg-hero">
                <h1>TOÀN THỂ CÁN BỘ CÔNG NHÂN VIÊN CÔNG TY CAM KẾT</h1>
                <div class="rule"></div>
                <p class="en">All company staff and employees are committed to</p>
            </div>

            <div class="lg-stage">
                <div class="lg-band">NGON&nbsp; TASTE<br>&amp; ĐẸP BEAUTIFUL</div>

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
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="">
                        @endif
                        <div class="nm">BlueFire</div>
                        <div class="tg">TASTE BEAUTY</div>
                        <div class="iso">ISO 22000:2018</div>
                    </div>
                </div>
            </div>

            <div class="lg-values">
                <div class="lg-val">
                    <div class="ic"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></div>
                    <div class="t">AN TOÀN</div>
                    <div class="s">Thực phẩm an toàn cho sức khỏe</div>
                </div>
                <div class="lg-val">
                    <div class="ic"><i class="fa-solid fa-medal" aria-hidden="true"></i></div>
                    <div class="t">CHẤT LƯỢNG</div>
                    <div class="s">Cam kết chất lượng tuyệt đối</div>
                </div>
                <div class="lg-val">
                    <div class="ic"><i class="fa-solid fa-users" aria-hidden="true"></i></div>
                    <div class="t">CHUYÊN NGHIỆP</div>
                    <div class="s">Đội ngũ chuyên nghiệp, tận tâm</div>
                </div>
                <div class="lg-val">
                    <div class="ic"><i class="fa-solid fa-handshake" aria-hidden="true"></i></div>
                    <div class="t">ĐỒNG HÀNH</div>
                    <div class="s">Đồng hành phát triển bền vững</div>
                </div>
            </div>

            <div class="lg-foot">
                <span>© {{ now()->year }} Bluefire Co., Ltd. All rights reserved.</span>
                <span>Version 1.0.0</span>
                <span>www.bluefire.vn</span>
            </div>
        </div>

        {{-- ══════════ CỘT PHẢI: CARD ĐĂNG NHẬP ══════════ --}}
        <div class="lg-cardwrap">
            <div class="lg-lang">
                @include('filament.components.language-switcher')
            </div>

            <div class="lg-card">
                <div class="lg-card-logo">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $siteName }}">
                    @endif
                    <div class="nm">BlueFire</div>
                    <div class="tg">TASTE&nbsp;BEAUTY</div>
                </div>

                <h2 class="lg-title">Chào mừng bạn trở lại!</h2>
                <div class="lg-title-rule"></div>
                <p class="lg-sub">Đăng nhập để truy cập hệ thống quản lý sản xuất và kho vận của Bluefire.</p>

                {{-- Submit qua authenticate() của Filament — giữ nguyên rate limit / remember / redirect --}}
                <form wire:submit="authenticate" novalidate>
                    <div class="lg-field">
                        <label class="lg-label" for="lg-email">Tên đăng nhập</label>
                        <div class="lg-inputwrap">
                            <i class="fa-solid fa-user" aria-hidden="true"></i>
                            <input id="lg-email" type="email" class="lg-input"
                                   placeholder="Nhập tên đăng nhập"
                                   wire:model="data.email"
                                   autocomplete="email" required autofocus>
                        </div>
                        @error('data.email') <span class="lg-err">{{ $message }}</span> @enderror
                    </div>

                    <div class="lg-field" x-data="{ show: false }">
                        <label class="lg-label" for="lg-password">Mật khẩu</label>
                        <div class="lg-inputwrap">
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            <input id="lg-password" class="lg-input"
                                   placeholder="Nhập mật khẩu"
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
                            <input type="checkbox" wire:model="data.remember">
                            <span>Ghi nhớ đăng nhập</span>
                        </label>
                        @if (filament()->hasPasswordReset())
                            <a class="lg-forgot" href="{{ filament()->getRequestPasswordResetUrl() }}">Quên mật khẩu?</a>
                        @else
                            <a class="lg-forgot" href="#" onclick="return false" title="Liên hệ quản trị viên để cấp lại mật khẩu">Quên mật khẩu?</a>
                        @endif
                    </div>

                    <button type="submit" class="lg-submit" wire:loading.attr="disabled">
                        <span wire:loading.remove>Đăng nhập</span>
                        <span wire:loading>Đang đăng nhập…</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
