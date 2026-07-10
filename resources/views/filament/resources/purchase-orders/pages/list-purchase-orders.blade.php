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
            <a href="{{ url('/admin/list-hang') }}" class="po-btn po-btn-primary">
                <i class="fa-solid fa-plus"></i>
                Tạo đơn đặt hàng
            </a>
        </div>
    </div>

    <!-- KPIs Stats -->
    <div class="py-krow" style="margin-bottom:16px">
        <div class="py-kcard">
            <div class="py-kico" style="background:#EBF3FF;color:var(--po-bl)">
                <i class="fa-solid fa-file-lines"></i>
            </div>
            <div>
                <div class="py-klbl">Tổng đơn tháng này</div>
                <div class="py-kval">{{ $statsData['total_orders'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-or-s);color:var(--po-or)">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <div class="py-klbl">Chờ kiểm hàng</div>
                <div class="py-kval">{{ $statsData['pending_orders'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-gn-s);color:var(--po-gn)">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="py-klbl">Đã hoàn thành</div>
                <div class="py-kval">{{ $statsData['done_orders'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:#F0FDF4;color:#15803D">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div>
                <div class="py-klbl">Tổng giá trị tháng</div>
                <div class="py-kval" style="font-size:18px">
                    {{ number_format($statsData['total_value'] / 1000000, 0) }} tr
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="mp-bar" style="margin-bottom:14px">
        <div class="mp-srch">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input wire:model.live.debounce.250ms="search" type="text" placeholder="Tìm kiếm đơn hàng, NCC...">
        </div>

        <select wire:model.live="monthFilter" class="mp-sel">
            @foreach($months as $val => $lbl)
                <option value="{{ $val }}">{{ $lbl }}</option>
            @endforeach
        </select>

        <select wire:model.live="typeFilter" class="mp-sel">
            <option value="">Tất cả loại</option>
            <option value="week">Đặt hàng tuần</option>
            <option value="day">Đặt hàng ngày</option>
        </select>

        <select wire:model.live="statusFilter" class="mp-sel" id="ohStFilter">
            <option value="">Tất cả trạng thái</option>
            <option value="draft">Nháp</option>
            <option value="sent">Đã gửi</option>
            <option value="checking">Đang kiểm hàng</option>
            <option value="done">Hoàn thành</option>
        </select>

        <div class="tsp"></div>

        <button wire:click="resetFilters" class="att-rbtn" title="Cài lại bộ lọc">
            <i class="fa-solid fa-rotate-right"></i>
        </button>
    </div>

    <!-- Orders List -->
    <div class="po-list">
        @forelse($ordersList as $order)
            @php
                $orderTotal = $order->items->sum(fn($it) => $it->quantity_ordered * $it->unit_price);
                $isWeek = str_contains(mb_strtolower($order->note ?? ''), 'tuần') || str_contains(strtolower($order->code ?? ''), 'tuan');
            @endphp
            <div class="oh-item" style="position:relative">
                {{-- Stretched link: cả dòng bấm được, điều hướng chuẩn (SPA-safe) thay vì onclick JS --}}
                <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('edit', ['record' => $order]) }}" wire:navigate aria-label="Xem chi tiết đơn {{ $order->code }}" style="position:absolute; inset:0; z-index:0"></a>
                <!-- Status Icon -->
                @if($order->status === 'done')
                    <div class="oh-item-ico" style="background:var(--po-gn-s);color:var(--po-gn)">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                @elseif($order->status === 'checking')
                    <div class="oh-item-ico" style="background:var(--po-or-s);color:var(--po-or)">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                @else
                    <div class="oh-item-ico" style="background:#EBF3FF;color:var(--po-bl)">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                @endif

                <!-- Order Info -->
                <div class="oh-item-info">
                    <div class="oh-item-title">
                        {{ $order->code }}
                    </div>
                    <div style="font-size:12px;color:var(--po-mu);margin-top:2px">
                        NCC: {{ $order->supplier?->name ?? 'Chưa gán' }}
                    </div>
                    <div class="oh-item-meta">
                        <span>
                            <i class="fa-regular fa-calendar"></i>
                            {{ $order->estimated_delivery_date ? $order->estimated_delivery_date->format('d/m/Y') : '--' }}
                        </span>
                        @if($isWeek)
                            <span class="ot-kho" style="font-size:11px">Tuần</span>
                        @else
                            <span class="ot-uot" style="font-size:11px">Ngày</span>
                        @endif
                        <span>
                            <i class="fa-solid fa-seedling"></i>
                            {{ $order->items_count }} nguyên liệu
                        </span>
                        <span>
                            <i class="fa-regular fa-clock"></i>
                            Tạo: {{ $order->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>

                <!-- Order Right -->
                <div class="oh-item-right" style="position:relative; z-index:1">
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
                    <div class="oh-item-val">
                        {{ number_format($orderTotal / 1000, 0, ',', '.') }} nghìn đ
                    </div>

                    <!-- Actions -->
                    <div style="display:flex;gap:5px">
                        <!-- Xem chi tiết -->
                        <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('edit', ['record' => $order]) }}" class="abt" title="Xem chi tiết">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                        <!-- Kiểm hàng (chuyển hướng sang kho tab nhập PO) -->
                        @if(in_array($order->status, ['sent', 'checking']) && is_null($order->stocked_at))
                            <a href="{{ url('/admin/stocks?tab=in&inMode=po&po_id=' . $order->id) }}" class="abt" title="Kiểm hàng" style="color:var(--po-or)">
                                <i class="fa-solid fa-clipboard-check"></i>
                            </a>
                        @endif

                        <!-- Xuất Excel của đơn này -->
                        <button wire:click="exportSingleOrder({{ $order->id }})" class="abt" title="Xuất Excel">
                            <i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i>
                        </button>

                        <!-- Menu/Xóa đơn -->
                        <button wire:click="deleteOrder({{ $order->id }})" wire:confirm="Bạn có chắc chắn muốn xóa đơn hàng này?" class="abt" title="Xóa đơn hàng">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="po-empty">
                <div class="po-empty-icon">
                    <i class="fa-solid fa-circle-info" style="font-size: 24px;"></i>
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
                            <i class="fa-solid fa-chevron-left" style="font-size: 10px;"></i>
                        </span>
                    @else
                        <button type="button" wire:click="previousPage" rel="prev">
                            <i class="fa-solid fa-chevron-left" style="font-size: 10px;"></i>
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
                            <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
                        </button>
                    @else
                        <span aria-disabled="true">
                            <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
                        </span>
                    @endif
                </nav>
            </div>
        </div>
    @endif
</div>
