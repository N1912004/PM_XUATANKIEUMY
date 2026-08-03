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
                <i class="fa-solid fa-filter-circle-xmark"></i> {{ __('catalog.common.reset_filters') }}
            </button>
        </div>
        <div class="tw">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; background:var(--po-bd2)">
                        <th style="padding:12px 14px; width:70px; text-align:center">{{ __('catalog.common.index') }}</th>
                        <th style="padding:12px 14px; width:150px; text-align:center; white-space:nowrap">{{ __('catalog.shift.table.sort_order') }}</th>
                        <th style="padding:12px 14px; width:22%">{{ __('catalog.shift.list.columns.name') }}</th>
                        <th style="padding:12px 14px; width:28%">{{ __('catalog.shift.list.columns.time_range') }}</th>
                        <th style="padding:12px 14px; width:25%; white-space:nowrap">{{ __('catalog.common.created_at') }}</th>
                        <th style="padding:12px 14px; text-align:center; width:110px; white-space:nowrap">{{ __('catalog.common.actions_upper') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $index => $row)
                        <tr style="border-bottom:1px solid var(--po-bd2); color:var(--po-tx)" class="emp-row">
                            <td style="padding:12px 14px; text-align:center; font-weight:400; color:var(--po-mu)">
                                {{ ($shifts->firstItem() ?? 1) + $index }}
                            </td>
                            <td style="padding:12px 14px; text-align:center; font-weight:400; color:var(--po-tx); font-variant-numeric:tabular-nums">
                                {{ $row->sort_order }}
                            </td>
                            <td style="padding:12px 14px; font-weight:400; color:var(--po-tx)">
                                {{ $row->name }}
                            </td>
                            <td style="padding:12px 14px; font-variant-numeric:tabular-nums; font-weight:400">
                                {{ $row->time_range }}
                            </td>
                            <td style="padding:12px 14px; font-weight:400; color:var(--po-mu); font-variant-numeric:tabular-nums; white-space:nowrap">
                                {{ $row->created_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td style="padding:12px 14px; text-align:center">
                                <div style="display:inline-flex; gap:6px">
                                    <a href="{{ \App\Filament\Resources\ShiftResource::getUrl('edit', ['record' => $row->id]) }}" wire:navigate class="abt" title="{{ __('catalog.common.edit') }}">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>
                                    <button type="button" @click="askConfirm('deleteShift', {{ $row->id }}, @js(__('catalog.shift.delete_title')), @js(__('catalog.shift.list.confirm_delete')), @js(__('catalog.common.delete')))" class="abt" title="{{ __('catalog.common.delete') }}">
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
        @if($shifts->total() > 0)
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:14px;padding:14px 16px;border-top:1px solid var(--po-bd2);font-size:12px;color:var(--po-mu)">
                <div>{{ __('catalog.pagination.summary', ['from'=>$shifts->firstItem()??0,'to'=>$shifts->lastItem()??0,'total'=>$shifts->total(),'entity'=>__('catalog.shift.list.pagination_entity')]) }}</div>
                <div class="pgwrap"><span>{{ __('common.pagination.per_page_label') }}</span><select wire:model.live="shiftPerPage" class="lv-per-page-select">@foreach([5, 10, 20, 30, 50] as $count)<option value="{{ $count }}">{{ $count }}</option>@endforeach</select>
                @if($shifts->total() > 0)<nav style="display:flex;align-items:center;gap:4px">@if($shifts->onFirstPage())<span style="opacity:.4;padding:4px"><i class="fa-solid fa-chevron-left"></i></span>@else<button type="button" wire:click="previousPage('shiftsPage')" style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border:1px solid var(--po-bd);border-radius:6px;background:transparent"><i class="fa-solid fa-chevron-left"></i></button>@endif
                @php
                    $pageWindow = collect([1, $shifts->currentPage() - 1, $shifts->currentPage(), $shifts->currentPage() + 1, $shifts->lastPage()])
                        ->filter(fn ($page) => $page >= 1 && $page <= $shifts->lastPage())
                        ->unique()
                        ->sort()
                        ->values();
                @endphp
                @foreach($pageWindow as $i=>$page) @if($i>0&&$page-$pageWindow[$i-1]>1)<span style="padding:0 4px">…</span>@endif @if($page==$shifts->currentPage())<span style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border-radius:6px;background:var(--po-bl);color:#fff;font-weight:700">{{ $page }}</span>@else<button type="button" wire:click="gotoPage({{ $page }}, 'shiftsPage')" style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border:1px solid var(--po-bd);border-radius:6px;background:transparent">{{ $page }}</button>@endif @endforeach
                @if($shifts->hasMorePages())<button type="button" wire:click="nextPage('shiftsPage')" style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border:1px solid var(--po-bd);border-radius:6px;background:transparent"><i class="fa-solid fa-chevron-right"></i></button>@else<span style="opacity:.4;padding:4px"><i class="fa-solid fa-chevron-right"></i></span>@endif</nav>@endif</div></div>
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
