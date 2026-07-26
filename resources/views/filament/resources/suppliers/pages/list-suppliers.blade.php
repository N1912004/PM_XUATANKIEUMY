<div class="sup-page bf-list-page w-full">
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
                class="{{ $fiBtn }}"
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
                    <tr>
                        <th style="width:56px;text-align:center">STT</th>
                        <th style="width:110px">{{ __('supplier.table.code') }}</th>
                        <th>{{ __('supplier.table.name_short') }}</th>
                        <th style="width:130px">{{ __('supplier.table.phone') }}</th>
                        <th>Email</th>
                        <th style="width:180px">{{ __('supplier.table.food_types') }}</th>
                        <th style="width:130px;text-align:center">{{ __('supplier.table.ingredient_count') }}</th>
                        <th style="width:140px">{{ __('supplier.table.status') }}</th>
                        <th style="text-align:center;width:120px">{{ __('supplier.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliersList as $index => $supplier)
                        <tr>
                            <td style="text-align:center" class="sup-muted">{{ ($suppliersList->currentPage() - 1) * $suppliersList->perPage() + $index + 1 }}</td>
                            <td><span style="font-size:12px;font-weight:600;color:var(--sup-mu)">{{ $supplier->code }}</span></td>
                            <td class="sup-name">{{ $supplier->name }}</td>
                            <td>{{ $supplier->phone }}</td>
                            <td>{{ $supplier->email ?: '--' }}</td>
                            <td>{{ $supplier->type }}</td>
                            <td class="sup-link-num">{{ $supplier->ingredients_count }}</td>
                            <td>
                                @if($supplier->status)
                                    <span class="spill s-ok">{{ __('supplier.status.active') }}</span>
                                @else
                                    <span class="spill s-qt">{{ __('supplier.status.locked') }}</span>
                                @endif
                            </td>
                            <td style="text-align:center">
                                <div class="sup-row-actions" style="justify-content:center">
                                    <a href="{{ \App\Filament\Resources\SupplierResource::getUrl('view', ['record' => $supplier->id]) }}" class="sup-row-action" title="Xem" style="color: var(--sup-mu)">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>
                                    <a href="{{ \App\Filament\Resources\SupplierResource::getUrl('edit', ['record' => $supplier->id]) }}" class="sup-row-action" title="{{ __('supplier.actions.edit') }}" style="color: var(--sup-mu)">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    <button wire:click="deleteSupplier({{ $supplier->id }})" wire:confirm="{{ __('supplier.confirm.delete') }}" class="sup-row-action sup-row-danger" title="{{ __('supplier.actions.delete') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
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

        @if($suppliersList->hasPages())
            <div class="sup-footer">
                <div>
                    {{ __('supplier.pagination.summary', ['from' => $suppliersList->firstItem(), 'to' => $suppliersList->lastItem(), 'total' => $suppliersList->total()]) }}
                </div>
                <div class="sup-pagination">
                    <select wire:model.live="perPage" class="sup-select" style="min-width:7rem;height:2rem;padding:0 .5rem;border-radius:.5rem">
                        <option value="10">10 / trang</option>
                        <option value="20">20 / trang</option>
                        <option value="50">50 / trang</option>
                    </select>

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

                        {{-- Pagination Elements (dạng cửa sổ: 1 … n-1 n n+1 … cuối) --}}
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
                                <span aria-current="page">
                                    <span>{{ $page }}</span>
                                </span>
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
                </div>
            </div>
        @endif
    </div>
</div>
