<div class="po-page">
    @include('filament.resources.purchase-orders.partials.styles')

    @php
        $statsData = $this->stats();
        $ordersList = $this->orders();
        $months = $this->monthOptions();
    @endphp

    <div class="po-head">
        <div>
            <h1 class="po-title">{{ __('purchase_order.list.title') }}</h1>
            <p class="po-subtitle">{{ __('purchase_order.list.subtitle') }}</p>
        </div>
        <div class="po-actions">
            <a href="{{ url('/admin/list-hang') }}" class="po-btn po-btn-primary">
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
            <div class="py-kico" style="background:var(--po-or-s);color:var(--po-or)">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <div class="py-klbl">{{ __('purchase_order.kpi.awaiting_check') }}</div>
                <div class="py-kval">{{ $statsData['pending_orders'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-gn-s);color:var(--po-gn)">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="py-klbl">{{ __('purchase_order.kpi.completed') }}</div>
                <div class="py-kval">{{ $statsData['done_orders'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-gn-s);color:var(--po-gn)">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div>
                <div class="py-klbl">{{ __('purchase_order.kpi.month_value') }}</div>
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
            <input wire:model.live.debounce.250ms="search" type="text" placeholder="{{ __('purchase_order.placeholders.search') }}">
        </div>

        <select wire:model.live="monthFilter" class="mp-sel">
            @foreach($months as $val => $lbl)
                <option value="{{ $val }}">{{ $lbl }}</option>
            @endforeach
        </select>

        <select wire:model.live="typeFilter" class="mp-sel">
            <option value="">{{ __('purchase_order.filters.all_types') }}</option>
            <option value="week">{{ __('purchase_order.types.week') }}</option>
            <option value="day">{{ __('purchase_order.types.day') }}</option>
        </select>

        <select wire:model.live="statusFilter" class="mp-sel" id="ohStFilter">
            <option value="">{{ __('purchase_order.filters.all_statuses') }}</option>
            <option value="draft">{{ __('purchase_order.status.draft') }}</option>
            <option value="sent">{{ __('purchase_order.status.sent_short') }}</option>
            <option value="checking">{{ __('purchase_order.status.checking') }}</option>
            <option value="done">{{ __('purchase_order.status.done') }}</option>
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
                            {{ __('purchase_order.labels.created_at', ['date' => $order->created_at->format('d/m/Y H:i')]) }}
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
                        {{ __('purchase_order.currency.thousand', ['value' => number_format($orderTotal / 1000, 0, ',', '.')]) }}
                    </div>

                    <!-- Actions -->
                    <div style="display:flex;gap:5px">
                        <!-- Xem chi tiết -->
                        <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('edit', ['record' => $order]) }}" class="abt" title="{{ __('purchase_order.actions.view_details') }}">
                            <i class="fa-solid fa-eye"></i>
                        </a>

                        <!-- Kiểm hàng (chuyển hướng sang kho tab nhập PO) -->
                        @if(in_array($order->status, ['sent', 'checking']) && is_null($order->stocked_at))
                            <a href="{{ url('/admin/stocks?tab=in&inMode=po&po_id=' . $order->id) }}" class="abt" title="{{ __('purchase_order.actions.check_goods') }}" style="color:var(--po-or)">
                                <i class="fa-solid fa-clipboard-check"></i>
                            </a>
                        @endif

                        <!-- Xuất Excel của đơn này -->
                        <button wire:click="exportSingleOrder({{ $order->id }})" class="abt" title="{{ __('purchase_order.actions.export_excel') }}">
                            <i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i>
                        </button>

                        <!-- Menu/Xóa đơn -->
                        <button wire:click="deleteOrder({{ $order->id }})" wire:confirm="{{ __('purchase_order.confirm.delete') }}" class="abt" title="{{ __('purchase_order.actions.delete') }}">
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
