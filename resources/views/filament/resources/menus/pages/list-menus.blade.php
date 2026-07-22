<div class="emp-page w-full space-y-6">
    @include('filament.resources.menus.partials.styles')

    @php
        $stats = $this->getStats();
        $menusList = $this->menus();
        $kitchens = $this->getKitchens();
        $shifts = $this->getShifts();
        $recipes = $this->getRecipes();
        // O(1) lookup theo id — tránh scan tuyến tính firstWhere() mỗi ô grid.
        $recipesById = $recipes->keyBy('id');
    @endphp

    @if (session()->has('message'))
        <div style="background:var(--po-gn-s); color:var(--po-gn-t); padding:12px 16px; border-radius:8px; border:1px solid var(--po-gn); margin-bottom:16px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('message') }}
        </div>
    @endif

    @if($activeView === 'list')
        <!-- =========================================================================
             VIEW 1: DANH SÁCH THỰC ĐƠN CHÍNH
             ========================================================================= -->
        <!-- Header Section -->
        <div class="emp-head" style="margin-bottom: 14px;">
            <div>
                <h1 class="emp-title">{{ __('menu.list.title') }}</h1>
                <p class="emp-subtitle">{{ __('menu.list.subtitle') }}</p>
            </div>
            <div class="emp-actions">
                <button wire:click="loadWeekMenu({{ $kitchens->first()?->id ?? 1 }}, '{{ now()->startOfWeek()->toDateString() }}', null, false)" class="emp-btn">
                    <i class="fa-solid fa-calendar-days"></i>
                    {{ __('menu.actions.create_week') }}
                </button>
                <button wire:click="loadDayMenu({{ $kitchens->first()?->id ?? 1 }}, '{{ now()->toDateString() }}', false)" class="emp-btn emp-btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    {{ __('menu.actions.create_day') }}
                </button>
            </div>
        </div>

        <!-- KPIs Stats -->
        <div class="mp-krow" style="margin-bottom: 16px;">
            <div class="mp-kcard">
                <div class="mp-kico" style="background:var(--po-bl-s); color:var(--po-bl)"><i class="fa-solid fa-calendar-days"></i></div>
                <div>
                    <div class="mp-klbl">{{ __('menu.kpi.running_weekly') }}</div>
                    <div class="mp-kval">{{ $stats['total_active_weeks'] }}</div>
                </div>
            </div>
            <div class="mp-kcard">
                <div class="mp-kico" style="background:var(--po-bl-s); color:var(--po-bl)"><i class="fa-solid fa-paper-plane"></i></div>
                <div>
                    <div class="mp-klbl">{{ __('menu.kpi.sent_this_month') }}</div>
                    <div class="mp-kval">{{ $stats['sent_month'] }}</div>
                </div>
            </div>
            <div class="mp-kcard">
                <div class="mp-kico" style="background:var(--po-bl-s); color:var(--po-bl)"><i class="fa-solid fa-file-pen"></i></div>
                <div>
                    <div class="mp-klbl">{{ __('menu.kpi.still_draft') }}</div>
                    <div class="mp-kval">{{ $stats['pending'] }}</div>
                </div>
            </div>
            <div class="mp-kcard">
                <div class="mp-kico" style="background:var(--po-bl-s); color:var(--po-bl)"><i class="fa-solid fa-lock"></i></div>
                <div>
                    <div class="mp-klbl">{{ __('menu.kpi.locked_this_month') }}</div>
                    <div class="mp-kval">{{ $stats['locked_month'] }}</div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mp-bar" style="margin-bottom: 14px;">
            <!-- 1. Ô select bếp ăn (Dạng VỪA TÌM VỪA CHỌN theo chuẩn dự án) -->
            @if($this->canChooseKitchen())
                <div style="min-width: 220px;" wire:key="kitchen-filter-search-select">
                    @include('filament.components.search-select', [
                        'name' => 'kitchenFilter',
                        'live' => true,
                        'nullable' => true,
                        'placeholder' => __('menu.filters.all_kitchens'),
                        'emptyLabel' => __('menu.filters.all_kitchens'),
                        'searchPlaceholder' => __('menu.placeholders.search_kitchen'),
                        'options' => $this->getKitchenOptions(),
                    ])
                </div>
            @else
                @php
                    $assignedKitchen = $kitchens->firstWhere('id', (int) $kitchenFilter) ?? $kitchens->first();
                @endphp
                <div style="min-width: 180px; height: 34px; border: 1px solid var(--po-bd); border-radius: 8px; padding: 0 10px; display: flex; align-items: center; font-size: 12.5px; font-weight: 700; color: var(--po-su); background: var(--po-bd2);" wire:key="assigned-kitchen-badge">
                    <i class="fa-solid fa-utensils" style="margin-right: 6px; color: var(--po-bl);"></i>
                    {{ $assignedKitchen?->name ?? __('menu.fields.kitchen') }}
                </div>
            @endif

            <!-- 2. Select loại thực đơn tuần hoặc ngày hoặc tất cả -->
            <select wire:key="type-filter-select" wire:model.live="typeFilter" class="mp-sel">
                <option value="">{{ __('menu.filters.all_types') }}</option>
                <option value="week">{{ __('menu.types.week') }}</option>
                <option value="day">{{ __('menu.types.day') }}</option>
            </select>

            <!-- 3. Trạng thái (đúng các trạng thái chốt: Nháp, Đã gửi khách hàng, Đã chốt) -->
            <select wire:key="status-filter-select" wire:model.live="statusFilter" class="mp-sel">
                <option value="">{{ __('menu.filters.all_statuses') }}</option>
                <option value="draft">{{ __('menu.status.draft') }}</option>
                <option value="sent">{{ __('menu.status.sent') }}</option>
                <option value="locked">{{ __('menu.status.locked') }}</option>
            </select>

            <!-- 4. Ô filter thời gian động (Tuần / Ngày / Tháng) - Có wire:key giữ giá trị ngày đã chọn -->
            @if($typeFilter === 'week')
                <input
                    wire:key="filter-week-input"
                    wire:model.live="weekFilter"
                    type="week"
                    class="mp-date-filter"
                    aria-label="{{ __('menu.filters.week') }}"
                    title="{{ __('menu.filters.week') }}"
                >
            @elseif($typeFilter === 'day')
                <input
                    wire:key="filter-day-input"
                    wire:model.live="dayFilter"
                    type="date"
                    class="mp-date-filter"
                    aria-label="{{ __('menu.filters.day') }}"
                    title="{{ __('menu.filters.day') }}"
                >
            @else
                <input
                    wire:key="filter-month-input"
                    wire:model.live="monthFilter"
                    type="month"
                    class="mp-date-filter"
                    aria-label="{{ __('menu.filters.month') }}"
                    title="{{ __('menu.filters.month') }}"
                >
            @endif

            <div class="tsp"></div>

            <button wire:click="resetFilters" class="att-rbtn" title="{{ __('menu.actions.reset_filters') }}">
                <i class="fa-solid fa-rotate-right"></i>
            </button>
        </div>

        <!-- List cards -->
        <div class="mp-card-list">
            @forelse($menusList as $row)
                <div wire:click="{{ $row['type'] === 'week' ? "editWeekMenu({$row['id']})" : "editDayMenu({$row['id']})" }}" class="mp-item">
                    <div class="mp-item-ico" style="{{ $row['type'] === 'week' ? 'background:var(--po-bl-s);color:var(--po-bl)' : 'background:var(--po-pu-s);color:var(--po-pu)' }}">
                        @if($row['type'] === 'week')
                            <i class="fa-solid fa-calendar-days"></i>
                        @else
                            <i class="fa-solid fa-calendar-day"></i>
                        @endif
                    </div>
                    <div class="mp-item-info">
                        <div class="mp-item-title">{{ $row['title'] }}</div>
                        <div class="mp-item-sub">{{ $row['sub'] }}</div>
                        <div class="mp-item-meta">
                            <span class="mp-item-tag"><i class="fa-solid fa-building"></i>{{ $row['meta_company'] }}</span>
                            <span class="mp-item-tag"><i class="fa-solid fa-utensils"></i>{{ $row['meta_info'] }}</span>
                            <span class="mp-item-tag"><i class="fa-solid fa-clock"></i>{{ __('menu.labels.applies_on', ['date' => date('d/m/Y', strtotime($row['start_date']))]) }}</span>
                        </div>
                    </div>
                    <div class="mp-item-right" wire:click.stop>
                        @if($row['status'] === 'locked')
                            <span class="ms-locked">{{ __('menu.status.locked') }}</span>
                        @elseif($row['status'] === 'sent')
                            <span class="ms-sent">{{ __('menu.status.sent') }}</span>
                        @else
                            <span class="ms-draft">{{ __('menu.status.draft') }}</span>
                        @endif

                        <div class="mp-item-actions" style="margin-top: 8px">
                            <!-- Xem chi tiết / Chỉnh sửa -->
                            <button wire:click="{{ $row['type'] === 'week' ? "editWeekMenu({$row['id']})" : "editDayMenu({$row['id']})" }}" class="abt" title="{{ __('menu.actions.edit') }}">
                                <i class="fa-solid fa-pencil"></i>
                            </button>
                            <!-- Xuất Excel -->
                            <button class="abt" title="{{ __('menu.actions.export_excel') }}" wire:click="{{ $row['type'] === 'day' ? "exportDayMenu({$row['id']})" : "exportWeekMenu({$row['id']})" }}">
                                <i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div style="background:var(--po-wh); border:1px solid var(--po-bd); border-radius:12px; padding:40px; text-align:center; color:var(--po-mu)">
                    <i class="fa-regular fa-calendar" style="font-size:32px; opacity:.3; margin-bottom:8px"></i>
                    <div style="font-weight:700; color:var(--po-tx)">{{ __('menu.empty.title') }}</div>
                    <div style="font-size:12px">{{ __('menu.empty.subtitle') }}</div>
                </div>
            @endforelse
        </div>

        @if($menusList->total() > 0)
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-top:14px; font-size:12px; color:var(--po-mu)">
                <div>
                    {{ __('menu.pagination.summary', ['from' => $menusList->firstItem() ?? 0, 'to' => $menusList->lastItem() ?? 0, 'total' => $menusList->total()]) }}
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <select wire:model.live="perPage" style="height:30px; border:1px solid var(--po-bd); border-radius:6px; padding:0 8px; font-size:12px; background:transparent;">
                        <option value="5">{{ __('menu.pagination.per_page', ['count' => 5]) }}</option>
                        <option value="10">{{ __('menu.pagination.per_page', ['count' => 10]) }}</option>
                        <option value="20">{{ __('menu.pagination.per_page', ['count' => 20]) }}</option>
                        <option value="50">{{ __('menu.pagination.per_page', ['count' => 50]) }}</option>
                    </select>

                    @if($menusList->hasPages())
                        <nav role="navigation" aria-label="Pagination Navigation" style="display:flex; align-items:center; gap:4px;">
                            {{-- Trang trước --}}
                            @if ($menusList->onFirstPage())
                                <span aria-disabled="true" style="opacity:.4; padding:4px;">
                                    <i class="fa-solid fa-chevron-left" style="font-size:11px"></i>
                                </span>
                            @else
                                <button type="button" wire:click="previousPage" rel="prev" style="display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; border:1px solid var(--po-bd); border-radius:6px; background:transparent; cursor:pointer;">
                                    <i class="fa-solid fa-chevron-left" style="font-size:11px"></i>
                                </button>
                            @endif

                            {{-- Số trang (dạng cửa sổ: 1 … n-1 n n+1 … cuối) --}}
                            @php
                                $mpCurrentPage = $menusList->currentPage();
                                $mpLastPage = $menusList->lastPage();
                                $mpPageWindow = collect([1, $mpCurrentPage - 1, $mpCurrentPage, $mpCurrentPage + 1, $mpLastPage])
                                    ->filter(fn ($p) => $p >= 1 && $p <= $mpLastPage)
                                    ->unique()
                                    ->sort()
                                    ->values();
                            @endphp
                            @foreach ($mpPageWindow as $i => $page)
                                @if ($i > 0 && $page - $mpPageWindow[$i - 1] > 1)
                                    <span aria-hidden="true" style="padding:0 4px">…</span>
                                @endif
                                @if ($page == $mpCurrentPage)
                                    <span aria-current="page" style="display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; border-radius:6px; background:var(--po-bl); color:#fff; font-weight:700;">{{ $page }}</span>
                                @else
                                    <button type="button" wire:click="gotoPage({{ $page }})" style="display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; border:1px solid var(--po-bd); border-radius:6px; background:transparent; cursor:pointer;">{{ $page }}</button>
                                @endif
                            @endforeach

                            {{-- Trang sau --}}
                            @if ($menusList->hasMorePages())
                                <button type="button" wire:click="nextPage" rel="next" style="display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; border:1px solid var(--po-bd); border-radius:6px; background:transparent; cursor:pointer;">
                                    <i class="fa-solid fa-chevron-right" style="font-size:11px"></i>
                                </button>
                            @else
                                <span aria-disabled="true" style="opacity:.4; padding:4px;">
                                    <i class="fa-solid fa-chevron-right" style="font-size:11px"></i>
                                </span>
                            @endif
                        </nav>
                    @endif
                </div>
            </div>
        @endif

    @elseif($activeView === 'week')
        <div x-data="{
            isOpen: false,
            search: '',
            activeGroup: 'all',
            targetDay: null,
            targetShiftId: null,
            targetCategoryIdx: null,
            subtitle: '',
            selectedRecipeId: null,
            selectedRecipeName: '',
            selectedRecipeGroup: '',
            selectedRecipeCost: '',
            customName: '',
            portions: 1,
            servings: 1,
            recipes: @js($this->recipesData),
            displayLimit: 50,

            init() {
                const set = new Set();
                this.recipes.forEach(r => { if (r.group) set.add(r.group); });
                this.groups = Array.from(set);
            },

            openModal(day, shiftId, categoryIdx, shiftName, dayDow, dayDateStr, categoryLabel, currentRecipeId, currentPortions, currentCustomName, currentServings) {
                this.targetDay = day;
                this.targetShiftId = shiftId;
                this.targetCategoryIdx = categoryIdx;
                
                const activeDishTitle = currentCustomName || categoryLabel;
                this.subtitle = `${shiftName} – ${dayDow} ${dayDateStr} / ${activeDishTitle}`;
                this.search = '';
                this.activeGroup = 'all';
                this.displayLimit = 50;
                this.portions = currentPortions || 1;
                this.servings = currentServings || 1;
                this.customName = currentCustomName || '';

                let found = null;
                if (currentRecipeId) {
                    found = this.recipes.find(r => String(r.id) === String(currentRecipeId));
                }

                if (!found && activeDishTitle) {
                    const targetName = activeDishTitle.toLowerCase().trim();
                    found = this.recipes.find(r => r.name.toLowerCase().trim() === targetName);
                }

                if (found) {
                    this.selectedRecipeId = found.id;
                    this.selectedRecipeName = found.name;
                    this.selectedRecipeGroup = found.group;
                    this.selectedRecipeCost = found.cost_formatted;
                    if (!this.customName) {
                        this.customName = found.name;
                    }
                } else {
                    this.selectedRecipeId = null;
                    this.selectedRecipeName = '';
                    this.selectedRecipeGroup = '';
                    this.selectedRecipeCost = '';
                }

                this.isOpen = true;
                this.$nextTick(() => {
                    if (this.$refs.searchInput) this.$refs.searchInput.focus();
                });
            },

            closeModal() {
                this.isOpen = false;
            },

            selectRecipe(rec) {
                if (String(this.selectedRecipeId) === String(rec.id)) {
                    this.clearSelection();
                    return;
                }
                this.selectedRecipeId = rec.id;
                this.selectedRecipeName = rec.name;
                this.selectedRecipeGroup = rec.group;
                this.selectedRecipeCost = rec.cost_formatted;
                this.customName = rec.name;
            },

            clearSelection() {
                this.selectedRecipeId = null;
                this.selectedRecipeName = '';
                this.selectedRecipeGroup = '';
                this.selectedRecipeCost = '';
                this.customName = '';
            },

            filteredRecipes() {
                const q = this.search.toLowerCase().trim();
                return this.recipes.filter(r => {
                    const matchGroup = this.activeGroup === 'all' || r.group === this.activeGroup;
                    const matchSearch = !q || r.name.toLowerCase().includes(q);
                    return matchGroup && matchSearch;
                });
            },

            visibleRecipes() {
                return this.filteredRecipes().slice(0, this.displayLimit);
            },

            loadMore() {
                if (this.displayLimit < this.filteredRecipes().length) {
                    this.displayLimit += 50;
                }
            },

            confirmSelection() {
                if (this.targetDay !== null && this.targetShiftId !== null && this.targetCategoryIdx !== null) {
                    $wire.set('weekCells.' + this.targetDay + '.' + this.targetShiftId + '.' + this.targetCategoryIdx + '.recipe_id', this.selectedRecipeId);
                    $wire.set('weekCells.' + this.targetDay + '.' + this.targetShiftId + '.' + this.targetCategoryIdx + '.portions', this.servings);
                    $wire.set('weekCells.' + this.targetDay + '.' + this.targetShiftId + '.' + this.targetCategoryIdx + '.servings', this.servings);
                }
                this.closeModal();
            }
        }">
        <!-- =========================================================================
             VIEW 2: BIỂU MẪU THỰC ĐƠN TUẦN
             ========================================================================= -->
        <!-- Header -->
        <div class="emp-head" style="margin-bottom: 16px;">
            <div>
                <h1 class="emp-title">{{ $isEditingWeek ? __('menu.week_form.edit_title') : __('menu.week_form.title') }}</h1>
                <p class="emp-subtitle">{{ __('menu.week_form.subtitle') }}</p>
            </div>
            <div class="emp-actions">
                @php $weekRank = \App\Models\Menu::STATUS_ORDER[$weekStatus] ?? 0; @endphp
                <button wire:click="switchView('list')" class="emp-btn"><i class="fa-solid fa-arrow-left"></i> {{ __('menu.actions.back') }}</button>
                @if($isEditingWeek)
                    <button wire:click="exportWeekForm" class="emp-btn">
                        <i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i> {{ __('menu.actions.export_excel') }}
                    </button>
                @endif
                @unless($weekHasPastLockedMenus)
                    @if(! $isEditingWeek || $weekRank <= \App\Models\Menu::STATUS_ORDER['draft'])
                        <button wire:click="saveWeekMenu('draft')" class="emp-btn">
                            <i class="fa-regular fa-floppy-disk"></i> {{ $isEditingWeek ? __('menu.actions.save_changes') : __('menu.actions.save_draft') }}
                        </button>
                    @endif
                    @if(! $isEditingWeek || $weekRank <= \App\Models\Menu::STATUS_ORDER['sent'])
                        <button wire:click="saveWeekMenu('sent')" class="emp-btn"><i class="fa-regular fa-paper-plane"></i> {{ __('menu.actions.send_customer') }}</button>
                    @endif
                    <button wire:click="saveWeekMenu('locked')" wire:loading.attr="disabled" wire:target="saveWeekMenu" class="emp-btn emp-btn-primary">
                        <i class="fa-solid {{ $weekRank >= \App\Models\Menu::STATUS_ORDER['locked'] ? 'fa-floppy-disk' : 'fa-lock' }}"></i>
                        {{ $weekRank >= \App\Models\Menu::STATUS_ORDER['locked'] ? __('menu.actions.save_changes') : __('menu.actions.lock') }}
                    </button>
                @endunless
            </div>
        </div>

        <!-- Form settings -->
        <div class="tcard" style="padding:16px; margin-bottom:14px; background:var(--po-bd2); display:flex; gap:12px; flex-wrap:wrap">
            <div class="field" style="min-width:260px">
                <label>{{ __('menu.fields.kitchen') }} *</label>
                @include('filament.components.search-select', [
                    'name' => 'weekKitchenId',
                    'live' => true,
                    'options' => $this->getKitchenOptions(),
                    'nullable' => false,
                ])
            </div>
            <div class="field" style="min-width:180px">
                <label>{{ __('menu.labels.date_from') }} *</label>
                <input wire:model.live="weekDateFrom" type="date" class="ctrl" required>
            </div>
            <div class="field" style="min-width:180px">
                <label>{{ __('menu.labels.date_to') }} *</label>
                <input wire:model.live="weekDateTo" type="date" class="ctrl" required>
            </div>
            @if($isEditingWeek && $weekHasEditableLockedMenus)
                <div class="field" style="min-width:280px; flex:1">
                    <label>{{ __('menu.fields.audit_reason') }} *</label>
                    <input wire:model="weekEditReason" type="text" class="ctrl" placeholder="{{ __('menu.placeholders.audit_reason') }}" required>
                    @error('weekEditReason') <span style="color:var(--po-rd);font-size:12px">{{ $message }}</span> @enderror
                </div>
            @endif
        </div>

        <!-- Meta Info Bar & Status Badge -->
        <div class="tcard" style="padding:12px 16px; margin-bottom:14px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; font-size:12.5px; color:var(--po-su)">
            <div style="display:flex; align-items:center; gap:8px">
                <i class="fa-solid fa-building" style="color:var(--po-bl)"></i>
                <strong>{{ $kitchens->firstWhere('id', (int) $weekKitchenId)?->name ?? __('menu.fields.kitchen') }}</strong>
            </div>
            <div style="width:1px; height:20px; background:var(--po-bd); flex-shrink:0"></div>
            <div style="display:flex; align-items:center; gap:8px">
                <i class="fa-regular fa-calendar-days" style="color:var(--po-bl)"></i>
                <span>{{ date('d/m/Y', strtotime($weekDateFrom)) }} – {{ date('d/m/Y', strtotime($weekDateTo)) }}</span>
            </div>
            <div style="width:1px; height:20px; background:var(--po-bd); flex-shrink:0"></div>

            <!-- Shift Filter Checkboxes -->
            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap">
                <i class="fa-solid fa-sun" style="color:var(--po-or); font-size:12px"></i>
                <span style="font-weight:600; color:var(--po-mu); margin-right:2px">{{ __('menu.labels.shift') }}:</span>
                @foreach($shifts as $index => $shift)
                    <label style="display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px; background:var(--po-bd2); border:1px solid var(--po-bd); cursor:pointer; font-weight:600; font-size:11.5px">
                        <input type="checkbox" value="{{ $shift->id }}" wire:model.live="selectedShifts">
                        {{ $shift->name }}
                    </label>
                @endforeach
            </div>

            @if($isEditingWeek)
                <div style="margin-left:auto">
                    @if($weekStatus === 'locked')
                        <span class="ms-locked"><i class="fa-solid fa-lock"></i> {{ __('menu.status.locked') }}</span>
                    @elseif($weekStatus === 'sent')
                        <span class="ms-sent"><i class="fa-regular fa-paper-plane"></i> {{ __('menu.status.sent') }}</span>
                    @else
                        <span class="ms-draft"><i class="fa-regular fa-file-lines"></i> {{ __('menu.status.draft') }}</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Cảnh báo lặp món so với 3 tuần gần nhất (BA R33) --}}
        @php $dupWarnings = $this->getWeekDuplicateWarnings(); @endphp
        @if(!empty($dupWarnings))
            <div class="tcard" style="padding:12px 16px; margin-bottom:14px; border-left:4px solid var(--po-wn, #f59e0b); background:var(--po-bd2)">
                <div style="font-weight:700; color:var(--po-wn, #b45309); display:flex; align-items:center; gap:8px">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    {{ __('menu.week_form.duplicate_warning', ['count' => count($dupWarnings)]) }}
                </div>
                <div style="margin-top:6px; font-size:13px; color:var(--po-tx2)">
                    {{ implode(' · ', $dupWarnings) }}
                </div>
            </div>
        @endif

        <!-- Grid Matrix Table -->
        <div class="tcard" style="overflow-x:auto">
            <table class="grid-table" style="width:100%; border-collapse:collapse; min-width:900px">
                <thead>
                    <tr style="background:var(--po-bl, {{ \App\Models\Setting::get('primary_color', '#267DC1') }}); color:#fff">
                        <th colspan="2" style="padding:10px 12px; text-align:center; min-width:160px; position:sticky; left:0; z-index:3; background:var(--po-bl, {{ \App\Models\Setting::get('primary_color', '#267DC1') }}); color:#fff; font-size:12px; font-weight:800">{{ __('menu.week_form.shift_day') }}</th>
                        @php $weekDays = $this->weekDays; @endphp
                        @foreach($weekDays as $d => $wDay)
                            <th style="padding:10px 12px; text-align:center; min-width:140px; background:var(--po-bl, {{ \App\Models\Setting::get('primary_color', '#267DC1') }}); color:#fff; border-left:1px solid rgba(255,255,255,.15)">
                                <div style="font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.02em">
                                    {{ $wDay['day_name'] }}
                                </div>
                                <div style="font-size:11px; font-weight:500; opacity:.9; margin-top:2px">
                                    {{ date('d-m', strtotime($wDay['date'])) }}
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $shiftPalettes = [
                            0 => ['bg' => '#EFF6FF', 'border' => '#BFDBFE', 'text' => '#1E40AF'],
                            1 => ['bg' => '#F0FDF4', 'border' => '#A7F3D0', 'text' => '#065F46'],
                            2 => ['bg' => '#FEF3C7', 'border' => '#FDE68A', 'text' => '#78350F'],
                            3 => ['bg' => '#F5F3FF', 'border' => '#DDD6FE', 'text' => '#4C1D95'],
                        ];
                        $categories = $this->dishCategories;
                        $catCount = count($categories);
                    @endphp
                    @foreach($shifts as $index => $shift)
                        @continue(!empty($selectedShifts) && !in_array((string)$shift->id, array_map('strval', $selectedShifts), true))
                        @php
                            $pal = $shiftPalettes[$index % 4];
                            $categories = $this->getShiftDishCategories($shift->id);
                            $catCount = count($categories);
                        @endphp

                        @foreach($categories as $ci => $catLabel)
                            <tr style="border-bottom:1px solid var(--po-bd)">
                                @if($ci === 0)
                                    <td rowspan="{{ $catCount + 1 }}" style="background:{{ $pal['bg'] }}; border:1px solid {{ $pal['border'] }}; text-align:center; padding:8px 4px; font-weight:800; font-size:11px; color:{{ $pal['text'] }}; writing-mode:vertical-lr; transform:rotate(180deg); letter-spacing:.05em; white-space:nowrap; min-width:34px; vertical-align:middle; position:sticky; left:0; z-index:2">
                                        <div style="position:sticky; top:120px; text-align:center; display:inline-block">
                                            {{ $shift->name }}
                                        </div>
                                    </td>
                                @endif

                                <td style="padding:4px 8px; font-size:11px; font-weight:700; color:var(--po-tx2); background:var(--po-wh); white-space:nowrap; border-right:1px solid var(--po-bd); position:sticky; left:34px; z-index:1; min-width:140px">
                                    <div style="display:flex; align-items:center; gap:4px">
                                        <input wire:model.blur="customDishCategories.{{ $shift->id }}.{{ $ci }}" type="text" class="ctrl" style="font-size:11px; font-weight:700; text-transform:uppercase; height:26px; padding:0 6px; border:1px solid transparent; background:transparent; width:100%" onfocus="this.style.borderColor='var(--po-bd)'; this.style.background='var(--po-wh)'" onblur="this.style.borderColor='transparent'; this.style.background='transparent'" @disabled($weekHasPastLockedMenus)>
                                        @unless($weekHasPastLockedMenus || count($categories) <= 1)
                                            <button type="button" wire:click="removeCategoryRow({{ $shift->id }}, {{ $ci }})" title="{{ __('menu.actions.remove_dish') }}" style="width:20px; height:20px; border:none; background:transparent; color:var(--po-fa); cursor:pointer; font-size:11px; flex-shrink:0" onmouseover="this.style.color='var(--po-rd)'" onmouseout="this.style.color='var(--po-fa)'">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        @endunless
                                    </div>
                                </td>

                                @foreach($weekDays as $d => $wDay)
                                    <td style="padding:4px 6px; text-align:left; background:var(--po-wh); vertical-align:middle; border-right:1px solid var(--po-bd2)">
                                        @php
                                            $cellVal = $this->weekCells[$d][$shift->id][$ci] ?? ['recipe_id' => '', 'portions' => 1, 'servings' => 1];
                                            $selectedRec = $recipesById->get($cellVal['recipe_id'] ?? null);
                                            $portionsVal = (int) ($cellVal['portions'] ?? 1);
                                            $servingsVal = (int) ($cellVal['servings'] ?? $cellVal['estimated_portions'] ?? 1);
                                            $cellTitle = $selectedRec?->name ?? '';
                                        @endphp
                                        <div style="min-width:130px">
                                            <button type="button" 
                                                    @click="openModal({{ $d }}, {{ $shift->id }}, {{ $ci }}, @js($shift->name), @js($wDay['day_name']), '{{ date('d-m', strtotime($wDay['date'])) }}', @js($customDishCategories[$shift->id][$ci] ?? $catLabel), '{{ $cellVal['recipe_id'] ?? '' }}', {{ $portionsVal }}, @js($cellTitle), {{ $servingsVal }})"
                                                    @disabled($weekHasPastLockedMenus)
                                                    style="border:1px solid {{ $selectedRec ? 'var(--po-bl-m, #bfdbfe)' : 'var(--po-bd, #e2e8f0)' }}; border-radius:8px; padding:6px 8px; background:{{ $selectedRec ? '#FAFCFF' : '#fff' }}; text-align:left; cursor:pointer; width:100%; transition:all .15s; outline:none; display:flex; flex-direction:column; gap:2px">
                                                @if($selectedRec)
                                                    <div style="font-size:12px; font-weight:700; color:var(--po-tx, #0f172a); line-clamp:2; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; line-height:1.3">
                                                        {{ $selectedRec->name }}
                                                    </div>
                                                    <div style="font-size:11px; font-weight:700; color:var(--po-bl, #2563eb); display:flex; align-items:center; gap:4px">
                                                        <span>{{ $portionsVal }} {{ __('menu.popup.phan_suffix') }}</span>
                                                    </div>
                                                @else
                                                    <div style="font-size:11.5px; font-weight:500; color:var(--po-mu)">
                                                        <em>{{ __('menu.placeholders.select_dish') }}</em>
                                                    </div>
                                                    <div style="font-size:11px; font-weight:600; color:var(--po-bl); opacity:.8">
                                                        {{ $portionsVal }} {{ __('menu.popup.phan_suffix') }}
                                                    </div>
                                                @endif
                                            </button>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach

                        <!-- Bottom Add Category Row inside Shift -->
                        <tr style="border-bottom:1px solid var(--po-bd)">
                            <td style="padding:6px 8px; background:var(--po-wh); border-right:1px solid var(--po-bd); position:sticky; left:34px; z-index:1">
                                @unless($weekHasPastLockedMenus)
                                    <button type="button" wire:click="addCategoryRow({{ $shift->id }})" style="height:26px; width:100%; border:1.5px dashed var(--po-gn); border-radius:6px; background:var(--po-gn-s); font-size:11.5px; font-weight:700; color:var(--po-gn-t); cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:4px">
                                        <i class="fa-solid fa-plus"></i> {{ __('menu.actions.add_dish_row') }}
                                    </button>
                                @endunless
                            </td>
                            @foreach($weekDays as $d => $wDay)
                                <td style="background:var(--po-wh)"></td>
                            @endforeach
                        </tr>

                        <!-- Separator row between shifts -->
                        <tr style="height:6px; background:var(--po-bd2)"><td colspan="{{ 2 + count($weekDays) }}" style="padding:0"></td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Shift Legend & Grid Instruction Bar -->
        <div style="display:flex; gap:16px; margin-top:12px; flex-wrap:wrap; align-items:center; font-size:12.5px; color:var(--po-mu)">
            @foreach($shifts as $index => $shift)
                @php $pal = $shiftPalettes[$index % 4]; @endphp
                <span style="display:flex; align-items:center; gap:6px">
                    <span style="display:inline-block; width:12px; height:12px; border-radius:3px; background:{{ $pal['bg'] }}; border:1px solid {{ $pal['border'] }}"></span>
                    {{ $shift->name }}
                </span>
            @endforeach
            <span style="margin-left:auto; color:var(--po-fa); font-size:11.5px">
                <i class="fa-solid fa-circle-info"></i> {{ __('menu.placeholders.select_dish_hint') }}
            </span>
        </div>

        <!-- ════════════════════════ POPUP CHỌN MÓN ════════════════════════ -->
        <div x-show="isOpen" x-cloak style="position:fixed; inset:0; background:rgba(15,23,42,.48); backdrop-filter:blur(4px); z-index:9999" @click="closeModal()"></div>

        <div x-show="isOpen" x-cloak 
             style="position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:10000; width:640px; max-width:94vw; max-height:calc(100vh - 32px); height:fit-content; background:var(--po-wh, #fff); border-radius:16px; box-shadow:0 20px 60px rgba(15,23,42,.3); overflow:hidden; display:flex; flex-direction:column"
             @keydown.escape.window="closeModal()">

            <!-- Popup Header -->
            <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--po-bd, #e2e8f0); flex-shrink:0">
                <div>
                    <div style="font-size:15px; font-weight:800; color:var(--po-tx, #1e293b)">{{ __('menu.popup.title') }}</div>
                    <div style="font-size:12px; color:var(--po-mu, #64748b); margin-top:2px" x-text="subtitle"></div>
                </div>
                <button type="button" @click="closeModal()" style="width:32px; height:32px; border:none; background:#F1F5F9; border-radius:8px; cursor:pointer; font-size:16px; color:var(--po-mu); display:grid; place-items:center; transition:.13s" onmouseover="this.style.background='#E2E8F0'" onmouseout="this.style.background='#F1F5F9'">✕</button>
            </div>

            <!-- Search + Group Filters -->
            <div style="padding:12px 20px 0; flex-shrink:0">
                <div style="display:flex; align-items:center; gap:8px; background:var(--po-bg, #f8fafc); border:1.5px solid var(--po-bd, #e2e8f0); border-radius:9px; padding:0 12px; height:38px; margin-bottom:10px; transition:.13s">
                    <i class="fa-solid fa-magnifying-glass" style="color:var(--po-fa, #94a3b8); font-size:13px; flex-shrink:0"></i>
                    <input x-ref="searchInput" x-model="search" @input="displayLimit = 50" type="text" placeholder="{{ __('menu.popup.search_placeholder') }}" style="border:none; background:transparent; outline:none; font-size:13.5px; color:var(--po-tx, #1e293b); width:100%">
                </div>
                <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:10px">
                    <button type="button" class="popup-grp-btn" :class="{ 'active': activeGroup === 'all' }" @click="activeGroup = 'all'; displayLimit = 50">{{ __('menu.popup.all') }}</button>
                    <template x-for="grp in groups" :key="grp">
                        <button type="button" class="popup-grp-btn" :class="{ 'active': activeGroup === grp }" @click="activeGroup = grp; displayLimit = 50" x-text="grp"></button>
                    </template>
                </div>
            </div>

            <!-- Dish List -->
            <div style="flex:1 1 auto; min-height:100px; max-height:260px; overflow-y:auto; padding:0 20px 12px; scrollbar-width:thin; scrollbar-color:var(--po-bd, #cbd5e1) transparent" @scroll="if ($el.scrollTop + $el.clientHeight >= $el.scrollHeight - 60) loadMore()">
                <template x-for="d in visibleRecipes()" :key="d.id">
                    <div class="popup-dish" :class="{ 'selected': selectedRecipeId == d.id }" @click="selectRecipe(d)">
                        <div class="popup-dish-chk" x-text="selectedRecipeId == d.id ? '✓' : ''"></div>
                        <div class="popup-dish-ico"><i class="fa-solid fa-bowl-food"></i></div>
                        <div style="flex:1; min-width:0">
                            <div class="popup-dish-nm" x-text="d.name"></div>
                            <div class="popup-dish-meta" x-text="'{{ __('menu.popup.group_prefix') }}' + d.group"></div>
                        </div>
                        <div class="popup-dish-cost">
                            <span x-text="d.cost_formatted" style="font-size:12px; font-weight:700; color:var(--po-gn, #059669)"></span>
                            <span style="display:block; font-size:10px; font-weight:500; color:var(--po-fa, #94a3b8)">{{ __('menu.popup.cost_per_portion') }}</span>
                        </div>
                    </div>
                </template>

                <div x-show="filteredRecipes().length > displayLimit" style="text-align:center; padding:12px 0 4px">
                    <button type="button" @click="loadMore()" style="padding:7px 18px; font-size:12px; font-weight:700; color:var(--po-bl, #2563eb); background:var(--po-bl-s, #eff6ff); border:1.5px solid var(--po-bl-m, #bfdbfe); border-radius:20px; cursor:pointer; transition:.15s">
                        <i class="fa-solid fa-chevron-down" style="margin-right:4px"></i>
                        <span>Xem thêm (Đã hiện <span x-text="visibleRecipes().length"></span> / <span x-text="filteredRecipes().length"></span> món)</span>
                    </button>
                </div>

                <div x-show="filteredRecipes().length === 0" style="text-align:center; padding:32px; color:var(--po-fa); font-size:13px">
                    <i class="fa-solid fa-magnifying-glass" style="font-size:26px; display:block; margin-bottom:8px; opacity:.25"></i>
                    <span>{{ __('menu.popup.no_results') }}</span>
                </div>
            </div>

            <!-- Footer: Selected Dish Info + Portion Settings + Action Buttons -->
            <div style="padding:12px 20px 14px; border-top:1px solid var(--po-bd, #e2e8f0); background:#FAFBFC; flex-shrink:0">
                <div x-show="selectedRecipeId" style="margin-bottom:8px">
                    <div style="display:flex; align-items:center; justify-content:space-between; background:var(--po-bl-s, #eff6ff); border:1.5px solid var(--po-bl-m, #bfdbfe); border-radius:9px; padding:8px 12px">
                        <div style="display:flex; align-items:center; gap:8px">
                            <i class="fa-solid fa-bowl-food" style="color:var(--po-bl, #2563eb); font-size:14px"></i>
                            <div>
                                <div style="font-size:13px; font-weight:700; color:var(--po-tx, #1e293b)" x-text="selectedRecipeName"></div>
                                <div style="font-size:11px; color:var(--po-mu, #64748b)" x-text="'Cost: ' + selectedRecipeCost + ' / ' + @js(__('menu.popup.phan_suffix'))"></div>
                            </div>
                        </div>
                        <button type="button" @click="clearSelection()" style="font-size:11px; color:var(--po-rd, #dc2626); font-weight:600; background:none; border:none; cursor:pointer">{{ __('menu.popup.unselect') }}</button>
                    </div>
                </div>


                <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:nowrap">
                    <div style="display:flex; align-items:center; gap:14px; flex-wrap:nowrap">
                        <!-- Ô Số suất -->
                        <div style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:var(--po-tx2)">
                            <i class="fa-solid fa-users" style="color:var(--po-bl, #2563eb); font-size:13px"></i>
                            <span style="font-weight:600">{{ __('menu.popup.portions_label') }}</span>
                            <input x-model.number="servings" type="number" min="1" style="width:68px; height:34px; border:1.5px solid var(--po-bd, #e2e8f0); border-radius:8px; padding:0 6px; font-size:13.5px; font-weight:700; color:var(--po-bl, #2563eb); text-align:center; outline:none">
                            <span style="font-size:12px; color:var(--po-mu)">{{ __('menu.popup.portions_suffix') }}</span>
                        </div>

                    </div>
                    <div style="display:flex; align-items:center; gap:8px; flex-shrink:0">
                        <button type="button" @click="closeModal()" style="height:34px; padding:0 14px; border:1px solid var(--po-bd, #e2e8f0); border-radius:8px; background:#fff; font-size:12.5px; font-weight:600; cursor:pointer; color:var(--po-tx2)">{{ __('menu.popup.cancel') }}</button>
                        <button type="button" @click="confirmSelection()" style="height:34px; padding:0 16px; border:none; border-radius:8px; background:linear-gradient(135deg, var(--po-bl, #2563eb), #0059DD); color:#fff; font-size:12.5px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(18,103,232,.3); display:flex; align-items:center; gap:6px; white-space:nowrap">
                            <i class="fa-solid fa-check"></i>{{ __('menu.popup.confirm') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @elseif($activeView === 'day')
        <div x-data="{
            isOpen: false,
            search: '',
            activeGroup: 'all',
            displayLimit: 50,
            targetShiftId: null,
            targetRecipeIndex: null,
            subtitle: '',
            selectedRecipeId: null,
            selectedRecipeName: '',
            selectedRecipeCost: '',
            portions: 1,
            servings: 1,
            recipes: @js($this->recipesData),
            groups: [],

            init() {
                this.groups = [...new Set(this.recipes.map(recipe => recipe.group).filter(Boolean))];
            },

            openDayModal(shiftId, recipeIndex, shiftName, label, currentRecipeId, currentPortions) {
                this.targetShiftId = shiftId;
                this.targetRecipeIndex = recipeIndex;
                this.subtitle = `${shiftName} / ${label}`;
                this.search = '';
                this.activeGroup = 'all';
                this.displayLimit = 50;
                this.servings = currentPortions || 1;
                this.portions = currentPortions || 1;
                const selected = this.recipes.find(recipe => String(recipe.id) === String(currentRecipeId));
                this.selectedRecipeId = selected?.id ?? null;
                this.selectedRecipeName = selected?.name ?? '';
                this.selectedRecipeCost = selected?.cost_formatted ?? '';
                this.isOpen = true;
                this.$nextTick(() => this.$refs.daySearchInput?.focus());
            },

            closeDayModal() {
                this.isOpen = false;
            },

            selectDayRecipe(recipe) {
                if (String(this.selectedRecipeId) === String(recipe.id)) {
                    this.clearDaySelection();
                    return;
                }
                this.selectedRecipeId = recipe.id;
                this.selectedRecipeName = recipe.name;
                this.selectedRecipeCost = recipe.cost_formatted;
            },

            clearDaySelection() {
                this.selectedRecipeId = null;
                this.selectedRecipeName = '';
                this.selectedRecipeCost = '';
            },

            filteredDayRecipes() {
                const query = this.search.toLowerCase().trim();
                return this.recipes.filter(recipe =>
                    (this.activeGroup === 'all' || recipe.group === this.activeGroup)
                    && (!query || recipe.name.toLowerCase().includes(query))
                );
            },

            visibleDayRecipes() {
                return this.filteredDayRecipes().slice(0, this.displayLimit);
            },

            confirmDaySelection() {
                if (this.targetShiftId !== null && this.targetRecipeIndex !== null) {
                    $wire.set(`dayItems.${this.targetShiftId}.recipes.${this.targetRecipeIndex}.recipe_id`, this.selectedRecipeId);
                    $wire.set(`dayItems.${this.targetShiftId}.recipes.${this.targetRecipeIndex}.portions`, this.servings);
                }
                this.closeDayModal();
            }
        }">
        <!-- =========================================================================
             VIEW 3: BIỂU MẪU THỰC ĐƠN NGÀY (DAY VIEW)
             ========================================================================= -->
        <!-- Header -->
        <div class="emp-head" style="margin-bottom: 16px;">
            <div>
                <h1 class="emp-title">{{ $isEditingDay ? __('menu.day_form.edit_title') : __('menu.day_form.title') }}</h1>
                <p class="emp-subtitle">{{ __('menu.day_form.subtitle') }}</p>
            </div>
            <div class="emp-actions">
                @php $dayRank = \App\Models\Menu::STATUS_ORDER[$dayStatus] ?? 0; @endphp
                <button wire:click="switchView('list')" class="emp-btn"><i class="fa-solid fa-arrow-left"></i> {{ __('menu.actions.back') }}</button>
                @if($isEditingDay)
                    <button wire:click="exportDayMenu({{ $dayMenuId }})" class="emp-btn">
                        <i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i> {{ __('menu.actions.export_excel') }}
                    </button>
                @endif
                @unless($dayHasPastLockedMenus)
                    @if($dayRank <= \App\Models\Menu::STATUS_ORDER['draft'])
                        <button wire:click="saveDayMenu('draft')" class="emp-btn">
                            <i class="fa-regular fa-floppy-disk"></i> {{ $isEditingDay ? __('menu.actions.save_changes') : __('menu.actions.save_draft') }}
                        </button>
                    @endif
                    @if($dayRank <= \App\Models\Menu::STATUS_ORDER['sent'])
                        <button wire:click="saveDayMenu('sent')" class="emp-btn"><i class="fa-regular fa-paper-plane"></i> {{ __('menu.actions.send_customer') }}</button>
                    @endif
                    <button wire:click="saveDayMenu('locked')" class="emp-btn emp-btn-primary">
                        <i class="fa-solid {{ $isEditingDay ? 'fa-lock' : 'fa-floppy-disk' }}"></i> {{ $isEditingDay ? __('menu.actions.lock') : __('menu.actions.save') }}
                    </button>
                @endunless
            </div>
        </div>

        <!-- Settings form -->
        <div class="tcard" style="padding:16px 20px; margin-bottom:14px">
            <div style="display:flex; align-items:flex-start; gap:16px; flex-wrap:wrap">
                <div class="field" style="min-width:260px">
                    <label>{{ __('menu.fields.kitchen') }} *</label>
                    @include('filament.components.search-select', [
                        'name' => 'dayKitchenId',
                        'live' => true,
                        'options' => $this->getKitchenOptions(),
                        'nullable' => false,
                    ])
                </div>
                <div class="field" style="min-width:200px">
                    <label>{{ __('menu.day_form.date') }} *</label>
                    <input wire:model.live="dayDate" type="date" class="ctrl" required>
                </div>
                @if($isEditingDay && $dayHasEditableLockedMenus)
                    <div class="field" style="min-width:280px; flex:1">
                        <label>{{ __('menu.fields.audit_reason') }} *</label>
                        <input wire:model="dayEditReason" type="text" class="ctrl" placeholder="{{ __('menu.placeholders.audit_reason') }}" required>
                        @error('dayEditReason') <span style="color:var(--po-rd);font-size:12px">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>
        </div>

        <!-- Meta Info Bar & Status Badge -->
        <div class="tcard" style="padding:12px 16px; margin-bottom:14px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; font-size:12.5px; color:var(--po-su)">
            <div style="display:flex; align-items:center; gap:8px">
                <i class="fa-solid fa-building" style="color:var(--po-bl)"></i>
                <strong>{{ $kitchens->firstWhere('id', (int) $dayKitchenId)?->name ?? __('menu.fields.kitchen') }}</strong>
            </div>
            <div style="width:1px; height:20px; background:var(--po-bd); flex-shrink:0"></div>
            <div style="display:flex; align-items:center; gap:8px">
                <i class="fa-regular fa-calendar-days" style="color:var(--po-bl)"></i>
                <span>{{ date('d/m/Y', strtotime($dayDate)) }}</span>
            </div>
            <div style="width:1px; height:20px; background:var(--po-bd); flex-shrink:0"></div>

            <!-- Shift Filter Checkboxes -->
            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap">
                <i class="fa-solid fa-sun" style="color:var(--po-or); font-size:12px"></i>
                <span style="font-weight:600; color:var(--po-mu); margin-right:2px">{{ __('menu.labels.shift') }}:</span>
                @foreach($shifts as $index => $shift)
                    <label style="display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px; background:var(--po-bd2); border:1px solid var(--po-bd); cursor:pointer; font-weight:600; font-size:11.5px">
                        <input type="checkbox" value="{{ $shift->id }}" wire:model.live="selectedShifts">
                        {{ $shift->name }}
                    </label>
                @endforeach
            </div>

            @if($isEditingDay)
                <div style="margin-left:auto">
                    @if($dayStatus === 'locked')
                        <span class="ms-locked"><i class="fa-solid fa-lock"></i> {{ __('menu.status.locked') }}</span>
                    @elseif($dayStatus === 'sent')
                        <span class="ms-sent"><i class="fa-regular fa-paper-plane"></i> {{ __('menu.status.sent') }}</span>
                    @else
                        <span class="ms-draft"><i class="fa-regular fa-file-lines"></i> {{ __('menu.status.draft') }}</span>
                    @endif
                </div>
            @endif
        </div>

        @if($dayHasPastLockedMenus)
            <div class="tcard" style="padding:12px 16px;margin-bottom:14px;border-left:4px solid var(--po-rd);color:var(--po-rd)">
                {{ __('menu.errors.past_locked_edit') }}
            </div>
        @endif

        <div>
            <!-- Shifts Cards Stack -->
            <div style="display:flex; flex-direction:column; gap:16px">
                @php
                    $badgeClasses = ['dv-ca-b1', 'dv-ca-b2', 'dv-ca-b3', 'dv-ca-b4'];
                @endphp
                @foreach($dayItems as $shiftId => $shiftData)
                    @continue(!empty($selectedShifts) && !in_array((string)$shiftId, array_map('strval', $selectedShifts), true))
                    @php
                        $bCls = $badgeClasses[($loop->iteration - 1) % 4];
                        $shiftName = $shiftData['shift_name'] ?? $shifts->firstWhere('id', (int) $shiftId)?->name ?? __('menu.fields.shift');
                        $shiftRecipes = $shiftData['recipes'] ?? [];
                    @endphp
                    <div class="tcard" style="padding:14px 16px">
                        <!-- Shift Header -->
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; padding-bottom:8px; border-bottom:1px solid var(--po-bd2); flex-wrap:wrap; gap:10px">
                            <div style="font-size:13px; font-weight:700; color:var(--po-tx); display:flex; align-items:center; gap:8px">
                                <span class="dv-ca-badge {{ $bCls }}">{{ $shiftName }}</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:12px">
                            </div>
                        </div>

                        <!-- Dishes 2-Column Grid -->
                        <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:10px 16px">
                            @foreach($shiftRecipes as $index => $item)
                                <div class="dv-field">
                                    <input wire:model="dayItems.{{ $shiftId }}.recipes.{{ $index }}.label" class="ctrl" style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--po-tx2); border:none; background:transparent; outline:none; height:auto; padding:0 2px; margin-bottom:3px; width:100%" placeholder="{{ __('menu.placeholders.dish_label') }}" @disabled($dayHasPastLockedMenus)>
                                    <div style="display:flex; gap:6px; align-items:center">
                                        @php $dayRecipe = $recipesById->get($item['recipe_id'] ?? null); @endphp
                                        <button
                                            type="button"
                                            @click="openDayModal({{ $shiftId }}, {{ $index }}, @js($shiftName), @js($item['label'] ?? __('menu.labels.dish_index', ['index' => $index + 1])), '{{ $item['recipe_id'] ?? '' }}', {{ (int) ($item['portions'] ?? 1) }})"
                                            @disabled($dayHasPastLockedMenus)
                                            style="flex:1; border:1px solid {{ $dayRecipe ? 'var(--po-bl-m, #bfdbfe)' : 'var(--po-bd, #e2e8f0)' }}; border-radius:8px; padding:6px 8px; background:{{ $dayRecipe ? '#FAFCFF' : '#fff' }}; text-align:left; cursor:pointer; display:flex; align-items:center; justify-content:space-between; gap:8px; outline:none; transition:all .15s"
                                        >
                                            <span style="font-size:12px; font-weight:{{ $dayRecipe ? '700' : '500' }}; color:{{ $dayRecipe ? 'var(--po-tx, #0f172a)' : 'var(--po-mu)' }}">
                                                {{ $dayRecipe?->name ?? __('menu.placeholders.select_dish') }}
                                            </span>
                                            <span style="font-size:11px; font-weight:700; color:var(--po-bl, #2563eb); white-space:nowrap">
                                                {{ (int) ($item['portions'] ?? 1) }} {{ __('menu.popup.phan_suffix') }}
                                            </span>
                                        </button>
                                        @unless($dayHasPastLockedMenus)
                                            <button type="button" wire:click="removeRecipeFromShift({{ $shiftId }}, {{ $index }})" title="{{ __('menu.actions.remove_dish') }}" style="width:28px; height:32px; border:1px solid var(--po-bd); border-radius:6px; background:var(--po-wh); color:var(--po-rd); cursor:pointer; flex-shrink:0; display:grid; place-items:center">
                                                <i class="fa-solid fa-trash-can" style="font-size:11px"></i>
                                            </button>
                                        @endunless
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Add Dish inside Card -->
                        @unless($dayHasPastLockedMenus)
                            <button type="button" wire:click="addRecipeToShift({{ $shiftId }})" style="margin-top:12px; height:26px; padding:0 10px; border:1.5px dashed var(--po-gn); border-radius:6px; background:var(--po-gn-s); color:var(--po-gn-t); font-size:11.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px">
                                <i class="fa-solid fa-plus"></i> {{ __('menu.actions.add_dish') }}
                            </button>
                        @endunless
                    </div>
                @endforeach


            </div>
        </div>

        <div x-show="isOpen" x-cloak style="position:fixed; inset:0; background:rgba(15,23,42,.48); backdrop-filter:blur(4px); z-index:9999" @click="closeDayModal()"></div>
        <div x-show="isOpen" x-cloak style="position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:10000; width:640px; max-width:94vw; max-height:calc(100vh - 32px); background:var(--po-wh); border-radius:16px; box-shadow:0 20px 60px rgba(15,23,42,.3); overflow:hidden; display:flex; flex-direction:column" @keydown.escape.window="closeDayModal()">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--po-bd)">
                <div>
                    <div style="font-size:15px; font-weight:800; color:var(--po-tx)">{{ __('menu.popup.title') }}</div>
                    <div style="font-size:12px; color:var(--po-mu); margin-top:2px" x-text="subtitle"></div>
                </div>
                <button type="button" @click="closeDayModal()" style="width:32px; height:32px; border:none; background:var(--po-bd2); border-radius:8px; cursor:pointer; color:var(--po-mu)">✕</button>
            </div>

            <div style="padding:12px 20px 0">
                <div style="display:flex; align-items:center; gap:8px; background:var(--po-bg); border:1.5px solid var(--po-bd); border-radius:9px; padding:0 12px; height:38px; margin-bottom:10px">
                    <i class="fa-solid fa-magnifying-glass" style="color:var(--po-fa)"></i>
                    <input x-ref="daySearchInput" x-model="search" @input="displayLimit = 50" type="text" placeholder="{{ __('menu.popup.search_placeholder') }}" style="border:none; background:transparent; outline:none; width:100%; color:var(--po-tx)">
                </div>
                <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:10px">
                    <button type="button" class="popup-grp-btn" :class="{ 'active': activeGroup === 'all' }" @click="activeGroup = 'all'; displayLimit = 50">{{ __('menu.popup.all') }}</button>
                    <template x-for="group in groups" :key="group">
                        <button type="button" class="popup-grp-btn" :class="{ 'active': activeGroup === group }" @click="activeGroup = group; displayLimit = 50" x-text="group"></button>
                    </template>
                </div>
            </div>

            <div style="min-height:120px; max-height:300px; overflow-y:auto; padding:0 20px 12px" @scroll="if ($el.scrollTop + $el.clientHeight >= $el.scrollHeight - 60) displayLimit += 50">
                <template x-for="recipe in visibleDayRecipes()" :key="recipe.id">
                    <div class="popup-dish" :class="{ 'selected': selectedRecipeId == recipe.id }" @click="selectDayRecipe(recipe)">
                        <div class="popup-dish-chk" x-text="selectedRecipeId == recipe.id ? '✓' : ''"></div>
                        <div class="popup-dish-ico"><i class="fa-solid fa-bowl-food"></i></div>
                        <div style="flex:1; min-width:0">
                            <div class="popup-dish-nm" x-text="recipe.name"></div>
                            <div class="popup-dish-meta" x-text="@js(__('menu.popup.group_prefix')) + recipe.group"></div>
                        </div>
                        <div class="popup-dish-cost">
                            <span x-text="recipe.cost_formatted" style="font-size:12px; font-weight:700; color:var(--po-gn)"></span>
                            <span style="display:block; font-size:10px; color:var(--po-fa)">{{ __('menu.popup.cost_per_portion') }}</span>
                        </div>
                    </div>
                </template>
                <div x-show="filteredDayRecipes().length === 0" style="text-align:center; padding:28px; color:var(--po-fa)">{{ __('menu.popup.no_results') }}</div>
            </div>

            <div style="padding:12px 20px 14px; border-top:1px solid var(--po-bd); background:var(--po-bg)">
                <div x-show="selectedRecipeId" style="display:flex; align-items:center; justify-content:space-between; background:var(--po-bl-s); border:1.5px solid var(--po-bl-m); border-radius:9px; padding:8px 12px; margin-bottom:10px">
                    <div>
                        <div style="font-size:13px; font-weight:700; color:var(--po-tx)" x-text="selectedRecipeName"></div>
                        <div style="font-size:11px; color:var(--po-mu)" x-text="'Cost: ' + selectedRecipeCost + ' / ' + @js(__('menu.popup.phan_suffix'))"></div>
                    </div>
                    <button type="button" @click="clearDaySelection()" style="border:none; background:none; color:var(--po-rd); font-weight:600; cursor:pointer">{{ __('menu.popup.unselect') }}</button>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:nowrap">
                    <div style="display:flex; align-items:center; gap:14px; flex-wrap:nowrap">
                        <!-- Ô Số suất -->
                        <div style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:var(--po-tx2)">
                            <i class="fa-solid fa-users" style="color:var(--po-bl, #2563eb); font-size:13px"></i>
                            <span style="font-weight:600">{{ __('menu.popup.portions_label') }}</span>
                            <input x-model.number="servings" type="number" min="1" style="width:68px; height:34px; border:1.5px solid var(--po-bd, #e2e8f0); border-radius:8px; padding:0 6px; font-size:13.5px; font-weight:700; color:var(--po-bl, #2563eb); text-align:center; outline:none">
                            <span style="font-size:12px; color:var(--po-mu)">{{ __('menu.popup.portions_suffix') }}</span>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; flex-shrink:0">
                        <button type="button" @click="closeDayModal()" style="height:34px; padding:0 14px; border:1px solid var(--po-bd, #e2e8f0); border-radius:8px; background:#fff; font-size:12.5px; font-weight:600; cursor:pointer; color:var(--po-tx2)">{{ __('menu.popup.cancel') }}</button>
                        <button type="button" @click="confirmDaySelection()" style="height:34px; padding:0 16px; border:none; border-radius:8px; background:linear-gradient(135deg, var(--po-bl, #2563eb), #0059DD); color:#fff; font-size:12.5px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(18,103,232,.3); display:flex; align-items:center; gap:6px; white-space:nowrap"><i class="fa-solid fa-check"></i> {{ __('menu.popup.confirm') }}</button>
                    </div>
                </div>
            </div>
        </div>
        </div>
    @endif
</div>
