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
                <button wire:click="loadWeekMenu({{ $kitchens->first()?->id ?? 1 }}, '{{ now()->startOfWeek()->toDateString() }}')" class="emp-btn">
                    <i class="fa-regular fa-calendar-week"></i>
                    {{ __('menu.actions.create_week') }}
                </button>
                <button wire:click="loadDayMenu({{ $kitchens->first()?->id ?? 1 }}, '{{ now()->toDateString() }}')" class="emp-btn emp-btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    {{ __('menu.actions.create_day') }}
                </button>
            </div>
        </div>

        <!-- 4 KPIs Stats -->
        <div class="mp-krow" style="margin-bottom: 16px;">
            <div class="mp-kcard">
                <div class="mp-kico" style="background:var(--po-bl-s); color:var(--po-bl)"><i class="fa-regular fa-calendar-week"></i></div>
                <div>
                    <div class="mp-klbl">{{ __('menu.kpi.running_weekly') }}</div>
                    <div class="mp-kval">{{ $stats['total_active_weeks'] }}</div>
                </div>
            </div>
            <div class="mp-kcard">
                <div class="mp-kico" style="background:var(--po-bl-s); color:var(--po-bl)"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <div class="mp-klbl">{{ __('menu.kpi.sent_this_month') }}</div>
                    <div class="mp-kval">{{ $stats['sent_month'] }}</div>
                </div>
            </div>
            <div class="mp-kcard">
                <div class="mp-kico" style="background:var(--po-bl-s); color:var(--po-bl)"><i class="fa-solid fa-check-double"></i></div>
                <div>
                    <div class="mp-klbl">{{ __('menu.kpi.customer_confirmed') }}</div>
                    <div class="mp-kval">{{ $stats['confirmed_month'] }}</div>
                </div>
            </div>
            <div class="mp-kcard">
                <div class="mp-kico" style="background:var(--po-bl-s); color:var(--po-bl)"><i class="fa-solid fa-clock-rotate-left"></i></div>
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
            <div class="mp-srch">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input wire:model.live.debounce.250ms="search" type="text" placeholder="{{ __('menu.placeholders.search') }}">
            </div>
            <select wire:model.live="typeFilter" class="mp-sel">
                <option value="">{{ __('menu.filters.all_types') }}</option>
                <option value="week">{{ __('menu.types.week') }}</option>
                <option value="day">{{ __('menu.types.day') }}</option>
            </select>
            <select wire:model.live="statusFilter" class="mp-sel">
                <option value="">{{ __('menu.filters.all_statuses') }}</option>
                <option value="draft">{{ __('menu.status.draft') }}</option>
                <option value="sent">{{ __('menu.status.sent') }}</option>
                <option value="confirmed">{{ __('menu.status.confirmed') }}</option>
                <option value="locked">{{ __('menu.status.locked') }}</option>
            </select>
            <select wire:model.live="monthFilter" class="mp-sel">
                @foreach($this->getMonthOptions() as $ym => $label)
                    <option value="{{ $ym }}">{{ $label }}</option>
                @endforeach
            </select>
            <div class="tsp"></div>
            <button wire:click="resetFilters" class="att-rbtn" title="{{ __('menu.actions.reset_filters') }}">
                <i class="fa-solid fa-rotate-right"></i>
            </button>
        </div>

        <!-- Locked Edit Warning Panel -->
        <div class="mp-locked-panel" style="margin-bottom: 16px;">
            <div class="mp-locked-panel-hd">
                <div>
                    <div class="mp-locked-panel-title">
                        <i class="fa-solid fa-lock-open"></i>
                        {{ __('menu.locked_edit.title') }}
                    </div>
                    <div class="mp-locked-panel-sub">
                        {{ __('menu.locked_edit.description') }}
                    </div>
                </div>
                <span class="ms-locked">{{ __('menu.status.locked') }}</span>
            </div>
            <div class="mp-locked-tools">
                <div class="dv-field" style="min-width:260px">
                    <label>{{ __('menu.filters.locked_menus') }}</label>
                    <select class="dv-sel" id="mpLockedMenuSelect">
                        @php
                            $lockedMenus = $this->getLockedMenus();
                        @endphp
                        @forelse($lockedMenus as $lm)
                            <option value="{{ $lm->kitchen_id }}_{{ $lm->date->toDateString() }}">
                                {{ __('menu.labels.date_kitchen', ['date' => $lm->date->format('d/m/Y'), 'kitchen' => $lm->kitchen?->name]) }}
                            </option>
                        @empty
                            <option value="">{{ __('menu.empty.no_locked_menus') }}</option>
                        @endforelse
                    </select>
                </div>
                <button type="button" onclick="const val = document.getElementById('mpLockedMenuSelect').value.split('_'); if(val.length === 2) { @this.loadDayMenu(val[0], val[1]); }" class="btn btn-p">
                    <i class="fa-solid fa-pen-to-square"></i> {{ __('menu.actions.open_edit') }}
                </button>
                <button type="button" onclick="const val = document.getElementById('mpLockedMenuSelect').value.split('_'); if(val.length === 2) { @this.loadWeekMenu(val[0], val[1]); }" class="btn">
                    <i class="fa-regular fa-calendar-week"></i> {{ __('menu.actions.view_week') }}
                </button>
            </div>
        </div>

        <!-- List cards -->
        <div class="mp-card-list">
            @forelse($menusList as $row)
                <div wire:click="{{ $row['type'] === 'week' ? "loadWeekMenu({$row['kitchen_id']}, '{$row['start_date']}')" : "loadDayMenu({$row['kitchen_id']}, '{$row['start_date']}')" }}" class="mp-item">
                    <div class="mp-item-ico" style="{{ $row['type'] === 'week' ? 'background:var(--po-bl-s);color:var(--po-bl)' : 'background:var(--po-pu-s);color:var(--po-pu)' }}">
                        @if($row['type'] === 'week')
                            <i class="fa-regular fa-calendar-week"></i>
                        @else
                            <i class="fa-regular fa-calendar-day"></i>
                        @endif
                    </div>
                    <div class="mp-item-info">
                        <div class="mp-item-title">{{ $row['title'] }}</div>
                        <div class="mp-item-sub">{{ $row['sub'] }}</div>
                        <div class="mp-item-meta">
                            <span class="mp-item-tag"><i class="fa-solid fa-building"></i>{{ $row['meta_company'] }}</span>
                            <span class="mp-item-tag"><i class="fa-solid fa-utensils"></i>{{ $row['meta_info'] }}</span>
                            <span class="mp-item-tag"><i class="fa-regular fa-clock"></i>{{ __('menu.labels.applies_on', ['date' => date('d/m/Y', strtotime($row['start_date']))]) }}</span>
                        </div>
                    </div>
                    <div class="mp-item-right" wire:click.stop>
                        @if($row['status'] === 'locked')
                            <span class="ms-locked">{{ __('menu.status.locked') }}</span>
                        @elseif($row['status'] === 'confirmed')
                            <span class="ms-sent">{{ __('menu.status.confirmed') }}</span>
                        @elseif($row['status'] === 'sent')
                            <span class="ms-sent">{{ __('menu.status.sent') }}</span>
                        @else
                            <span class="ms-draft">{{ __('menu.status.draft') }}</span>
                        @endif

                        <div class="mp-item-actions" style="margin-top: 8px">
                            <!-- Xem chi tiết -->
                            <button wire:click="{{ $row['type'] === 'week' ? "loadWeekMenu({$row['kitchen_id']}, '{$row['start_date']}')" : "loadDayMenu({$row['kitchen_id']}, '{$row['start_date']}')" }}" class="abt" title="{{ __('menu.actions.edit') }}">
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
                <button wire:click="switchView('list')" class="emp-btn"><i class="fa-solid fa-arrow-left"></i> {{ __('menu.actions.back') }}</button>
                <button type="button" class="emp-btn" wire:click="exportWeekForm"><i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i> {{ __('menu.actions.export_excel') }}</button>
                <button wire:click="saveWeekMenu('draft')" class="emp-btn"><i class="fa-regular fa-floppy-disk"></i> {{ __('menu.actions.save_draft') }}</button>
                <button wire:click="saveWeekMenu('sent')" class="emp-btn"><i class="fa-regular fa-paper-plane"></i> {{ __('menu.actions.send_confirmation') }}</button>
                <button wire:click="saveWeekMenu('confirmed')" class="emp-btn"><i class="fa-solid fa-check-double"></i> {{ __('menu.actions.customer_confirmed') }}</button>
                <button wire:click="saveWeekMenu('locked')" class="emp-btn emp-btn-primary"><i class="fa-solid fa-lock"></i> {{ __('menu.actions.lock') }}</button>
            </div>
        </div>

        <!-- Form settings -->
        <div class="tcard" style="padding:16px; margin-bottom:14px; background:var(--po-bd2); display:flex; gap:12px; flex-wrap:wrap">
            <div class="field" style="min-width:240px">
                <label>{{ __('menu.fields.kitchen') }} *</label>
                <select wire:model="weekKitchenId" class="ctrl" required>
                    @foreach($kitchens as $kit)
                        <option value="{{ $kit->id }}">{{ $kit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="min-width:200px">
                <label>{{ __('menu.weekly.fields.week_start') }} *</label>
                <input wire:model="weekStartDate" type="date" class="ctrl" required>
            </div>
            <div class="field" style="min-width:280px; flex:1">
                <label>{{ __('menu.fields.audit_reason') }}</label>
                <input wire:model="weekEditReason" type="text" class="ctrl" placeholder="{{ __('menu.placeholders.audit_reason') }}">
            </div>
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
                                                    <select wire:model="weekCells.{{ $d }}.{{ $shift->id }}.{{ $ci }}.recipe_id" class="ctrl" style="font-size:12px; height:30px; flex:1">
                                                        <option value="">{{ __('menu.placeholders.select_dish') }}</option>
                                                        @foreach($recipes as $rec)
                                                            <option value="{{ $rec->id }}">{{ $rec->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" wire:click="removeWeekDish({{ $d }}, {{ $shift->id }}, {{ $ci }})"
                                                        title="{{ __('menu.actions.remove_dish') }}"
                                                        style="width:26px; height:30px; flex-shrink:0; border:none; border-radius:6px; background:var(--po-rd-s); color:var(--po-rd); cursor:pointer; font-size:12px">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>
                                                <div style="display:flex; align-items:center; gap:4px">
                                                    <input wire:model="weekCells.{{ $d }}.{{ $shift->id }}.{{ $ci }}.portions" type="number" class="ctrl" style="font-size:11.5px; height:26px; text-align:center; padding:0 4px" placeholder="{{ __('menu.placeholders.portions') }}">
                                                    <span style="font-size:10px; color:var(--po-fa)">{{ __('menu.labels.portions') }}</span>
                                                </div>
                                            </div>
                                        @endforeach

                                        {{-- Nút (+) thêm món cho đúng ô ngày/ca này --}}
                                        <button type="button" wire:click="addWeekDish({{ $d }}, {{ $shift->id }})"
                                            style="height:28px; border:1px dashed var(--po-bl-m); border-radius:8px; background:var(--po-bl-s); color:var(--po-bl); cursor:pointer; font-size:11.5px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:4px">
                                            <i class="fa-solid fa-plus"></i> {{ __('menu.actions.add_dish') }}
                                        </button>
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
                <h1 class="emp-title">{{ __('menu.day_form.title', ['date' => $dayDate ? date('d/m/Y', strtotime($dayDate)) : '']) }}</h1>
                <p class="emp-subtitle">{{ __('menu.day_form.subtitle') }}</p>
            </div>
            <div class="emp-actions">
                <button wire:click="switchView('list')" class="emp-btn"><i class="fa-solid fa-arrow-left"></i> {{ __('menu.actions.back') }}</button>
                <button type="button" class="emp-btn" wire:click="exportDayForm"><i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i> {{ __('menu.actions.export_excel') }}</button>
                <button wire:click="saveDayMenu('draft')" class="emp-btn"><i class="fa-regular fa-floppy-disk"></i> {{ __('menu.actions.save_draft') }}</button>
                <button wire:click="saveDayMenu('sent')" class="emp-btn"><i class="fa-regular fa-paper-plane"></i> {{ __('menu.actions.send_confirmation') }}</button>
                <button wire:click="saveDayMenu('confirmed')" class="emp-btn"><i class="fa-solid fa-check-double"></i> {{ __('menu.actions.customer_confirmed') }}</button>
                <button wire:click="saveDayMenu('locked')" class="emp-btn emp-btn-primary"><i class="fa-solid fa-lock"></i> {{ __('menu.actions.lock') }}</button>
            </div>
        </div>

        <!-- Settings form -->
        <div class="tcard" style="padding:16px; margin-bottom:14px; background:var(--po-bd2); display:flex; gap:12px; flex-wrap:wrap">
            <div class="field" style="min-width:240px">
                <label>{{ __('menu.fields.kitchen') }} *</label>
                <select wire:model="dayKitchenId" class="ctrl" required>
                    @foreach($kitchens as $kit)
                        <option value="{{ $kit->id }}">{{ $kit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="min-width:200px">
                <label>{{ __('menu.day_form.date') }} *</label>
                <input wire:model="dayDate" type="date" class="ctrl" required>
            </div>
            <div class="field" style="min-width:280px; flex:1">
                <label>{{ __('menu.fields.audit_reason') }}</label>
                <input wire:model="dayEditReason" type="text" class="ctrl" placeholder="{{ __('menu.placeholders.audit_reason') }}">
            </div>
        </div>

        <!-- 2 Columns Layout: Form + Summary -->
        <div style="display:grid; grid-template-columns:1fr 300px; gap:16px; align-items:start">
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
                                    <select wire:model="dayItems.{{ $shiftId }}.recipes.{{ $index }}.recipe_id" class="ctrl" style="flex:1">
                                        <option value="">{{ __('menu.placeholders.select_dish') }}</option>
                                        @foreach($recipes as $rec)
                                            <option value="{{ $rec->id }}">{{ $rec->name }}</option>
                                        @endforeach
                                    </select>
                                    <input wire:model="dayItems.{{ $shiftId }}.recipes.{{ $index }}.portions" type="number" class="ctrl" placeholder="{{ __('menu.placeholders.portions') }}" style="width:100px; text-align:center">
                                    <span style="font-size:12.5px; color:var(--po-mu)">{{ __('menu.labels.portions') }}</span>
                                    <button type="button" wire:click="removeRecipeFromShift({{ $shiftId }}, {{ $index }})" class="abt" title="{{ __('menu.actions.remove_dish') }}" style="border-color:var(--po-rd-s); color:var(--po-rd)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <div style="margin-top:14px; display:flex; justify-content:flex-start">
                            <button type="button" wire:click="addRecipeToShift({{ $shiftId }})" class="emp-btn" style="height:32px; font-size:12px">
                                <i class="fa-solid fa-plus"></i> {{ __('menu.actions.add_dish') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Right: Summary & Rules -->
            <div style="display:flex; flex-direction:column; gap:16px">
                <div class="lf-sum">
                    <div class="lf-sum-ttl">{{ __('menu.summary.title') }}</div>
                    <div class="lf-sum-row">
                        <span class="lf-sum-k">{{ __('menu.fields.date') }}</span>
                        <span class="lf-sum-v" style="font-weight:700">{{ $dayDate ? date('d/m/Y', strtotime($dayDate)) : '—' }}</span>
                    </div>
                    <div class="lf-sum-row">
                        <span class="lf-sum-k">{{ __('menu.fields.kitchen') }}</span>
                        <span class="lf-sum-v">{{ \App\Models\Kitchen::find($dayKitchenId)?->name ?: '—' }}</span>
                    </div>
                    <div class="lf-sum-row" style="border-bottom:none">
                        <span class="lf-sum-k">{{ __('menu.summary.shifts') }}</span>
                        <span class="lf-sum-v" style="font-weight:700; color:var(--po-bl)">
                            @php
                                $activeShifts = [];
                                foreach($dayItems as $sId => $sData) {
                                    $hasRecipe = collect($sData['recipes'])->contains(fn($r) => !empty($r['recipe_id']));
                                    if($hasRecipe) $activeShifts[] = $sData['shift_name'];
                                }
                                echo empty($activeShifts) ? __('menu.summary.none') : implode(' / ', $activeShifts);
                            @endphp
                        </span>
                    </div>
                </div>

                <div class="lf-notice">
                    <div class="lf-notice-ttl">{{ __('menu.summary.lock_notice') }}</div>
                    <ul class="lf-rule">
                        <li><i class="fa-solid fa-circle" style="font-size:5px; color:var(--po-mu)"></i> {{ __('menu.summary.lock_notice_inventory') }}</li>
                        <li><i class="fa-solid fa-circle" style="font-size:5px; color:var(--po-mu)"></i> {{ __('menu.summary.lock_notice_audit') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>
