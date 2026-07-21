<div class="emp-page w-full space-y-6">
    @include('filament.resources.menus.partials.styles')

    @php
        $stats = $this->getStats();
        $menusList = $this->menus();
        $kitchens = $this->getKitchens();
        $shifts = $this->getShifts();
        $recipes = $this->getRecipes();
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
                <button wire:click="loadWeekMenu({{ $kitchens->first()?->id ?? 1 }}, '{{ now()->startOfWeek()->toDateString() }}', false)" class="emp-btn">
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
                <div wire:click="{{ $row['type'] === 'week' ? "loadWeekMenu({$row['kitchen_id']}, '{$row['start_date']}', true)" : "loadDayMenu({$row['kitchen_id']}, '{$row['start_date']}', true)" }}" class="mp-item">
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
                            <button wire:click="{{ $row['type'] === 'week' ? "loadWeekMenu({$row['kitchen_id']}, '{$row['start_date']}', true)" : "loadDayMenu({$row['kitchen_id']}, '{$row['start_date']}', true)" }}" class="abt" title="{{ __('menu.actions.edit') }}">
                                <i class="fa-solid fa-pencil"></i>
                            </button>
                            <!-- Xuất Excel -->
                            <button class="abt" title="{{ __('menu.actions.export_excel') }}" wire:click="exportMenus({{ $row['kitchen_id'] }}, '{{ $row['start_date'] }}', '{{ $row['end_date'] }}')">
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

        @if($menusList->hasPages() || $menusList->total() > 10)
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-top:14px; font-size:12px; color:var(--po-mu)">
                <div>
                    {{ __('menu.pagination.summary', ['from' => $menusList->firstItem() ?? 0, 'to' => $menusList->lastItem() ?? 0, 'total' => $menusList->total()]) }}
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <select wire:model.live="perPage" style="height:30px; border:1px solid var(--po-bd); border-radius:6px; padding:0 8px; font-size:12px; background:transparent;">
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
        <!-- =========================================================================
             VIEW 2: BIỂU MẪU THỰC ĐƠN TUẦN
             ========================================================================= -->
        <!-- Header -->
        <div class="emp-head" style="margin-bottom: 20px;">
            <div>
                <h1 class="emp-title">{{ __('menu.week_form.title') }}</h1>
                <p class="emp-subtitle">{{ __('menu.week_form.subtitle') }}</p>
            </div>
            <div class="emp-actions">
                @php $weekRank = \App\Models\Menu::STATUS_ORDER[$weekStatus] ?? 0; @endphp
                <button wire:click="switchView('list')" class="emp-btn"><i class="fa-solid fa-arrow-left"></i> {{ __('menu.actions.back') }}</button>
                @unless($weekHasPastLockedMenus)
                    @if($weekRank <= \App\Models\Menu::STATUS_ORDER['draft'])
                        <button wire:click="saveWeekMenu('draft')" class="emp-btn"><i class="fa-regular fa-floppy-disk"></i> {{ __('menu.actions.save_draft') }}</button>
                    @endif
                    @if($weekRank <= \App\Models\Menu::STATUS_ORDER['sent'])
                        <button wire:click="saveWeekMenu('sent')" class="emp-btn"><i class="fa-regular fa-paper-plane"></i> {{ __('menu.actions.send_customer') }}</button>
                    @endif
                    <button wire:click="saveWeekMenu('locked')" class="emp-btn emp-btn-primary"><i class="fa-solid fa-lock"></i> {{ __('menu.actions.lock') }}</button>
                @endunless
            </div>
        </div>

        <!-- Form settings -->
        <div class="tcard" style="padding:16px; margin-bottom:14px; background:var(--po-bd2); display:flex; gap:12px; flex-wrap:wrap">
            <div class="field" style="min-width:240px">
                <label>{{ __('menu.fields.kitchen') }} *</label>
                <select wire:model.live="weekKitchenId" class="ctrl" required @disabled(! $this->canChooseKitchen())>
                    @foreach($kitchens as $kit)
                        <option value="{{ $kit->id }}" @selected((int) $weekKitchenId === (int) $kit->id)>{{ $kit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="min-width:200px">
                <label>{{ __('menu.weekly.fields.week_start') }} *</label>
                <input wire:model.live="weekStartDate" type="date" class="ctrl" required>
            </div>
            @if($weekHasEditableLockedMenus)
                <div class="field" style="min-width:280px; flex:1">
                    <label>{{ __('menu.fields.audit_reason') }} *</label>
                    <input wire:model="weekEditReason" type="text" class="ctrl" placeholder="{{ __('menu.placeholders.audit_reason') }}" required>
                    @error('weekEditReason') <span style="color:var(--po-rd);font-size:12px">{{ $message }}</span> @enderror
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
                    <tr style="background:#267DC1; color:#fff">
                        <th style="padding:12px 14px; text-align:left; width:120px">{{ __('menu.week_form.shift_day') }}</th>
                        @for($d = 0; $d < 7; $d++)
                            @php
                                $dayDate = \Illuminate\Support\Carbon::parse($this->weekStartDate)->addDays($d);
                            @endphp
                            <th style="padding:12px 14px; text-align:center">
                                {{ $d === 6 ? __('menu.days.sunday') : __('menu.days.weekday', ['day' => $d + 2]) }}
                                <div style="font-size:10.5px; font-weight:500; opacity:.85; margin-top:2px">
                                    {{ $dayDate->format('d/m/Y') }}
                                </div>
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($shifts as $shift)
                        <tr style="border-bottom:1px solid var(--po-bd)">
                            <td style="padding:14px; font-weight:800; background:var(--po-bd2); color:var(--po-tx)">
                                {{ $shift->name }}
                                <div style="font-size:10px; font-weight:500; color:var(--po-mu); margin-top:2px">
                                    {{ $shift->time_range }}
                                </div>
                            </td>
                            @for($d = 0; $d < 7; $d++)
                                <td style="padding:8px; text-align:center; background:var(--po-wh); vertical-align:top">
                                    <div style="display:flex; flex-direction:column; gap:8px">
                                        {{-- Mỗi ô = danh sách món (nhiều món/ca), thêm bằng nút (+) từng ô --}}
                                        @foreach(($weekCells[$d][$shift->id] ?? [['recipe_id'=>'','portions'=>200]]) as $ci => $cellItem)
                                            <div style="display:flex; flex-direction:column; gap:4px; padding:6px; border:1px dashed var(--po-bd); border-radius:8px; background:var(--po-bd2)">
                                                <div style="display:flex; align-items:center; gap:4px">
                                                    <select wire:model="weekCells.{{ $d }}.{{ $shift->id }}.{{ $ci }}.recipe_id" class="ctrl" style="font-size:12px; height:30px; flex:1" @disabled($weekHasPastLockedMenus)>
                                                        <option value="">{{ __('menu.placeholders.select_dish') }}</option>
                                                        @foreach($recipes as $rec)
                                                            <option value="{{ $rec->id }}">{{ $rec->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @unless($weekHasPastLockedMenus)
                                                        <button type="button" wire:click="removeWeekDish({{ $d }}, {{ $shift->id }}, {{ $ci }})"
                                                            title="{{ __('menu.actions.remove_dish') }}"
                                                            style="width:26px; height:30px; flex-shrink:0; border:none; border-radius:6px; background:var(--po-rd-s); color:var(--po-rd); cursor:pointer; font-size:12px">
                                                            <i class="fa-solid fa-xmark"></i>
                                                        </button>
                                                    @endunless
                                                </div>
                                                <div style="display:flex; align-items:center; gap:4px">
                                                    <input wire:model="weekCells.{{ $d }}.{{ $shift->id }}.{{ $ci }}.portions" type="number" class="ctrl" style="font-size:11.5px; height:26px; text-align:center; padding:0 4px" placeholder="{{ __('menu.placeholders.portions') }}" @disabled($weekHasPastLockedMenus)>
                                                    <span style="font-size:10px; color:var(--po-fa)">{{ __('menu.labels.portions') }}</span>
                                                </div>
                                            </div>
                                        @endforeach

                                        {{-- Nút (+) thêm món cho đúng ô ngày/ca này --}}
                                        @unless($weekHasPastLockedMenus)
                                            <button type="button" wire:click="addWeekDish({{ $d }}, {{ $shift->id }})"
                                                style="height:28px; border:1px dashed var(--po-bl-m); border-radius:8px; background:var(--po-bl-s); color:var(--po-bl); cursor:pointer; font-size:11.5px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:4px">
                                                <i class="fa-solid fa-plus"></i> {{ __('menu.actions.add_dish') }}
                                            </button>
                                        @endunless
                                    </div>
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @elseif($activeView === 'day')
        <!-- =========================================================================
             VIEW 3: BIỂU MẪU THỰC ĐƠN NGÀY
             ========================================================================= -->
        <!-- Header -->
        <div class="emp-head" style="margin-bottom: 20px;">
            <div>
                <h1 class="emp-title">{{ __('menu.day_form.title') }}</h1>
                <p class="emp-subtitle">{{ __('menu.day_form.subtitle') }}</p>
            </div>
            <div class="emp-actions">
                @php $dayRank = \App\Models\Menu::STATUS_ORDER[$dayStatus] ?? 0; @endphp
                <button wire:click="switchView('list')" class="emp-btn"><i class="fa-solid fa-arrow-left"></i> {{ __('menu.actions.back') }}</button>
                @unless($dayHasPastLockedMenus)
                    @if($dayRank <= \App\Models\Menu::STATUS_ORDER['draft'])
                        <button wire:click="saveDayMenu('draft')" class="emp-btn"><i class="fa-regular fa-floppy-disk"></i> {{ __('menu.actions.save_draft') }}</button>
                    @endif
                    @if($dayRank <= \App\Models\Menu::STATUS_ORDER['sent'])
                        <button wire:click="saveDayMenu('sent')" class="emp-btn"><i class="fa-regular fa-paper-plane"></i> {{ __('menu.actions.send_customer') }}</button>
                    @endif
                    <button wire:click="saveDayMenu('locked')" class="emp-btn emp-btn-primary"><i class="fa-solid fa-lock"></i> {{ __('menu.actions.lock') }}</button>
                @endunless
            </div>
        </div>

        <!-- Settings form -->
        <div class="tcard" style="padding:16px; margin-bottom:14px; background:var(--po-bd2); display:flex; gap:12px; flex-wrap:wrap">
            <div class="field" style="min-width:240px">
                <label>{{ __('menu.fields.kitchen') }} *</label>
                <select wire:model.live="dayKitchenId" class="ctrl" required @disabled(! $this->canChooseKitchen())>
                    @foreach($kitchens as $kit)
                        <option value="{{ $kit->id }}" @selected((int) $dayKitchenId === (int) $kit->id)>{{ $kit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="min-width:200px">
                <label>{{ __('menu.day_form.date') }} *</label>
                <input wire:model.live="dayDate" type="date" class="ctrl" required>
            </div>
            @if($dayHasEditableLockedMenus)
                <div class="field" style="min-width:280px; flex:1">
                    <label>{{ __('menu.fields.audit_reason') }} *</label>
                    <input wire:model="dayEditReason" type="text" class="ctrl" placeholder="{{ __('menu.placeholders.audit_reason') }}" required>
                    @error('dayEditReason') <span style="color:var(--po-rd);font-size:12px">{{ $message }}</span> @enderror
                </div>
            @endif
        </div>

        @if($dayHasPastLockedMenus)
            <div class="tcard" style="padding:12px 16px;margin-bottom:14px;border-left:4px solid var(--po-rd);color:var(--po-rd)">
                {{ __('menu.errors.past_locked_edit') }}
            </div>
        @endif

        <div>
            <!-- Left: Shifts Lists -->
            <div style="display:flex; flex-direction:column; gap:16px">
                @foreach($dayItems as $shiftId => $shiftData)
                    <div class="fc">
                        <div class="fch">
                            <div class="fci" style="background:var(--po-bl-s); color:var(--po-bl)"><i class="fa-regular fa-clock"></i></div>
                            <div class="fct">Ca {{ $shiftData['shift_name'] }}</div>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:10px">
                            @foreach($shiftData['recipes'] as $index => $item)
                                <div style="display:flex; align-items:center; gap:8px">
                                    <span style="font-size:12px; font-weight:700; color:var(--po-mu); width:60px">{{ __('menu.labels.dish_index', ['index' => $index + 1]) }}</span>
                                    <select wire:model="dayItems.{{ $shiftId }}.recipes.{{ $index }}.recipe_id" class="ctrl" style="flex:1" @disabled($dayHasPastLockedMenus)>
                                        <option value="">{{ __('menu.placeholders.select_dish') }}</option>
                                        @foreach($recipes as $rec)
                                            <option value="{{ $rec->id }}">{{ $rec->name }}</option>
                                        @endforeach
                                    </select>
                                    <input wire:model="dayItems.{{ $shiftId }}.recipes.{{ $index }}.portions" type="number" class="ctrl" placeholder="{{ __('menu.placeholders.portions') }}" style="width:100px; text-align:center" @disabled($dayHasPastLockedMenus)>
                                    <span style="font-size:12.5px; color:var(--po-mu)">{{ __('menu.labels.portions') }}</span>
                                    @unless($dayHasPastLockedMenus)
                                        <button type="button" wire:click="removeRecipeFromShift({{ $shiftId }}, {{ $index }})" class="abt" title="{{ __('menu.actions.remove_dish') }}" style="border-color:var(--po-rd-s); color:var(--po-rd)">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endunless
                                </div>
                            @endforeach
                        </div>

                        @unless($dayHasPastLockedMenus)
                            <div style="margin-top:14px; display:flex; justify-content:flex-start">
                                <button type="button" wire:click="addRecipeToShift({{ $shiftId }})" class="emp-btn" style="height:32px; font-size:12px">
                                    <i class="fa-solid fa-plus"></i> {{ __('menu.actions.add_dish') }}
                                </button>
                            </div>
                        @endunless
                    </div>
                @endforeach
            </div>

        </div>
    @endif
</div>
