{{--
    Hộp xác nhận dùng modal trong trang (Alpine) thay cho wire:confirm.
    Lý do: confirm() gốc của trình duyệt bị Chrome chặn sau vài lần bấm
    ("Ngăn trang này tạo thêm hộp thoại") khiến nút xóa như mất tác dụng.
--}}
<div class="recipe-page bf-list-page w-full"
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
    @include('filament.resources.recipes.partials.styles')

    @php
        $recipesList = $this->recipes();
        $types = $this->typeOptions();
        $pageIds = $recipesList->pluck('id')->toArray();
    @endphp

    <!-- Page Head -->
    <div class="ph">
        <div class="ph-l">
            <h1>{{ __('recipe.list.title') }}</h1>
            <p>{{ __('recipe.list.subtitle') }}</p>
        </div>
        <div class="ph-r">
            {{-- Import/Export cùng pattern trang Nguyên liệu: wizard 2 bước có XEM TRƯỚC (dry-run) --}}
            {{ $this->importAction }}
            {{ $this->exportAction }}
            {{ $this->createAction }}
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="mn-bar">
        <div class="mn-srch">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input wire:model.live.debounce.250ms="search" type="text" placeholder="{{ __('recipe.placeholders.search_name_code') }}">
        </div>

        {{-- Filter giá bán cho khách (Từ - Đến) --}}
        <div class="mn-range-grp" title="{{ __('recipe.filters.selling_price') }}">
            <span class="mn-range-label">{{ __('recipe.filters.selling_price') }}:</span>
            <input
                x-data="{
                    val: @entangle('sellingPriceFrom').live,
                    format(v) {
                        if (v === null || v === undefined || v === '') return '';
                        let n = v.toString().replace(/\D/g, '');
                        return n ? new Intl.NumberFormat('vi-VN').format(n) : '';
                    },
                    onInput(e) {
                        let digits = e.target.value.replace(/\D/g, '');
                        this.val = digits;
                        e.target.value = this.format(digits);
                    }
                }"
                x-effect="$el.value = format(val)"
                @input="onInput($event)"
                type="text"
                placeholder="{{ __('recipe.filters.from') }}"
                class="mn-num-input"
            >
            <span class="mn-range-sep">-</span>
            <input
                x-data="{
                    val: @entangle('sellingPriceTo').live,
                    format(v) {
                        if (v === null || v === undefined || v === '') return '';
                        let n = v.toString().replace(/\D/g, '');
                        return n ? new Intl.NumberFormat('vi-VN').format(n) : '';
                    },
                    onInput(e) {
                        let digits = e.target.value.replace(/\D/g, '');
                        this.val = digits;
                        e.target.value = this.format(digits);
                    }
                }"
                x-effect="$el.value = format(val)"
                @input="onInput($event)"
                type="text"
                placeholder="{{ __('recipe.filters.to') }}"
                class="mn-num-input"
            >
        </div>

        {{-- Filter giá cost chuẩn (Từ - Đến) --}}
        <div class="mn-range-grp" title="{{ __('recipe.filters.cost_price') }}">
            <span class="mn-range-label">{{ __('recipe.filters.cost_price') }}:</span>
            <input
                x-data="{
                    val: @entangle('costPriceFrom').live,
                    format(v) {
                        if (v === null || v === undefined || v === '') return '';
                        let n = v.toString().replace(/\D/g, '');
                        return n ? new Intl.NumberFormat('vi-VN').format(n) : '';
                    },
                    onInput(e) {
                        let digits = e.target.value.replace(/\D/g, '');
                        this.val = digits;
                        e.target.value = this.format(digits);
                    }
                }"
                x-effect="$el.value = format(val)"
                @input="onInput($event)"
                type="text"
                placeholder="{{ __('recipe.filters.from') }}"
                class="mn-num-input"
            >
            <span class="mn-range-sep">-</span>
            <input
                x-data="{
                    val: @entangle('costPriceTo').live,
                    format(v) {
                        if (v === null || v === undefined || v === '') return '';
                        let n = v.toString().replace(/\D/g, '');
                        return n ? new Intl.NumberFormat('vi-VN').format(n) : '';
                    },
                    onInput(e) {
                        let digits = e.target.value.replace(/\D/g, '');
                        this.val = digits;
                        e.target.value = this.format(digits);
                    }
                }"
                x-effect="$el.value = format(val)"
                @input="onInput($event)"
                type="text"
                placeholder="{{ __('recipe.filters.to') }}"
                class="mn-num-input"
            >
        </div>

        <div style="min-width: 170px;">
            @include('filament.components.search-select', [
                'name' => 'typeFilter',
                'live' => true,
                'placeholder' => __('recipe.filters.all_groups'),
                'searchPlaceholder' => __('recipe.placeholders.search_short'),
                'options' => collect($this->typeOptions())->map(fn($lbl, $val) => ['value' => (string)$val, 'label' => (string)$lbl])->values()->all(),
            ])
        </div>

        <div style="min-width: 150px;">
            @include('filament.components.search-select', [
                'name' => 'statusFilter',
                'live' => true,
                'placeholder' => __('recipe.fields.status'),
                'searchPlaceholder' => __('common.select.search_placeholder'),
                'emptyLabel' => __('recipe.fields.status'),
                'options' => [
                    ['value' => 'active', 'label' => __('recipe.status.active_applied')],
                    ['value' => 'pending', 'label' => __('recipe.status.pending')],
                    ['value' => 'inactive', 'label' => __('recipe.status.inactive')],
                ],
            ])
        </div>

        <div style="min-width: 150px;">
            @include('filament.components.search-select', [
                'name' => 'trashedFilter',
                'live' => true,
                'placeholder' => __('recipe.trash.without'),
                'searchPlaceholder' => __('common.select.search_placeholder'),
                'emptyLabel' => __('recipe.trash.without'),
                'options' => [
                    ['value' => 'with', 'label' => __('recipe.trash.with')],
                    ['value' => 'only', 'label' => __('recipe.trash.only')],
                ],
            ])
        </div>

        <button wire:click="resetFilters" class="mn-fbtn" title="{{ __('recipe.actions.reset_filters') }}">
            <i class="fa-solid fa-sliders"></i>
            {{ __('recipe.filters.title') }}
        </button>
        <span wire:click="resetFilters" class="mn-clr">{{ __('recipe.actions.clear_filters') }}</span>
    </div>

    <!-- Info Banner -->
    <div class="mn-info">
        <i class="fa-solid fa-circle-info"></i>
        {{ __('recipe.messages.ingredient_price_source') }}
    </div>

    <!-- Recipes Main Table -->
    @if(count($selectedRecipes) > 0)
        <div class="mn-bulk-actions" style="display:flex;align-items:center;justify-content:space-between;background:var(--bl-s);border:1px solid var(--bl-m);padding:10px 16px;border-radius:8px;margin-bottom:12px;gap:12px; animation: fadeIn 0.2s ease;">
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-weight:600;color:var(--bl);font-size:13px"><i class="fa-solid fa-square-check"></i> {{ __('recipe.bulk.selected', ['count' => count($selectedRecipes)]) }}</span>
            </div>
            <div style="display:flex;gap:8px">
                @if($trashedFilter === 'only')
                    <button type="button" @click="askConfirm('bulkRestore', null, @js(__('recipe.bulk.restore')), @js(__('recipe.confirm.bulk_restore')), @js(__('recipe.bulk.restore')), false)" class="mn-fbtn" style="background:#fff;border-color:var(--bl);color:var(--bl);height:30px;font-size:12px">
                        <i class="fa-solid fa-rotate-left"></i> {{ __('recipe.bulk.restore') }}
                    </button>
                    <button type="button" @click="askConfirm('bulkForceDelete', null, @js(__('recipe.bulk.force_delete')), @js(__('recipe.confirm.bulk_force_delete')), @js(__('recipe.bulk.force_delete')))" class="mn-fbtn" style="background:var(--rd-s);border-color:#fecaca;color:var(--rd);height:30px;font-size:12px">
                        <i class="fa-solid fa-trash-can"></i> {{ __('recipe.bulk.force_delete') }}
                    </button>
                @else
                    <button type="button" @click="askConfirm('bulkDelete', null, @js(__('recipe.bulk.delete')), @js(__('recipe.confirm.bulk_delete')), @js(__('recipe.bulk.delete')))" class="mn-fbtn" style="background:var(--rd-s);border-color:#fecaca;color:var(--rd);height:30px;font-size:12px">
                        <i class="fa-solid fa-trash"></i> {{ __('recipe.bulk.delete') }}
                    </button>
                @endif
            </div>
        </div>
    @endif

    <div class="mn-card">
        <div style="overflow-x:auto">
            <table class="mn-table">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--po-bd2, #f1f5f9); color:var(--po-mu, #64748b); font-weight:700; text-transform:uppercase; font-size:11px; background:var(--po-bd2, #f1f5f9)">
                        <th style="width: 40px; text-align: center; vertical-align: middle;">
                            <input type="checkbox" 
                                   class="fi-checkbox-input rounded border-gray-300 text-primary-600 focus:ring-primary-600 dark:border-gray-700 dark:bg-gray-900 dark:checked:bg-primary-500" 
                                   style="cursor: pointer;"
                                   @php
                                       $allSelected = count(array_intersect($pageIds, $selectedRecipes)) === count($pageIds) && count($pageIds) > 0;
                                   @endphp
                                   {{ $allSelected ? 'checked' : '' }}
                                   wire:click="selectPage({{ json_encode($pageIds) }}, {{ $allSelected ? 'false' : 'true' }})"
                            >
                        </th>
                        <th style="padding:12px 14px; width: 60px; text-align: center;">STT</th>
                        <th style="padding:12px 14px; width: 110px; text-align: center; white-space: nowrap;">{{ __('recipe.table.code') }}</th>
                        <th style="padding:12px 14px; width: 18%;">{{ __('recipe.table.name') }}</th>
                        <th style="padding:12px 14px; width: 120px;">{{ __('recipe.table.type') }}</th>
                        <th style="padding:12px 14px; text-align: right; width: 110px; white-space: nowrap;">{{ __('recipe.table.selling_price_per_portion') }}</th>
                        <th style="padding:12px 14px; text-align: right; width: 110px; white-space: nowrap;">{{ __('recipe.table.cost_per_portion') }}</th>
                        <th style="padding:12px 14px; text-align: center; width: 80px; white-space: nowrap;">{{ __('recipe.table.ingredients_count') }}</th>
                        <th style="padding:12px 14px; text-align: right; width: 110px; white-space: nowrap;">{{ __('recipe.table.total_weight') }}</th>
                        <th style="padding:12px 14px; text-align: right; width: 120px; white-space: nowrap;">{{ __('recipe.table.total_cost') }}</th>
                        <th style="padding:12px 14px; text-align: center; width: 130px; white-space: nowrap;">{{ __('recipe.table.status') }}</th>
                        <th style="padding:12px 14px; width: 140px; white-space: nowrap;">{{ __('recipe.table.created_at') }}</th>
                        <th style="padding:12px 14px; text-align: center; width: 110px; white-space: nowrap;">{{ __('recipe.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recipesList as $recipe)
                        @php
                            $recipeCost = $recipe->effectiveCostPerPortion();
                            $recipeWeight = $recipe->ingredients->sum('pivot.quantity_per_portion');
                            $ingredientsCount = $recipe->ingredients->count();

                            // Chọn class badge cho loại món
                            $typeClass = match ($recipe->type) {
                                'Món mặn' => 'mg-man',
                                'Món xào' => 'mg-xao',
                                'Món canh' => 'mg-canh',
                                'Món chiên' => 'mg-chien',
                                default => 'mg-man',
                            };
                            
                            // Chọn class cho trạng thái
                            $statusClass = match ($recipe->status) {
                                'active' => 'ms-active',
                                'pending' => 'ms-review',
                                'inactive' => 'ms-inactive',
                                default => 'ms-inactive',
                            };
                            $statusText = match ($recipe->status) {
                                'active' => __('recipe.status.active'),
                                'pending' => __('recipe.status.pending'),
                                'inactive' => __('recipe.status.inactive'),
                                default => $recipe->status,
                            };
                        @endphp
                        <tr style="{{ $recipe->trashed() ? 'opacity: 0.6;' : '' }}">
                            <td style="text-align: center; vertical-align: middle;">
                                <input type="checkbox" 
                                       value="{{ $recipe->id }}" 
                                       class="fi-checkbox-input rounded border-gray-300 text-primary-600 focus:ring-primary-600 dark:border-gray-700 dark:bg-gray-900 dark:checked:bg-primary-500" 
                                       style="cursor: pointer;"
                                       wire:model.live="selectedRecipes"
                                >
                            </td>
                            {{--
                            <td style="text-align: center; vertical-align: middle;">
                                <button type="button" wire:click="toggleExpand({{ $recipe->id }})" class="mn-expand-btn {{ $isExpanded ? 'open' : '' }}">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </td>
                            --}}
                            <td style="padding:12px 14px; text-align: center; font-weight: 600; color: var(--mu, #64748b); font-variant-numeric: tabular-nums;">
                                {{ $loop->iteration + ($recipesList->currentPage() - 1) * $recipesList->perPage() }}
                            </td>
                            <td style="padding:12px 14px; text-align: center; font-weight: 800; color: var(--po-bl, #1267e8); font-variant-numeric: tabular-nums; white-space: nowrap;">
                                <span class="mn-code" style="{{ $recipe->trashed() ? 'text-decoration: line-through; color: var(--mu);' : '' }}">{{ $recipe->code }}</span>
                            </td>
                            <td style="padding:12px 14px;">
                                <span class="mn-name" style="font-weight:700; {{ $recipe->trashed() ? 'text-decoration: line-through; color: var(--mu);' : '' }}">{{ $recipe->name }}</span>
                                @if($recipe->trashed())
                                    <span style="display: inline-block; background: var(--rd-s); color: var(--rd); font-size: 10px; padding: 2px 6px; border-radius: 4px; margin-left: 6px; font-weight: bold; vertical-align: middle;">{{ __('recipe.trash.deleted') }}</span>
                                @endif
                            </td>
                            <td style="padding:12px 14px;"><span class="mn-group-pill {{ $typeClass }}">{{ $recipe->type }}</span></td>
                            <td style="padding:12px 14px; text-align: right; font-variant-numeric: tabular-nums;"><span class="mn-price">{{ __('recipe.currency.amount', ['value' => number_format($recipe->selling_price_per_portion, 0, ',', '.')]) }}</span></td>
                            <td style="padding:12px 14px; text-align: right; font-variant-numeric: tabular-nums;"><span class="mn-price">{{ __('recipe.currency.amount', ['value' => number_format($recipe->cost_per_portion, 0, ',', '.')]) }}</span></td>
                            <td style="padding:12px 14px; text-align: center; font-weight: 800; color: var(--po-bl, #1267e8); font-variant-numeric: tabular-nums;"><span class="mn-num">{{ $ingredientsCount }}</span></td>
                            <td style="padding:12px 14px; text-align: right; font-variant-numeric: tabular-nums;"><span class="mn-kg">{{ str_replace('.', ',', round($recipeWeight, 2)) }} kg</span></td>
                            <td style="padding:12px 14px; text-align: right; font-variant-numeric: tabular-nums;">
                                <span class="mn-cost" style="{{ $recipe->cost_override !== null ? 'color:var(--or)' : '' }}">
                                    {{ __('recipe.currency.amount', ['value' => number_format($recipeCost, 0, ',', '.')]) }}
                                </span>
                                @if($recipe->cost_override !== null)
                                    <span style="display:block;font-size:10px;color:var(--or)">{{ __('recipe.labels.adjusted') }}</span>
                                @endif
                            </td>
                            <td style="padding:12px 14px; text-align: center; white-space: nowrap;"><span class="{{ $statusClass }}">{{ $statusText }}</span></td>
                            <td style="padding:12px 14px; color: var(--mu, #64748b); font-variant-numeric: tabular-nums; white-space: nowrap;"><span class="mn-date">{{ $recipe->created_at->format('d/m/Y H:i') }}</span></td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    @if($recipe->trashed())
                                        <!-- Khôi phục món ăn -->
                                        <button type="button" @click="askConfirm('restoreRecipe', {{ $recipe->id }}, @js(__('recipe.actions.restore')), @js(__('recipe.confirm.restore')), @js(__('recipe.actions.restore')), false)" class="abt" style="color: var(--bl); border-color: var(--bl-m);" title="{{ __('recipe.actions.restore') }}">
                                            <i class="fa-solid fa-rotate-left"></i>
                                        </button>
                                        <!-- Xóa vĩnh viễn -->
                                        <button type="button" @click="askConfirm('forceDeleteRecipe', {{ $recipe->id }}, @js(__('recipe.actions.force_delete')), @js(__('recipe.confirm.force_delete')), @js(__('recipe.actions.force_delete')))" class="abt abt-danger" title="{{ __('recipe.actions.force_delete') }}">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    @else
                                        <!-- Xem chi tiết -->
                                        <a href="{{ \App\Filament\Resources\RecipeResource::getUrl('view', ['record' => $recipe]) }}" class="abt" title="{{ __('recipe.actions.view') }}">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <!-- Chỉnh sửa -->
                                        <a href="{{ \App\Filament\Resources\RecipeResource::getUrl('edit', ['record' => $recipe]) }}" class="abt" title="{{ __('recipe.actions.edit') }}">
                                            <i class="fa-solid fa-pencil"></i>
                                        </a>
                                        <!-- Xóa món -->
                                        <button type="button" @click="askConfirm('deleteRecipe', {{ $recipe->id }}, @js(__('recipe.actions.delete')), @js(__('recipe.confirm.delete')), @js(__('recipe.actions.delete')))" class="abt" title="{{ __('recipe.actions.delete') }}">
                                            <i class="fa-solid fa-trash" style="color:#dc2626"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- Accordion Detail Row
                        @if($isExpanded)
                            <tr class="mn-expand-row">
                                <td colspan="13">
                                    <div class="mn-sub">
                                        <div class="mn-sub-inner">
                                            <!-- Sub table -->
                                            <div class="mn-sub-table-wrap">
                                                <div class="mn-sub-ttl">{{ __('recipe.detail.ingredients_of', ['name' => $recipe->name]) }}</div>
                                                <table class="mn-sub-table">
                                                    <thead>
                                                        <tr>
                                                            <th style="width:50px">STT</th>
                                                            <th>{{ __('recipe.fields.ingredient') }}</th><th>{{ __('recipe.detail.quantity_per_portion') }}</th><th>{{ __('recipe.fields.ingredient_price') }}</th><th>{{ __('recipe.fields.line_total') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($recipe->ingredients as $index => $ing)
                                                            @php
                                                                $qty = $ing->pivot->quantity_per_portion;
                                                                $price = $ing->reference_price;
                                                                $total = $qty * $price;
                                                            @endphp
                                                            <tr>
                                                                <td style="font-weight:600;color:var(--mu)">{{ $index + 1 }}</td>
                                                                <td style="font-weight:600">{{ $ing->name }}</td>
                                                                <td>{{ (float) $qty }} kg</td>
                                                                <td>{{ __('recipe.detail.currency', ['value' => number_format($price, 0, ',', '.')]) }}</td>
                                                                <td style="font-weight:700;color:var(--or)">{{ __('recipe.detail.currency', ['value' => number_format($total, 0, ',', '.')]) }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" style="text-align:center;color:var(--fa)">{{ __('recipe.empty.no_ingredient_quantities') }}</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Summary block right -->
                                            <div class="mn-sub-summary">
                                                <div class="mn-sub-sum-lbl">{{ __('recipe.detail.total_weight_label') }}</div>
                                                <div class="mn-sub-sum-val">{{ str_replace('.', ',', round($recipeWeight, 2)) }} kg</div>
                                                
                                                <div class="mn-sub-cost-lbl">{{ __('recipe.detail.total_cost_label') }}</div>
                                                <div class="mn-sub-cost-val">{{ __('recipe.detail.currency', ['value' => number_format($recipeCost, 0, ',', '.')]) }}</div>
                                                
                                                @if($recipe->cost_override !== null)
                                                    <div style="margin-top:10px;padding:6px 8px;background:var(--or-s);border:1px solid #fed7aa;border-radius:6px;font-size:11px;color:var(--or-t)">
                                                        <strong>{{ __('recipe.detail.adjustment_reason') }}</strong><br>
                                                        {{ $recipe->recipeCostLogs->first()?->reason ?? __('recipe.detail.no_adjustment_reason') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                        --}}
                    @empty
                        <tr>
                            <td colspan="13" style="text-align:center;padding:30px;color:var(--mu)">{{ __('recipe.empty.no_filtered_dishes') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer Pagination -->
        @if($recipesList->total() > 0)
            @php
                $currentPage = $recipesList->currentPage();
                $lastPage = $recipesList->lastPage();
                $pageWindow = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                    ->filter(fn ($p) => $p >= 1 && $p <= $lastPage)
                    ->unique()
                    ->sort()
                    ->values();
            @endphp
            <div class="tf">
                <div>
                    {{ __('recipe.pagination.summary', ['from' => $recipesList->firstItem() ?? 0, 'to' => $recipesList->lastItem() ?? 0, 'total' => number_format($recipesList->total(), 0, ',', '.')]) }}
                </div>
                <div class="pgwrap">
                    <select wire:model.live="perPage" class="pgsel">
                        @foreach([5, 10, 20, 50] as $count)
                            <option value="{{ $count }}">{{ __('recipe.pagination.per_page', ['count' => $count]) }}</option>
                        @endforeach
                    </select>
                    @if($recipesList->total() > 0)
                        <div class="pgbs">
                            {{-- Previous --}}
                            @if ($recipesList->onFirstPage())
                                <button type="button" class="pgb" disabled style="opacity: 0.5;">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                            @else
                                <button type="button" wire:click="previousPage" class="pgb">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                            @endif

                            {{-- Windowed page numbers --}}
                            @foreach ($pageWindow as $i => $page)
                                @if ($i > 0 && $page - $pageWindow[$i - 1] > 1)
                                    <span class="pgdot">...</span>
                                @endif
                                @if ($page == $currentPage)
                                    <button type="button" class="pgb cur">{{ $page }}</button>
                                @else
                                    <button type="button" wire:click="gotoPage({{ $page }})" class="pgb">{{ $page }}</button>
                                @endif
                            @endforeach

                            {{-- Next --}}
                            @if ($recipesList->hasMorePages())
                                <button type="button" wire:click="nextPage" class="pgb">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            @else
                                <button type="button" class="pgb" disabled style="opacity: 0.5;">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Hộp thoại xác nhận trong trang (teleport ra body để không bị overflow của bảng cắt) --}}
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
                        {{ __('recipe.actions.cancel') }}
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

    {{-- Modal của Action import/export (wizard 2 bước) --}}
    <x-filament-actions::modals />
</div>
