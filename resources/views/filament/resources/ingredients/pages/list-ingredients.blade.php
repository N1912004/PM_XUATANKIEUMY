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
    @endphp

    <div class="sup-head">
        <div>
            <h1 class="sup-title">{{ __('ingredient.navigation.list_heading') }}</h1>
            <p class="sup-subtitle">{{ __('ingredient.navigation.subheading') }}</p>
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

    <!-- KPIs -->
    <div class="sup-kpis">
        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-blue">
                <i class="fa-solid fa-cubes" style="font-size:18px"></i>
            </div>
            <div class="sup-kpi-value">{{ number_format($statsData['total'], 0, ',', '.') }}</div>
            <div class="sup-kpi-label">Tổng số nguyên liệu</div>
        </div>

        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-green">
                <i class="fa-solid fa-tags" style="font-size:18px"></i>
            </div>
            <div class="sup-kpi-value">{{ number_format($statsData['active'], 0, ',', '.') }}</div>
            <div class="sup-kpi-label">Nguyên liệu có báo giá</div>
        </div>

        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-orange">
                <i class="fa-solid fa-layer-group" style="font-size:18px"></i>
            </div>
            <div class="sup-kpi-value">{{ number_format($statsData['types'], 0, ',', '.') }}</div>
            <div class="sup-kpi-label">Loại nguyên liệu</div>
        </div>

        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-purple">
                <i class="fa-solid fa-truck" style="font-size:18px"></i>
            </div>
            <div class="sup-kpi-value">{{ number_format($statsData['suppliers'], 0, ',', '.') }}</div>
            <div class="sup-kpi-label">Nhà cung cấp liên kết</div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="sup-card sup-table-card">
        <!-- Filter Bar -->
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

            <select wire:model.live="typeFilter" class="sup-select" style="height:38px">
                <option value="">-- {{ __('ingredient.filter.type') }} --</option>
                @foreach($this->typeOptions() as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>

            <select wire:model.live="trashedFilter" class="sup-select" style="height:38px">
                <option value="">Trạng thái xóa: Mặc định</option>
                <option value="with">Tất cả (Bao gồm đã xóa)</option>
                <option value="only">Chỉ nguyên liệu đã xóa</option>
            </select>

            <div class="sup-spacer"></div>

            @if($search !== '' || $typeFilter !== '' || $trashedFilter !== '')
                <button
                    type="button"
                    wire:click="resetFilters"
                    class="{{ $fiBtn }} sup-btn-danger"
                >
                    <svg class="fi-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                    <span class="fi-btn-label">Bỏ lọc</span>
                </button>
            @endif
        </div>

        <div class="sup-table-wrap">
            <table class="sup-table">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--sup-bd2, #f1f5f9); color:var(--sup-mu, #64748b); font-weight:700; text-transform:uppercase; font-size:11px; background:var(--sup-bd2, #f1f5f9)">
                        <th style="padding:12px 14px; width:70px; text-align:center">STT</th>
                        <th style="padding:12px 14px; width:130px; text-align:center; white-space:nowrap">{{ __('ingredient.table.code') }}</th>
                        <th style="padding:12px 14px; width:25%">{{ __('ingredient.table.name') }}</th>
                        <th style="padding:12px 14px; width:18%">{{ __('ingredient.table.type') }}</th>
                        <th style="padding:12px 14px; width:100px; text-align:center">{{ __('ingredient.table.unit') }}</th>
                        <th style="padding:12px 14px; width:130px; text-align:right">{{ __('ingredient.table.reference_price') }}</th>
                        <th style="padding:12px 14px; width:20%">{{ __('ingredient.table.supplier') }}</th>
                        <th style="padding:12px 14px; width:120px; text-align:center; white-space:nowrap">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ingredientsList as $index => $ingredient)
                        <tr style="border-bottom:1px solid var(--sup-bd2, #f1f5f9); {{ $ingredient->trashed() ? 'opacity:0.65; background:#f8fafc;' : '' }}">
                            <td style="padding:12px 14px; text-align:center; font-weight:600; color:var(--sup-mu, #64748b); font-variant-numeric:tabular-nums">{{ ($ingredientsList->currentPage() - 1) * $ingredientsList->perPage() + $index + 1 }}</td>
                            <td style="padding:12px 14px; text-align:center; font-weight:800; color:var(--po-bl, #1267e8); font-variant-numeric:tabular-nums; white-space:nowrap">{{ $ingredient->code }}</td>
                            <td style="padding:12px 14px; font-weight:700; color:var(--sup-tx, #0f172a)">
                                {{ $ingredient->name }}
                                @if($ingredient->trashed())
                                    <span class="spill s-qt" style="margin-left:6px;font-size:10px">Đã xóa</span>
                                @endif
                            </td>
                            <td style="padding:12px 14px; font-weight:600">{{ $ingredient->typeRelation?->name ?: ($ingredient->type ?: '—') }}</td>
                            <td style="padding:12px 14px; text-align:center">{{ $ingredient->unitRelation?->name ?: ($ingredient->unit ?: '—') }}</td>
                            <td style="padding:12px 14px; text-align:right; font-weight:700; font-variant-numeric:tabular-nums; color:#059669">
                                {{ number_format($ingredient->reference_price, 0, ',', '.') }} đ
                            </td>
                            <td style="padding:12px 14px; color:var(--sup-mu, #64748b)">{{ $ingredient->supplier?->name ?: '—' }}</td>
                            <td style="padding:12px 14px; text-align:center">
                                <div style="display:inline-flex; gap:6px; justify-content:center">
                                    <a href="{{ \App\Filament\Resources\IngredientResource::getUrl('view', ['record' => $ingredient->id]) }}" class="abt" title="Xem">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @unless($ingredient->trashed())
                                        <a href="{{ \App\Filament\Resources\IngredientResource::getUrl('edit', ['record' => $ingredient->id]) }}" class="abt" title="Sửa">
                                            <i class="fa-solid fa-pencil"></i>
                                        </a>
                                        <button type="button" @click="askConfirm('deleteIngredient', {{ $ingredient->id }}, 'Xóa', 'Bạn có chắc chắn muốn xóa nguyên liệu này?', 'Xóa')" class="abt" title="Xóa">
                                            <i class="fa-solid fa-trash" style="color:#dc2626"></i>
                                        </button>
                                    @else
                                        <button type="button" @click="askConfirm('restoreIngredient', {{ $ingredient->id }}, 'Khôi phục', 'Bạn có chắc chắn muốn khôi phục nguyên liệu này?', 'Khôi phục', false)" class="abt" title="Khôi phục">
                                            <i class="fa-solid fa-rotate-left" style="color:#1267e8"></i>
                                        </button>
                                        <button type="button" @click="askConfirm('forceDeleteIngredient', {{ $ingredient->id }}, 'Xóa vĩnh viễn', 'Bạn có chắc chắn muốn xóa vĩnh viễn nguyên liệu này?', 'Xóa vĩnh viễn')" class="abt" title="Xóa vĩnh viễn">
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
                    <select wire:model.live="perPage" class="lv-per-page-select">
                        @foreach([5, 10, 20, 50] as $count)
                            <option value="{{ $count }}">{{ $count }}</option>
                        @endforeach
                    </select>

                    @if($ingredientsList->total() > 0)
                        <nav role="navigation" aria-label="Pagination Navigation">
                            {{-- Previous Page Link --}}
                            @if ($ingredientsList->onFirstPage())
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
                                    <span aria-hidden="true" style="padding:0 4px">…</span>
                                @endif
                                @if ($page == $ingCurrentPage)
                                    <span aria-current="page">{{ $page }}</span>
                                @else
                                    <button type="button" wire:click="gotoPage({{ $page }})" class="sup-small-btn">{{ $page }}</button>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($ingredientsList->hasMorePages())
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
