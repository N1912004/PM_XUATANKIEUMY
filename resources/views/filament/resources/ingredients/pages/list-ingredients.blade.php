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
        $ingredientsList = $this->ingredients();
        $fiBtn = 'sup-btn';

        $supplierSelectOptions = collect($this->supplierOptions())->map(fn ($name, $id) => [
            'value' => (string) $id,
            'label' => $name,
        ])->values()->all();

        $unitSelectOptions = collect($this->unitOptions())->map(fn ($name, $id) => [
            'value' => (string) $id,
            'label' => $name,
        ])->values()->all();

        $typeSelectOptions = collect($this->typeOptions())->map(fn ($name, $id) => [
            'value' => (string) $id,
            'label' => $name,
        ])->values()->all();
    @endphp

    <div class="sup-head">
        <div>
            <h1 class="sup-title">{{ __('ingredient.navigation.list_heading') }}</h1>
            <p class="sup-subtitle">{{ __('ingredient.navigation.subheading') }}</p>
        </div>
        <div class="sup-actions">
            <button
                type="button"
                wire:click="mountAction('import_excel')"
                class="{{ $fiBtn }}"
            >
                <i class="fa-solid fa-file-arrow-up" style="color:#1267E8"></i>
                <span class="fi-btn-label">{{ __('ingredient.actions.import') }}</span>
            </button>
            <button
                type="button"
                wire:click="exportExcel"
                wire:loading.attr="disabled"
                wire:target="exportExcel"
                class="{{ $fiBtn }}"
            >
                <i wire:loading.remove wire:target="exportExcel" class="fa-solid fa-file-excel" style="color:#059669"></i>
                <i wire:loading wire:target="exportExcel" class="fa-solid fa-spinner fa-spin"></i>
                <span class="fi-btn-label">{{ __('ingredient.actions.export') }}</span>
            </button>
            <a
                href="{{ \App\Filament\Resources\IngredientResource::getUrl('create') }}"
                class="{{ $fiBtn }} sup-btn-primary fi-btn-color-primary"
            >
                <svg class="fi-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span class="fi-btn-label">{{ __('ingredient.actions.create') }}</span>
            </a>
        </div>
    </div>

    <!-- KPIs Overview -->
    <div class="sup-kpis">
        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-blue">
                <i class="fa-solid fa-cubes" style="font-size:18px"></i>
            </div>
            <div class="sup-kpi-value">{{ number_format($statsData['total'], 0, ',', '.') }}</div>
            <div class="sup-kpi-label">Tổng nguyên liệu</div>
            <div class="sup-kpi-note"><i class="fa-solid fa-circle-info" style="font-size:11px"></i> Đang quản lý</div>
        </div>

        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-green">
                <i class="fa-solid fa-truck" style="font-size:18px"></i>
            </div>
            <div class="sup-kpi-value">{{ number_format($statsData['suppliers'], 0, ',', '.') }}</div>
            <div class="sup-kpi-label">Nhà cung cấp</div>
            <div class="sup-kpi-note"><i class="fa-solid fa-circle-info" style="font-size:11px"></i> Đang liên kết</div>
        </div>

        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-orange">
                <i class="fa-solid fa-scale-balanced" style="font-size:18px"></i>
            </div>
            <div class="sup-kpi-value">{{ number_format($statsData['units'], 0, ',', '.') }}</div>
            <div class="sup-kpi-label">Đơn vị tính</div>
            <div class="sup-kpi-note"><i class="fa-solid fa-circle-info" style="font-size:11px"></i> Kg, Gói, Chai...</div>
        </div>

        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-purple">
                <i class="fa-solid fa-tags" style="font-size:18px"></i>
            </div>
            <div class="sup-kpi-value">{{ number_format($statsData['types'], 0, ',', '.') }}</div>
            <div class="sup-kpi-label">Loại nguyên liệu</div>
            <div class="sup-kpi-note"><i class="fa-solid fa-circle-info" style="font-size:11px"></i> Động vật, thực vật...</div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="sup-card sup-table-card">
        <!-- Filter Bar matching Image 1 100% -->
        <div class="sup-toolbar">
            <div class="sup-search">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('ingredient.table.search_placeholder') }}"
                />
            </div>

            <div class="sup-filter" style="min-width:170px">
                <label>{{ __('ingredient.filter.supplier') }}</label>
                @include('filament.components.search-select', [
                    'name' => 'supplierFilter',
                    'live' => true,
                    'placeholder' => __('common.select.all'),
                    'nullable' => true,
                    'emptyLabel' => __('common.select.all'),
                    'options' => $supplierSelectOptions,
                ])
            </div>

            <div class="sup-filter" style="min-width:130px">
                <label>{{ __('ingredient.filter.unit') }}</label>
                @include('filament.components.search-select', [
                    'name' => 'unitFilter',
                    'live' => true,
                    'placeholder' => __('common.select.all'),
                    'nullable' => true,
                    'emptyLabel' => __('common.select.all'),
                    'options' => $unitSelectOptions,
                ])
            </div>

            <div class="sup-filter" style="min-width:140px">
                <label>{{ __('ingredient.filter.type') }}</label>
                @include('filament.components.search-select', [
                    'name' => 'typeFilter',
                    'live' => true,
                    'placeholder' => __('common.select.all'),
                    'nullable' => true,
                    'emptyLabel' => __('common.select.all'),
                    'options' => $typeSelectOptions,
                ])
            </div>

            <div class="sup-filter">
                <label>{{ __('ingredient.filter.trashed') }}</label>
                <select wire:model.live="trashedFilter" class="sup-select">
                    <option value="">{{ __('ingredient.filter.without_trashed') }}</option>
                    <option value="with">{{ __('ingredient.filter.with_trashed') }}</option>
                    <option value="only">{{ __('ingredient.filter.only_trashed') }}</option>
                </select>
            </div>

            <button
                type="button"
                wire:click="resetFilters"
                wire:loading.attr="disabled"
                wire:target="resetFilters"
                class="fbtn"
                style="align-self: flex-end; margin-bottom: 1px;"
            >
                <span style="width:16px; height:16px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i wire:loading.remove wire:target="resetFilters" class="fa-solid fa-filter-circle-xmark" style="font-size:14px; color:var(--sup-tx,#0f172a)"></i>
                    <i wire:loading wire:target="resetFilters" class="fa-solid fa-spinner fa-spin" style="font-size:14px"></i>
                </span>
                <span>{{ __('ingredient.filter.reset') }}</span>
            </button>
        </div>

        <div class="sup-table-wrap">
            <table class="sup-table">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--sup-bd2, #f1f5f9); color:var(--sup-mu, #64748b); font-weight:400; text-transform:uppercase; font-size:11px; background:var(--sup-bd2, #f1f5f9)">
                        <th style="padding:12px 14px; width:60px; text-align:center">{{ __('ingredient.table.index') }}</th>
                        <th style="padding:12px 14px; width:160px; text-align:left; white-space:nowrap">
                            <button type="button" wire:click="sortBy('code')" style="display:inline-flex; align-items:center; gap:4px; font-weight:400; text-transform:uppercase; background:none; border:none; padding:0; color:inherit; cursor:pointer">
                                <span>{{ __('ingredient.table.code') }}</span>
                                <svg style="width:12px; height:12px; opacity:0.65" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </th>
                        <th style="padding:12px 14px; width:22%; text-align:left">
                            <button type="button" wire:click="sortBy('name')" style="display:inline-flex; align-items:center; gap:4px; font-weight:400; text-transform:uppercase; background:none; border:none; padding:0; color:inherit; cursor:pointer">
                                <span>{{ __('ingredient.table.name') }}</span>
                                <svg style="width:12px; height:12px; opacity:0.65" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </th>
                        <th style="padding:12px 14px; width:25%; text-align:left">{{ __('ingredient.table.supplier') }}</th>
                        <th style="padding:12px 14px; width:100px; text-align:left">{{ __('ingredient.table.unit') }}</th>
                        <th style="padding:12px 14px; width:130px; text-align:left">{{ __('ingredient.table.type') }}</th>
                        <th style="padding:12px 14px; width:140px; text-align:center; white-space:nowrap">{{ __('ingredient.table.status') }}</th>
                        <th style="padding:12px 14px; width:120px; text-align:center; white-space:nowrap">{{ __('ingredient.table.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ingredientsList as $index => $ingredient)
                        <tr style="border-bottom:1px solid var(--sup-bd2, #f1f5f9); {{ $ingredient->trashed() ? 'opacity:0.65; background:#f8fafc;' : '' }}">
                            <td style="padding:12px 14px; text-align:center; font-weight:400; font-size:13px; color:var(--sup-tx, #0f172a); font-variant-numeric:tabular-nums">{{ ($ingredientsList->currentPage() - 1) * $ingredientsList->perPage() + $index + 1 }}</td>
                            <td style="padding:12px 14px; text-align:left; font-variant-numeric:tabular-nums; white-space:nowrap">
                                <a href="{{ \App\Filament\Resources\IngredientResource::getUrl('view', ['record' => $ingredient->id]) }}" style="font-weight:400; font-size:13px; color:var(--sup-tx, #0f172a); text-decoration:none;" class="hover:underline">
                                    {{ $ingredient->code }}
                                </a>
                            </td>
                            <td style="padding:12px 14px; text-align:left; font-weight:400; font-size:13px; color:var(--sup-tx, #0f172a)">
                                {{ $ingredient->name }}
                                @if($ingredient->trashed())
                                    <span class="spill s-qt" style="margin-left:6px;font-size:10px;font-weight:400">{{ __('ingredient.status.trashed') }}</span>
                                @endif
                            </td>
                            <td style="padding:12px 14px; text-align:left; font-weight:400; font-size:13px; color:var(--sup-tx, #0f172a)">
                                @php
                                    $supNames = $ingredient->suppliers->pluck('name')->toArray();
                                    $supTotal = count($supNames);
                                    if ($supTotal > 2) {
                                        $displaySups = implode(', ', array_slice($supNames, 0, 2)) . ', +' . ($supTotal - 2) . ' NCC';
                                    } else {
                                        $displaySups = implode(', ', $supNames);
                                    }
                                @endphp
                                {{ $displaySups ?: '—' }}
                            </td>
                            <td style="padding:12px 14px; text-align:left; font-weight:400; font-size:13px; color:var(--sup-tx, #0f172a)">{{ $ingredient->unitRelation?->name ?: ($ingredient->unit ?: '—') }}</td>
                            <td style="padding:12px 14px; text-align:left; font-weight:400; font-size:13px; color:var(--sup-tx, #0f172a)">{{ $ingredient->typeRelation?->name ?: ($ingredient->type ?: '—') }}</td>
                            <td style="padding:12px 14px; text-align:center">
                                @if($ingredient->status)
                                    <span class="spill s-ok">{{ __('ingredient.status.active') }}</span>
                                @else
                                    <span class="spill s-qt">{{ __('ingredient.status.inactive') }}</span>
                                @endif
                            </td>
                            <td style="padding:12px 14px; text-align:center">
                                <div style="display:inline-flex; gap:6px; justify-content:center">
                                    <a href="{{ \App\Filament\Resources\IngredientResource::getUrl('view', ['record' => $ingredient->id]) }}" class="abt" title="Xem">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @unless($ingredient->trashed())
                                        <a href="{{ \App\Filament\Resources\IngredientResource::getUrl('edit', ['record' => $ingredient->id]) }}" class="abt" title="Sửa">
                                            <i class="fa-solid fa-pencil"></i>
                                        </a>
                                        <button type="button" @click="askConfirm('deleteIngredient', {{ $ingredient->id }}, 'Xóa', 'Bạn có chắc chắn muốn xóa nguyên liệu này không?', 'Xóa')" class="abt" title="Xóa">
                                            <i class="fa-solid fa-trash" style="color:#dc2626"></i>
                                        </button>
                                    @else
                                        <button type="button" @click="askConfirm('restoreIngredient', {{ $ingredient->id }}, 'Khôi phục', 'Bạn có chắc chắn muốn khôi phục nguyên liệu này không?', 'Khôi phục', false)" class="abt" title="Khôi phục">
                                            <i class="fa-solid fa-rotate-left" style="color:#1267e8"></i>
                                        </button>
                                        <button type="button" @click="askConfirm('forceDeleteIngredient', {{ $ingredient->id }}, 'Xóa vĩnh viễn', 'Bạn có chắc chắn muốn xóa vĩnh viễn nguyên liệu này không?', 'Xóa vĩnh viễn')" class="abt" title="Xóa vĩnh viễn">
                                            <i class="fa-solid fa-trash-can" style="color:#dc2626"></i>
                                        </button>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:30px;color:var(--sup-mu)">
                                {{ __('ingredient.table.empty_heading') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ingredientsList->total() > 0)
            <div class="sup-footer">
                <div>
                    Hiển thị {{ $ingredientsList->firstItem() ?? 0 }} - {{ $ingredientsList->lastItem() ?? 0 }} trong tổng số {{ number_format($ingredientsList->total(), 0, ',', '.') }} nguyên liệu
                </div>
                <div class="sup-pagination">
                    <span>{{ __('common.pagination.per_page_label') }}</span>
                    <select wire:model.live="perPage" class="lv-per-page-select" style="min-width:68px; height:32px; padding:0 24px 0 10px; font-size:13px; font-weight:400; color:var(--sup-tx, #0f172a); background-color:var(--sup-wh, #ffffff); border:1px solid var(--sup-bd, #cbd5e1); border-radius:8px; outline:none; appearance:none; -webkit-appearance:none; background-image:url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-position:right 8px center; background-repeat:no-repeat; background-size:16px 16px; cursor:pointer;">
                        @foreach([5, 10, 20, 30, 50] as $count)
                            <option value="{{ $count }}">{{ $count }}</option>
                        @endforeach
                    </select>

                    @if($ingredientsList->total() > 0)
                        <nav role="navigation" aria-label="Pagination Navigation" style="display:flex; align-items:center; gap:4px;">
                            {{-- Previous Page Link --}}
                            @if ($ingredientsList->onFirstPage())
                                <span aria-disabled="true" style="display:inline-flex; align-items:center; justify-content:center; min-width:30px; height:30px; padding:0 6px; border:1px solid var(--sup-bd, #e2e8f0); border-radius:6px; background:transparent; color:var(--sup-mu, #94a3b8); opacity:.4; cursor:not-allowed;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="15 18 9 12 15 6"/></svg>
                                </span>
                            @else
                                <button type="button" wire:click="previousPage" class="sup-small-btn" rel="prev" style="display:inline-flex; align-items:center; justify-content:center; min-width:30px; height:30px; padding:0 6px; border:1px solid var(--sup-bd, #cbd5e1); border-radius:6px; background:var(--sup-wh, #fff); color:var(--sup-mu, #64748b); cursor:pointer;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="15 18 9 12 15 6"/></svg>
                                </button>
                            @endif

                            {{-- Pagination Elements --}}
                            @php
                                $ingCurrentPage = $ingredientsList->currentPage();
                                $ingLastPage = $ingredientsList->lastPage();
                                $ingPageWindow = collect([1, $ingCurrentPage - 1, $ingCurrentPage, $ingCurrentPage + 1, $ingLastPage])
                                    ->filter(fn ($p) => $p >= 1 && $p <= $ingLastPage)
                                    ->unique()
                                    ->sort()
                                    ->values();
                            @endphp
                            @foreach ($ingPageWindow as $i => $page)
                                @if ($i > 0 && $page - $ingPageWindow[$i - 1] > 1)
                                    <span aria-hidden="true" style="display:inline-flex; align-items:center; justify-content:center; min-width:20px; height:30px; padding:0 2px; color:var(--sup-mu, #94a3b8);">…</span>
                                @endif
                                @if ($page == $ingCurrentPage)
                                    <span aria-current="page" style="display:inline-flex; align-items:center; justify-content:center; min-width:30px; height:30px; border-radius:6px; background:var(--sup-bl, #2563eb); color:#ffffff; font-weight:700; font-size:13px;">{{ $page }}</span>
                                @else
                                    <button type="button" wire:click="gotoPage({{ $page }})" class="sup-small-btn" style="display:inline-flex; align-items:center; justify-content:center; min-width:30px; height:30px; padding:0 8px; border:1px solid var(--sup-bd, #cbd5e1); border-radius:6px; background:var(--sup-wh, #fff); color:var(--sup-tx, #0f172a); font-size:13px; font-weight:500; cursor:pointer;">{{ $page }}</button>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($ingredientsList->hasMorePages())
                                <button type="button" wire:click="nextPage" class="sup-small-btn" rel="next" style="display:inline-flex; align-items:center; justify-content:center; min-width:30px; height:30px; padding:0 6px; border:1px solid var(--sup-bd, #cbd5e1); border-radius:6px; background:var(--sup-wh, #fff); color:var(--sup-mu, #64748b); cursor:pointer;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="9 18 15 12 9 6"/></svg>
                                </button>
                            @else
                                <span aria-disabled="true" style="display:inline-flex; align-items:center; justify-content:center; min-width:30px; height:30px; padding:0 6px; border:1px solid var(--sup-bd, #e2e8f0); border-radius:6px; background:transparent; color:var(--sup-mu, #94a3b8); opacity:.4; cursor:not-allowed;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="9 18 15 12 9 6"/></svg>
                                </span>
                            @endif
                        </nav>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Hộp thoại xác nhận trong trang (teleport ra body từ commit e8fa7cb) --}}
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

    <x-filament-actions::modals />
</div>
