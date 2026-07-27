<div class="emp-page bf-list-page w-full">
    @include('filament.resources.areas.partials.styles')

    @php
        $stats = $this->getStats();
        $shifts = $this->shifts();
        $createUrl = \App\Filament\Resources\ShiftResource::getUrl('create');
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
            <h1 class="emp-title">{{ __('catalog.shift.list.title') }}</h1>
            <p class="emp-subtitle">{{ __('catalog.shift.list.subtitle') }}</p>
        </div>
        <div class="emp-actions">
            <button type="button" wire:click="resetFilters" class="emp-btn">
                <i class="fa-solid fa-rotate-left"></i>
                {{ __('catalog.common.refresh') }}
            </button>
            <a href="{{ $createUrl }}" wire:navigate class="emp-btn emp-btn-primary">
                <i class="fa-solid fa-plus"></i>
                {{ __('catalog.shift.list.create') }}
            </a>
        </div>
    </div>

    <!-- 3 KPIs Stats -->
    <div class="krow" style="grid-template-columns:repeat(3,1fr); margin-bottom: 16px;">
        <div class="kcard">
            <div class="ktop"><div class="kico ki-b"><i class="fa-solid fa-clock"></i></div></div>
            <div class="kval">{{ $stats['total'] }}</div>
            <div class="klbl">{{ __('catalog.shift.list.kpi.total_label') }}</div>
            <div class="knote">{{ __('catalog.shift.list.kpi.total_note') }}</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-g"><i class="fa-solid fa-circle-check"></i></div></div>
            <div class="kval">{{ $stats['in_use'] }}</div>
            <div class="klbl">{{ __('catalog.shift.list.kpi.in_use_label') }}</div>
            <div class="knote">{{ __('catalog.shift.list.kpi.in_use_note') }}</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-o"><i class="fa-solid fa-pause-circle"></i></div></div>
            <div class="kval">{{ $stats['unused'] }}</div>
            <div class="klbl">{{ __('catalog.shift.list.kpi.unused_label') }}</div>
            <div class="knote">{{ __('catalog.shift.list.kpi.unused_note') }}</div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="tcard">
        <div class="tbar">
            <div class="tsbox" style="height:38px; min-width:260px; max-width:360px">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input wire:model.live.debounce.250ms="shiftSearch" type="text" placeholder="{{ __('catalog.shift.list.search_placeholder') }}">
            </div>
            <div class="tsp"></div>
            <button type="button" wire:click="resetFilters" class="fbtn">
                <i class="fa-solid fa-filter-circle-xmark"></i> {{ __('catalog.common.clear_selection') }}
            </button>
        </div>
        <div class="tw">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; background:var(--po-bd2)">
                        <th style="padding:12px 14px; width:60px">{{ __('catalog.common.index') }}</th>
                        <th style="padding:12px 14px; width:100px; text-align:center">{{ __('catalog.shift.table.sort_order') }}</th>
                        <th style="padding:12px 14px">{{ __('catalog.shift.list.columns.name') }}</th>
                        <th style="padding:12px 14px">{{ __('catalog.shift.list.columns.time_range') }}</th>
                        <th style="padding:12px 14px">{{ __('catalog.common.created_at') }}</th>
                        <th style="padding:12px 14px; text-align:center; width:100px">{{ __('catalog.common.actions_upper') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $index => $row)
                        <tr style="border-bottom:1px solid var(--po-bd2); color:var(--po-tx)" class="emp-row">
                            <td style="padding:12px 14px; font-weight:600; color:var(--po-mu)">
                                {{ $shifts->firstItem() + $index }}
                            </td>
                            <td style="padding:12px 14px; text-align:center; font-weight:800; color:var(--po-bl); font-variant-numeric:tabular-nums">
                                {{ $row->sort_order }}
                            </td>
                            <td style="padding:12px 14px; font-weight:700; color:var(--po-tx)">
                                {{ $row->name }}
                            </td>
                            <td style="padding:12px 14px; font-variant-numeric:tabular-nums; font-weight:600">
                                {{ $row->time_range }}
                            </td>
                            <td style="padding:12px 14px; color:var(--po-mu); font-variant-numeric:tabular-nums">
                                {{ $row->created_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td style="padding:12px 14px; text-align:center">
                                <div style="display:inline-flex; gap:6px">
                                    <a href="{{ \App\Filament\Resources\ShiftResource::getUrl('edit', ['record' => $row->id]) }}" wire:navigate class="abt" title="{{ __('catalog.common.edit') }}">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>
                                    <button type="button" wire:click="deleteShift({{ $row->id }})" wire:confirm="{{ __('catalog.shift.list.confirm_delete') }}" class="abt" title="{{ __('catalog.common.delete') }}">
                                        <i class="fa-solid fa-trash" style="color:var(--po-rd)"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:32px; text-align:center; color:var(--po-mu)">
                                {{ __('catalog.shift.list.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($shifts->hasPages())
            <div style="padding: 10px 16px; border-top: 1px solid var(--po-bd2); background: var(--po-bd2);">
                {{ $shifts->links() }}
            </div>
        @endif
    </div>
</div>
