<div class="emp-page bf-list-page w-full">
    @include('filament.resources.areas.partials.styles')

    @php
        $stats = $this->getStats();
        $areas = $this->areas();
        $kitchens = $this->kitchens();
        $allAreas = \App\Models\Area::orderBy('name')->pluck('name', 'id')->toArray();
        $createAreaUrl = \App\Filament\Resources\AreaResource::getUrl('create');
        $createKitchenUrl = \App\Filament\Resources\KitchenResource::getUrl('create');
    @endphp

    @if (session()->has('message'))
        <div style="background:var(--po-gn-s); color:var(--po-gn-t); padding:12px 16px; border-radius:8px; border:1px solid var(--po-gn); margin-bottom:16px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div style="background:var(--po-rd-s); color:var(--po-rd-t); padding:12px 16px; border-radius:8px; border:1px solid var(--po-rd); margin-bottom:16px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px">
            <i class="fa-solid fa-triangle-exclamation"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Header Section -->
    <div class="emp-head" style="margin-bottom: 14px;">
        <div>
            <h1 class="emp-title">{{ __('catalog.area.unified.title') }}</h1>
            <p class="emp-subtitle">{{ __('catalog.area.unified.subtitle') }}</p>
        </div>
        <div class="emp-actions">
            <button type="button" wire:click="resetForms" class="emp-btn">
                <i class="fa-solid fa-rotate-left"></i>
                {{ __('catalog.common.refresh') }}
            </button>
            @if($activeTab === 'area')
                <a href="{{ $createAreaUrl }}" wire:navigate class="emp-btn emp-btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    {{ __('catalog.area.list.create') }}
                </a>
            @else
                <a href="{{ $createKitchenUrl }}" wire:navigate class="emp-btn emp-btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    {{ __('catalog.kitchen.list.create') }}
                </a>
            @endif
        </div>
    </div>

    <!-- 4 KPIs Stats -->
    <div class="krow" style="grid-template-columns:repeat(4,1fr); margin-bottom: 16px;">
        <div class="kcard">
            <div class="ktop"><div class="kico ki-b"><i class="fa-solid fa-map-location-dot"></i></div></div>
            <div class="kval">{{ $stats['total_areas'] }}</div>
            <div class="klbl">{{ __('catalog.area.list.kpi.total_label') }}</div>
            <div class="knote">{{ __('catalog.area.list.kpi.total_note') }}</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-g"><i class="fa-solid fa-utensils"></i></div></div>
            <div class="kval">{{ $stats['total_kitchens'] }}</div>
            <div class="klbl">{{ __('catalog.area.list.kpi.kitchens_label') }}</div>
            <div class="knote">{{ __('catalog.kitchen.list.kpi.total_note') }}</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-o"><i class="fa-solid fa-circle-check"></i></div></div>
            <div class="kval">{{ $stats['active_areas'] }}</div>
            <div class="klbl">{{ __('catalog.area.list.kpi.active_label') }}</div>
            <div class="knote">{{ __('catalog.area.list.kpi.active_note') }}</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-p"><i class="fa-solid fa-user-tie"></i></div></div>
            <div class="kval">{{ $stats['managers'] }}</div>
            <div class="klbl">{{ __('catalog.area.list.kpi.managers_label') }}</div>
            <div class="knote">{{ __('catalog.area.list.kpi.managers_note') }}</div>
        </div>
    </div>

    <!-- Sub-Tab Navigation Bar -->
    <div class="area-tabs">
        <button type="button" wire:click="setTab('area')" class="area-tab {{ $activeTab === 'area' ? 'active' : '' }}">
            <i class="fa-solid fa-map-location-dot"></i> {{ __('catalog.area.label') }}
        </button>
        <button type="button" wire:click="setTab('canteen')" class="area-tab {{ $activeTab === 'canteen' ? 'active' : '' }}">
            <i class="fa-solid fa-utensils"></i> {{ __('catalog.kitchen.label') }}
        </button>
    </div>

    <!-- ==========================================
         TAB 1: KHU VỰC (FULL WIDTH TABLE)
         ========================================== -->
    @if($activeTab === 'area')
        <div class="tcard">
            <div class="tbar">
                <div class="tsbox" style="height:38px; min-width:260px; max-width:360px">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input wire:model.live.debounce.250ms="areaSearch" type="text" placeholder="{{ __('catalog.area.list.search_placeholder') }}">
                </div>
                <div class="tsp"></div>
                <button type="button" wire:click="resetAreaFilters" class="fbtn">
                    <i class="fa-solid fa-filter-circle-xmark"></i> {{ __('catalog.common.clear_selection') }}
                </button>
            </div>
            <div class="tw">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px">
                    <thead>
                        <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; background:var(--po-bd2)">
                            <th style="padding:12px 14px; width:100px">{{ __('catalog.area.table.code') }}</th>
                            <th style="padding:12px 14px">{{ __('catalog.area.table.name') }}</th>
                            <th style="padding:12px 14px">{{ __('catalog.area.table.manager') }}</th>
                            <th style="padding:12px 14px; text-align:center; width:90px">{{ __('catalog.area.table.kitchens_count') }}</th>
                            <th style="padding:12px 14px; width:140px">{{ __('catalog.common.status') }}</th>
                            <th style="padding:12px 14px; text-align:center; width:100px">{{ __('catalog.common.actions_upper') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($areas as $row)
                            <tr style="border-bottom:1px solid var(--po-bd2); color:var(--po-tx)" class="emp-row">
                                <td style="padding:12px 14px; font-weight:700; color:var(--po-mu)">{{ $row->code ?: ('KV-'.$row->id) }}</td>
                                <td style="padding:12px 14px;">
                                    <div style="font-weight:700; color:var(--po-tx)">{{ $row->name }}</div>
                                    <div style="font-size:11px; color:var(--po-mu); margin-top:2px">{{ $row->notes ?: __('catalog.area.list.no_notes') }}</div>
                                </td>
                                <td style="padding:12px 14px; font-weight:600">{{ $row->manager?->name ?: '—' }}</td>
                                <td style="padding:12px 14px; text-align:center; font-weight:800; font-size:15px; color:var(--po-bl)">
                                    {{ $row->kitchens->count() }}
                                </td>
                                <td style="padding:12px 14px;">
                                    @if($row->status)
                                        <span class="st-pill st-ok">{{ __('catalog.kitchen_status.active') }}</span>
                                    @else
                                        <span class="st-pill st-late">{{ __('catalog.kitchen_status.paused') }}</span>
                                    @endif
                                </td>
                                <td style="padding:12px 14px; text-align:center">
                                    <div style="display:inline-flex; gap:6px">
                                        <a href="{{ \App\Filament\Resources\AreaResource::getUrl('edit', ['record' => $row->id]) }}" wire:navigate class="abt" title="{{ __('catalog.common.edit') }}">
                                            <i class="fa-solid fa-pencil"></i>
                                        </a>
                                        <button type="button" wire:click="deleteArea({{ $row->id }})" wire:confirm="{{ __('catalog.area.list.confirm_delete') }}" class="abt" title="{{ __('catalog.common.delete') }}">
                                            <i class="fa-solid fa-trash" style="color:var(--po-rd)"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding:32px; text-align:center; color:var(--po-mu)">
                                    {{ __('catalog.area.list.empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($areas->hasPages())
                <div style="padding: 10px 16px; border-top: 1px solid var(--po-bd2); background: var(--po-bd2);">
                    {{ $areas->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- ==========================================
         TAB 2: NHÀ ĂN / BẾP (FULL WIDTH TABLE)
         ========================================== -->
    @if($activeTab === 'canteen')
        <div class="tcard">
            <div class="tbar">
                <div class="tsbox" style="height:38px; min-width:260px; max-width:360px">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input wire:model.live.debounce.250ms="canteenSearch" type="text" placeholder="{{ __('catalog.kitchen.list.search_placeholder') }}">
                </div>
                <div style="margin-left: 8px;">
                    <select wire:model.live="canteenAreaFilter" class="lv-sel">
                        <option value="">{{ __('catalog.kitchen.list.filters.all_areas') }}</option>
                        @foreach($allAreas as $aId => $aName)
                            <option value="{{ $aId }}">{{ $aName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tsp"></div>
                <button type="button" wire:click="resetCanteenFilters" class="fbtn">
                    <i class="fa-solid fa-filter-circle-xmark"></i> {{ __('catalog.common.clear_selection') }}
                </button>
            </div>
            <div class="tw">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px">
                    <thead>
                        <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; background:var(--po-bd2)">
                            <th style="padding:12px 14px; width:50px">#</th>
                            <th style="padding:12px 14px">{{ __('catalog.kitchen.table.name') }}</th>
                            <th style="padding:12px 14px">{{ __('catalog.kitchen.table.type') }}</th>
                            <th style="padding:12px 14px; text-align:right">{{ __('catalog.kitchen.table.capacity') }}</th>
                            <th style="padding:12px 14px">{{ __('catalog.kitchen.table.manager') }}</th>
                            <th style="padding:12px 14px">{{ __('catalog.common.status') }}</th>
                            <th style="padding:12px 14px; text-align:center; width:100px">{{ __('catalog.common.actions_upper') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kitchens as $index => $kRow)
                            <tr style="border-bottom:1px solid var(--po-bd2); color:var(--po-tx)" class="emp-row">
                                <td style="padding:12px 14px; font-weight:700; color:var(--po-mu)">{{ $index + 1 }}</td>
                                <td style="padding:12px 14px;">
                                    <div style="font-weight:700; color:var(--po-tx)">{{ $kRow->name }}</div>
                                    <div style="font-size:11px; color:var(--po-mu); margin-top:2px">{{ __('catalog.common.area_prefix', ['name' => $kRow->area?->name ?: '—']) }}</div>
                                </td>
                                <td style="padding:12px 14px;">{{ $kRow->kitchenType?->name ?: ($kRow->type ?: '—') }}</td>
                                <td style="padding:12px 14px; text-align:right; font-weight:800; color:var(--po-bl)">
                                    {{ __('catalog.kitchen.list.capacity_value', ['count' => number_format((float)$kRow->capacity, 0, ',', '.')]) }}
                                </td>
                                <td style="padding:12px 14px; font-weight:600">{{ $kRow->manager?->name ?: '—' }}</td>
                                <td style="padding:12px 14px;">
                                    @if($kRow->status === 'active')
                                        <span class="st-pill st-ok">{{ __('catalog.kitchen_status.active') }}</span>
                                    @elseif($kRow->status === 'paused')
                                        <span class="st-pill st-late">{{ __('catalog.kitchen_status.paused') }}</span>
                                    @else
                                        <span class="st-pill" style="background:var(--po-rd-s); color:var(--po-rd-t); border-color:#FECACA">{{ __('catalog.kitchen_status.maintenance') }}</span>
                                    @endif
                                </td>
                                <td style="padding:12px 14px; text-align:center">
                                    <div style="display:inline-flex; gap:6px">
                                        <a href="{{ \App\Filament\Resources\KitchenResource::getUrl('edit', ['record' => $kRow->id]) }}" wire:navigate class="abt" title="{{ __('catalog.common.edit') }}">
                                            <i class="fa-solid fa-pencil"></i>
                                        </a>
                                        <button type="button" wire:click="deleteCanteen({{ $kRow->id }})" wire:confirm="{{ __('catalog.kitchen.list.confirm_delete') }}" class="abt" title="{{ __('catalog.common.delete') }}">
                                            <i class="fa-solid fa-trash" style="color:var(--po-rd)"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding:32px; text-align:center; color:var(--po-mu)">
                                    {{ __('catalog.kitchen.list.empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($kitchens->hasPages())
                <div style="padding: 10px 16px; border-top: 1px solid var(--po-bd2); background: var(--po-bd2);">
                    {{ $kitchens->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
