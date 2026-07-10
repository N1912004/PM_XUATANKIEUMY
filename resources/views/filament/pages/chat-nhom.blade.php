<div>
    @include('filament.pages.chat-partials.styles')

    @php
        $currentUserId = filament()->auth()->id();
        $user = filament()->auth()->user();
        $kitchenName = $user->kitchen?->name ?? 'Vận hành ca sáng';
        $avatarColors = ['#1267E8', '#059669', '#D97706', '#7C3AED', '#EA580C', '#EC4899'];
    @endphp

    <div
        x-data="{
            scrollDown() {
                this.$nextTick(() => {
                    const box = this.$refs.messages;
                    if (box) { box.scrollTop = box.scrollHeight; }
                });
            }
        }"
        x-init="scrollDown()"
        @messages-updated.window="scrollDown()"
        class="chat-page"
    >
        <div class="chat-root">
            <!-- LEFT PANEL: GROUP LIST -->
            <div class="cl">
                <div class="cl-head">
                    <h1>Chat nhóm</h1>
                    <p>Truy cập các nhóm chat được phân quyền để trao đổi tin nhắn và gửi file nội bộ</p>
                </div>
                <div class="grp-srch">
                    <div class="gsbox">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Tìm nhóm chat...">
                    </div>
                    <div class="filt-ico">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                </div>
                <div class="grp-list">
                    <!-- Group 1 (Active) -->
                    <div class="grp-item sel">
                        <div class="g-av g-av-b"><i class="fa-solid fa-users"></i></div>
                        <div class="g-info">
                            <div class="g-name">{{ $kitchenName }}</div>
                            <div class="g-desc">Trao đổi công việc ca sáng</div>
                            <div class="g-meta">18 thành viên <span class="g-dot"></span> 2 phút trước</div>
                        </div>
                        <div class="g-right"><div class="g-badge">5</div></div>
                    </div>
                    <!-- Group 2 -->
                    <div class="grp-item">
                        <div class="g-av g-av-o"><i class="fa-solid fa-utensils"></i></div>
                        <div class="g-info">
                            <div class="g-name">Bếp trung tâm</div>
                            <div class="g-desc">Trao đổi công việc bếp</div>
                            <div class="g-meta">15 thành viên <span class="g-dot"></span> 15 phút trước</div>
                        </div>
                        <div class="g-right"><div class="g-badge">3</div></div>
                    </div>
                    <!-- Group 3 -->
                    <div class="grp-item">
                        <div class="g-av g-av-g"><i class="fa-solid fa-boxes-stacked"></i></div>
                        <div class="g-info">
                            <div class="g-name">Kho nguyên liệu</div>
                            <div class="g-desc">Nhập xuất &amp; tồn kho</div>
                            <div class="g-meta">12 thành viên <span class="g-dot"></span> 1 giờ trước</div>
                        </div>
                        <div class="g-right"><div class="g-badge">2</div></div>
                    </div>
                    <!-- Group 4 -->
                    <div class="grp-item">
                        <div class="g-av g-av-p"><i class="fa-solid fa-user-tie"></i></div>
                        <div class="g-info">
                            <div class="g-name">Nhân sự nội bộ</div>
                            <div class="g-desc">Trao đổi công việc nhân sự</div>
                            <div class="g-meta">22 thành viên <span class="g-dot"></span> 2 giờ trước</div>
                        </div>
                        <div class="g-right"></div>
                    </div>
                    <!-- Group 5 -->
                    <div class="grp-item">
                        <div class="g-av g-av-k"><i class="fa-solid fa-diagram-project"></i></div>
                        <div class="g-info">
                            <div class="g-name">Dự án triển khai</div>
                            <div class="g-desc">Cập nhật tiến độ dự án</div>
                            <div class="g-meta">10 thành viên <span class="g-dot"></span> Hôm qua</div>
                        </div>
                        <div class="g-right"></div>
                    </div>
                </div>
            </div>

            <!-- MIDDLE PANEL: CHAT WINDOW -->
            <div class="cm">
                <div class="cm-head">
                    <div class="g-av g-av-b" style="width:34px; height:34px; border-radius:9px; font-size:14px; flex-shrink:0">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="cm-ginfo">
                        <div class="cm-gname">{{ $kitchenName }}</div>
                        <div class="cm-gmeta">18 thành viên <span class="cm-onl"></span> 6 đang hoạt động</div>
                    </div>
                    <button type="button" class="cm-ico"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <button type="button" class="cm-ico"><i class="fa-solid fa-paperclip"></i></button>
                    <button type="button" class="cm-ico"><i class="fa-solid fa-ellipsis"></i></button>
                </div>

                <!-- Messages area -->
                <div x-ref="messages" class="msgs" id="msgArea">
                    <div class="day-sep">Hôm nay</div>

                    <!-- MOCK MESSAGE 1 (Trần Thị Bình) -->
                    <div class="msg-row">
                        <div class="msg-av">
                            <img src="https://images.unsplash.com/photo-1494790108755-2616b612b884?w=60&h=60&fit=crop&crop=face" alt="Binh">
                        </div>
                        <div class="msg-body">
                            <div class="msg-sender">Trần Thị Bình</div>
                            <div class="bubble">Chào cả team, ca sáng hôm nay mọi người chú ý kiểm tra nhiệt độ bảo quản nguyên liệu nhé.</div>
                            <div class="msg-time">06:45</div>
                        </div>
                    </div>

                    <!-- MOCK MESSAGE 2 (Lê Hoàng Cường) -->
                    <div class="msg-row">
                        <div class="msg-av">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=60&h=60&fit=crop&crop=face" alt="Cuong">
                        </div>
                        <div class="msg-body">
                            <div class="msg-sender">Lê Hoàng Cường</div>
                            <div class="bubble">Mình đã cập nhật file hướng dẫn vận hành mới. Mọi người xem và thực hiện theo nhé.</div>
                            <div class="file-bbl">
                                <div class="fic f-pdf">PDF</div>
                                <div style="flex:1; min-width:0">
                                    <div class="fi-nm">Huong_dan_van_hanh_ca_sang.pdf</div>
                                    <div class="fi-sz">PDF &nbsp;•&nbsp; 1.8 MB</div>
                                </div>
                                <i class="fa-solid fa-download" style="color:var(--fa); font-size:12px; margin-left:auto"></i>
                            </div>
                            <div class="msg-time">06:52</div>
                        </div>
                    </div>

                    <!-- MOCK MESSAGE 3 (Phạm Thị Dung) -->
                    <div class="msg-row">
                        <div class="msg-av">
                            <img src="https://images.unsplash.com/photo-1544725176-7c40e5a71c5e?w=60&h=60&fit=crop&crop=face" alt="Dung">
                        </div>
                        <div class="msg-body">
                            <div class="msg-sender">Phạm Thị Dung</div>
                            <div class="bubble">Hình ảnh kiểm tra khu vực bếp sáng nay.</div>
                            <div class="img-bbl">
                                <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=320&h=180&fit=crop" alt="Bep">
                            </div>
                            <div class="msg-time">06:58</div>
                        </div>
                    </div>

                    <!-- REALTIME MESSAGES FROM DB -->
                    @foreach ($messages as $message)
                        @php
                            $isOwn = $message['user_id'] == $currentUserId;
                            $mColor = $avatarColors[$message['user_id'] % count($avatarColors)];
                            $mInitials = '';
                            $mWords = explode(' ', $message['user_name']);
                            if (count($mWords) >= 2) {
                                $mInitials = mb_substr($mWords[0], 0, 1) . mb_substr($mWords[count($mWords)-1], 0, 1);
                            } else {
                                $mInitials = mb_substr($message['user_name'], 0, 2);
                            }
                            $mInitials = mb_strtoupper($mInitials);
                        @endphp
                        
                        <div class="msg-row {{ $isOwn ? 'me' : '' }}">
                            @unless($isOwn)
                                <div class="msg-av" style="background:{{ $mColor }}1A; color:{{ $mColor }}; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:10px">
                                    {{ $mInitials }}
                                </div>
                            @endunless
                            <div class="msg-body">
                                @unless($isOwn)
                                    <div class="msg-sender">{{ $message['user_name'] }}</div>
                                @endunless
                                <div class="bubble">
                                    {{ $message['body'] }}
                                </div>
                                <div class="msg-time">
                                    {{ $message['created_at'] }}
                                    @if($isOwn)
                                        <i class="fa-solid fa-check-double msg-tick"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Input area -->
                <form
                    wire:submit.prevent="sendMessage"
                    x-on:submit="scrollDown()"
                    class="msg-inp"
                >
                    <button type="button" class="mi-att"><i class="fa-solid fa-paperclip"></i></button>
                    <div class="mi-box">
                        <input
                            type="text"
                            wire:model="newMessage"
                            placeholder="Nhập nội dung..."
                            autocomplete="off"
                        >
                        <span class="mi-emoji">☺</span>
                    </div>
                    <button type="submit" class="mi-send">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script data-navigate-once>
        (function registerChatMorphHook() {
            if (window.__chatMorphHookRegistered) {
                return;
            }

            const register = () => {
                window.__chatMorphHookRegistered = true;
                Livewire.hook('morph.updated', () => {
                    window.dispatchEvent(new CustomEvent('messages-updated'));
                });
            };

            if (window.Livewire?.hook) {
                register();
            } else {
                document.addEventListener('livewire:initialized', register, { once: true });
            }
        })();
    </script>
</div>
