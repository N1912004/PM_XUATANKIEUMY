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
    @include('filament.resources.employees.partials.styles')

    @php
        $statsData = $this->stats();
        $employeesList = $this->employees();
        $depts = $this->getDepartments();
        $positions = $this->getPositions();
        $areas = $this->getAreas();
        
        // Tính số bộ lọc đang hoạt động
        $activeFiltersCount = 0;
        if ($this->departmentFilter !== '') $activeFiltersCount++;
        if ($this->positionFilter !== '') $activeFiltersCount++;
        if ($this->areaFilter !== '') $activeFiltersCount++;
        if ($this->statusFilter !== '') $activeFiltersCount++;
    @endphp

    @if (session()->has('message'))
        <div style="background:var(--po-gn-s); color:var(--po-gn-t); padding:12px 16px; border-radius:8px; border:1px solid var(--po-gn); margin-bottom:16px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('message') }}
        </div>
    @endif

    <!-- Header Section -->
    <div class="emp-head">
        <div>
            <h1 class="emp-title">{{ __('employee.navigation') }}</h1>
            <p class="emp-subtitle">{{ __('employee.ui.subtitle') }}</p>
        </div>
        <div class="emp-actions">
            <button wire:click="exportEmployees" class="emp-btn">
                <i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i>
                <span>{{ __('employee.actions.export') }}</span>
            </button>
            <a href="{{ \App\Filament\Resources\EmployeeResource::getUrl('create') }}" class="emp-btn emp-btn-primary">
                <i class="fa-solid fa-plus"></i>
                {{ __('employee.actions.create') }}
            </a>
        </div>
    </div>

    <!-- KPIs Stats -->
    <div class="py-krow" style="margin-bottom:16px">
        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-bl-s);color:var(--po-bl)">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="py-klbl">{{ __('employee.ui.total') }}</div>
                <div class="py-kval">{{ $statsData['total'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-bl-s);color:var(--po-bl)">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <div class="py-klbl">{{ __('employee.status.working') }}</div>
                <div class="py-kval">{{ $statsData['working'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-bl-s);color:var(--po-bl)">
                <i class="fa-solid fa-umbrella-beach"></i>
            </div>
            <div>
                <div class="py-klbl">{{ __('employee.status.on_leave') }}</div>
                <div class="py-kval">{{ $statsData['leave'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-bl-s);color:var(--po-bl)">
                <i class="fa-solid fa-user-minus"></i>
            </div>
            <div>
                <div class="py-klbl">{{ __('employee.status.resigned') }}</div>
                <div class="py-kval">{{ $statsData['resign'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-bl-s);color:var(--po-bl)">
                <i class="fa-regular fa-clock"></i>
            </div>
            <div>
                <div class="py-klbl">{{ __('employee.ui.expiring_documents') }}</div>
                <div class="py-kval">{{ $statsData['exp_docs'] }}</div>
                <span style="font-size:11px; color:var(--po-mu)">{{ __('employee.ui.expires_within') }}</span>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="mp-bar" style="margin-bottom:14px">
        <div class="mp-srch" style="max-width: 240px !important;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input wire:model.live.debounce.250ms="search" type="text" placeholder="{{ __('employee.placeholders.search') }}">
        </div>

        <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:11px; font-weight:700; color:var(--po-mu); text-transform:uppercase">{{ __('employee.fields.department') }}</span>
            <select wire:model.live="departmentFilter" class="mp-sel">
                <option value="">{{ __('employee.ui.all') }}</option>
                @foreach($depts as $deptId => $deptName)
                    <option value="{{ $deptId }}">{{ $deptName }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:11px; font-weight:700; color:var(--po-mu); text-transform:uppercase">{{ __('employee.fields.position') }}</span>
            <select wire:model.live="positionFilter" class="mp-sel">
                <option value="">{{ __('employee.ui.all') }}</option>
                @foreach($positions as $posId => $posName)
                    <option value="{{ $posId }}">{{ $posName }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:11px; font-weight:700; color:var(--po-mu); text-transform:uppercase">{{ __('employee.fields.area') }}</span>
            <select wire:model.live="areaFilter" class="mp-sel">
                <option value="">{{ __('employee.ui.all') }}</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:11px; font-weight:700; color:var(--po-mu); text-transform:uppercase">{{ __('employee.fields.status') }}</span>
            <select wire:model.live="statusFilter" class="mp-sel">
                <option value="">{{ __('employee.ui.all') }}</option>
                <option value="working">{{ __('employee.status.working') }}</option>
                <option value="on_leave">{{ __('employee.status.on_leave') }}</option>
                <option value="resigned">{{ __('employee.status.resigned') }}</option>
            </select>
        </div>

        <div class="tsp"></div>

        <!-- Chỉ báo số bộ lọc đang áp dụng -->
        <div style="background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-tx); padding:6px 12px; font-size:12.5px; border-radius:8px; display:inline-flex; align-items:center; gap:6px">
            <i class="fa-solid fa-sliders" style="color:var(--po-mu)"></i> {{ __('employee.ui.filters') }}
            @if($activeFiltersCount > 0)
                <span style="background:var(--po-bl); color:#fff; width:17px; height:17px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:10px; font-weight:700">
                    {{ $activeFiltersCount }}
                </span>
            @endif
        </div>

        <button wire:click="resetFilters" class="att-rbtn" title="{{ __('employee.ui.reset_filters') }}">
            <i class="fa-solid fa-rotate-right"></i>
        </button>
    </div>

    <!-- Employees Table Section -->
    <div class="po-list-card" style="background:var(--po-wh); border:1px solid var(--po-bd2); border-radius:12px; padding:0; box-shadow:var(--po-sh2); overflow:hidden">
        <div style="overflow-x:auto">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; background:var(--po-bd2)">
                        <th style="padding:14px 12px; width:40px"><input type="checkbox"></th>
                        <th style="padding:14px 12px; width:140px">{{ __('employee.table.code') }}</th>
                        <th style="padding:14px 12px">{{ __('employee.table.name') }}</th>
                        <th style="padding:14px 12px">{{ __('employee.table.department') }}</th>
                        <th style="padding:14px 12px">{{ __('employee.table.position') }}</th>
                        <th style="padding:14px 12px">{{ __('employee.table.area') }}</th>
                        <th style="padding:14px 12px; width:130px">{{ __('employee.table.start_date') }}</th>
                        <th style="padding:14px 12px; width:160px">{{ __('employee.table.status') }}</th>
                        <th style="padding:14px 12px; width:120px; text-align:center">{{ __('employee.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employeesList as $emp)
                        @php
                            $colors = ['#267DC1', '#059669', '#D97706', '#7C3AED', '#EA580C', '#EC4899'];
                            $bgIdx = $emp->id % count($colors);
                            $bgColor = $colors[$bgIdx];
                            
                            // Tạo avatar viết tắt
                            $words = explode(' ', $emp->name);
                            $initials = '';
                            if (count($words) >= 2) {
                                $initials = mb_substr($words[0], 0, 1) . mb_substr($words[count($words)-1], 0, 1);
                            } else {
                                $initials = mb_substr($emp->name, 0, 2);
                            }
                            $initials = mb_strtoupper($initials);
                        @endphp
                        <tr style="border-bottom:1px solid var(--po-bd2); color:var(--po-tx)" class="emp-row">
                            <td style="padding:12px 12px;"><input type="checkbox"></td>
                            <td style="padding:12px 12px; font-weight:600; color:var(--po-mu)">{{ $emp->code }}</td>
                            <td style="padding:12px 12px;">
                                <div style="display:flex; align-items:center; gap:10px">
                                    <!-- Avatar Image or Initials -->
                                    @if($emp->avatar_url && filter_var($emp->avatar_url, FILTER_VALIDATE_URL))
                                        <img src="{{ $emp->avatar_url }}" style="width:34px; height:34px; border-radius:50%; object-fit:cover;">
                                    @elseif($emp->avatar_url)
                                        <img src="{{ asset('storage/' . $emp->avatar_url) }}" style="width:34px; height:34px; border-radius:50%; object-fit:cover;">
                                    @else
                                        <div style="width:34px; height:34px; border-radius:50%; background:{{ $bgColor }}1A; color:{{ $bgColor }}; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12.5px">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight:700; color:var(--po-tx)">{{ $emp->name }}</div>
                                        <div style="font-size:11px; color:var(--po-mu)">{{ $emp->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:12px 12px;">{{ $emp->department?->name }}</td>
                            <td style="padding:12px 12px;">{{ $emp->position?->name }}</td>
                            <td style="padding:12px 12px;">{{ $emp->area?->name }}</td>
                            <td style="padding:12px 12px;">{{ $emp->start_date ? $emp->start_date->format('d/m/Y') : '—' }}</td>
                            <td style="padding:12px 12px;">
                                @if($emp->status === 'working')
                                    <span class="es-badge es-working">
                                        <span class="es-dot"></span> {{ __('employee.status.working') }}
                                    </span>
                                @elseif($emp->status === 'on_leave')
                                    <span class="es-badge es-leave">
                                        <span class="es-dot"></span> {{ __('employee.status.on_leave') }}
                                    </span>
                                @elseif($emp->status === 'resigned')
                                    <span class="es-badge es-resign">
                                        <span class="es-dot"></span> {{ __('employee.status.resigned') }}
                                    </span>
                                @else
                                    <span class="es-badge" style="background:var(--po-bd2); color:var(--po-su)">
                                        <span class="es-dot" style="background:var(--po-mu)"></span> {{ $emp->status }}
                                    </span>
                                @endif
                            </td>
                            <td style="padding:12px 12px; text-align:center">
                                <div style="display:inline-flex; gap:5px">
                                    <!-- Xem chi tiết (Edit) -->
                                    <a href="{{ \App\Filament\Resources\EmployeeResource::getUrl('edit', ['record' => $emp]) }}" class="abt" title="{{ __('employee.actions.view') }}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <!-- Chỉnh sửa -->
                                    <a href="{{ \App\Filament\Resources\EmployeeResource::getUrl('edit', ['record' => $emp]) }}" class="abt" title="{{ __('employee.actions.edit') }}">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>

                                    <!-- Xóa nhân viên -->
                                    <button type="button" @click="askConfirm('deleteEmployee', {{ $emp->id }}, @js(__('employee.ui.delete')), @js(__('employee.ui.confirm_delete')), @js(__('employee.ui.delete')))" class="abt" title="{{ __('employee.ui.delete') }}">
                                        <i class="fa-solid fa-trash" style="color:var(--po-rd)"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding:32px; text-align:center;">
                                <div style="font-size:24px; color:var(--po-mu); margin-bottom:8px">
                                    <i class="fa-solid fa-users-slash"></i>
                                </div>
                                <div style="font-weight:700; color:var(--po-tx)">{{ __('employee.ui.empty') }}</div>
                                <div style="font-size:12px; color:var(--po-mu)">{{ __('employee.ui.empty_hint') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination -->
        @if($employeesList->total() > 0)
            @php
                $currentPage = $employeesList->currentPage();
                $lastPage = $employeesList->lastPage();
                $pageWindow = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                    ->filter(fn ($p) => $p >= 1 && $p <= $lastPage)
                    ->unique()
                    ->sort()
                    ->values();
            @endphp
            <div class="po-footer" style="border-top:1px solid var(--po-bd2); padding:14px 18px; display:flex; justify-content:space-between; align-items:center; font-size:12.5px; color:var(--po-mu)">
                <div>
                    {{ __('employee.ui.pagination', ['from' => $employeesList->firstItem() ?? 0, 'to' => $employeesList->lastItem() ?? 0, 'total' => number_format($employeesList->total(), 0, ',', '.')]) }}
                </div>
                <div class="po-pagination">
                    <span>{{ __('common.pagination.per_page_label') }}</span>
                    <select wire:model.live="perPage" class="lv-per-page-select">
                        @foreach([5, 10, 20, 50] as $count)
                            <option value="{{ $count }}">{{ $count }}</option>
                        @endforeach
                    </select>

                    @if($employeesList->total() > 0)
                        <nav role="navigation" aria-label="{{ __('common.pagination.navigation') }}" style="display:flex; align-items:center; gap:4px">
                            {{-- Previous --}}
                            @if ($employeesList->onFirstPage())
                                <span aria-disabled="true" style="width:30px; height:30px; border-radius:6px; border:1px solid var(--po-bd); display:flex; align-items:center; justify-content:center; color:var(--po-fa); cursor:not-allowed">
                                    <i class="fa-solid fa-chevron-left" style="font-size: 10px;"></i>
                                </span>
                            @else
                                <button type="button" wire:click="previousPage" rel="prev" style="width:30px; height:30px; border-radius:6px; border:1px solid var(--po-bd); display:flex; align-items:center; justify-content:center; color:var(--po-su); cursor:pointer; background:var(--po-wh)">
                                    <i class="fa-solid fa-chevron-left" style="font-size: 10px;"></i>
                                </button>
                            @endif

                            {{-- Windowed page numbers --}}
                            @foreach ($pageWindow as $i => $page)
                                @if ($i > 0 && $page - $pageWindow[$i - 1] > 1)
                                    <span class="po-page-dots" aria-hidden="true" style="padding:0 4px">…</span>
                                @endif
                                @if ($page == $currentPage)
                                    <span aria-current="page" style="width:30px; height:30px; border-radius:6px; background:var(--po-bl); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700">
                                        <span>{{ $page }}</span>
                                    </span>
                                @else
                                    <button type="button" wire:click="gotoPage({{ $page }})" style="width:30px; height:30px; border-radius:6px; border:1px solid var(--po-bd); display:flex; align-items:center; justify-content:center; color:var(--po-su); cursor:pointer; background:var(--po-wh); font-weight:500">{{ $page }}</button>
                                @endif
                            @endforeach

                            {{-- Next --}}
                            @if ($employeesList->hasMorePages())
                                <button type="button" wire:click="nextPage" rel="next" style="width:30px; height:30px; border-radius:6px; border:1px solid var(--po-bd); display:flex; align-items:center; justify-content:center; color:var(--po-su); cursor:pointer; background:var(--po-wh)">
                                    <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
                                </button>
                            @else
                                <span aria-disabled="true" style="width:30px; height:30px; border-radius:6px; border:1px solid var(--po-bd); display:flex; align-items:center; justify-content:center; color:var(--po-fa); cursor:not-allowed">
                                    <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
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
