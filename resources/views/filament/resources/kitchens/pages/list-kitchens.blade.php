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
                        <th style="padding:12px 14px; width:70px; text-align:center">STT</th>
                        <th style="padding:12px 14px; width:24%">{{ __('catalog.kitchen.list.columns.kitchen') }}</th>
                        <th style="padding:12px 14px; width:18%">{{ __('catalog.kitchen.list.columns.area') }}</th>
                        <th style="padding:12px 14px; width:16%">{{ __('catalog.kitchen.list.columns.type') }}</th>
                        <th style="padding:12px 14px; text-align:center; width:160px; white-space:nowrap">{{ __('catalog.kitchen.list.columns.capacity') }}</th>
                        <th style="padding:12px 14px; width:18%">{{ __('catalog.kitchen.list.columns.manager') }}</th>
                        <th style="padding:12px 14px; text-align:center; width:140px; white-space:nowrap">{{ __('catalog.common.status') }}</th>
                        <th style="padding:12px 14px; text-align:center; width:110px; white-space:nowrap">{{ __('catalog.common.actions_upper') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kitchens as $index => $row)
                        <tr style="border-bottom:1px solid var(--po-bd2); color:var(--po-tx)" class="emp-row">
                            <td style="padding:12px 14px; text-align:center; font-weight:600; color:var(--po-mu); font-variant-numeric:tabular-nums">{{ ($kitchens->currentPage() - 1) * $kitchens->perPage() + $index + 1 }}</td>
                            <td style="padding:12px 14px; font-weight:700">{{ $row->name }}</td>
                            <td style="padding:12px 14px; font-weight:600">{{ $row->area?->name ?: '—' }}</td>
                            <td style="padding:12px 14px; font-weight:600">{{ $row->kitchenType?->name ?: ($row->type ?: '—') }}</td>
                            <td style="padding:12px 14px; text-align:center; font-weight:800; color:var(--po-bl); font-variant-numeric:tabular-nums">
                                {{ __('catalog.kitchen.list.capacity_value', ['count' => number_format((float)$row->capacity, 0, ',', '.')]) }}
                            </td>
                            <td style="padding:12px 14px; font-weight:600">{{ $row->manager?->name ?: '—' }}</td>
                            <td style="padding:12px 14px; text-align:center">
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
                                    <button type="button" @click="askConfirm('deleteKitchen', {{ $row->id }}, @js(__('catalog.kitchen.delete_title')), @js(__('catalog.kitchen.list.confirm_delete')), @js(__('catalog.common.delete')))" class="abt" title="{{ __('catalog.common.delete') }}">
                                        <i class="fa-solid fa-trash" style="color:var(--po-rd)"></i>
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
        @if($kitchens->total() > 0)
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-top:14px; padding:14px 16px; border-top:1px solid var(--po-bd2); font-size:12px; color:var(--po-mu)">
                <div>{{ __('catalog.pagination.summary', ['from' => $kitchens->firstItem() ?? 0, 'to' => $kitchens->lastItem() ?? 0, 'total' => $kitchens->total(), 'entity' => __('catalog.kitchen.list.pagination_entity')]) }}</div>
                <div class="pgwrap" style="display:flex;align-items:center;gap:8px">
                    <span>{{ __('common.pagination.per_page_label') }}</span>
                    <select wire:model.live="kitchenPerPage" class="lv-per-page-select" style="min-width:68px; height:32px; padding:0 24px 0 10px; font-size:13px; font-weight:400; color:var(--po-tx, #0f172a); background-color:var(--po-wh, #ffffff); border:1px solid var(--po-bd, #cbd5e1); border-radius:8px; outline:none; appearance:none; -webkit-appearance:none; background-image:url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%2364748b\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-position:right 8px center; background-repeat:no-repeat; background-size:16px 16px; cursor:pointer;">
                        @foreach([5, 10, 20, 30, 50] as $count)
                            <option value="{{ $count }}">{{ $count }}</option>
                        @endforeach
                    </select>
                    @include('filament.components.pagination-page-numbers', ['paginator' => $kitchens, 'pageName' => 'kitchensPage'])
                </div>
            </div>
        @endif
    </div>

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
