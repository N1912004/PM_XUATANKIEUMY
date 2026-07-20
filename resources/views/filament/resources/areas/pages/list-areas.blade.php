<div class="emp-page w-full space-y-6">
    @include('filament.resources.areas.partials.styles')

    @php
        $stats = $this->getStats();
        $areas = $this->areas();
        $createUrl = \App\Filament\Resources\AreaResource::getUrl('create');
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
            <h1 class="emp-title">{{ __('catalog.area.list.title') }}</h1>
            <p class="emp-subtitle">{{ __('catalog.area.list.subtitle') }}</p>
        </div>
        <div class="emp-actions">
            <a href="{{ $createUrl }}" wire:navigate class="emp-btn emp-btn-primary">
                <i class="fa-solid fa-plus"></i>
                {{ __('catalog.area.list.create') }}
            </a>
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
            <div class="ktop"><div class="kico ki-o"><i class="fa-solid fa-circle-check"></i></div></div>
            <div class="kval">{{ $stats['active_areas'] }}</div>
            <div class="klbl">{{ __('catalog.area.list.kpi.active_label') }}</div>
            <div class="knote">{{ __('catalog.area.list.kpi.active_note') }}</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-g"><i class="fa-solid fa-utensils"></i></div></div>
            <div class="kval">{{ $stats['total_kitchens'] }}</div>
            <div class="klbl">{{ __('catalog.area.list.kpi.kitchens_label') }}</div>
            <div class="knote">{{ __('catalog.area.list.kpi.kitchens_note') }}</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-p"><i class="fa-solid fa-user-tie"></i></div></div>
            <div class="kval">{{ $stats['managers'] }}</div>
            <div class="klbl">{{ __('catalog.area.list.kpi.managers_label') }}</div>
            <div class="knote">{{ __('catalog.area.list.kpi.managers_note') }}</div>
        </div>
    </div>

    <!-- ==========================================
         DANH SÁCH KHU VỰC
         ========================================== -->
    <div class="tcard">
        <div class="tbar">
            <div class="tsbox" style="height:38px; min-width:260px; max-width:360px">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input wire:model.live.debounce.250ms="areaSearch" type="text" placeholder="{{ __('catalog.area.list.search_placeholder') }}">
            </div>
            <div class="tsp"></div>
            <button wire:click="resetFilters" class="fbtn">
                <i class="fa-solid fa-filter-circle-xmark"></i> {{ __('catalog.common.reset_filters') }}
            </button>
        </div>
        <div class="tw">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; background:var(--po-bd2)">
                        <th style="padding:12px 14px; width:100px">{{ __('catalog.area.list.columns.code') }}</th>
                        <th style="padding:12px 14px">{{ __('catalog.area.list.columns.area') }}</th>
                        <th style="padding:12px 14px">{{ __('catalog.area.list.columns.manager') }}</th>
                        <th style="padding:12px 14px; text-align:center; width:90px">{{ __('catalog.area.list.columns.kitchens') }}</th>
                        <th style="padding:12px 14px; width:140px">{{ __('catalog.common.status') }}</th>
                        <th style="padding:12px 14px; text-align:center; width:100px">{{ __('catalog.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($areas as $row)
                        <tr style="border-bottom:1px solid var(--po-bd2); color:var(--po-tx)" class="emp-row">
                            <td style="padding:12px 14px; font-weight:700; color:var(--po-mu)">{{ $row->code }}</td>
                            <td style="padding:12px 14px;">
                                <div style="font-weight:700; color:var(--po-tx)">{{ $row->name }}</div>
                                <div style="font-size:11px; color:var(--po-mu); margin-top:2px">{{ $row->notes ?: __('catalog.area.list.no_notes') }}</div>
                            </td>
                            <td style="padding:12px 14px; font-weight:600">{{ $row->manager?->name }}</td>
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
                                    <button wire:click="deleteArea({{ $row->id }})" wire:confirm="{{ __('catalog.area.list.confirm_delete') }}" class="abt" title="{{ __('catalog.common.delete') }}">
                                        <i class="fa-solid fa-trash"></i>
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
</div>
