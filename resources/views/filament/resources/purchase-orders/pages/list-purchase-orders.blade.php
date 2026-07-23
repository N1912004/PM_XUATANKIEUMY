<div class="po-page w-full space-y-6">
    @include('filament.resources.purchase-orders.partials.styles')

    @php
        $statsData = $this->stats();
        $ordersList = $this->orders();
    @endphp

    <div class="po-head">
        <div>
            <h1 class="po-title">{{ __('purchase_order.list.title') }}</h1>
            <p class="po-subtitle">{{ __('purchase_order.list.subtitle') }}</p>
        </div>
        <div class="po-actions">
            <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('create') }}" class="po-btn po-btn-primary">
                <i class="fa-solid fa-plus"></i>
                {{ __('purchase_order.actions.create') }}
            </a>
        </div>
    </div>

    <!-- KPIs Stats -->
    <div class="py-krow" style="margin-bottom:16px">
        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-bl-s);color:var(--po-bl)">
                <i class="fa-solid fa-file-lines"></i>
            </div>
            <div>
                <div class="py-klbl">{{ __('purchase_order.kpi.month_total') }}</div>
                <div class="py-kval">{{ $statsData['total_orders'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-bl-s);color:var(--po-bl)">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <div class="py-klbl">{{ __('purchase_order.kpi.awaiting_check') }}</div>
                <div class="py-kval">{{ $statsData['pending_orders'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-bl-s);color:var(--po-bl)">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="py-klbl">{{ __('purchase_order.kpi.completed') }}</div>
                <div class="py-kval">{{ $statsData['done_orders'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-bl-s);color:var(--po-bl)">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div>
                <div class="py-klbl">{{ __('purchase_order.kpi.month_value') }}</div>
                <div class="py-kval" style="font-size:16px">
                    {{ __('purchase_order.currency.amount', ['value' => number_format($statsData['total_value'], 0, ',', '.')]) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="mp-bar" style="margin-bottom:14px; display:flex; align-items:center; gap:10px; flex-wrap:wrap">
        <div class="mp-srch">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input wire:model.live.debounce.250ms="search" type="text" placeholder="{{ __('purchase_order.placeholders.search') }}">
        </div>

        <div style="display:inline-flex; align-items:center; gap:6px; background:#fff; padding:6px 12px; border:1px solid var(--po-bd); border-radius:8px">
            <span style="font-size:12.5px; font-weight:600; color:var(--po-mu); white-space:nowrap">{{ __('purchase_order.filters.from_date') }}:</span>
            <input wire:model.live="fromDate" type="date" style="font-size:12.5px; font-weight:600; color:var(--po-tx); border:none; outline:none; background:transparent; cursor:pointer">
        </div>

        <div style="display:inline-flex; align-items:center; gap:6px; background:#fff; padding:6px 12px; border:1px solid var(--po-bd); border-radius:8px">
            <span style="font-size:12.5px; font-weight:600; color:var(--po-mu); white-space:nowrap">{{ __('purchase_order.filters.to_date') }}:</span>
            <input wire:model.live="toDate" type="date" style="font-size:12.5px; font-weight:600; color:var(--po-tx); border:none; outline:none; background:transparent; cursor:pointer">
        </div>

        <select wire:model.live="statusFilter" class="mp-sel" id="ohStFilter">
            <option value="">{{ __('purchase_order.filters.all_statuses') }}</option>
            <option value="sent">{{ __('purchase_order.status.sent_short') }}</option>
            <option value="checking">{{ __('purchase_order.status.checking') }}</option>
            <option value="done">{{ __('purchase_order.status.done') }}</option>
            <option value="cancelled">{{ __('purchase_order.status.cancelled') }}</option>
        </select>

        <div class="tsp"></div>

        <button wire:click="resetFilters" class="att-rbtn" title="{{ __('purchase_order.actions.reset_filters') }}">
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
                <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('edit', ['record' => $order]) }}" wire:navigate aria-label="{{ __('purchase_order.actions.view_order', ['code' => $order->code]) }}" style="position:absolute; inset:0; z-index:0"></a>
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
                    <div class="oh-item-ico" style="background:var(--po-bl-s);color:var(--po-bl)">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                @endif

                <!-- Order Info -->
                <div class="oh-item-info">
                    <div class="oh-item-title">
                        {{ $order->code }}
                    </div>
                    <div style="font-size:12px;color:var(--po-mu);margin-top:2px">
                        {{ __('purchase_order.fields.supplier_abbr') }}: {{ $order->supplier?->name ?? __('purchase_order.labels.unassigned') }}
                    </div>
                    <div class="oh-item-meta">
                        <span>
                            <i class="fa-regular fa-calendar"></i>
                            {{ $order->estimated_delivery_date ? $order->estimated_delivery_date->format('d/m/Y') : '--' }}
                        </span>
                        @if($isWeek)
                            <span class="ot-kho" style="font-size:11px">{{ __('purchase_order.types.week_short') }}</span>
                        @else
                            <span class="ot-uot" style="font-size:11px">{{ __('purchase_order.types.day_short') }}</span>
                        @endif
                        <span>
                            <i class="fa-solid fa-seedling"></i>
                            {{ __('purchase_order.labels.ingredient_count', ['count' => $order->items_count]) }}
                        </span>
                        <span>
                            <i class="fa-regular fa-clock"></i>
                            {{ __('purchase_order.labels.created_at', ['date' => $order->created_at?->timezone('Asia/Ho_Chi_Minh')?->format('d/m/Y H:i')]) }}
                        </span>
                    </div>
                </div>

                <!-- Order Right -->
                <div class="oh-item-right" style="position:relative; z-index:1">
                    <!-- Status Badge -->
                    @if($order->status === 'done')
                        <span class="os-done">{{ __('purchase_order.status.done') }}</span>
                    @elseif($order->status === 'checking')
                        <span class="os-checking">{{ __('purchase_order.status.checking') }}</span>
                    @elseif($order->status === 'sent')
                        <span class="os-sent">{{ __('purchase_order.status.sent') }}</span>
                    @else
                        <span class="os-draft">{{ __('purchase_order.status.draft') }}</span>
                    @endif

                    <!-- Value -->
                    <div class="oh-item-val">
                        {{ __('purchase_order.currency.amount', ['value' => number_format($orderTotal, 0, ',', '.')]) }}
                    </div>

                    <!-- Actions -->
                    <div style="display:flex;gap:5px">
                        <!-- Xem chi tiết -->
                        <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('view', ['record' => $order]) }}" class="abt" title="{{ __('purchase_order.actions.view_details') }}">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                        <!-- Kiểm hàng (chuyển hướng sang trang Kiểm hàng vOrderCheck) -->
                        <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('check', ['record' => $order]) }}" class="abt" title="{{ __('purchase_order.actions.check_goods') }}" style="color:var(--po-or)">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </a>

                        <!-- Xuất Excel của đơn này -->
                        <button wire:click="exportSingleOrder({{ $order->id }})" class="abt" title="{{ __('purchase_order.actions.export_excel') }}">
                            <i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="po-empty">
                <div class="po-empty-icon">
                    <i class="fa-solid fa-circle-info" style="font-size: 24px;"></i>
                </div>
                <div class="po-empty-title">{{ __('purchase_order.empty.title') }}</div>
                <div class="po-empty-sub">{{ __('purchase_order.empty.subtitle') }}</div>
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
                {{ __('purchase_order.pagination.summary', ['from' => $ordersList->firstItem(), 'to' => $ordersList->lastItem(), 'total' => number_format($ordersList->total(), 0, ',', '.')]) }}
            </div>
            <div class="po-pagination">
                <select wire:model.live="perPage" class="po-select" style="min-width:7rem;height:2rem;padding:0 .5rem;border-radius:.5rem">
                    <option value="10">{{ __('purchase_order.pagination.per_page', ['count' => 10]) }}</option>
                    <option value="20">{{ __('purchase_order.pagination.per_page', ['count' => 20]) }}</option>
                    <option value="50">{{ __('purchase_order.pagination.per_page', ['count' => 50]) }}</option>
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
