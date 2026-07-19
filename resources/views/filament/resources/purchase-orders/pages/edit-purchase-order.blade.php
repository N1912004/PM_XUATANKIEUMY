<x-filament-panels::page>
    @include('filament.resources.purchase-orders.partials.styles')

    @php
        $record = $this->record;
        $record->loadMissing(['supplier', 'items.ingredient']); // tránh N+1 khi lặp items
        $relatedPOs = $this->getRelatedPOs();
        $colors = ['#267DC1', '#059669', '#D97706', '#7C3AED', '#EA580C', '#EC4899'];
        $currentPOTotal = $record->items->sum(fn($it) => $it->quantity_ordered * $it->unit_price);
        $isWeek = str_contains(mb_strtolower($record->note ?? ''), 'tuần') || str_contains(strtolower($record->code ?? ''), 'tuan');
    @endphp

    <div class="po-page" style="padding: 0 !important; background: transparent !important;">
        <!-- Header Section -->
        <div class="po-head" style="margin-bottom: 20px;">
            <div>
                <h1 class="po-title">Đơn {{ $record->code }}</h1>
                <p class="po-subtitle">
                    Gộp nguyên liệu theo nhà cung cấp · Ngày đặt {{ $record->estimated_delivery_date ? $record->estimated_delivery_date->format('d/m/Y') : '--' }}
                </p>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap">
                <!-- Nút Quay lại -->
                <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('index') }}" class="po-btn" style="background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-tx)">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>

                <!-- Nút Xuất Excel (tất cả NCC) -->
                <button wire:click="exportAllNcc" class="po-btn" style="background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-tx)">
                    <i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i> Xuất Excel (tất cả NCC)
                </button>

                <!-- Nút Kiểm hàng (chuyển hướng sang kho tab nhập PO) -->
                @if(in_array($record->status, ['sent', 'checking']) && is_null($record->stocked_at))
                    <a href="{{ url('/admin/stocks?tab=in&inMode=po&po_id=' . $record->id) }}" class="po-btn po-btn-primary">
                        <i class="fa-solid fa-clipboard-check"></i> Kiểm hàng
                    </a>
                @endif
            </div>
        </div>

        <!-- NCC Tabs -->
        <div class="oh-ncc-tabs" style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap">
            @foreach($relatedPOs as $index => $po)
                @php
                    $poTotal = $po->items->sum(fn($it) => $it->quantity_ordered * $it->unit_price);
                    $isActive = $po->id === $record->id;
                    $color = $colors[$index % count($colors)];
                @endphp
                <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('edit', ['record' => $po]) }}" 
                   class="oh-ncc-tab" 
                   style="display:flex; align-items:center; gap:8px; padding:8px 14px; border-radius:30px; font-size:13px; font-weight:700; text-decoration:none; transition:.13s; border:1px solid; 
                          {{ $isActive ? 'background:'.$color.'; color:#fff; border-color:'.$color.'; box-shadow: 0 4px 12px '.($color).'33;' : 'background:var(--po-wh); color:var(--po-tx); border-color:var(--po-bd);' }}">
                    
                    @if(!$isActive)
                        <span style="width:8px; height:8px; border-radius:50%; background:{{ $color }}; display:inline-block"></span>
                    @endif

                    <span>{{ $po->supplier?->name }}</span>

                    <span style="display:inline-flex; align-items:center; justify-content:center; width:20px; height:20px; border-radius:50%; font-size:11px; 
                                 {{ $isActive ? 'background:rgba(255,255,255,0.2); color:#fff;' : 'background:var(--po-bd2); color:var(--po-mu);' }}">
                        {{ $po->items->count() }}
                    </span>

                    <span style="font-size:12px; font-weight:500; {{ $isActive ? 'color:rgba(255,255,255,0.85);' : 'color:var(--po-mu);' }}">
                        {{ number_format($poTotal / 1000, 0, '', '') }}k đ
                    </span>
                </a>
            @endforeach
        </div>

        <!-- Main Content Area: NCC Detail Card -->
        <div class="po-list-card" style="background:var(--po-wh); border:1px solid var(--po-bd2); border-radius:12px; padding:20px; box-shadow:var(--po-sh2)">
            <!-- Detail Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px">
                <div style="display:flex; align-items:center; gap:8px">
                    <span style="width:10px; height:10px; border-radius:50%; background:{{ $colors[array_search($record->id, $relatedPOs->pluck('id')->toArray()) % count($colors)] }}; display:inline-block"></span>
                    <span style="font-size:16px; font-weight:800; color:var(--po-tx)">{{ $record->supplier?->name }}</span>
                    <span style="font-size:12px; color:var(--po-mu); font-weight:500">
                        {{ $record->supplier?->type === 'uot' ? 'Thịt/Ướt' : ($record->supplier?->type === 'kho' ? 'Hàng Khô' : 'Nhà cung cấp') }}
                    </span>
                </div>
                <!-- Nút xuất Excel NCC này -->
                <button wire:click="exportCurrentNcc" class="po-btn" style="background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-tx); padding:6px 12px; font-size:12px">
                    <i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i> Xuất Excel NCC này
                </button>
            </div>

            <!-- Items Table -->
            <div style="overflow-x:auto">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px">
                    <thead>
                        <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px">
                            <th style="padding:10px 8px; width:40px">#</th>
                            <th style="padding:10px 8px">Tên nguyên liệu</th>
                            <th style="padding:10px 8px; width:120px">Loại</th>
                            <th style="padding:10px 8px; width:120px; text-align:center">Số lượng</th>
                            <th style="padding:10px 8px; width:120px; text-align:right">Đơn giá</th>
                            <th style="padding:10px 8px; width:140px; text-align:right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($record->items as $index => $item)
                            @php
                                $total = $item->quantity_ordered * $item->unit_price;
                                $ingType = strtolower($item->ingredient?->type ?? '');
                                $ingName = strtolower($item->ingredient?->name ?? '');
                                
                                // Phân tích loại nguyên liệu dựa trên type và tên
                                $isThit = str_contains($ingType, 'động vật') || str_contains($ingType, 'thịt') || str_contains($ingType, 'cá') || str_contains($ingType, 'thủy sản') || str_contains($ingName, 'thịt') || str_contains($ingName, 'cá') || str_contains($ingName, 'gà') || str_contains($ingName, 'vịt') || str_contains($ingName, 'trứng') || str_contains($ingName, 'giò') || str_contains($ingName, 'chả');
                                $isRau = str_contains($ingType, 'thực vật') || str_contains($ingType, 'rau') || str_contains($ingType, 'củ') || str_contains($ingType, 'quả') || str_contains($ingName, 'rau') || str_contains($ingName, 'củ') || str_contains($ingName, 'quả') || str_contains($ingName, 'hành') || str_contains($ingName, 'tỏi') || str_contains($ingName, 'ớt') || str_contains($ingName, 'nấm') || str_contains($ingName, 'lá');
                            @endphp
                            <tr style="border-bottom:1px solid var(--po-bd2); color:var(--po-tx)">
                                <td style="padding:12px 8px; color:var(--po-mu)">{{ $index + 1 }}</td>
                                <td style="padding:12px 8px; font-weight:700">{{ $item->ingredient?->name }}</td>
                                <td style="padding:12px 8px">
                                    @if($isThit)
                                        <span class="ot-thit" style="font-size:11px; display:inline-flex; align-items:center; gap:4px">
                                            <i class="fa-solid fa-drumstick-bite" style="font-size: 10px;"></i> Thịt
                                        </span>
                                    @elseif($isRau)
                                        <span class="ot-uot" style="font-size:11px; display:inline-flex; align-items:center; gap:4px">
                                            <i class="fa-solid fa-leaf" style="font-size: 10px;"></i> Rau/Ướt
                                        </span>
                                    @else
                                        <span class="ot-kho" style="font-size:11px; display:inline-flex; align-items:center; gap:4px">
                                            <i class="fa-solid fa-box" style="font-size: 10px;"></i> Hàng Khô
                                        </span>
                                    @endif
                                </td>
                                <td style="padding:12px 8px; text-align:center; font-weight:700">
                                    {{ (float)$item->quantity_ordered }} {{ $item->ingredient?->unit }}
                                </td>
                                <td style="padding:12px 8px; text-align:right; color:var(--po-mu)">
                                    {{ number_format($item->unit_price, 0, ',', '.') }} đ/{{ $item->ingredient?->unit }}
                                </td>
                                <td style="padding:12px 8px; text-align:right; font-weight:700; color:var(--po-rd)">
                                    {{ number_format($total, 0, ',', '.') }} đ
                                </td>
                            </tr>
                        @endforeach
                        <!-- Tổng cộng dòng NCC -->
                        <tr style="background:var(--po-bl-s); color:var(--po-bl); font-weight:700; border-top:1.5px solid var(--po-bl-m)">
                            <td colspan="4" style="padding:14px 12px; text-align:right; font-size:14px">
                                Tổng đơn {{ $record->supplier?->name }}:
                            </td>
                            <td colspan="2" style="padding:14px 12px; text-align:right; font-size:16px; color:var(--po-bl)">
                                {{ number_format($currentPOTotal, 0, ',', '.') }} đ
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
