<x-filament-panels::page>
    @include('filament.resources.purchase-orders.partials.styles')

    @php
        $record = $this->record;
        $relatedPOs = $this->getRelatedPOsProperty();
        $activeOrder = $this->getActiveOrderProperty();
        $colors = ['#267DC1', '#059669', '#D97706', '#7C3AED', '#EA580C', '#EC4899'];
    @endphp

    <div class="po-page w-full space-y-6" style="padding: 0 !important; background: transparent !important;">
        <!-- Header Section -->
        <div class="po-head" style="margin-bottom: 16px;">
            <div>
                <h1 class="po-title" style="font-size:20px">{{ __('purchase_order.check.title', ['code' => $record->code]) }}</h1>
                <p class="po-subtitle">
                    {{ __('purchase_order.check.subtitle', ['date' => $record->estimated_delivery_date ? $record->estimated_delivery_date->format('d/m/Y') : '--']) }}
                </p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap">
                <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('index') }}" class="po-btn">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('purchase_order.actions.back') }}
                </a>
                <button wire:click="completeCheck" wire:loading.attr="disabled" wire:target="completeCheck" class="po-btn po-btn-primary">
                    <i class="fa-solid fa-circle-check"></i> {{ __('purchase_order.actions.complete_check') }}
                </button>
            </div>
        </div>

        <!-- Supplier Selector Tabs -->
        <div class="oh-ncc-tabs" style="display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap">
            @foreach($relatedPOs as $index => $po)
                @php
                    $isActive = $po->id === $activeOrder->id;
                    $color = $colors[$index % count($colors)];
                    $checkedCount = $po->items->filter(fn($it) => isset($receivedQuantities[$it->id]) && $receivedQuantities[$it->id] !== '')->count();
                    $totalCount = $po->items->count();
                @endphp
                <button type="button" wire:click="switchPo({{ $po->id }})" 
                        class="oh-ncc-tab" 
                        style="display:flex; align-items:center; gap:8px; padding:8px 14px; border-radius:30px; font-size:13px; font-weight:700; cursor:pointer; transition:.13s; border:1px solid;
                               {{ $isActive ? 'background:'.$color.'; color:#fff; border-color:'.$color.'; box-shadow: 0 4px 12px '.($color).'33;' : 'background:var(--po-wh); color:var(--po-tx); border-color:var(--po-bd);' }}">
                    
                    <span style="width:9px; height:9px; border-radius:50%; background:{{ $isActive ? '#fff' : $color }}; display:inline-block"></span>
                    <span>{{ $po->supplier?->name }}</span>
                    <span style="display:inline-flex; align-items:center; justify-content:center; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:800;
                                 {{ $isActive ? 'background:rgba(255,255,255,0.25); color:#fff;' : 'background:var(--po-bd2); color:var(--po-mu);' }}">
                        {{ $checkedCount }}/{{ $totalCount }}
                    </span>
                </button>
            @endforeach
        </div>

        <!-- Check Table Card -->
        @php
            $activeItems = $activeOrder->items;
            $activeCheckedCount = $activeItems->filter(fn($it) => isset($receivedQuantities[$it->id]) && $receivedQuantities[$it->id] !== '')->count();
            $activeTotalCount = $activeItems->count();
            $progressPercent = $activeTotalCount > 0 ? round(($activeCheckedCount / $activeTotalCount) * 100) : 0;
            $activeColor = $colors[array_search($activeOrder->id, $relatedPOs->pluck('id')->toArray()) % count($colors)] ?? '#267DC1';
        @endphp

        <div style="background:var(--po-wh); border:1px solid var(--po-bd); border-radius:var(--po-r); box-shadow:var(--po-sh2); overflow:hidden">
            <!-- Table Sub-Header -->
            <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid var(--po-bd2); background:#FAFBFC">
                <div style="font-size:14px; font-weight:700; color:var(--po-tx); display:flex; align-items:center; gap:9px">
                    <span style="display:inline-block; width:11px; height:11px; border-radius:50%; background:{{ $activeColor }}"></span>
                    {{ __('purchase_order.check.section_title', ['supplier' => $activeOrder->supplier?->name]) }}
                </div>
                <div style="font-size:12.5px; color:var(--po-mu)">
                    {{ __('purchase_order.check.checked_progress', ['checked' => $activeCheckedCount, 'total' => $activeTotalCount]) }}
                    <div style="display:inline-block; width:80px; height:6px; background:var(--po-bd2); border-radius:3px; margin-left:8px; vertical-align:middle; overflow:hidden">
                        <div style="width:{{ $progressPercent }}%; height:6px; background:var(--po-bl); border-radius:3px"></div>
                    </div>
                </div>
            </div>

            <!-- Table Body -->
            <table class="oh-table" style="width:100%">
                <thead>
                    <tr>
                        <th style="text-align:center; width:36px">#</th>
                        <th>{{ __('purchase_order.table.ingredient_name') }}</th>
                        <th>{{ __('purchase_order.table.type') }}</th>
                        <th style="text-align:right">{{ __('purchase_order.table.quantity_ordered') }}</th>
                        <th style="text-align:center">{{ __('purchase_order.table.quantity_received_actual') }}</th>
                        <th style="text-align:center; min-width:90px">{{ __('purchase_order.table.difference') }}</th>
                        <th>{{ __('purchase_order.fields.note') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeItems as $idx => $item)
                        @php
                            $ordered = (float) $item->quantity_ordered;
                            $receivedVal = $receivedQuantities[$item->id] ?? '';
                            $hasValue = $receivedVal !== '';
                            $receivedFloat = (float) $receivedVal;
                            $diff = $hasValue ? ($receivedFloat - $ordered) : null;
                            $hasDiff = $hasValue && abs($diff) > 0.001;

                            $ingType = strtolower($item->ingredient?->typeRelation?->name ?? $item->ingredient?->type ?? '');
                            $ingName = strtolower($item->ingredient?->name ?? '');
                            
                            $isThit = str_contains($ingType, 'động vật') || str_contains($ingType, 'thịt') || str_contains($ingType, 'cá') || str_contains($ingType, 'thủy sản') || str_contains($ingName, 'thịt') || str_contains($ingName, 'cá') || str_contains($ingName, 'gà') || str_contains($ingName, 'vịt') || str_contains($ingName, 'trứng');
                            $isRau = str_contains($ingType, 'thực vật') || str_contains($ingType, 'rau') || str_contains($ingType, 'củ') || str_contains($ingType, 'quả') || str_contains($ingName, 'rau') || str_contains($ingName, 'củ') || str_contains($ingName, 'quả') || str_contains($ingName, 'giá') || str_contains($ingName, 'nấm');
                        @endphp
                        <tr>
                            <td style="text-align:center; color:var(--po-mu)">{{ $idx + 1 }}</td>
                            <td style="font-weight:600; color:var(--po-tx)">{{ $item->ingredient?->name }}</td>
                            <td>
                                @if($isThit)
                                    <span class="ot-thit" style="font-size:11px"><i class="fa-solid fa-drumstick-bite"></i> {{ __('purchase_order.ingredient_types.meat') }}</span>
                                @elseif($isRau)
                                    <span class="ot-uot" style="font-size:11px"><i class="fa-solid fa-leaf"></i> {{ __('purchase_order.ingredient_types.vegetable_wet') }}</span>
                                @else
                                    <span class="ot-kho" style="font-size:11px"><i class="fa-solid fa-box"></i> {{ __('purchase_order.ingredient_types.dry') }}</span>
                                @endif
                            </td>
                            <td style="text-align:right; font-weight:600; color:var(--po-tx)">
                                {{ (float) $ordered }} {{ $item->ingredient?->unitRelation?->name ?? $item->ingredient?->unit ?? 'kg' }}
                            </td>
                            <td style="text-align:center">
                                <input class="oh-check-inp" type="number" step="0.01" min="0" wire:model.live="receivedQuantities.{{ $item->id }}"
                                       placeholder="–" style="{{ $hasDiff ? 'border-color:var(--po-or); background:#FFF7ED;' : '' }}">
                            </td>
                            <td style="text-align:center">
                                @if(!$hasValue)
                                    <span class="oh-diff-pending">–</span>
                                @elseif(abs($diff) < 0.001)
                                    <span class="oh-diff-ok">✓ Đủ</span>
                                @elseif($diff > 0)
                                    <span class="oh-diff-ok">+{{ number_format($diff, 2) }}</span>
                                @else
                                    <span class="oh-diff-bad">{{ number_format($diff, 2) }}</span>
                                @endif
                            </td>
                            <td>
                                <input type="text" wire:model.live="itemNotes.{{ $item->id }}" placeholder="{{ __('purchase_order.placeholders.note') }}" 
                                       style="height:30px; border-radius:7px; font-size:12px; width:100%; border:1px solid var(--po-bd); padding:0 8px; outline:none">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
