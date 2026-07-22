<x-filament-panels::page>
    @include('filament.resources.purchase-orders.partials.styles')

    @php
        $shifts = $this->shifts;
        $suppliers = $this->suppliers;
        $aggregatedGroups = $this->getAggregatedGroupsProperty();

        $allFlatItems = [];
        foreach ($aggregatedGroups as $grp) {
            foreach ($grp['items'] as $it) {
                $allFlatItems[] = $it;
            }
        }

        $totalCount = count($allFlatItems);
        $selectedItems = array_filter($allFlatItems, fn($it) => !empty($it['selected']));
        $selectedCount = count($selectedItems);
        $distinctSuppliersCount = collect($selectedItems)->pluck('supplier_id')->unique()->count();
        $distinctSplitsCount = collect($selectedItems)->pluck('split')->unique()->count();
        $grandTotal = collect($selectedItems)->sum('line_total');
    @endphp

    <div class="po-page w-full space-y-6" style="padding: 0 !important; background: transparent !important;">
        <!-- Header Bar -->
        <div class="po-head" style="margin-bottom:16px">
            <div>
                <h1 class="po-title" style="font-size:20px">{{ __('purchase_order.actions.create_order_title') }}</h1>
                <p class="po-subtitle">{{ __('purchase_order.actions.create_order_subtitle') }}</p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap">
                <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('index') }}" class="po-btn">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('purchase_order.actions.back') }}
                </a>
                <button wire:click="createAndSendOrders" class="po-btn po-btn-primary">
                    <i class="fa-solid fa-paper-plane"></i> {{ __('purchase_order.actions.create_and_send') }}
                </button>
            </div>
        </div>

        <!-- Step Wizard -->
        <div class="oh-steps">
            <div class="oh-step oh-step-done">
                <div class="oh-step-num"><i class="fa-solid fa-check" style="font-size:11px"></i></div>
                <div>
                    <div class="oh-step-lbl">{{ __('purchase_order.steps.step1_title') }}</div>
                    <div style="font-size:11px; color:var(--po-fa)">{{ date('d/m/Y', strtotime($sourceFrom)) }}</div>
                </div>
            </div>
            <div class="oh-step-line done"></div>
            <div class="oh-step oh-step-active">
                <div class="oh-step-num">2</div>
                <div>
                    <div class="oh-step-lbl">{{ __('purchase_order.steps.step2_title') }}</div>
                    <div style="font-size:11px; color:var(--po-fa)">{{ __('purchase_order.steps.step2_sub') }}</div>
                </div>
            </div>
            <div class="oh-step-line"></div>
            <div class="oh-step oh-step-pending">
                <div class="oh-step-num">3</div>
                <div>
                    <div class="oh-step-lbl">{{ __('purchase_order.steps.step3_title') }}</div>
                    <div style="font-size:11px; color:var(--po-fa)">{{ __('purchase_order.steps.step3_sub') }}</div>
                </div>
            </div>
        </div>

        <!-- Source Selector Box -->
        <div class="tcard" style="padding:14px 18px; margin-bottom:14px; display:flex; align-items:flex-end; gap:14px; flex-wrap:wrap">
            <div class="field" style="min-width:180px">
                <label style="font-size:11.5px; font-weight:700; color:var(--po-su)">{{ __('purchase_order.fields.order_date') }}</label>
                <input type="date" class="ctrl" wire:model.live="orderDate" style="height:38px" required>
            </div>
            <div class="field" style="min-width:160px">
                <label style="font-size:11.5px; font-weight:700; color:var(--po-su)">{{ __('purchase_order.fields.source_from') }}</label>
                <input type="date" class="ctrl" wire:model.live="sourceFrom" style="height:38px" required>
            </div>
            <div class="field" style="min-width:160px">
                <label style="font-size:11.5px; font-weight:700; color:var(--po-su)">{{ __('purchase_order.fields.source_to') }}</label>
                <input type="date" class="ctrl" wire:model.live="sourceTo" style="height:38px" required>
            </div>
            <div class="field" style="min-width:280px; flex:1">
                <label style="font-size:11.5px; font-weight:700; color:var(--po-su)">{{ __('purchase_order.fields.selected_shifts') }}</label>
                <div style="display:flex; gap:6px; flex-wrap:wrap; background:var(--po-bg); border:1px solid var(--po-bd); border-radius:8px; padding:7px 9px; min-height:38px">
                    @foreach($shifts as $shift)
                        <label style="font-size:12px; font-weight:700; color:var(--po-tx); display:flex; align-items:center; gap:4px; cursor:pointer">
                            <input type="checkbox" value="{{ $shift->id }}" wire:model.live="selectedShifts">
                            {{ $shift->name }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Groups of Ingredients -->
        <div style="display:flex; flex-direction:column; gap:14px">
            @forelse($aggregatedGroups as $gKey => $group)
                @php
                    $groupTotal = collect($group['items'])->sum('line_total');
                @endphp
                <div class="oh-group-card">
                    <!-- Group Header -->
                    <div class="oh-group-head">
                        <div class="oh-group-title">
                            <span class="{{ $group['class'] }}">{{ $group['label'] }}</span>
                            <span style="font-size:12px; color:var(--po-mu)">{{ count($group['items']) }} {{ __('purchase_order.labels.ingredients_count') }}</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px">
                            <span style="font-size:12.5px; color:var(--po-mu)">Gán nhanh NCC:</span>
                            <select class="oh-ncc-sel" wire:model.live="groupSuppliers.{{ $gKey }}">
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <table class="oh-table">
                        <thead>
                            <tr>
                                <th style="width:36px; text-align:center">#</th>
                                <th style="width:40px; text-align:center">ĐẶT</th>
                                <th>TÊN NGUYÊN LIỆU</th>
                                <th>THUỘC MÓN</th>
                                <th style="text-align:center">SỐ SUẤT</th>
                                <th style="text-align:right">SL HỆ THỐNG</th>
                                <th style="text-align:center">SL ĐẶT TAY</th>
                                <th style="text-align:right">ĐƠN GIÁ</th>
                                <th style="text-align:right">THÀNH TIỀN</th>
                                <th style="width:110px">PHIẾU</th>
                                <th style="width:180px">NHÀ CUNG CẤP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group['items'] as $idx => $item)
                                <tr>
                                    <td style="text-align:center; color:var(--po-mu)">{{ $idx + 1 }}</td>
                                    <td style="text-align:center">
                                        <input type="checkbox" wire:model.live="itemSelected.{{ $item['ingredient_id'] }}" style="width:16px; height:16px; accent-color:var(--po-bl); cursor:pointer">
                                    </td>
                                    <td style="font-weight:600; color:var(--po-tx)">
                                        <div>{{ $item['name'] }}</div>
                                    </td>
                                    <td style="font-size:12px; color:var(--po-mu)">
                                        {{ $item['dish_string'] }}
                                    </td>
                                    <td style="text-align:center; color:var(--po-tx); font-weight:500">
                                        {{ number_format($item['servings'], 0) }}
                                    </td>
                                    <td style="text-align:right; font-weight:700; color:var(--po-tx)">
                                        {{ number_format($item['total_kg'], 2) }} {{ $item['unit'] }}
                                    </td>
                                    <td style="text-align:center">
                                        <input type="number" step="0.01" min="0" wire:model.live="itemQuantities.{{ $item['ingredient_id'] }}" 
                                               style="width:75px; height:28px; border:1px solid var(--po-bd); border-radius:6px; text-align:center; font-weight:700; color:var(--po-bl)">
                                    </td>
                                    <td style="text-align:right; color:var(--po-mu)">
                                        {{ number_format($item['reference_price'], 0, ',', '.') }} đ/{{ $item['unit'] }}
                                    </td>
                                    <td style="text-align:right; font-weight:700; color:var(--po-rd)">
                                        {{ number_format($item['line_total'], 0, ',', '.') }} đ
                                    </td>
                                    <td>
                                        <select class="oh-ncc-sel" wire:model.live="itemSplits.{{ $item['ingredient_id'] }}" style="width:100%; min-width:90px">
                                            <option value="Phiếu 1">Phiếu 1</option>
                                            <option value="Phiếu 2">Phiếu 2</option>
                                            <option value="Phiếu 3">Phiếu 3</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="oh-ncc-sel" wire:model.live="itemSuppliers.{{ $item['ingredient_id'] }}" style="width:100%">
                                            @foreach($suppliers as $sup)
                                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                            <!-- Group Total Row -->
                            <tr style="background:#F8FAFC">
                                <td colspan="8" style="text-align:right; font-weight:700; padding:9px 12px; color:var(--po-su)">
                                    {{ __('purchase_order.labels.group_total', ['group' => $group['label']]) }}:
                                </td>
                                <td colspan="3" style="font-weight:800; color:var(--po-bl); font-size:13px; padding:9px 12px">
                                    {{ number_format($groupTotal, 0, ',', '.') }} đ
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="po-empty">
                    <div class="po-empty-icon"><i class="fa-solid fa-seedling" style="font-size:24px"></i></div>
                    <div class="po-empty-title">{{ __('purchase_order.empty.no_ingredients_title') }}</div>
                    <div class="po-empty-sub">{{ __('purchase_order.empty.no_ingredients_sub') }}</div>
                </div>
            @endforelse
        </div>

        <!-- Grand Total Blue Banner (lhn-grand) -->
        <div class="lhn-grand" style="margin-top:14px">
            <div>
                <div class="lhn-grand-lbl">
                    TỔNG ĐƠN ĐẶT HÀNG – ĐẶT HÀNG {{ date('d/m/Y', strtotime($orderDate)) }} · LIST {{ date('d/m/Y', strtotime($sourceFrom)) }} – {{ date('d/m/Y', strtotime($sourceTo)) }}
                </div>
                <div style="font-size:12px; opacity:.85; margin-top:3px">
                    {{ $distinctSuppliersCount }} NCC · {{ $selectedCount }}/{{ $totalCount }} nguyên liệu được đặt · {{ $distinctSplitsCount }} phiếu
                </div>
            </div>
            <div class="lhn-grand-val">
                {{ number_format($grandTotal, 0, ',', '.') }} đ
            </div>
        </div>
    </div>
</x-filament-panels::page>
