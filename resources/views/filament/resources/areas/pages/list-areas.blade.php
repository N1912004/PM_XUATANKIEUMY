<div class="emp-page bf-list-page w-full"
     x-data="{
        cf: { open: false, title: '', message: '', confirmLabel: '', danger: true, method: null, arg: null, busy: false },
        askConfirm(method, arg, title, message, confirmLabel, danger = true) {
            this.cf = { open: true, title, message, confirmLabel, danger, method, arg, busy: false };
        },
        closeConfirm() {
            if (this.cf.busy) return;
            this.cf.open = false;
            this.cf.method = null;
        },
        async runConfirm() {
            if (this.cf.busy || ! this.cf.method) return;
            this.cf.busy = true;
            try {
                if (this.cf.arg === null) {
                    await $wire.call(this.cf.method);
                } else {
                    await $wire.call(this.cf.method, this.cf.arg);
                }
            } finally {
                this.cf.busy = false;
                this.cf.open = false;
                this.cf.method = null;
            }
        },
     }"
     @keydown.escape.window="closeConfirm()">
    @include('filament.resources.areas.partials.styles')

    @php
        $stats = $this->getStats();
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
        @php
            $areas = $this->areas();
        @endphp
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
                            <th style="padding:12px 14px; width:70px; text-align:center">{{ __('catalog.common.index') }}</th>
                            <th style="padding:12px 14px; width:130px; text-align:center; white-space:nowrap">{{ __('catalog.area.table.code') }}</th>
                            <th style="padding:12px 14px; width:28%">{{ __('catalog.area.table.name') }}</th>
                            <th style="padding:12px 14px; width:22%">{{ __('catalog.area.table.manager') }}</th>
                            <th style="padding:12px 14px; text-align:center; width:130px; white-space:nowrap">{{ __('catalog.area.table.kitchens_count') }}</th>
                            <th style="padding:12px 14px; text-align:center; width:140px; white-space:nowrap">{{ __('catalog.common.status') }}</th>
                            <th style="padding:12px 14px; text-align:center; width:110px; white-space:nowrap">{{ __('catalog.common.actions_upper') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($areas as $index => $row)
                            <tr style="border-bottom:1px solid var(--po-bd2); color:var(--po-tx)" class="emp-row">
                                <td style="padding:12px 14px; text-align:center; font-weight:600; color:var(--po-mu); font-variant-numeric:tabular-nums">{{ ($areas->firstItem() ?? 1) + $index }}</td>
                                <td style="padding:12px 14px; text-align:center; font-weight:800; color:var(--po-bl); font-variant-numeric:tabular-nums; white-space:nowrap">{{ $row->code ?: ('KV-'.$row->id) }}</td>
                                <td style="padding:12px 14px;">
                                    <div style="font-weight:700; color:var(--po-tx)">{{ $row->name }}</div>
                                    <div style="font-size:11px; color:var(--po-mu); margin-top:2px">{{ $row->notes ?: __('catalog.area.list.no_notes') }}</div>
                                </td>
                                <td style="padding:12px 14px; font-weight:600">{{ $row->manager?->name ?: '—' }}</td>
                                <td style="padding:12px 14px; text-align:center; font-weight:800; font-size:14px; color:var(--po-bl); font-variant-numeric:tabular-nums">
                                    {{ $row->kitchens_count }}
                                </td>
                                <td style="padding:12px 14px; text-align:center">
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
                                        <button type="button" @click="askConfirm('deleteArea', {{ $row->id }}, @js(__('catalog.area.delete_title')), @js(__('catalog.area.list.confirm_delete')), @js(__('catalog.common.delete')))" class="abt" title="{{ __('catalog.common.delete') }}">
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
            @if($areas->total() > 0)
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-top:14px; padding:14px 16px; border-top:1px solid var(--po-bd2); font-size:12px; color:var(--po-mu)">
                    <div>{{ __('catalog.pagination.summary', ['from' => $areas->firstItem() ?? 0, 'to' => $areas->lastItem() ?? 0, 'total' => $areas->total(), 'entity' => __('catalog.area.list.pagination_entity')]) }}</div>
                    <div class="pgwrap"><span>{{ __('common.pagination.per_page_label') }}</span><select wire:model.live="areaPerPage" class="lv-per-page-select">@foreach([5, 10, 20, 50] as $count)<option value="{{ $count }}">{{ $count }}</option>@endforeach</select>
                    @if($areas->total() > 0)
                        <nav style="display:flex; align-items:center; gap:4px;">@if($areas->onFirstPage())<span style="opacity:.4; padding:4px"><i class="fa-solid fa-chevron-left" style="font-size:11px"></i></span>@else<button type="button" wire:click="previousPage('areasPage')" style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border:1px solid var(--po-bd);border-radius:6px;background:transparent"><i class="fa-solid fa-chevron-left" style="font-size:11px"></i></button>@endif
                        @php
                            $pageWindow = collect([1, $areas->currentPage() - 1, $areas->currentPage(), $areas->currentPage() + 1, $areas->lastPage()])
                                ->filter(fn ($page) => $page >= 1 && $page <= $areas->lastPage())
                                ->unique()
                                ->sort()
                                ->values();
                        @endphp
                        @foreach($pageWindow as $i => $page) @if($i > 0 && $page - $pageWindow[$i-1] > 1)<span style="padding:0 4px">…</span>@endif @if($page == $areas->currentPage())<span style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border-radius:6px;background:var(--po-bl);color:#fff;font-weight:700">{{ $page }}</span>@else<button type="button" wire:click="gotoPage({{ $page }}, 'areasPage')" style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border:1px solid var(--po-bd);border-radius:6px;background:transparent">{{ $page }}</button>@endif @endforeach
                        @if($areas->hasMorePages())<button type="button" wire:click="nextPage('areasPage')" style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border:1px solid var(--po-bd);border-radius:6px;background:transparent"><i class="fa-solid fa-chevron-right" style="font-size:11px"></i></button>@else<span style="opacity:.4;padding:4px"><i class="fa-solid fa-chevron-right" style="font-size:11px"></i></span>@endif</nav>@endif</div></div>
            @endif
        </div>
    @endif

    <!-- ==========================================
         TAB 2: NHÀ ĂN / BẾP (FULL WIDTH TABLE)
         ========================================== -->
    @if($activeTab === 'canteen')
        @php
            $kitchens = $this->kitchens();
            $allAreas = \App\Models\Area::orderBy('name')->pluck('name', 'id')->toArray();
        @endphp
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
                            <th style="padding:12px 14px; width:70px; text-align:center">STT</th>
                            <th style="padding:12px 14px; width:26%">{{ __('catalog.kitchen.table.name') }}</th>
                            <th style="padding:12px 14px; width:18%">{{ __('catalog.kitchen.table.type') }}</th>
                            <th style="padding:12px 14px; text-align:center; width:160px; white-space:nowrap">{{ __('catalog.kitchen.table.capacity') }}</th>
                            <th style="padding:12px 14px; width:20%">{{ __('catalog.kitchen.table.manager') }}</th>
                            <th style="padding:12px 14px; text-align:center; width:140px; white-space:nowrap">{{ __('catalog.common.status') }}</th>
                            <th style="padding:12px 14px; text-align:center; width:110px; white-space:nowrap">{{ __('catalog.common.actions_upper') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kitchens as $index => $kRow)
                            <tr style="border-bottom:1px solid var(--po-bd2); color:var(--po-tx)" class="emp-row">
                                <td style="padding:12px 14px; text-align:center; font-weight:600; color:var(--po-mu); font-variant-numeric:tabular-nums">{{ ($kitchens->firstItem() ?? 1) + $index }}</td>
                                <td style="padding:12px 14px;">
                                    <div style="font-weight:700; color:var(--po-tx)">{{ $kRow->name }}</div>
                                    <div style="font-size:11px; color:var(--po-mu); margin-top:2px">{{ __('catalog.common.area_prefix', ['name' => $kRow->area?->name ?: '—']) }}</div>
                                </td>
                                <td style="padding:12px 14px; font-weight:600">{{ $kRow->kitchenType?->name ?: ($kRow->type ?: '—') }}</td>
                                <td style="padding:12px 14px; text-align:center; font-weight:800; color:var(--po-bl); font-variant-numeric:tabular-nums">
                                    {{ __('catalog.kitchen.list.capacity_value', ['count' => number_format((float)$kRow->capacity, 0, ',', '.')]) }}
                                </td>
                                <td style="padding:12px 14px; font-weight:600">{{ $kRow->manager?->name ?: '—' }}</td>
                                <td style="padding:12px 14px; text-align:center">
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
                                        <button type="button" @click="askConfirm('deleteCanteen', {{ $kRow->id }}, @js(__('catalog.kitchen.delete_title')), @js(__('catalog.kitchen.list.confirm_delete')), @js(__('catalog.common.delete')))" class="abt" title="{{ __('catalog.common.delete') }}">
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
            @if($kitchens->total() > 0)
                @php
                    $pageWindow = collect([1, $kitchens->currentPage() - 1, $kitchens->currentPage(), $kitchens->currentPage() + 1, $kitchens->lastPage()])
                        ->filter(fn ($page) => $page >= 1 && $page <= $kitchens->lastPage())
                        ->unique()
                        ->sort()
                        ->values();
                @endphp
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:14px;padding:14px 16px;border-top:1px solid var(--po-bd2);font-size:12px;color:var(--po-mu)"><div>{{ __('catalog.pagination.summary', ['from'=>$kitchens->firstItem()??0,'to'=>$kitchens->lastItem()??0,'total'=>$kitchens->total(),'entity'=>__('catalog.kitchen.list.pagination_entity')]) }}</div><div class="pgwrap"><span>{{ __('common.pagination.per_page_label') }}</span><select wire:model.live="canteenPerPage" class="lv-per-page-select">@foreach([5,10,20,50] as $count)<option value="{{ $count }}">{{ $count }}</option>@endforeach</select>@if($kitchens->total() > 0)<nav style="display:flex;align-items:center;gap:4px">@if($kitchens->onFirstPage())<span style="opacity:.4;padding:4px"><i class="fa-solid fa-chevron-left"></i></span>@else<button type="button" wire:click="previousPage('canteensPage')" style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border:1px solid var(--po-bd);border-radius:6px;background:transparent"><i class="fa-solid fa-chevron-left"></i></button>@endif @foreach($pageWindow as $i=>$page) @if($i>0&&$page-$pageWindow[$i-1]>1)<span style="padding:0 4px">…</span>@endif @if($page==$kitchens->currentPage())<span style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border-radius:6px;background:var(--po-bl);color:#fff;font-weight:700">{{ $page }}</span>@else<button type="button" wire:click="gotoPage({{$page}}, 'canteensPage')" style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border:1px solid var(--po-bd);border-radius:6px;background:transparent">{{ $page }}</button>@endif @endforeach @if($kitchens->hasMorePages())<button type="button" wire:click="nextPage('canteensPage')" style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border:1px solid var(--po-bd);border-radius:6px;background:transparent"><i class="fa-solid fa-chevron-right"></i></button>@else<span style="opacity:.4;padding:4px"><i class="fa-solid fa-chevron-right"></i></span>@endif</nav>@endif</div></div>
            @endif
        </div>
    @endif

    {{-- Hộp thoại xác nhận trong trang (teleport ra body) --}}
    <template x-teleport="body">
        <div x-show="cf.open" x-cloak class="rcf-overlay" @click.self="closeConfirm()">
            <div class="rcf-box" role="dialog" aria-modal="true">
                <div class="rcf-head">
                    <span class="rcf-ico" :class="cf.danger ? 'rcf-ico-danger' : 'rcf-ico-info'">
                        <i class="fa-solid" :class="cf.danger ? 'fa-triangle-exclamation' : 'fa-rotate-left'"></i>
                    </span>
                    <h3 class="rcf-title" x-text="cf.title"></h3>
                </div>
                <p class="rcf-msg" x-text="cf.message"></p>
                <div class="rcf-actions">
                    <button type="button" class="rcf-btn rcf-btn-ghost" @click="closeConfirm()" :disabled="cf.busy">
                        {{ __('common.actions.cancel') }}
                    </button>
                    <button type="button" class="rcf-btn" :class="cf.danger ? 'rcf-btn-danger' : 'rcf-btn-primary'"
                            @click="runConfirm()" :disabled="cf.busy">
                        <i class="fa-solid fa-spinner fa-spin" x-show="cf.busy"></i>
                        <span x-text="cf.confirmLabel"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
