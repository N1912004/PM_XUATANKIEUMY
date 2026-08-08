<x-filament-panels::page>
    @include('filament.resources.purchase-orders.partials.styles')

    @php
        $groups = $this->getAggregatedGroupsProperty();
        $suppliers = $this->suppliers;
        $shifts = $this->shifts;

        $totalCount = 0;
        $selectedCount = 0;
        $grandTotal = 0;
        $uniqueSupplierIds = [];

        // Phân bổ theo NCC + tách phiếu cho sidebar tóm tắt (chỉ tính dòng đã tích chọn)
        $bySupplier = [];
        $bySplit = [];

        foreach ($groups as $grp) {
            foreach ($grp['items'] as $item) {
                $totalCount++;
                if (!empty($item['selected'])) {
                    $selectedCount++;
                    $grandTotal += $item['line_total'];
                    if (!empty($item['supplier_id'])) {
                        $uniqueSupplierIds[$item['supplier_id']] = true;
                        $sid = $item['supplier_id'];
                        $bySupplier[$sid] ??= ['count' => 0, 'total' => 0];
                        $bySupplier[$sid]['count']++;
                        $bySupplier[$sid]['total'] += $item['line_total'];
                    }
                    $sp = $item['split'] ?? 'P1';
                    $bySplit[$sp] ??= ['count' => 0, 'total' => 0];
                    $bySplit[$sp]['count']++;
                    $bySplit[$sp]['total'] += $item['line_total'];
                }
            }
        }
        ksort($bySplit);
        $supplierCount = count($uniqueSupplierIds);
        $splitCount = max(1, count($bySplit));
        $supplierNamesById = $suppliers->pluck('name', 'id');
        $nccDotColors = ['#1267E8', '#059669', '#D97706', '#7C3AED', '#DC2626', '#0891B2'];
    @endphp

    <div class="po-page w-full" style="padding: 0 !important; background: transparent !important; padding-bottom: 20px !important;">
        <!-- Header Bar -->
        <div class="po-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
            <div>
                <h1 class="po-title" style="font-size:20px; font-weight:800">{{ __('purchase_order.create.title') }}</h1>
                <p class="po-subtitle" style="font-size:12.5px; color:var(--po-mu)">{{ __('purchase_order.create.subtitle') }}</p>
            </div>
            <div style="display:flex; align-items:center; gap:10px">
                <a href="{{ \App\Filament\Resources\PurchaseOrderResource::getUrl('index') }}" class="po-btn" style="background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-tx)">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('purchase_order.actions.back') }}
                </a>
                <button wire:click="createAndSendOrders" wire:loading.attr="disabled" wire:target="createAndSendOrders" class="po-btn po-btn-primary" style="padding:8px 18px; font-size:13.5px">
                    <i class="fa-solid fa-paper-plane"></i> {{ __('purchase_order.actions.create_and_send') }}
                </button>
            </div>
        </div>

        <!-- 3-step Progress Bar -->
        <div class="oh-steps" style="display:flex; justify-content:space-between; margin-bottom:20px; background:var(--po-wh); padding:12px 20px; border-radius:var(--po-r); border:1px solid var(--po-bd)">
            <div class="oh-step active" style="display:flex; align-items:center; gap:8px; font-weight:700; color:var(--po-bl)">
                <span style="width:24px; height:24px; border-radius:50%; background:var(--po-bl); color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px">1</span>
                <span>{{ __('purchase_order.steps.step1_title') }}</span>
            </div>
            <div class="oh-step" style="display:flex; align-items:center; gap:8px; font-weight:600; color:var(--po-mu)">
                <span style="width:24px; height:24px; border-radius:50%; background:var(--po-bd2); color:var(--po-mu); display:flex; align-items:center; justify-content:center; font-size:12px">2</span>
                <span>{{ __('purchase_order.steps.step2_title') }}</span>
            </div>
            <div class="oh-step" style="display:flex; align-items:center; gap:8px; font-weight:600; color:var(--po-mu)">
                <span style="width:24px; height:24px; border-radius:50%; background:var(--po-bd2); color:var(--po-mu); display:flex; align-items:center; justify-content:center; font-size:12px">3</span>
                <span>{{ __('purchase_order.steps.step3_title') }}</span>
            </div>
        </div>

        <!-- 2-col layout: content trái + sidebar tóm tắt phải (theo mẫu bluefire_demo).
             Mobile: sidebar không ẩn — xếp xuống dưới content, full width. -->
        <div class="oc-layout" style="display:flex; gap:16px; align-items:flex-start">
        <div class="oc-main" style="flex:1; min-width:0">

        <!-- Date & Shift Filters Card -->
        <div style="background:var(--po-wh); border:1px solid var(--po-bd); border-radius:var(--po-r); padding:16px; margin-bottom:20px; box-shadow:var(--po-sh)">
            <div style="margin-bottom: 14px; padding: 10px 14px; background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; font-size: 12.5px; color: #1E40AF; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-info" style="font-size: 14px; color: #3B82F6;"></i>
                <span><strong>Nguồn dữ liệu:</strong> Số suất và nhu cầu thực phẩm được tổng hợp tự động từ các <strong>Thực đơn Đã chốt (status = locked)</strong>. Số lượng mua đề xuất đã được tự động trừ tồn kho khả dụng tại bếp.</span>
            </div>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px; align-items:end">
                <div>
                    <label style="font-size:12px; font-weight:700; color:var(--po-tx); display:block; margin-bottom:4px">{{ __('purchase_order.fields.order_date') }}</label>
                    <input type="date" wire:model.live="orderDate" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--po-bd); font-size:13px; font-weight:600">
                </div>

                <div>
                    <label style="font-size:12px; font-weight:700; color:var(--po-tx); display:block; margin-bottom:4px">{{ __('purchase_order.fields.source_from') }}</label>
                    <input type="date" wire:model.live="sourceFrom" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--po-bd); font-size:13px; font-weight:600">
                </div>

                <div>
                    <label style="font-size:12px; font-weight:700; color:var(--po-tx); display:block; margin-bottom:4px">{{ __('purchase_order.fields.source_to') }}</label>
                    <input type="date" wire:model.live="sourceTo" style="width:100%; padding:6px 10px; border-radius:6px; border:1px solid var(--po-bd); font-size:13px; font-weight:600">
                </div>

                <div style="grid-column: span 2;">
                    <label style="font-size:12px; font-weight:700; color:var(--po-tx); display:block; margin-bottom:6px">{{ __('purchase_order.fields.selected_shifts') }}</label>
                    <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center">
                        @foreach($shifts as $s)
                            @php
                                $sId = (string) $s->id;
                                $isSelected = in_array($sId, $selectedShifts, true) || in_array($s->id, $selectedShifts, true);
                            @endphp
                            <label style="display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer; padding:6px 12px; border-radius:8px; transition:all 0.15s ease; user-select:none; @if($isSelected) background:var(--po-bl-s, #EFF6FF); color:var(--po-bl, #1D4ED8); border:1px solid var(--po-bl-m, #93C5FD); box-shadow:0 1px 2px rgba(59,130,246,0.08); @else background:var(--po-wh, #FFFFFF); color:var(--po-mu, #64748b); border:1px solid var(--po-bd2, #E2E8F0); opacity:0.75; @endif" class="dark:bg-gray-800 dark:border-gray-700">
                                <input type="checkbox" value="{{ $s->id }}" wire:model.live="selectedShifts" style="accent-color:var(--po-bl); width:14px; height:14px; cursor:pointer">
                                <span>{{ $s->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Ingredient Groups -->
        @forelse($groups as $group)
            @php
                $groupTotal = collect($group['items'])->sum('line_total');
            @endphp
            <div class="oh-group-card" style="background:var(--po-wh); border:1px solid var(--po-bd); border-radius:var(--po-r); margin-bottom:20px; box-shadow:var(--po-sh)">
                <!-- Group Header -->
                <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 18px; background:#FAFBFC; border-bottom:1px solid var(--po-bd2); border-top-left-radius:var(--po-r); border-top-right-radius:var(--po-r)">
                    <div style="display:flex; align-items:center; gap:10px">
                        <span class="{{ $group['class'] }}" style="font-size:13px; font-weight:800; padding:3px 10px; border-radius:20px">
                            {{ $group['label'] }}
                        </span>
                        <span style="font-size:12px; color:var(--po-mu); font-weight:600">
                            {{ __('purchase_order.labels.ingredient_count', ['count' => count($group['items'])]) }}
                        </span>
                    </div>

                    <!-- Quick Supplier Assignment for Group (Vừa tìm vừa chọn với x-teleport) -->
                    <div style="display:flex; align-items:center; gap:8px">
                        <span style="font-size:12px; font-weight:700; color:var(--po-mu)">{{ __('purchase_order.create.quick_assign_supplier') }}</span>
                        <div x-data="{
                            open: false,
                            search: '',
                            top: 0,
                            left: 0,
                            suppliers: {{ json_encode($suppliers->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()->toArray()) }},
                            get filtered() {
                                if (!this.search) return this.suppliers;
                                return this.suppliers.filter(s => s.name.toLowerCase().includes(this.search.toLowerCase()));
                            },
                            updatePos() {
                                let rect = $refs.trigger.getBoundingClientRect();
                                let spaceBelow = window.innerHeight - rect.bottom;
                                if (spaceBelow < 230 && rect.top > 230) {
                                    this.top = rect.top - 210;
                                } else {
                                    this.top = rect.bottom + 4;
                                }
                                this.left = rect.right - 230;
                            },
                            toggleOpen() {
                                if (!this.open) {
                                    this.updatePos();
                                }
                                this.open = !this.open;
                            },
                            selectSupplier(id, name) {
                                $wire.assignGroupSupplier(id, '{{ $group['key'] }}');
                                this.search = name || '';
                                this.open = false;
                            }
                        }">
                            <div x-ref="trigger" @click="toggleOpen()" style="display:flex; align-items:center; justify-content:space-between; background:#fff; border:1px solid var(--po-bd); border-radius:6px; padding:4px 10px; cursor:pointer; font-size:12px; font-weight:600; color:var(--po-tx); box-shadow:0 1px 2px rgba(0,0,0,0.05); min-width:180px">
                                <span x-text="search || '{{ __('purchase_order.create.select_supplier') }}'" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:140px"></span>
                                <i class="fa-solid fa-chevron-down" style="font-size:10px; color:var(--po-mu)"></i>
                            </div>

                            <template x-teleport="body">
                                <div x-show="open" x-cloak @click.outside="open = false" 
                                     :style="`position:fixed; top:${top}px; left:${left}px; width:230px; background:#fff; border:1px solid var(--po-bd); border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,0.25); z-index:99999; padding:6px`">
                                    <input type="text" x-model="search" placeholder="{{ __('purchase_order.create.search_supplier') }}" style="width:100%; padding:6px 9px; font-size:12px; border:1px solid var(--po-bd2); border-radius:4px; outline:none; margin-bottom:4px" @click.stop>
                                    
                                    <div style="max-height:180px; overflow-y:auto">
                                        <div @click="selectSupplier(null, '')" 
                                             style="padding:6px 8px; font-size:12px; font-weight:600; color:var(--po-mu); cursor:pointer; border-radius:4px; border-bottom:1px solid var(--po-bd2); margin-bottom:4px"
                                             onmouseover="this.style.background='#F1F5F9'" 
                                             onmouseout="this.style.background='transparent'">
                                            -- {{ __('purchase_order.create.select_supplier') }} --
                                        </div>
                                        <template x-for="sup in filtered" :key="sup.id">
                                            <div @click="selectSupplier(sup.id, sup.name)" 
                                                 style="padding:6px 8px; font-size:12px; font-weight:600; color:var(--po-tx); cursor:pointer; border-radius:4px; transition:0.1s"
                                                 onmouseover="this.style.background='#F1F5F9'" 
                                                 onmouseout="this.style.background='transparent'"
                                                 x-text="sup.name">
                                            </div>
                                        </template>
                                        <div x-show="filtered.length === 0" style="padding:8px; font-size:11.5px; color:var(--po-mu); text-align:center">
                                            {{ __('purchase_order.create.no_supplier_found') }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Group Table -->
                <div style="overflow-x:auto">
                    <table class="oh-table" style="width:100%; border-collapse:collapse; text-align:left; font-size:12.5px">
                        <thead>
                            <tr>
                                <th style="width:36px; text-align:center">{{ __('purchase_order.table.index') }}</th>
                                <th style="width:40px; text-align:center">{{ __('purchase_order.table.order_check') }}</th>
                                <th>{{ __('purchase_order.table.ingredient_name') }}</th>
                                <th>{{ __('purchase_order.table.for_dishes') }}</th>
                                <th style="text-align:center">{{ __('purchase_order.table.servings_count') }}</th>
                                <th style="text-align:right">{{ __('purchase_order.table.system_qty') }}</th>
                                <th style="text-align:center">{{ __('purchase_order.table.manual_qty') }}</th>
                                <th style="text-align:right">{{ __('purchase_order.table.unit_price') }}</th>
                                <th style="text-align:right">{{ __('purchase_order.table.line_total') }}</th>
                                <th style="width:180px">{{ __('purchase_order.table.supplier') }}</th>
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
                                        @if(!empty($item['already_ordered_pos']))
                                            <div style="display:flex; gap:4px; flex-wrap:wrap; margin-top:3px">
                                                @foreach($item['already_ordered_pos'] as $poCode)
                                                    <span style="font-size:10.5px; font-weight:700; color:#C2410C; background:#FFF7ED; border:1px solid #FED7AA; padding:1px 6px; border-radius:4px; display:inline-flex; align-items:center; gap:3px">
                                                        <i class="fa-solid fa-file-invoice" style="font-size:9px"></i> {{ __('purchase_order.create.already_ordered', ['code' => $poCode]) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
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
                                         <input type="number" step="0.01" min="0" 
                                                onkeydown="if(event.key==='-') event.preventDefault();"
                                                oninput="if(this.value < 0) this.value = 0;"
                                                wire:model.live="itemQuantities.{{ $item['ingredient_id'] }}" 
                                                style="width:75px; height:28px; border:1px solid var(--po-bd); border-radius:6px; text-align:center; font-weight:700; color:var(--po-bl)">
                                    </td>
                                    <td style="text-align:right; color:var(--po-mu)">
                                        {{ number_format($item['reference_price'], 0, ',', '.') }} đ/{{ $item['unit'] }}
                                    </td>
                                    <td style="text-align:right; font-weight:700; color:var(--po-rd)">
                                        {{ number_format($item['line_total'], 0, ',', '.') }} đ
                                    </td>
                                    <td>
                                        <!-- Per-row Searchable Select với x-teleport body chống tràn tuyệt đối -->
                                        <div x-data="{
                                            open: false,
                                            search: '',
                                            top: 0,
                                            left: 0,
                                            suppliers: {{ json_encode($suppliers->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()->toArray()) }},
                                            get selectedName() {
                                                let currentId = $wire.itemSuppliers[{{ $item['ingredient_id'] }}] !== undefined ? $wire.itemSuppliers[{{ $item['ingredient_id'] }}] : {{ json_encode($item['supplier_id']) }};
                                                if (!currentId) return '{{ __('purchase_order.create.select_supplier') }}';
                                                let found = this.suppliers.find(s => s.id == currentId);
                                                return found ? found.name : '{{ __('purchase_order.create.select_supplier') }}';
                                            },
                                            get filtered() {
                                                if (!this.search) return this.suppliers;
                                                return this.suppliers.filter(s => s.name.toLowerCase().includes(this.search.toLowerCase()));
                                            },
                                            updatePos() {
                                                let rect = $refs.rowTrigger.getBoundingClientRect();
                                                let spaceBelow = window.innerHeight - rect.bottom;
                                                if (spaceBelow < 220 && rect.top > 220) {
                                                    this.top = rect.top - 200;
                                                } else {
                                                    this.top = rect.bottom + 4;
                                                }
                                                this.left = rect.right - 220;
                                            },
                                            toggleOpen() {
                                                if (!this.open) {
                                                    this.updatePos();
                                                }
                                                this.open = !this.open;
                                            },
                                            selectSupplier(id) {
                                                $wire.set('itemSuppliers.{{ $item['ingredient_id'] }}', id);
                                                this.open = false;
                                                this.search = '';
                                            }
                                        }">
                                            <div x-ref="rowTrigger" @click="toggleOpen()" 
                                                 :style="`display:flex; align-items:center; justify-content:space-between; background:${selectedName === '{{ __('purchase_order.create.select_supplier') }}' ? '#FEF2F2' : '#fff'}; border:1.5px solid ${selectedName === '{{ __('purchase_order.create.select_supplier') }}' ? '#DC2626' : 'var(--po-bd)'}; border-radius:6px; padding:4px 8px; cursor:pointer; font-size:12px; font-weight:600; color:${selectedName === '{{ __('purchase_order.create.select_supplier') }}' ? '#DC2626' : 'var(--po-tx)'}; width:100%; min-width:160px`">
                                                <span x-text="selectedName" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:130px"></span>
                                                <i class="fa-solid fa-chevron-down" style="font-size:10px; color:${selectedName === '{{ __('purchase_order.create.select_supplier') }}' ? '#DC2626' : 'var(--po-mu)'}"></i>
                                            </div>
                                             <div x-show="selectedName === '{{ __('purchase_order.create.select_supplier') }}'" 
                                                  style="font-size:11px; font-weight:700; color:#DC2626; margin-top:3px; display:flex; align-items:center; gap:3px">
                                                 <i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i> {{ __('purchase_order.create.invalid_supplier') }}
                                             </div>

                                            <template x-teleport="body">
                                                <div x-show="open" x-cloak @click.outside="open = false" 
                                                     :style="`position:fixed; top:${top}px; left:${left}px; width:220px; background:#fff; border:1px solid var(--po-bd); border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,0.25); z-index:99999; padding:6px`">
                                                    <input type="text" x-model="search" placeholder="{{ __('purchase_order.create.search_supplier') }}" style="width:100%; padding:5px 8px; font-size:12px; border:1px solid var(--po-bd2); border-radius:4px; outline:none; margin-bottom:4px" @click.stop>
                                                    
                                                    <div style="max-height:170px; overflow-y:auto">
                                                        <div @click="selectSupplier(null)" 
                                                             style="padding:6px 8px; font-size:12px; font-weight:600; color:var(--po-mu); cursor:pointer; border-radius:4px; border-bottom:1px solid var(--po-bd2); margin-bottom:4px"
                                                             onmouseover="this.style.background='#F1F5F9'" 
                                                             onmouseout="this.style.background='transparent'">
                                                            -- {{ __('purchase_order.create.select_supplier') }} --
                                                        </div>
                                                        <template x-for="sup in filtered" :key="sup.id">
                                                            <div @click="selectSupplier(sup.id)" 
                                                                 style="padding:6px 8px; font-size:12px; font-weight:600; color:var(--po-tx); cursor:pointer; border-radius:4px; transition:0.1s"
                                                                 onmouseover="this.style.background='#F1F5F9'" 
                                                                 onmouseout="this.style.background='transparent'"
                                                                 x-text="sup.name">
                                                            </div>
                                                        </template>
                                                        <div x-show="filtered.length === 0" style="padding:8px; font-size:11.5px; color:var(--po-mu); text-align:center">
                                                            {{ __('purchase_order.create.no_supplier_found') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <!-- Group Total Row -->
                            <tr style="background:#F8FAFC; font-weight:700; border-top:1px solid var(--po-bd2)">
                                <td colspan="8" style="text-align:right; padding:10px 14px; font-size:13px; color:var(--po-mu)">
                                    {{ __('purchase_order.labels.group_total', ['group' => str_replace(['🥩 ', '🥬 ', '📦 '], '', $group['label'])]) }}
                                </td>
                                <td colspan="1" style="text-align:left; padding:10px 14px; font-size:14px; font-weight:800; color:var(--po-bl)">
                                    {{ number_format($groupTotal, 0, ',', '.') }} đ
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div style="text-align:center; padding:40px; background:var(--po-wh); border-radius:var(--po-r); border:1px solid var(--po-bd)">
                <i class="fa-solid fa-inbox" style="font-size:36px; color:var(--po-mu); margin-bottom:10px"></i>
                <p style="font-weight:700; color:var(--po-tx)">{{ __('purchase_order.empty.no_ingredients_title') }}</p>
                <p style="font-size:12.5px; color:var(--po-mu)">{{ __('purchase_order.empty.no_ingredients_sub') }}</p>
            </div>
        @endforelse

        <!-- Fixed Bottom Grand Total Blue Banner (Sticky inside content container) -->
        <div class="lhn-grand" style="position:sticky; bottom:16px; width:100%; border-radius:12px; background:linear-gradient(135deg, #1474FF, #0059DD); color:#fff; padding:12px 18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; box-shadow:0 8px 24px rgba(20, 116, 255, 0.35); z-index:10; margin-top:24px">
            <div style="min-width:200px; flex:1">
                <div style="font-size:14px; font-weight:800; line-height:1.3">
                    {{ __('purchase_order.create.grand_total_header', [
                        'date' => \Carbon\Carbon::parse($orderDate)->format('d/m/Y'),
                        'from' => \Carbon\Carbon::parse($sourceFrom)->format('d/m/Y'),
                        'to' => \Carbon\Carbon::parse($sourceTo)->format('d/m/Y')
                    ]) }}
                </div>
                <div style="font-size:12px; opacity:0.9; margin-top:2px">
                    {{ __('purchase_order.create.grand_total_sub', [
                        'supplier_count' => $supplierCount,
                        'selected_count' => $selectedCount,
                        'total_count' => $totalCount
                    ]) }}
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap; flex-shrink:0">
                <div style="text-align:right">
                    <div style="font-size:10.5px; opacity:0.85; text-transform:uppercase; font-weight:700">{{ __('purchase_order.create.grand_total_label') }}</div>
                    <div style="font-size:20px; font-weight:900; letter-spacing:-0.5px; white-space:nowrap">{{ number_format($grandTotal, 0, ',', '.') }} đ</div>
                </div>
                <button wire:click="createAndSendOrders" wire:loading.attr="disabled" wire:target="createAndSendOrders" class="po-btn" style="background:#fff; color:#0059DD; font-weight:800; padding:9px 18px; font-size:13.5px; border:none; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.15); white-space:nowrap; display:inline-flex; align-items:center; gap:8px; flex-shrink:0">
                    <i class="fa-solid fa-paper-plane"></i> {{ __('purchase_order.actions.create_and_send') }}
                </button>
            </div>
        </div>

        </div><!-- /content trái -->

        <!-- Sidebar Tóm tắt đơn hàng (mẫu bluefire_demo) -->
        <div class="oc-sidebar" style="width:260px; flex-shrink:0; position:sticky; top:16px">
            <div style="background:var(--po-wh); border:1px solid var(--po-bd); border-radius:var(--po-r); padding:14px; margin-bottom:12px; box-shadow:var(--po-sh)">
                <div style="font-size:13px; font-weight:800; color:var(--po-tx); margin-bottom:10px">{{ __('purchase_order.sidebar.summary_title') }}</div>
                @php
                    $isMultiDay = $sourceFrom !== $sourceTo;
                    $sumRows = [
                        [__('purchase_order.sidebar.scope'), $isMultiDay ? __('purchase_order.sidebar.scope_multi_day') : __('purchase_order.sidebar.scope_single_day')],
                        [__('purchase_order.sidebar.order_date'), \Carbon\Carbon::parse($orderDate)->format('d/m/Y')],
                        [__('purchase_order.sidebar.source_list'), \Carbon\Carbon::parse($sourceFrom)->format('d/m/Y').($isMultiDay ? ' – '.\Carbon\Carbon::parse($sourceTo)->format('d/m/Y') : '')],
                        [__('purchase_order.sidebar.shifts'), __('purchase_order.sidebar.shifts_count', ['count' => count($selectedShifts)])],
                        [__('purchase_order.sidebar.suppliers'), __('purchase_order.sidebar.suppliers_count', ['count' => $supplierCount])],
                        [__('purchase_order.sidebar.ingredients'), __('purchase_order.sidebar.ingredients_count', ['selected' => $selectedCount, 'total' => $totalCount])],
                    ];
                @endphp
                @foreach($sumRows as [$k, $v])
                    <div style="display:flex; justify-content:space-between; gap:8px; padding:5px 0; border-bottom:1px solid var(--po-bd2); font-size:12px">
                        <span style="color:var(--po-mu); font-weight:600">{{ $k }}</span>
                        <span style="color:var(--po-tx); font-weight:700; text-align:right">{{ $v }}</span>
                    </div>
                @endforeach
                <div style="display:flex; justify-content:space-between; gap:8px; padding:7px 0 2px; font-size:12px">
                    <span style="color:var(--po-mu); font-weight:600">{{ __('purchase_order.sidebar.grand_total') }}</span>
                    <span style="color:var(--po-bl); font-weight:800; font-size:14px; white-space:nowrap">{{ number_format($grandTotal, 0, ',', '.') }} đ</span>
                </div>
            </div>

            <div style="background:var(--po-wh); border:1px solid var(--po-bd); border-radius:var(--po-r); padding:14px; margin-bottom:12px; box-shadow:var(--po-sh)">
                <div style="font-size:13px; font-weight:800; color:var(--po-tx); margin-bottom:10px">{{ __('purchase_order.sidebar.ncc_distribution') }}</div>
                @forelse($bySupplier as $sid => $agg)
                    <div style="display:flex; align-items:center; gap:8px; background:var(--po-bd2); border-radius:8px; padding:8px 10px; margin-bottom:6px">
                        <div style="width:8px; height:8px; border-radius:50%; flex-shrink:0; background:{{ $nccDotColors[$loop->index % count($nccDotColors)] }}"></div>
                        <div style="flex:1; min-width:0">
                            <div style="font-size:12px; font-weight:700; color:var(--po-tx); overflow:hidden; text-overflow:ellipsis; white-space:nowrap">{{ $supplierNamesById[$sid] ?? 'NCC #'.$sid }}</div>
                            <div style="font-size:11px; color:var(--po-mu)">{{ __('purchase_order.sidebar.items_count', ['count' => $agg['count']]) }}</div>
                        </div>
                        <div style="font-size:12px; font-weight:800; color:var(--po-bl); white-space:nowrap">{{ number_format($agg['total'], 0, ',', '.') }}đ</div>
                    </div>
                @empty
                    <div style="font-size:12px; color:var(--po-mu); padding:4px 0">—</div>
                @endforelse
            </div>
        </div><!-- /sidebar -->

        </div><!-- /2-col -->
    </div>

    <style>
        @media (max-width: 1100px) {
            .oc-layout { flex-direction: column; }
            .oc-main { width: 100%; }
            .oc-sidebar { width: 100% !important; position: static !important; }
        }
    </style>
</x-filament-panels::page>
