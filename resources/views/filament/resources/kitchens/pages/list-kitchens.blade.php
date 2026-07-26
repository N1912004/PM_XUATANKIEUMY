<div class="emp-page bf-list-page w-full">
    @include('filament.resources.areas.partials.styles')

    @php
        $stats = $this->getStats();
        $kitchens = $this->kitchens();
        $allAreas = \App\Models\Area::orderBy('name')->get();
        $createUrl = \App\Filament\Resources\KitchenResource::getUrl('create');
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
            <h1 class="emp-title">{{ __('catalog.kitchen.list.title') }}</h1>
            <p class="emp-subtitle">{{ __('catalog.kitchen.list.subtitle') }}</p>
        </div>
        <div class="emp-actions">
            <a href="{{ $createUrl }}" wire:navigate class="emp-btn emp-btn-primary">
                <i class="fa-solid fa-plus"></i>
                {{ __('catalog.kitchen.list.create') }}
            </a>
        </div>
    </div>

    <!-- 4 KPIs Stats -->
    <div class="krow" style="grid-template-columns:repeat(4,1fr); margin-bottom: 16px;">
        <div class="kcard">
            <div class="ktop"><div class="kico ki-g"><i class="fa-solid fa-utensils"></i></div></div>
            <div class="kval">{{ $stats['total_kitchens'] }}</div>
            <div class="klbl">{{ __('catalog.kitchen.list.kpi.total_label') }}</div>
            <div class="knote">{{ __('catalog.kitchen.list.kpi.total_note') }}</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-o"><i class="fa-solid fa-circle-check"></i></div></div>
            <div class="kval">{{ $stats['active_kitchens'] }}</div>
            <div class="klbl">{{ __('catalog.kitchen.list.kpi.active_label') }}</div>
            <div class="knote">{{ __('catalog.kitchen.list.kpi.active_note') }}</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-b"><i class="fa-solid fa-map-location-dot"></i></div></div>
            <div class="kval">{{ $stats['total_areas'] }}</div>
            <div class="klbl">{{ __('catalog.kitchen.list.kpi.areas_label') }}</div>
            <div class="knote">{{ __('catalog.kitchen.list.kpi.areas_note') }}</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-p"><i class="fa-solid fa-user-tie"></i></div></div>
            <div class="kval">{{ $stats['managers'] }}</div>
            <div class="klbl">{{ __('catalog.kitchen.list.kpi.managers_label') }}</div>
            <div class="knote">{{ __('catalog.kitchen.list.kpi.managers_note') }}</div>
        </div>
    </div>

    <!-- ==========================================
         DANH SÁCH NHÀ ĂN / BẾP
         ========================================== -->
    <div class="tcard" style="margin-bottom:14px">
        <!-- Search & Quick Filters -->
        <div class="tbar">
            <div class="tsbox" style="height:38px; min-width:260px; max-width:360px">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input wire:model.live.debounce.250ms="kitchenSearch" type="text" placeholder="{{ __('catalog.kitchen.list.search_placeholder') }}">
            </div>

            <div style="display:flex; gap:6px; margin-left:12px">
                <select wire:model.live="kitchenAreaFilter" class="lv-sel" style="height:34px">
                    <option value="">{{ __('catalog.kitchen.list.filters.all_areas') }}</option>
                    @foreach($allAreas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="kitchenTypeFilter" class="lv-sel" style="height:34px">
                    <option value="">{{ __('catalog.kitchen.list.filters.all_types') }}</option>
                    @foreach($this->kitchenTypeOptions() as $ktId => $ktName)
                        <option value="{{ $ktId }}">{{ $ktName }}</option>
                    @endforeach
                </select>

                <select wire:model.live="kitchenStatusFilter" class="lv-sel" style="height:34px">
                    <option value="">{{ __('catalog.kitchen.list.filters.all_statuses') }}</option>
                    <option value="active">{{ __('catalog.kitchen_status.active') }}</option>
                    <option value="paused">{{ __('catalog.kitchen_status.paused') }}</option>
                    <option value="maintenance">{{ __('catalog.kitchen_status.maintenance') }}</option>
                </select>
            </div>

            <div class="tsp"></div>
            <button wire:click="resetFilters" class="fbtn">
                <i class="fa-solid fa-filter-circle-xmark"></i> {{ __('catalog.common.reset_filters') }}
            </button>
        </div>

        <!-- Kitchen List Table -->
        <div class="tw">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; background:var(--po-bd2)">
                        <th style="padding:12px 14px; width:60px">#</th>
                        <th style="padding:12px 14px">{{ __('catalog.kitchen.list.columns.kitchen') }}</th>
                        <th style="padding:12px 14px">{{ __('catalog.kitchen.list.columns.area') }}</th>
                        <th style="padding:12px 14px">{{ __('catalog.kitchen.list.columns.type') }}</th>
                        <th style="padding:12px 14px; text-align:right">{{ __('catalog.kitchen.list.columns.capacity') }}</th>
                        <th style="padding:12px 14px">{{ __('catalog.kitchen.list.columns.manager') }}</th>
                        <th style="padding:12px 14px; width:140px">{{ __('catalog.common.status') }}</th>
                        <th style="padding:12px 14px; text-align:center; width:100px">{{ __('catalog.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kitchens as $index => $row)
                        <tr style="border-bottom:1px solid var(--po-bd2); color:var(--po-tx)" class="emp-row">
                            <td style="padding:12px 14px; font-weight:600; color:var(--po-mu)">{{ ($kitchens->currentPage() - 1) * $kitchens->perPage() + $index + 1 }}</td>
                            <td style="padding:12px 14px; font-weight:700">{{ $row->name }}</td>
                            <td style="padding:12px 14px; font-weight:600; color:var(--po-mu)">{{ $row->area?->name }}</td>
                            <td style="padding:12px 14px;">{{ $row->kitchenType?->name }}</td>
                            <td style="padding:12px 14px; text-align:right; font-weight:700; color:var(--po-bl)">
                                {{ __('catalog.kitchen.list.capacity_value', ['count' => number_format($row->capacity, 0, ',', '.')]) }}
                            </td>
                            <td style="padding:12px 14px; font-weight:600">{{ $row->manager?->name }}</td>
                            <td style="padding:12px 14px;">
                                @if($row->status === 'active')
                                    <span class="st-pill st-ok">{{ __('catalog.kitchen_status.active') }}</span>
                                @elseif($row->status === 'paused')
                                    <span class="st-pill st-late">{{ __('catalog.kitchen_status.paused') }}</span>
                                @else
                                    <span class="st-pill" style="background:var(--po-bd2); color:var(--po-su); border-color:var(--po-bd)">{{ __('catalog.kitchen_status.maintenance') }}</span>
                                @endif
                            </td>
                            <td style="padding:12px 14px; text-align:center">
                                <div style="display:inline-flex; gap:6px">
                                    <a href="{{ \App\Filament\Resources\KitchenResource::getUrl('edit', ['record' => $row->id]) }}" wire:navigate class="abt" title="{{ __('catalog.common.edit') }}">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>
                                    <button wire:click="deleteKitchen({{ $row->id }})" wire:confirm="{{ __('catalog.kitchen.list.confirm_delete') }}" class="abt" title="{{ __('catalog.common.delete') }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding:32px; text-align:center; color:var(--po-mu)">
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
</div>
