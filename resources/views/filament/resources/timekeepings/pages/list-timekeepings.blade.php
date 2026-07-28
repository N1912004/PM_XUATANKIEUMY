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
    @include('filament.resources.timekeepings.partials.styles')

    @php
        $employee = $this->getAttendanceCardEmployee();
        $attendanceRecord = $this->getAttendanceCardRecord();
        $canUseAttendanceActions = $this->canUseAttendanceActions($employee);
        $timekeepingsList = $this->timekeepings();
        $shifts = $this->getShifts();
        $depts = $this->getDepartments();
        $areas = $this->getAreas();

        // Tính số bộ lọc đang hoạt động
        $activeFiltersCount = 0;
        if ($this->search !== '') $activeFiltersCount++;
        if ($this->dateFilter !== '') $activeFiltersCount++;
        if ($this->shiftFilter !== '') $activeFiltersCount++;
        if ($this->departmentFilter !== '') $activeFiltersCount++;
        if ($this->areaFilter !== '') $activeFiltersCount++;
        if ($this->statusFilter !== '') $activeFiltersCount++;

        $avatarColors = ['#267DC1', '#059669', '#D97706', '#7C3AED', '#EA580C', '#EC4899'];
        $empBgColor = $employee ? $avatarColors[$employee->id % count($avatarColors)] : '#267DC1';
        $empInitials = '';
        if ($employee) {
            $words = explode(' ', $employee->name);
            $empInitials = count($words) >= 2
                ? mb_substr($words[0], 0, 1) . mb_substr($words[count($words) - 1], 0, 1)
                : mb_substr($employee->name, 0, 2);
            $empInitials = mb_strtoupper($empInitials);
        }

        $cardDate = \Carbon\Carbon::parse($this->dateFilter ?: now()->toDateString());
        $dayNames = array_values(__('timekeeping.weekdays'));
        $statusLabel = $attendanceRecord?->status ?: __('timekeeping.status.unrecorded');
        $statusClass = match ($statusLabel) {
            'Đúng giờ' => 'ok',
            'Đi trễ' => 'late',
            'Tăng ca' => 'ot',
            'Nghỉ phép' => 'leave',
            'Vắng mặt' => 'absent',
            default => 'neutral',
        };
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

    <!-- Page Head -->
    <div class="emp-head" style="margin-bottom: 16px;">
        <div>
            <h1 class="emp-title">{{ __('timekeeping.navigation') }}</h1>
            <p class="emp-subtitle">{{ __('timekeeping.ui.subtitle') }}</p>
        </div>
        <div>
            <button wire:click="exportTimekeepings" class="emp-btn">
                <i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i>
                <span>{{ __('timekeeping.actions.export') }}</span>
            </button>
        </div>
    </div>

    @if($employee)
        <div class="ci-card">
            <div class="ci-top">
                <div class="ci-user">
                    @if($employee->avatar_url && filter_var($employee->avatar_url, FILTER_VALIDATE_URL))
                        <div class="ci-av"><img src="{{ $employee->avatar_url }}" alt="{{ $employee->name }}"></div>
                    @elseif($employee->avatar_url)
                        <div class="ci-av"><img src="{{ asset('storage/' . $employee->avatar_url) }}" alt="{{ $employee->name }}"></div>
                    @else
                        <div class="ci-av ci-av-fallback" style="background:{{ $empBgColor }}1A; color:{{ $empBgColor }}">
                            {{ $empInitials }}
                        </div>
                    @endif

                    <div>
                        <div class="ci-name">{{ $employee->name }}</div>
                        <div class="ci-id">{{ $employee->code }}</div>
                        <div class="ci-role">{{ __('timekeeping.ui.employee') }} · {{ $employee->department?->name ?? '—' }}</div>
                    </div>
                </div>

                <div class="ci-meta">
                    <div class="ci-meta-item">
                        <i class="ci-meta-ico fa-regular fa-calendar"></i>
                        <div>
                            <div class="ci-meta-lbl">{{ __('timekeeping.fields.date') }}</div>
                            <div class="ci-meta-val">{{ $cardDate->format('d/m/Y') }} ({{ $dayNames[$cardDate->dayOfWeek] }})</div>
                        </div>
                    </div>
                    <div class="ci-meta-item">
                        <i class="ci-meta-ico fa-regular fa-clock"></i>
                        <div>
                            <div class="ci-meta-lbl">{{ __('timekeeping.fields.shift') }}</div>
                            <div class="ci-meta-val">
                                @if($attendanceRecord?->shift)
                                    {{ $attendanceRecord->shift->name }} · {{ $attendanceRecord->shift->time_range }}
                                @else
                                    {{ __('timekeeping.attendance.not_assigned') }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="ci-meta-status">
                        <div class="ci-meta-lbl">{{ __('timekeeping.fields.status') }}</div>
                        <span class="ci-status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                </div>
            </div>

            <div class="ci-bottom">
                <div class="ci-time-box">
                    <div class="ci-time-lbl">{{ __('timekeeping.table.check_in') }}</div>
                    <div class="ci-time-val in">{{ $attendanceRecord?->check_in ? date('H:i', strtotime($attendanceRecord->check_in)) : '--' }}</div>
                    <div class="ci-time-date">{{ $attendanceRecord?->check_in ? $cardDate->format('d/m/Y') : '--' }}</div>
                </div>
                <div class="ci-divider"></div>
                <div class="ci-time-box">
                    <div class="ci-time-lbl">{{ __('timekeeping.table.check_out') }}</div>
                    <div class="ci-time-val out">{{ $attendanceRecord?->check_out ? date('H:i', strtotime($attendanceRecord->check_out)) : '--' }}</div>
                    <div class="ci-time-date">{{ $attendanceRecord?->check_out ? $cardDate->format('d/m/Y') : '--' }}</div>
                </div>
                <div class="ci-divider"></div>

                <div class="ci-actions">
                    @if($attendanceRecord?->check_in)
                        <button class="ci-btn done-in" type="button" disabled>
                            <i class="ci-btn-ico fa-solid fa-circle-check"></i>
                            <span>
                                <strong>{{ __('timekeeping.table.check_in') }}</strong>
                                <small>{{ __('timekeeping.ui.recorded_at', ['time' => date('H:i', strtotime($attendanceRecord->check_in))]) }}</small>
                            </span>
                        </button>
                    @elseif($canUseAttendanceActions)
                        <button class="ci-btn active-in" type="button" wire:click="checkIn">
                            <i class="ci-btn-ico fa-regular fa-clock"></i>
                            <span>
                                <strong>{{ __('timekeeping.table.check_in') }}</strong>
                                <small>{{ __('timekeeping.attendance.check_in_hint') }}</small>
                            </span>
                        </button>
                    @else
                        <button class="ci-btn disabled" type="button" disabled>
                            <i class="ci-btn-ico fa-regular fa-clock"></i>
                            <span>
                                <strong>{{ __('timekeeping.table.check_in') }}</strong>
                                <small>{{ __('timekeeping.attendance.no_data') }}</small>
                            </span>
                        </button>
                    @endif

                    @if($attendanceRecord?->check_out)
                        <button class="ci-btn done-out" type="button" disabled>
                            <i class="ci-btn-ico fa-solid fa-circle-check"></i>
                            <span>
                                <strong>{{ __('timekeeping.table.check_out') }}</strong>
                                <small>{{ __('timekeeping.ui.recorded_at', ['time' => date('H:i', strtotime($attendanceRecord->check_out))]) }}</small>
                            </span>
                        </button>
                    @elseif($attendanceRecord?->check_in && $canUseAttendanceActions)
                        <button class="ci-btn active-out" type="button" wire:click="checkOut">
                            <i class="ci-btn-ico fa-regular fa-clock"></i>
                            <span>
                                <strong>{{ __('timekeeping.table.check_out') }}</strong>
                                <small>{{ __('timekeeping.attendance.check_out_hint') }}</small>
                            </span>
                        </button>
                    @else
                        <button class="ci-btn disabled" type="button" disabled>
                            <i class="ci-btn-ico fa-regular fa-clock"></i>
                            <span>
                                <strong>{{ __('timekeeping.table.check_out') }}</strong>
                                <small>{{ $attendanceRecord?->check_in ? __('timekeeping.attendance.no_data') : __('timekeeping.attendance.check_in_first') }}</small>
                            </span>
                        </button>
                    @endif
                </div>
            </div>

            @if($attendanceRecord)
                <div class="ci-confirm">
                    <i class="fa-solid fa-circle-check"></i>
                    {{ __('timekeeping.attendance.saved') }}
                </div>
            @endif
        </div>
    @endif

    <!-- Attendance table card -->
    <div class="att-card">
        <!-- Filter Toolbar -->
        <div class="att-toolbar">
            <div class="att-srch">
                <input wire:model.live.debounce.250ms="search" type="text" placeholder="{{ __('timekeeping.placeholders.search') }}">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            
            <div style="display:flex; flex-direction:column; gap:2px">
                <span style="font-size:10px; font-weight:600; color:var(--fa); padding:0 2px">{{ __('timekeeping.fields.date') }}</span>
                <input wire:model.live="dateFilter" class="att-date" type="date" style="height:34px; padding:0 10px; border-radius:8px">
            </div>

            <div style="display:flex; flex-direction:column; gap:2px">
                <span style="font-size:10px; font-weight:600; color:var(--fa); padding:0 2px">{{ __('timekeeping.fields.shift') }}</span>
                <select wire:model.live="shiftFilter" class="att-sel">
                    <option value="">{{ __('timekeeping.ui.all') }}</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->time_range }})</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; flex-direction:column; gap:2px">
                <span style="font-size:10px; font-weight:600; color:var(--fa); padding:0 2px">{{ __('timekeeping.filters.department') }}</span>
                <select wire:model.live="departmentFilter" class="att-sel">
                    <option value="">{{ __('timekeeping.ui.all') }}</option>
                    @foreach($depts as $deptId => $deptName)
                        <option value="{{ $deptId }}">{{ $deptName }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; flex-direction:column; gap:2px">
                <span style="font-size:10px; font-weight:600; color:var(--fa); padding:0 2px">{{ __('timekeeping.filters.area') }}</span>
                <select wire:model.live="areaFilter" class="att-sel">
                    <option value="">{{ __('timekeeping.ui.all') }}</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; flex-direction:column; gap:2px">
                <span style="font-size:10px; font-weight:600; color:var(--fa); padding:0 2px">{{ __('timekeeping.fields.status') }}</span>
                <select wire:model.live="statusFilter" class="att-sel">
                    <option value="">{{ __('timekeeping.ui.all') }}</option>
                    <option value="Đúng giờ">{{ __('timekeeping.status.on_time') }}</option>
                    <option value="Đi trễ">{{ __('timekeeping.status.late') }}</option>
                    <option value="Tăng ca">{{ __('timekeeping.status.overtime') }}</option>
                    <option value="Nghỉ phép">{{ __('timekeeping.status.leave') }}</option>
                    <option value="Vắng mặt">{{ __('timekeeping.status.absent') }}</option>
                </select>
            </div>

            <div class="att-sp"></div>

            <div class="att-fbtn" style="height:34px">
                <i class="fa-solid fa-sliders"></i> {{ __('timekeeping.ui.filters') }}
                @if($activeFiltersCount > 0)
                    <span class="att-fdot">{{ $activeFiltersCount }}</span>
                @endif
            </div>

            <button wire:click="resetFilters" class="att-rbtn" title="{{ __('timekeeping.ui.reset_filters') }}">
                <i class="fa-solid fa-rotate-right"></i>
            </button>
        </div>

        <!-- Table View -->
        <div class="att-tw">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; background:var(--po-bd2)">
                        <th style="padding:14px 12px; width:40px"><input type="checkbox"></th>
                        <th style="padding:14px 12px; width:100px">{{ __('timekeeping.table.employee_code') }}</th>
                        <th style="padding:14px 12px">{{ __('timekeeping.table.name') }}</th>
                        <th style="padding:14px 12px">{{ __('timekeeping.table.department') }}</th>
                        <th style="padding:14px 12px">{{ __('timekeeping.table.shift') }}</th>
                        <th style="padding:14px 12px; width:100px">{{ __('timekeeping.table.check_in') }}</th>
                        <th style="padding:14px 12px; width:100px">{{ __('timekeeping.table.check_out') }}</th>
                        <th style="padding:14px 12px; width:100px">{{ __('timekeeping.table.total') }}</th>
                        <th style="padding:14px 12px; width:100px">{{ __('timekeeping.table.overtime') }}</th>
                        <th style="padding:14px 12px; width:140px">{{ __('timekeeping.table.status') }}</th>
                        <th style="padding:14px 12px; width:120px; text-align:center">{{ __('timekeeping.ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($timekeepingsList as $row)
                        @php
                            $rowColor = $row->employee ? $avatarColors[$row->employee->id % count($avatarColors)] : '#267DC1';
                            $rowInitials = '';
                            if ($row->employee) {
                                $words = explode(' ', $row->employee->name);
                                if (count($words) >= 2) {
                                    $rowInitials = mb_substr($words[0], 0, 1) . mb_substr($words[count($words)-1], 0, 1);
                                } else {
                                    $rowInitials = mb_substr($row->employee->name, 0, 2);
                                }
                                $rowInitials = mb_strtoupper($rowInitials);
                            }
                            
                            // Check icon của ca làm việc
                            $isNightShift = $row->shift && (str_contains(strtolower($row->shift->name), 'tối') || str_contains(strtolower($row->shift->name), 'ca 2') || str_contains(strtolower($row->shift->name), 'ca 3'));
                        @endphp
                        <tr style="border-bottom:1px solid var(--po-bd2); color:var(--po-tx)" class="emp-row">
                            <td style="padding:12px 12px;"><input type="checkbox"></td>
                            <td style="padding:12px 12px; font-weight:700; color:var(--po-mu)">{{ $row->employee?->code }}</td>
                            <td style="padding:12px 12px;">
                                <div style="display:flex; align-items:center; gap:10px">
                                    @if($row->employee && $row->employee->avatar_url && filter_var($row->employee->avatar_url, FILTER_VALIDATE_URL))
                                        <img src="{{ $row->employee->avatar_url }}" style="width:34px; height:34px; border-radius:50%; object-fit:cover;">
                                    @elseif($row->employee && $row->employee->avatar_url)
                                        <img src="{{ asset('storage/' . $row->employee->avatar_url) }}" style="width:34px; height:34px; border-radius:50%; object-fit:cover;">
                                    @else
                                        <div style="width:34px; height:34px; border-radius:50%; background:{{ $rowColor }}1A; color:{{ $rowColor }}; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px">
                                            {{ $rowInitials }}
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight:700; color:var(--po-tx)">{{ $row->employee?->name }}</div>
                                        <div style="font-size:11px; color:var(--po-mu)">{{ $row->employee?->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:12px 12px;">{{ $row->employee?->department?->name }}</td>
                            <td style="padding:12px 12px;">
                                <div class="att-ca">
                                    <div class="att-ca-ico {{ $isNightShift ? 'att-ca-night' : 'att-ca-morning' }}">
                                        <i class="fa-solid {{ $isNightShift ? 'fa-moon' : 'fa-sun' }}"></i>
                                    </div>
                                    <div>
                                        <div style="font-size:12.5px; font-weight:700; color:var(--po-tx)">{{ $row->shift?->name }}</div>
                                        <div style="font-size:10.5px; color:var(--po-mu)">{{ $row->shift?->time_range }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:12px 12px;">
                                @if($row->check_in)
                                    @php
                                        $isLate = strcmp(date('H:i:s', strtotime($row->check_in)), '07:05:00') > 0;
                                    @endphp
                                    <span class="att-time {{ $isLate ? 'late' : 'in' }}">{{ date('H:i', strtotime($row->check_in)) }}</span>
                                @else
                                    <span class="att-time empty">--</span>
                                @endif
                            </td>
                            <td style="padding:12px 12px;">
                                @if($row->check_out)
                                    <span class="att-time out">{{ date('H:i', strtotime($row->check_out)) }}</span>
                                @else
                                    <span class="att-time empty">--</span>
                                @endif
                            </td>
                            <td style="padding:12px 12px;">
                                <span class="att-hrs">
                                    {{ \App\Filament\Resources\TimekeepingResource\Pages\ListTimekeepings::workedHours($row->check_in, $row->check_out) ?? '—' }}
                                </span>
                            </td>
                            <td style="padding:12px 12px;">
                                <span class="att-ot {{ ($row->overtime_hours && $row->overtime_hours !== '0h') ? 'has' : 'none' }}">
                                    {{ $row->overtime_hours ?: '0h' }}
                                </span>
                            </td>
                            <td style="padding:12px 12px;">
                                @if($row->status === 'Đúng giờ')
                                    <span class="st-pill st-ok">{{ __('timekeeping.status.on_time') }}</span>
                                @elseif($row->status === 'Đi trễ')
                                    <span class="st-pill st-late">{{ __('timekeeping.status.late') }}</span>
                                @elseif($row->status === 'Tăng ca')
                                    <span class="st-pill st-ot">{{ __('timekeeping.status.overtime') }}</span>
                                @elseif($row->status === 'Nghỉ phép')
                                    <span class="st-pill st-leave">{{ __('timekeeping.status.leave') }}</span>
                                @elseif($row->status === 'Vắng mặt')
                                    <span class="st-pill st-absent">{{ __('timekeeping.status.absent') }}</span>
                                @else
                                    <span class="st-pill" style="background:var(--po-bd2); color:var(--po-su);">{{ $row->status }}</span>
                                @endif
                            </td>
                            <td style="padding:12px 12px; text-align:center">
                                <div style="display:inline-flex; gap:5px">
                                    <!-- Xem chi tiết (Edit) -->
                                    <a href="{{ \App\Filament\Resources\TimekeepingResource::getUrl('edit', ['record' => $row]) }}" class="abt" title="{{ __('timekeeping.actions.view') }}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <!-- Chỉnh sửa -->
                                    <a href="{{ \App\Filament\Resources\TimekeepingResource::getUrl('edit', ['record' => $row]) }}" class="abt" title="{{ __('timekeeping.actions.edit') }}">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>

                                    <!-- Xóa chấm công -->
                                    <button type="button" @click="askConfirm('deleteTimekeeping', {{ $row->id }}, @js(__('timekeeping.ui.delete')), @js(__('timekeeping.ui.confirm_delete')), @js(__('timekeeping.ui.delete')))" class="abt" title="{{ __('timekeeping.ui.delete') }}">
                                        <i class="fa-solid fa-trash" style="color:var(--po-rd)"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="padding:32px; text-align:center;">
                                <div style="font-size:24px; color:var(--po-mu); margin-bottom:8px">
                                    <i class="fa-solid fa-magnifying-glass" style="opacity:.3"></i>
                                </div>
                                <div style="font-weight:700; color:var(--po-tx)">{{ __('timekeeping.ui.empty') }}</div>
                                <div style="font-size:12px; color:var(--po-mu)">{{ __('timekeeping.ui.empty_hint') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination -->
        @if($timekeepingsList->total() > 0)
            @php
                $currentPage = $timekeepingsList->currentPage();
                $lastPage = $timekeepingsList->lastPage();
                $pageWindow = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                    ->filter(fn ($p) => $p >= 1 && $p <= $lastPage)
                    ->unique()
                    ->sort()
                    ->values();
            @endphp
            <div class="po-footer" style="border-top:1px solid var(--po-bd2); padding:14px 18px; display:flex; justify-content:space-between; align-items:center; font-size:12.5px; color:var(--po-mu)">
                <div>
                    {{ __('timekeeping.ui.pagination', ['from' => $timekeepingsList->firstItem() ?? 0, 'to' => $timekeepingsList->lastItem() ?? 0, 'total' => number_format($timekeepingsList->total(), 0, ',', '.')]) }}
                </div>
                <div class="po-pagination" style="display:flex; align-items:center; gap:8px">
                    <span style="font-size:12.5px; color:var(--po-mu)">{{ __('common.pagination.per_page_label') }}</span>
                    <select wire:model.live="perPage" class="po-select" style="height:2.1rem;padding:0 .5rem;border-radius:.5rem; border:1px solid var(--po-bd); outline:none; background:var(--po-wh); color:var(--po-tx)">
                        @foreach([5, 10, 20, 50] as $count)
                            <option value="{{ $count }}">{{ $count }}</option>
                        @endforeach
                    </select>

                    @if($timekeepingsList->total() > 0)
                        <nav role="navigation" aria-label="{{ __('common.pagination.navigation') }}" style="display:flex; align-items:center; gap:4px">
                            {{-- Previous --}}
                            @if ($timekeepingsList->onFirstPage())
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
                            @if ($timekeepingsList->hasMorePages())
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
