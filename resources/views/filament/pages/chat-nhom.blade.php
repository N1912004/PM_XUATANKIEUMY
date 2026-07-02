@push('styles')
<style>
        .chat-root-container {
            display: grid;
            grid-template-columns: 320px 1fr;
            height: calc(100vh - 170px);
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        .dark .chat-root-container {
            background: #0f172a;
            border-color: #1e293b;
            box-shadow: none;
        }

        /* LEFT SIDEBAR */
        .chat-sidebar {
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            background: #f8fafc;
        }
        .dark .chat-sidebar {
            border-color: #1e293b;
            background: #0f172a;
        }
        .sidebar-header {
            padding: 1.25rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .dark .sidebar-header {
            border-color: #1e293b;
        }
        .sidebar-header h1 {
            font-size: 1.125rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }
        .dark .sidebar-header h1 {
            color: #ffffff;
        }
        .sidebar-header p {
            font-size: 0.72rem;
            color: #64748b;
            line-height: 1.3;
        }
        .dark .sidebar-header p {
            color: #94a3b8;
        }

        .search-chat-box {
            padding: 0.75rem 1.25rem;
            display: flex;
            gap: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
            align-items: center;
        }
        .dark .search-chat-box {
            border-color: #1e293b;
        }
        .search-input-wrapper {
            position: relative;
            flex: 1;
            display: flex;
            align-items: center;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0 0.5rem;
            height: 34px;
        }
        .dark .search-input-wrapper {
            background: #1e293b;
            border-color: #334155;
        }
        .search-input-wrapper input {
            border: none;
            background: transparent;
            font-size: 0.78rem;
            width: 100%;
            padding-left: 0.375rem;
            outline: none;
            color: #0f172a;
        }
        .dark .search-input-wrapper input {
            color: #ffffff;
        }
        .filter-btn {
            width: 34px;
            height: 34px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            color: #64748b;
            cursor: pointer;
        }
        .dark .filter-btn {
            border-color: #334155;
            background: #1e293b;
            color: #cbd5e1;
        }

        .chat-list {
            flex: 1;
            overflow-y: auto;
        }
        .chat-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: all 0.2s;
        }
        .dark .chat-item {
            border-color: #1e293b;
        }
        .chat-item:hover {
            background: #f1f5f9;
        }
        .dark .chat-item:hover {
            background: #1e293b/40;
        }
        .chat-item.active {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
        }
        .dark .chat-item.active {
            background: #1e3a8a/30;
            border-left-color: #3b82f6;
        }

        .group-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #ffffff;
            flex-shrink: 0;
        }
        .avatar-blue { background: #3b82f6; }
        .avatar-orange { background: #f97316; }
        .avatar-green { background: #22c55e; }
        .avatar-purple { background: #a855f7; }
        .avatar-pink { background: #ec4899; }

        .group-details {
            flex: 1;
            min-width: 0;
        }
        .group-name {
            font-weight: 700;
            font-size: 0.8125rem;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .dark .group-name {
            color: #ffffff;
        }
        .group-desc {
            font-size: 0.72rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 0.125rem;
        }
        .dark .group-desc {
            color: #94a3b8;
        }
        .group-meta {
            font-size: 0.65rem;
            color: #94a3b8;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .meta-dot {
            width: 3px;
            height: 3px;
            background: #cbd5e1;
            border-radius: 50%;
        }
        .unread-badge {
            background: #ef4444;
            color: #ffffff;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.125rem 0.375rem;
            border-radius: 10px;
        }

        /* MAIN CHAT AREA */
        .chat-main {
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }
        .dark .chat-main {
            background: #0b0f19;
        }
        .chat-header {
            padding: 0.875rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .dark .chat-header {
            border-color: #1e293b;
        }
        .header-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            color: #ffffff;
        }
        .header-title-block {
            flex: 1;
        }
        .header-name {
            font-weight: 800;
            font-size: 0.875rem;
            color: #0f172a;
        }
        .dark .header-name {
            color: #ffffff;
        }
        .header-status {
            font-size: 0.72rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .dark .header-status {
            color: #94a3b8;
        }
        .online-dot {
            width: 6px;
            height: 6px;
            background: #22c55e;
            border-radius: 50%;
            display: inline-block;
        }
        .header-actions {
            display: flex;
            gap: 0.375rem;
        }
        .header-action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            transition: background 0.2s;
        }
        .header-action-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .dark .header-action-btn:hover {
            background: #1e293b;
            color: #ffffff;
        }

        /* MESSAGES LIST */
        .messages-area {
            flex: 1;
            padding: 1.25rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            background: #faf8f6/20;
        }
        .day-separator {
            text-align: center;
            font-size: 0.68rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0.5rem 0;
            position: relative;
        }
        .day-separator::before {
            content: "";
            position: absolute;
            left: 0;
            top: 50%;
            width: 40%;
            height: 1px;
            background: #f1f5f9;
        }
        .dark .day-separator::before { background: #1e293b; }
        .day-separator::after {
            content: "";
            position: absolute;
            right: 0;
            top: 50%;
            width: 40%;
            height: 1px;
            background: #f1f5f9;
        }
        .dark .day-separator::after { background: #1e293b; }

        .message-row {
            display: flex;
            gap: 0.75rem;
            max-width: 75%;
        }
        .message-row.me {
            align-self: flex-end;
            flex-direction: row-reverse;
        }
        .message-avatar img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }
        .message-content {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .message-sender {
            font-size: 0.72rem;
            font-weight: 700;
            color: #475569;
        }
        .dark .message-sender {
            color: #94a3b8;
        }
        .message-bubble {
            background: #f1f5f9;
            color: #0f172a;
            padding: 0.625rem 0.875rem;
            border-radius: 0 12px 12px 12px;
            font-size: 0.8125rem;
            line-height: 1.4;
        }
        .dark .message-bubble {
            background: #1e293b;
            color: #cbd5e1;
        }
        .message-row.me .message-bubble {
            background: #3b82f6;
            color: #ffffff;
            border-radius: 12px 0 12px 12px;
        }
        .message-time {
            font-size: 0.65rem;
            color: #94a3b8;
            margin-top: 0.125rem;
            align-self: flex-end;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .message-tick {
            color: #3b82f6;
        }
        .message-row.me .message-time {
            color: #94a3b8;
            align-self: flex-start;
        }

        /* Attachment layouts */
        .file-bubble {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.625rem;
            margin-top: 0.375rem;
            min-width: 240px;
        }
        .dark .file-bubble {
            background: #1e293b/60;
            border-color: #334155;
        }
        .file-icon {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            background: #ef4444;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.68rem;
            font-weight: 800;
        }
        .file-name {
            font-size: 0.78rem;
            font-weight: 700;
            color: #0f172a;
        }
        .dark .file-name {
            color: #ffffff;
        }
        .file-size {
            font-size: 0.68rem;
            color: #64748b;
            margin-top: 0.125rem;
        }
        .dark .file-size {
            color: #94a3b8;
        }

        .img-bubble {
            border-radius: 8px;
            overflow: hidden;
            margin-top: 0.375rem;
            border: 1px solid #e2e8f0;
            max-width: 320px;
        }
        .dark .img-bubble {
            border-color: #1e293b;
        }
        .img-bubble img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* CHAT INPUT AREA */
        .chat-input-bar {
            padding: 1rem 1.25rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: #ffffff;
        }
        .dark .chat-input-bar {
            border-color: #1e293b;
            background: #0f172a;
        }
        .attach-btn {
            color: #64748b;
            cursor: pointer;
            font-size: 1.125rem;
        }
        .attach-btn:hover {
            color: #0f172a;
        }
        .dark .attach-btn:hover {
            color: #ffffff;
        }
        .input-box-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 0.5rem 0.875rem;
            height: 40px;
        }
        .dark .input-box-wrapper {
            background: #1e293b;
        }
        .input-box-wrapper input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 0.8125rem;
            width: 100%;
            color: #0f172a;
        }
        .dark .input-box-wrapper input {
            color: #ffffff;
        }
        .emoji-btn {
            font-size: 1.125rem;
            cursor: pointer;
            user-select: none;
        }
</style>
@endpush

<x-filament-panels::page>
    <div class="chat-root-container">
        <!-- LEFT SIDEBAR: CHAT GROUPS -->
        <div class="chat-sidebar">
            <div class="sidebar-header">
                <h1>Chat nhóm</h1>
                <p>Truy cập các nhóm chat được phân quyền để trao đổi tin nhắn và gửi file nội bộ</p>
            </div>
            
            <div class="search-chat-box">
                <div class="search-input-wrapper">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" placeholder="Tìm nhóm chat...">
                </div>
                <button class="filter-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </button>
            </div>

            <div class="chat-list">
                <div class="chat-item active">
                    <div class="group-avatar avatar-blue">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="group-details">
                        <div class="group-name">Vận hành ca sáng</div>
                        <div class="group-desc">Trao đổi công việc ca sáng</div>
                        <div class="group-meta">
                            <span>18 thành viên</span>
                            <span class="meta-dot"></span>
                            <span>2 phút trước</span>
                        </div>
                    </div>
                    <div class="unread-badge">5</div>
                </div>

                <div class="chat-item">
                    <div class="group-avatar avatar-orange">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div class="group-details">
                        <div class="group-name">Bếp trung tâm</div>
                        <div class="group-desc">Trao đổi công việc bếp</div>
                        <div class="group-meta">
                            <span>15 thành viên</span>
                            <span class="meta-dot"></span>
                            <span>15 phút trước</span>
                        </div>
                    </div>
                    <div class="unread-badge">3</div>
                </div>

                <div class="chat-item">
                    <div class="group-avatar avatar-green">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div class="group-details">
                        <div class="group-name">Kho nguyên liệu</div>
                        <div class="group-desc">Nhập xuất & tồn kho</div>
                        <div class="group-meta">
                            <span>12 thành viên</span>
                            <span class="meta-dot"></span>
                            <span>1 giờ trước</span>
                        </div>
                    </div>
                    <div class="unread-badge">2</div>
                </div>

                <div class="chat-item">
                    <div class="group-avatar avatar-purple">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="group-details">
                        <div class="group-name">Nhân sự nội bộ</div>
                        <div class="group-desc">Trao đổi công việc nhân sự</div>
                        <div class="group-meta">
                            <span>22 thành viên</span>
                            <span class="meta-dot"></span>
                            <span>2 giờ trước</span>
                        </div>
                    </div>
                </div>

                <div class="chat-item">
                    <div class="group-avatar avatar-pink">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        </svg>
                    </div>
                    <div class="group-details">
                        <div class="group-name">Dự án triển khai</div>
                        <div class="group-desc">Cập nhật tiến độ dự án</div>
                        <div class="group-meta">
                            <span>10 thành viên</span>
                            <span class="meta-dot"></span>
                            <span>Hôm qua</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: CONVERSATION AREA -->
        <div class="chat-main">
            <div class="chat-header">
                <div class="header-avatar avatar-blue" style="width: 36px; height: 36px; border-radius: 8px;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="header-title-block">
                    <div class="header-name">Vận hành ca sáng</div>
                    <div class="header-status">
                        <span class="online-dot"></span>
                        <span>18 thành viên • 6 đang hoạt động</span>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="header-action-btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                    <button class="header-action-btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </button>
                    <button class="header-action-btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- MESSAGES CONTAINER -->
            <div class="messages-area" id="messageArea">
                <div class="day-separator">Hôm nay</div>

                <!-- Message 1 -->
                <div class="message-row">
                    <div class="message-avatar">
                        <img src="https://images.unsplash.com/photo-1494790108755-2616b612b884?w=60&h=60&fit=crop&crop=face" alt="Avatar">
                    </div>
                    <div class="message-content">
                        <div class="message-sender">Trần Thị Bình</div>
                        <div class="message-bubble">
                            Chào cả team, ca sáng hôm nay mọi người chú ý kiểm tra nhiệt độ bảo quản nguyên liệu nhé.
                        </div>
                        <div class="message-time">06:45</div>
                    </div>
                </div>

                <!-- Message 2 (File attachment) -->
                <div class="message-row">
                    <div class="message-avatar">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=60&h=60&fit=crop&crop=face" alt="Avatar">
                    </div>
                    <div class="message-content">
                        <div class="message-sender">Lê Hoàng Cường</div>
                        <div class="message-bubble">
                            Mình đã cập nhật file hướng dẫn vận hành mới. Mọi người xem và thực hiện theo nhé.
                        </div>
                        <div class="file-bubble">
                            <div class="file-icon">PDF</div>
                            <div>
                                <div class="file-name">Huong_dan_van_hanh_ca_sang.pdf</div>
                                <div class="file-size">PDF • 1.8 MB</div>
                            </div>
                            <svg class="w-4 h-4 ml-auto text-gray-400 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </div>
                        <div class="message-time">06:52</div>
                    </div>
                </div>

                <!-- Message 3 (Image attachment) -->
                <div class="message-row">
                    <div class="message-avatar">
                        <img src="https://images.unsplash.com/photo-1544725176-7c40e5a71c5e?w=60&h=60&fit=crop&crop=face" alt="Avatar">
                    </div>
                    <div class="message-content">
                        <div class="message-sender">Phạm Thị Dung</div>
                        <div class="message-bubble">
                            Hình ảnh kiểm tra khu vực bếp sáng nay.
                        </div>
                        <div class="img-bubble">
                            <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=320&h=180&fit=crop" alt="Kitchen View">
                        </div>
                        <div class="message-time">06:58</div>
                    </div>
                </div>

                <!-- Message 4 (Self message) -->
                <div class="message-row me">
                    <div class="message-content">
                        <div class="message-bubble">
                            Cảm ơn mọi người. Mình đã ghi nhận các thông tin.
                        </div>
                        <div class="message-time">
                            <span>07:02</span>
                            <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CHAT INPUT -->
            <div class="chat-input-bar">
                <svg class="attach-btn w-5 h-5 text-gray-500 hover:text-gray-900 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
                
                <div class="input-box-wrapper">
                    <input type="text" id="chatMessageInput" placeholder="Nhập nội dung..." onkeydown="handleInputSubmit(event)">
                    <span class="emoji-btn">☺</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>

@push('scripts')
    <script>
        function handleInputSubmit(event) {
            if (event.key !== 'Enter') return;
            const input = document.getElementById('chatMessageInput');
            const txt = input.value.trim();
            if (!txt) return;

            const area = document.getElementById('messageArea');
            const row = document.createElement('div');
            row.className = 'message-row me';

            const now = new Date();
            const timeStr = now.toLocaleTimeString('vi', { hour: '2-digit', minute: '2-digit' });

            row.innerHTML = `
                <div class="message-content">
                    <div class="message-bubble">
                        ${txt}
                    </div>
                    <div class="message-time">
                        <span>${timeStr}</span>
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            `;

            area.appendChild(row);
            input.value = '';
            area.scrollTop = area.scrollHeight;
        }
    </script>
@endpush
