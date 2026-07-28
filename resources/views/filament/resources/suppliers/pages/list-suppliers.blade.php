<div class="sup-page bf-list-page w-full"
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
    @include('filament.resources.suppliers.partials.styles')
    @php
        $statsData = $this->stats();

        $suppliersList = $this->suppliers();

        $fiBtn = 'sup-btn';
    @endphp

    <div class="sup-head">
        <div>
            <h1 class="sup-title">{{ __('supplier.list.title') }}</h1>
            <p class="sup-subtitle">{{ __('supplier.list.subtitle') }}</p>
        </div>
        <div class="sup-actions">
            <button
                type="button"
                wire:click="exportExcel"
                wire:loading.attr="disabled"
                wire:target="exportExcel"
                class="{{ $fiBtn }} fi-btn-color-success"
            >
                <i wire:loading.remove wire:target="exportExcel" class="fa-solid fa-file-excel" style="color:#059669"></i>
                <i wire:loading wire:target="exportExcel" class="fa-solid fa-spinner fa-spin"></i>
                <span class="fi-btn-label">{{ __('supplier.actions.export_excel') }}</span>
            </button>
            <a
                href="{{ \App\Filament\Resources\SupplierResource::getUrl('create') }}"
                class="{{ $fiBtn }} sup-btn-primary fi-btn-color-primary"
            >
                <svg class="fi-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span class="fi-btn-label">{{ __('supplier.actions.create') }}</span>
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="sup-kpis">
        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-blue">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="3" width="15" height="13" rx="2" ry="2"/>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                    <circle cx="5.5" cy="18.5" r="2.5"/>
                    <circle cx="18.5" cy="18.5" r="2.5"/>
                </svg>
            </div>
            <div class="sup-kpi-value">{{ $statsData['suppliers'] }}</div>
            <div class="sup-kpi-label">{{ __('supplier.kpi.total') }}</div>
            <div class="sup-kpi-note">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                {{ __('supplier.kpi.managed') }}
            </div>
        </div>

        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                </svg>
            </div>
            <div class="sup-kpi-value">{{ $statsData['types'] }}</div>
            <div class="sup-kpi-label">{{ __('supplier.kpi.food_types') }}</div>
            <div class="sup-kpi-note">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                {{ __('supplier.kpi.by_supply_group') }}
            </div>
        </div>

        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-green">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>
            <div class="sup-kpi-value">{{ $statsData['ingredients'] }}</div>
            <div class="sup-kpi-label">{{ __('supplier.kpi.linked_ingredients') }}</div>
            <div class="sup-kpi-note">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                {{ __('supplier.kpi.supplier_ingredient') }}
            </div>
        </div>

        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-purple">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="sup-kpi-value">{{ $statsData['quotes'] }}</div>
            <div class="sup-kpi-label">{{ __('supplier.kpi.quoted_prices') }}</div>
            <div class="sup-kpi-note">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                {{ __('supplier.kpi.by_ingredient') }}
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="sup-card sup-table-card">
        <div class="sup-toolbar">
            <div class="sup-search">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('supplier.placeholders.search') }}">
            </div>

            <div class="sup-filter" style="min-width:14rem">
                <label id="sup-type-label">{{ __('supplier.filters.food_types') }}</label>
                {{-- Combobox thuần Alpine: Choices.js không có trong dự án và Filament
                     không expose nó ra global, nên tự dựng để vừa tìm vừa chọn. --}}
                <div
                    class="sup-combo"
                    x-data="{
                        open: false,
                        search: '',
                        selected: @entangle('typeFilter').live,
                        options: @js(array_values($this->typeOptions())),
                        get filtered() {
                            const q = this.search.trim().toLowerCase();
                            return q === '' ? this.options : this.options.filter(o => o.toLowerCase().includes(q));
                        },
                        get label() {
                            if (! this.selected.length) return @js(__('supplier.filters.all'));
                            if (this.selected.length <= 2) return this.selected.join(', ');
                            return @js(__('supplier.filters.selected_types', ['count' => '__COUNT__'])).replace('__COUNT__', this.selected.length);
                        },
                        isChecked(option) {
                            return this.selected.includes(option);
                        },
                        toggleOption(option) {
                            this.isChecked(option)
                                ? this.selected = this.selected.filter(o => o !== option)
                                : this.selected = [...this.selected, option];
                        },
                        toggle() {
                            this.open = ! this.open;
                            if (this.open) {
                                this.search = '';
                                this.$nextTick(() => this.$refs.search?.focus());
                            }
                        },
                    }"
                    @click.outside="open = false"
                    @keydown.escape.stop="open = false"
                >
                    <button
                        type="button"
                        class="sup-select sup-combo-toggle"
                        aria-haspopup="listbox"
                        :aria-expanded="open"
                        aria-labelledby="sup-type-label"
                        @click="toggle()"
                    >
                        <span :class="! selected.length && 'sup-combo-placeholder'" x-text="label"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="sup-combo-caret">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>

                    <div x-show="open" x-cloak x-transition.opacity.duration.100ms class="sup-combo-panel">
                        <input
                            x-ref="search"
                            x-model="search"
                            type="text"
                            class="sup-combo-search"
                            placeholder="{{ __('supplier.placeholders.search_type') }}"
                            @keydown.enter.prevent="filtered.length && toggleOption(filtered[0])"
                        >

                        <ul class="sup-combo-list" role="listbox" aria-multiselectable="true">
                            <li>
                                <button type="button" class="sup-combo-option sup-combo-clear" @click="selected = []">
                                    {{ __('supplier.actions.clear_all') }}
                                </button>
                            </li>
                            <template x-for="option in filtered" :key="option">
                                <li>
                                    <button
                                        type="button"
                                        class="sup-combo-option sup-combo-check"
                                        :class="isChecked(option) && 'sup-combo-option-active'"
                                        role="option"
                                        :aria-selected="isChecked(option)"
                                        @click="toggleOption(option)"
                                    >
                                        <span class="sup-combo-box" :class="isChecked(option) && 'sup-combo-box-on'">
                                            <svg x-show="isChecked(option)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        </span>
                                        <span x-text="option"></span>
                                    </button>
                                </li>
                            </template>
                            <li x-show="filtered.length === 0" class="sup-combo-empty">{{ __('supplier.empty.no_matching_type') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="sup-filter">
                <label>{{ __('supplier.fields.status') }}</label>
                <select wire:model.live="statusFilter" class="sup-select">
                    <option value="">{{ __('supplier.filters.all') }}</option>
                    <option value="active">{{ __('supplier.status.active') }}</option>
                    <option value="inactive">{{ __('supplier.status.locked') }}</option>
                </select>
            </div>

            <div class="sup-spacer"></div>

            @if($search !== '' || $typeFilter !== [] || $statusFilter !== '')
                <button
                    type="button"
                    wire:click="resetFilters"
                    class="{{ $fiBtn }} sup-btn-danger"
                >
                    <svg class="fi-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                    <span class="fi-btn-label">{{ __('supplier.actions.clear_filters') }}</span>
                </button>
            @endif
        </div>

        <div class="sup-table-wrap">
            <table class="sup-table">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--sup-bd2, #f1f5f9); color:var(--sup-mu, #64748b); font-weight:700; text-transform:uppercase; font-size:11px; background:var(--sup-bd2, #f1f5f9)">
                        <th style="padding:12px 14px; width:70px; text-align:center">STT</th>
                        <th style="padding:12px 14px; width:130px; text-align:center; white-space:nowrap">{{ __('supplier.table.code') }}</th>
                        <th style="padding:12px 14px; width:22%">{{ __('supplier.table.name_short') }}</th>
                        <th style="padding:12px 14px; width:130px">{{ __('supplier.table.phone') }}</th>
                        <th style="padding:12px 14px; width:18%">Email</th>
                        <th style="padding:12px 14px; width:16%">{{ __('supplier.table.food_types') }}</th>
                        <th style="padding:12px 14px; width:130px; text-align:center; white-space:nowrap">{{ __('supplier.table.ingredient_count') }}</th>
                        <th style="padding:12px 14px; width:140px; text-align:center; white-space:nowrap">{{ __('supplier.table.status') }}</th>
                        <th style="padding:12px 14px; width:110px; text-align:center; white-space:nowrap">{{ __('supplier.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliersList as $index => $supplier)
                        <tr style="border-bottom:1px solid var(--sup-bd2, #f1f5f9)">
                            <td style="padding:12px 14px; text-align:center; font-weight:600; color:var(--sup-mu, #64748b); font-variant-numeric:tabular-nums">{{ ($suppliersList->currentPage() - 1) * $suppliersList->perPage() + $index + 1 }}</td>
                            <td style="padding:12px 14px; text-align:center; font-weight:800; color:var(--po-bl, #1267e8); font-variant-numeric:tabular-nums; white-space:nowrap">{{ $supplier->code }}</td>
                            <td style="padding:12px 14px; font-weight:700; color:var(--sup-tx, #0f172a)">{{ $supplier->name }}</td>
                            <td style="padding:12px 14px; font-variant-numeric:tabular-nums">{{ $supplier->phone }}</td>
                            <td style="padding:12px 14px; color:var(--sup-mu, #64748b)">{{ $supplier->email ?: '—' }}</td>
                            <td style="padding:12px 14px; font-weight:600">{{ $supplier->type ?: '—' }}</td>
                            <td style="padding:12px 14px; text-align:center; font-weight:800; color:var(--po-bl, #1267e8); font-variant-numeric:tabular-nums">{{ $supplier->ingredients_count }}</td>
                            <td style="padding:12px 14px; text-align:center">
                                @if($supplier->status)
                                    <span class="spill s-ok">{{ __('supplier.status.active') }}</span>
                                @else
                                    <span class="spill s-qt">{{ __('supplier.status.locked') }}</span>
                                @endif
                            </td>
                            <td style="padding:12px 14px; text-align:center">
                                <div style="display:inline-flex; gap:6px; justify-content:center">
                                    <a href="{{ \App\Filament\Resources\SupplierResource::getUrl('view', ['record' => $supplier->id]) }}" class="abt" title="{{ __('supplier.actions.view') }}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ \App\Filament\Resources\SupplierResource::getUrl('edit', ['record' => $supplier->id]) }}" class="abt" title="{{ __('supplier.actions.edit') }}">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>
                                    <button type="button" @click="askConfirm('deleteSupplier', {{ $supplier->id }}, @js(__('supplier.actions.delete')), @js(__('supplier.confirm.delete')), @js(__('supplier.actions.delete')))" class="abt" title="{{ __('supplier.actions.delete') }}">
                                        <i class="fa-solid fa-trash" style="color:#dc2626"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:30px;color:var(--sup-mu)">
                                {{ __('supplier.empty.no_suppliers') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliersList->total() > 0)
            <div class="sup-footer">
                <div>
                    {{ __('supplier.pagination.summary', ['from' => $suppliersList->firstItem() ?? 0, 'to' => $suppliersList->lastItem() ?? 0, 'total' => number_format($suppliersList->total(), 0, ',', '.')]) }}
                </div>
                <div class="sup-pagination">
                    <select wire:model.live="perPage" class="sup-select">
                        @foreach([5, 10, 20, 50] as $count)
                            <option value="{{ $count }}">{{ __('supplier.pagination.per_page', ['count' => $count]) }}</option>
                        @endforeach
                    </select>

                    @if($suppliersList->total() > 0)
                        <nav role="navigation" aria-label="Pagination Navigation">
                            {{-- Previous Page Link --}}
                            @if ($suppliersList->onFirstPage())
                                <span aria-disabled="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="15 18 9 12 15 6"/></svg>
                                </span>
                            @else
                                <button type="button" wire:click="previousPage" class="sup-small-btn" rel="prev">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="15 18 9 12 15 6"/></svg>
                                </button>
                            @endif

                            {{-- Pagination Elements --}}
                            @php
                                $supCurrentPage = $suppliersList->currentPage();
                                $supLastPage = $suppliersList->lastPage();
                                $supPageWindow = collect([1, $supCurrentPage - 1, $supCurrentPage, $supCurrentPage + 1, $supLastPage])
                                    ->filter(fn ($p) => $p >= 1 && $p <= $supLastPage)
                                    ->unique()
                                    ->sort()
                                    ->values();
                            @endphp
                            @foreach ($supPageWindow as $i => $page)
                                @if ($i > 0 && $page - $supPageWindow[$i - 1] > 1)
                                    <span aria-hidden="true" style="padding:0 4px">…</span>
                                @endif
                                @if ($page == $supCurrentPage)
                                    <span aria-current="page">{{ $page }}</span>
                                @else
                                    <button type="button" wire:click="gotoPage({{ $page }})" class="sup-small-btn">{{ $page }}</button>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($suppliersList->hasMorePages())
                                <button type="button" wire:click="nextPage" class="sup-small-btn" rel="next">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="9 18 15 12 9 6"/></svg>
                                </button>
                            @else
                                <span aria-disabled="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="9 18 15 12 9 6"/></svg>
                                </span>
                            @endif
                        </nav>
                    @endif
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
