<x-filament-panels::page>
    @include('filament.resources.purchase-orders.partials.styles')

    @php
        $record = $this->record;
        $record->loadMissing(['supplier', 'items.ingredient']);
        $relatedPOs = $this->getRelatedPOs();
        $colors = ['#267DC1', '#059669', '#D97706', '#7C3AED', '#EA580C', '#EC4899'];
        $currentPOTotal = $record->items->sum(fn($it) => $it->quantity_ordered * $it->unit_price);
        $deliveryDateStr = $record->estimated_delivery_date ? $record->estimated_delivery_date->format('d/m/Y') : '--';
        $activeColorIndex = array_search($record->id, $relatedPOs->pluck('id')->toArray());
        $activeColor = $colors[($activeColorIndex !== false ? $activeColorIndex : 0) % count($colors)];
    @endphp

    <div class="po-page w-full space-y-6" style="padding: 0 !important; background: transparent !important;">
        <!-- Header Section -->
        <div class="po-head" style="margin-bottom: 20px;">
            <div>
                <h1 class="po-title" style="font-size:20px; font-weight:800">
                    {{ __('purchase_order.detail.order_title', ['code' => $record->code, 'date' => $deliveryDateStr]) }}
                </h1>
                <p class="po-subtitle" style="font-size:12.5px; color:var(--po-mu)">
                    {{ __('purchase_order.detail.subtitle', ['date' => $deliveryDateStr]) }}
                </p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap">
                <!-- Nút Quay lại -->
                <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('index') }}" class="po-btn" style="background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-tx)">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('purchase_order.actions.back') }}
                </a>

                <!-- Nút Xuất Excel (tất cả NCC) -->
                <button wire:click="exportAllNcc" class="po-btn" style="background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-tx)">
                    <i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i> {{ __('purchase_order.actions.export_all_suppliers') }}
                </button>

                <!-- Nút Kiểm hàng -->
                <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('check', ['record' => $record]) }}" class="po-btn po-btn-primary">
                    <i class="fa-solid fa-clipboard-check"></i> {{ __('purchase_order.actions.check_goods') }}
                </a>
            </div>
        </div>

        <!-- NCC Tabs -->
        <div class="oh-ncc-tabs" style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap">
            @foreach($relatedPOs as $index => $po)
                @php
                    $poTotal = $po->items->sum(fn($it) => $it->quantity_ordered * $it->unit_price);
                    $isActive = $po->id === $record->id;
                    $color = $colors[$index % count($colors)];
                    $supplierName = $po->supplier?->name ?: $po->code;
                    $sameSupplierPOs = $relatedPOs->where('supplier_id', $po->supplier_id)->values();
                    $hasMultiplePOs = $sameSupplierPOs->count() > 1;
                    $slipSuffix = '';
                    if ($hasMultiplePOs) {
                        $supplierIdx = $sameSupplierPOs->search(fn($p) => $p->id === $po->id);
                        $slipSuffix = ' - P' . (($supplierIdx !== false ? $supplierIdx : 0) + 1);
                    }
                @endphp
                <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('view', ['record' => $po]) }}" 
                   class="oh-ncc-tab" 
                   style="display:flex; align-items:center; gap:8px; padding:8px 14px; border-radius:30px; font-size:13px; font-weight:700; text-decoration:none; transition:.13s; border:1px solid; 
                          {{ $isActive ? 'background:'.$color.'; color:#fff; border-color:'.$color.'; box-shadow: 0 4px 12px '.($color).'33;' : 'background:var(--po-wh); color:var(--po-tx); border-color:var(--po-bd);' }}">
                    
                    <span style="width:9px; height:9px; border-radius:50%; background:{{ $isActive ? '#fff' : $color }}; display:inline-block"></span>

                    <span>{{ $supplierName . $slipSuffix }}</span>

                    <span style="display:inline-flex; align-items:center; justify-content:center; padding:2px 7px; border-radius:10px; font-size:11px; font-weight:800; 
                                 {{ $isActive ? 'background:rgba(255,255,255,0.25); color:#fff;' : 'background:var(--po-bd2); color:var(--po-mu);' }}">
                        {{ $po->items->count() }}
                    </span>

                    <span style="font-size:12px; font-weight:500; {{ $isActive ? 'color:rgba(255,255,255,0.85);' : 'color:var(--po-mu);' }}">
                        {{ number_format(round($poTotal / 1000), 0, ',', '.') }}k đ
                    </span>
                </a>
            @endforeach
        </div>

        <!-- Main Content Area: NCC Detail Card -->
        <div class="po-list-card" style="background:var(--po-wh); border:1px solid var(--po-bd); border-radius:var(--po-r); padding:0; box-shadow:var(--po-sh2); overflow:hidden">
            <!-- Detail Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 18px; border-bottom:1px solid var(--po-bd2); background:#FAFBFC">
                <div style="display:flex; align-items:center; gap:8px">
                    <span style="width:10px; height:10px; border-radius:50%; background:{{ $activeColor }}; display:inline-block"></span>
                    <span style="font-size:15px; font-weight:800; color:var(--po-tx)">{{ $record->supplier?->name }}</span>
                    <span style="font-size:12px; color:var(--po-mu); font-weight:500">
                        {{ $record->supplier?->type === 'uot' ? __('purchase_order.ingredient_types.meat_wet') : ($record->supplier?->type === 'kho' ? __('purchase_order.ingredient_types.dry') : __('purchase_order.ingredient_types.meat_wet')) }}
                    </span>
                </div>
                <!-- Nút xuất Excel NCC này -->
                <button wire:click="exportCurrentNcc" class="po-btn" style="background:var(--po-wh); border:1px solid #A7F3D0; color:#065F46; padding:5px 12px; font-size:12px">
                    <i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i> {{ __('purchase_order.actions.export_current_supplier') }}
                </button>
            </div>

            <!-- Items Table -->
            <div style="overflow-x:auto">
                <table class="oh-table" style="width:100%; border-collapse:collapse; text-align:left; font-size:12.5px">
                    <thead>
                        <tr>
                            <th style="padding:9px 12px; width:36px; text-align:center">#</th>
                            <th style="padding:9px 12px">{{ __('purchase_order.table.ingredient_name') }}</th>
                            <th style="padding:9px 12px; width:110px">{{ __('purchase_order.table.type') }}</th>
                            <th style="padding:9px 12px; width:100px; text-align:center">{{ __('purchase_order.table.servings_count') }}</th>
                            <th style="padding:9px 12px; width:110px; text-align:center">{{ __('purchase_order.table.quantity') }}</th>
                            <th style="padding:9px 12px; width:120px; text-align:right">{{ __('purchase_order.table.unit_price') }}</th>
                            <th style="padding:9px 12px; width:140px; text-align:right">{{ __('purchase_order.table.line_total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($record->items as $index => $item)
                            @php
                                $total = $item->quantity_ordered * $item->unit_price;
                                $ingType = strtolower($item->ingredient?->typeRelation?->name ?? $item->ingredient?->type ?? '');
                                $ingName = strtolower($item->ingredient?->name ?? '');
                                
                                $isThit = str_contains($ingType, 'động vật') || str_contains($ingType, 'thịt') || str_contains($ingType, 'cá') || str_contains($ingType, 'thủy sản') || str_contains($ingName, 'thịt') || str_contains($ingName, 'cá') || str_contains($ingName, 'gà') || str_contains($ingName, 'vịt') || str_contains($ingName, 'trứng') || str_contains($ingName, 'giò') || str_contains($ingName, 'chả');
                                $isRau = str_contains($ingType, 'thực vật') || str_contains($ingType, 'rau') || str_contains($ingType, 'củ') || str_contains($ingType, 'quả') || str_contains($ingName, 'rau') || str_contains($ingName, 'củ') || str_contains($ingName, 'quả') || str_contains($ingName, 'hành') || str_contains($ingName, 'tỏi') || str_contains($ingName, 'ớt') || str_contains($ingName, 'nấm') || str_contains($ingName, 'lá');

                                $portionKg = (float)($item->ingredient?->quantity_per_portion ?? 0);
                                $servings = $portionKg > 0 ? round($item->quantity_ordered / $portionKg) : 240;
                            @endphp
                            <tr>
                                <td style="text-align:center; color:var(--po-mu)">{{ $index + 1 }}</td>
                                <td style="font-weight:600; color:var(--po-tx)">{{ $item->ingredient?->name }}</td>
                                <td>
                                    @if($isThit)
                                        <span class="ot-thit" style="font-size:11px; display:inline-flex; align-items:center; gap:4px">
                                            <i class="fa-solid fa-drumstick-bite" style="font-size: 10px;"></i> {{ __('purchase_order.ingredient_types.meat') }}
                                        </span>
                                    @elseif($isRau)
                                        <span class="ot-uot" style="font-size:11px; display:inline-flex; align-items:center; gap:4px">
                                            <i class="fa-solid fa-leaf" style="font-size: 10px;"></i> {{ __('purchase_order.ingredient_types.vegetable_wet') }}
                                        </span>
                                    @else
                                        <span class="ot-kho" style="font-size:11px; display:inline-flex; align-items:center; gap:4px">
                                            <i class="fa-solid fa-box" style="font-size: 10px;"></i> {{ __('purchase_order.ingredient_types.dry') }}
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align:center; color:var(--po-tx); font-weight:500">
                                    {{ number_format($servings, 0) }}
                                </td>
                                <td style="text-align:center; font-weight:700; color:var(--po-tx)">
                                    {{ (float)$item->quantity_ordered }} {{ $item->ingredient?->unitRelation?->name ?? $item->ingredient?->unit ?? 'kg' }}
                                </td>
                                <td style="text-align:right; color:var(--po-mu)">
                                    {{ number_format($item->unit_price, 0, ',', '.') }} đ/{{ $item->ingredient?->unitRelation?->name ?? $item->ingredient?->unit ?? 'kg' }}
                                </td>
                                <td style="text-align:right; font-weight:700; color:var(--po-rd)">
                                    {{ number_format($total, 0, ',', '.') }} đ
                                </td>
                            </tr>
                        @endforeach
                        <!-- Tổng cộng dòng NCC -->
                        <tr style="background:#EBF3FF; color:var(--po-bl); font-weight:700; border-top:1.5px solid var(--po-bl-m)">
                            <td colspan="5" style="padding:14px 16px; text-align:right; font-size:14px">
                                {{ __('purchase_order.detail.supplier_total', ['supplier' => $record->supplier?->name]) }}
                            </td>
                            <td colspan="2" style="padding:14px 16px; text-align:right; font-size:16px; font-weight:800; color:var(--po-bl)">
                                {{ number_format($currentPOTotal, 0, ',', '.') }} đ
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
