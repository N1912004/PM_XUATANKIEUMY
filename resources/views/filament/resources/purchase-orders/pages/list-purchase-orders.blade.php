<div class="po-page">
    @include('filament.resources.purchase-orders.partials.styles')

    @php
        $statsData = $this->stats();
        $ordersList = $this->orders();
        $months = $this->monthOptions();
    @endphp

    <div class="po-head">
        <div>
            <h1 class="po-title">Đặt hàng</h1>
            <p class="po-subtitle">Quản lý đơn đặt hàng nguyên liệu theo nhà cung cấp từ list hàng đã chốt</p>
        </div>
        <div class="po-actions">
            <button wire:click="exportExcel" class="po-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--po-gn)">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
                Xuất Excel
            </button>
            <a href="{{ url('/admin/list-hang') }}" class="po-btn po-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tạo đơn đặt hàng
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="po-kpis">
        <div class="po-kpi">
            <div class="po-kpi-icon po-ico-blue">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
            </div>
            <div class="po-kpi-info">
                <div class="po-kpi-label">Tổng đơn tháng này</div>
                <div class="po-kpi-value">{{ $statsData['total_orders'] }}</div>
            </div>
        </div>

        <div class="po-kpi">
            <div class="po-kpi-icon po-ico-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
            <div class="po-kpi-info">
                <div class="po-kpi-label">Chờ kiểm hàng</div>
                <div class="po-kpi-value">{{ $statsData['pending_orders'] }}</div>
            </div>
        </div>

        <div class="po-kpi">
            <div class="po-kpi-icon po-ico-green">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div class="po-kpi-info">
                <div class="po-kpi-label">Đã hoàn thành</div>
                <div class="po-kpi-value">{{ $statsData['done_orders'] }}</div>
            </div>
        </div>

        <div class="po-kpi">
            <div class="po-kpi-icon po-ico-purple">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="po-kpi-info">
                <div class="po-kpi-label">Tổng giá trị tháng</div>
                <div class="po-kpi-value">{{ $this->formatFriendly($statsData['total_value']) }}</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="po-filter-bar">
        <div class="po-search">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input wire:model.live.debounce.250ms="search" type="text" placeholder="Tìm kiếm đơn hàng, NCC...">
        </div>

        <select wire:model.live="monthFilter" class="po-select">
            @foreach($months as $val => $lbl)
                <option value="{{ $val }}">{{ $lbl }}</option>
            @endforeach
        </select>

        <select wire:model.live="typeFilter" class="po-select">
            <option value="">Tất cả loại</option>
            <option value="week">Đặt hàng tuần</option>
            <option value="day">Đặt hàng ngày</option>
        </select>

        <select wire:model.live="statusFilter" class="po-select">
            <option value="">Tất cả trạng thái</option>
            <option value="draft">Nháp</option>
            <option value="sent">Đã gửi</option>
            <option value="checking">Đang kiểm hàng</option>
            <option value="done">Hoàn thành</option>
        </select>

        <div style="flex:1"></div>

        <button wire:click="resetFilters" class="po-reset-btn" title="Cài lại bộ lọc">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
            </svg>
        </button>
    </div>

    <!-- Orders List -->
    <div class="po-list">
        @forelse($ordersList as $order)
            @php
                $orderTotal = $order->items->sum(fn($it) => $it->quantity_ordered * $it->unit_price);
                $isWeek = str_contains(mb_strtolower($order->note ?? ''), 'tuần') || str_contains(strtolower($order->code ?? ''), 'tuan');
            @endphp
            <div class="po-item">
                <!-- Status Icon -->
                @if($order->status === 'done')
                    <div class="po-item-icon" style="background:#ECFDF5;color:var(--po-gn)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                @elseif($order->status === 'checking')
                    <div class="po-item-icon" style="background:var(--po-or-s);color:var(--po-or)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                        </svg>
                    </div>
                @else
                    <div class="po-item-icon" style="background:#EBF3FF;color:var(--po-bl)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                    </div>
                @endif

                <div class="po-item-info">
                    <div class="po-item-title">
                        {{ $order->code }}
                        @if($isWeek)
                            <span class="ot-tuan">Tuần</span>
                        @else
                            <span class="ot-ngay">Ngày</span>
                        @endif
                    </div>
                    <div class="po-item-sub">NCC: {{ $order->supplier?->name ?? 'Chưa gán' }}</div>
                    <div class="po-item-meta">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            {{ $order->estimated_delivery_date ? $order->estimated_delivery_date->format('d/m/Y') : '--' }}
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                            {{ $order->items_count }} nguyên liệu
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Tạo: {{ $order->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>

                <div class="po-item-right">
                    <!-- Status Badge -->
                    @if($order->status === 'done')
                        <span class="os-done">Hoàn thành</span>
                    @elseif($order->status === 'checking')
                        <span class="os-checking">Đang kiểm hàng</span>
                    @elseif($order->status === 'sent')
                        <span class="os-sent">Đã gửi NCC</span>
                    @else
                        <span class="os-draft">Nháp</span>
                    @endif

                    <!-- Value -->
                    <div class="po-item-val">{{ $this->formatFriendly($orderTotal) }}</div>

                    <!-- Actions -->
                    <div class="po-item-actions">
                        <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('edit', ['record' => $order]) }}" class="po-item-action" title="Sửa đơn hàng">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </a>
                        <button wire:click="deleteOrder({{ $order->id }})" wire:confirm="Bạn có chắc chắn muốn xóa đơn hàng này?" class="po-item-action po-item-action-danger" title="Xóa đơn hàng">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                <line x1="10" y1="11" x2="10" y2="17"/>
                                <line x1="14" y1="11" x2="14" y2="17"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="po-empty">
                <div class="po-empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <div class="po-empty-title">Không tìm thấy đơn hàng nào</div>
                <div class="po-empty-sub">Hãy thử điều chỉnh bộ lọc hoặc nhập từ khóa tìm kiếm khác.</div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($ordersList->hasPages())
        @php
            $currentPage = $ordersList->currentPage();
            $lastPage = $ordersList->lastPage();
            $pageWindow = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($p) => $p >= 1 && $p <= $lastPage)
                ->unique()
                ->sort()
                ->values();
        @endphp
        <div class="po-footer">
            <div>
                Hiển thị {{ $ordersList->firstItem() }} - {{ $ordersList->lastItem() }} trong tổng số {{ number_format($ordersList->total(), 0, ',', '.') }} đơn hàng
            </div>
            <div class="po-pagination">
                <select wire:model.live="perPage" class="po-select" style="min-width:7rem;height:2rem;padding:0 .5rem;border-radius:.5rem">
                    <option value="10">10 / trang</option>
                    <option value="20">20 / trang</option>
                    <option value="50">50 / trang</option>
                </select>

                <nav role="navigation" aria-label="Pagination Navigation">
                    {{-- Previous --}}
                    @if ($ordersList->onFirstPage())
                        <span aria-disabled="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="15 18 9 12 15 6"/></svg>
                        </span>
                    @else
                        <button type="button" wire:click="previousPage" rel="prev">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                    @endif

                    {{-- Windowed page numbers --}}
                    @foreach ($pageWindow as $i => $page)
                        @if ($i > 0 && $page - $pageWindow[$i - 1] > 1)
                            <span class="po-page-dots" aria-hidden="true">…</span>
                        @endif
                        @if ($page == $currentPage)
                            <span aria-current="page"><span>{{ $page }}</span></span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }})">{{ $page }}</button>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($ordersList->hasMorePages())
                        <button type="button" wire:click="nextPage" rel="next">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    @else
                        <span aria-disabled="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="9 18 15 12 9 6"/></svg>
                        </span>
                    @endif
                </nav>
            </div>
        </div>
    @endif
</div>
