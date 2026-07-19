<div class="emp-page">
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
        $dayNames = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
        $statusLabel = $attendanceRecord?->status ?: 'Chưa ghi nhận';
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
            <h1 class="emp-title">Chấm công</h1>
            <p class="emp-subtitle">Theo dõi check-in, check-out, ca làm việc và tình trạng đi làm của nhân viên</p>
        </div>
        <div>
            <button wire:click="exportTimekeepings" class="emp-btn">
                <i class="fa-solid fa-download" style="font-size: 13px;"></i>
                Xuất dữ liệu
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
                        <div class="ci-role">Nhân viên · {{ $employee->department?->name ?? '—' }}</div>
                    </div>
                </div>

                <div class="ci-meta">
                    <div class="ci-meta-item">
                        <i class="ci-meta-ico fa-regular fa-calendar"></i>
                        <div>
                            <div class="ci-meta-lbl">Ngày làm việc</div>
                            <div class="ci-meta-val">{{ $cardDate->format('d/m/Y') }} ({{ $dayNames[$cardDate->dayOfWeek] }})</div>
                        </div>
                    </div>
                    <div class="ci-meta-item">
                        <i class="ci-meta-ico fa-regular fa-clock"></i>
                        <div>
                            <div class="ci-meta-lbl">Ca làm việc</div>
                            <div class="ci-meta-val">
                                @if($attendanceRecord?->shift)
                                    {{ $attendanceRecord->shift->name }} · {{ $attendanceRecord->shift->time_range }}
                                @else
                                    Chưa phân ca
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="ci-meta-status">
                        <div class="ci-meta-lbl">Trạng thái</div>
                        <span class="ci-status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                </div>
            </div>

            <div class="ci-bottom">
                <div class="ci-time-box">
                    <div class="ci-time-lbl">Check-in</div>
                    <div class="ci-time-val in">{{ $attendanceRecord?->check_in ? date('H:i', strtotime($attendanceRecord->check_in)) : '--' }}</div>
                    <div class="ci-time-date">{{ $attendanceRecord?->check_in ? $cardDate->format('d/m/Y') : '--' }}</div>
                </div>
                <div class="ci-divider"></div>
                <div class="ci-time-box">
                    <div class="ci-time-lbl">Check-out</div>
                    <div class="ci-time-val out">{{ $attendanceRecord?->check_out ? date('H:i', strtotime($attendanceRecord->check_out)) : '--' }}</div>
                    <div class="ci-time-date">{{ $attendanceRecord?->check_out ? $cardDate->format('d/m/Y') : '--' }}</div>
                </div>
                <div class="ci-divider"></div>

                <div class="ci-actions">
                    @if($attendanceRecord?->check_in)
                        <button class="ci-btn done-in" type="button" disabled>
                            <i class="ci-btn-ico fa-solid fa-circle-check"></i>
                            <span>
                                <strong>Check-in</strong>
                                <small>Đã thực hiện lúc {{ date('H:i', strtotime($attendanceRecord->check_in)) }}</small>
                            </span>
                        </button>
                    @elseif($canUseAttendanceActions)
                        <button class="ci-btn active-in" type="button" wire:click="checkIn">
                            <i class="ci-btn-ico fa-regular fa-clock"></i>
                            <span>
                                <strong>Check-in</strong>
                                <small>Bấm để ghi nhận vào ca</small>
                            </span>
                        </button>
                    @else
                        <button class="ci-btn disabled" type="button" disabled>
                            <i class="ci-btn-ico fa-regular fa-clock"></i>
                            <span>
                                <strong>Check-in</strong>
                                <small>Chưa có dữ liệu</small>
                            </span>
                        </button>
                    @endif

                    @if($attendanceRecord?->check_out)
                        <button class="ci-btn done-out" type="button" disabled>
                            <i class="ci-btn-ico fa-solid fa-circle-check"></i>
                            <span>
                                <strong>Check-out</strong>
                                <small>Đã thực hiện lúc {{ date('H:i', strtotime($attendanceRecord->check_out)) }}</small>
                            </span>
                        </button>
                    @elseif($attendanceRecord?->check_in && $canUseAttendanceActions)
                        <button class="ci-btn active-out" type="button" wire:click="checkOut">
                            <i class="ci-btn-ico fa-regular fa-clock"></i>
                            <span>
                                <strong>Check-out</strong>
                                <small>Bấm để ghi nhận ra ca</small>
                            </span>
                        </button>
                    @else
                        <button class="ci-btn disabled" type="button" disabled>
                            <i class="ci-btn-ico fa-regular fa-clock"></i>
                            <span>
                                <strong>Check-out</strong>
                                <small>{{ $attendanceRecord?->check_in ? 'Chưa có dữ liệu' : 'Cần check-in trước' }}</small>
                            </span>
                        </button>
                    @endif
                </div>
            </div>

            @if($attendanceRecord)
                <div class="ci-confirm">
                    <i class="fa-solid fa-circle-check"></i>
                    Thời gian đã được lưu vào bảng chấm công.
                </div>
            @endif
        </div>
    @endif

    <!-- Attendance table card -->
    <div class="att-card">
        <!-- Filter Toolbar -->
        <div class="att-toolbar">
            <div class="att-srch">
                <input wire:model.live.debounce.250ms="search" type="text" placeholder="Tìm kiếm nhân viên...">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            
            <div style="display:flex; flex-direction:column; gap:2px">
                <span style="font-size:10px; font-weight:600; color:var(--fa); padding:0 2px">Ngày làm việc</span>
                <input wire:model.live="dateFilter" class="att-date" type="date" style="height:34px; padding:0 10px; border-radius:8px">
            </div>

            <div style="display:flex; flex-direction:column; gap:2px">
                <span style="font-size:10px; font-weight:600; color:var(--fa); padding:0 2px">Ca làm việc</span>
                <select wire:model.live="shiftFilter" class="att-sel">
                    <option value="">Tất cả</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->time_range }})</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; flex-direction:column; gap:2px">
                <span style="font-size:10px; font-weight:600; color:var(--fa); padding:0 2px">Phòng ban</span>
                <select wire:model.live="departmentFilter" class="att-sel">
                    <option value="">Tất cả</option>
                    @foreach($depts as $deptId => $deptName)
                        <option value="{{ $deptId }}">{{ $deptName }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; flex-direction:column; gap:2px">
                <span style="font-size:10px; font-weight:600; color:var(--fa); padding:0 2px">Khu vực</span>
                <select wire:model.live="areaFilter" class="att-sel">
                    <option value="">Tất cả</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; flex-direction:column; gap:2px">
                <span style="font-size:10px; font-weight:600; color:var(--fa); padding:0 2px">Trạng thái</span>
                <select wire:model.live="statusFilter" class="att-sel">
                    <option value="">Tất cả</option>
                    <option value="Đúng giờ">Đúng giờ</option>
                    <option value="Đi trễ">Đi trễ</option>
                    <option value="Tăng ca">Tăng ca</option>
                    <option value="Nghỉ phép">Nghỉ phép</option>
                    <option value="Vắng mặt">Vắng mặt</option>
                </select>
            </div>

            <div class="att-sp"></div>

            <div class="att-fbtn" style="height:34px">
                <i class="fa-solid fa-sliders"></i> Bộ lọc
                @if($activeFiltersCount > 0)
                    <span class="att-fdot">{{ $activeFiltersCount }}</span>
                @endif
            </div>

            <button wire:click="resetFilters" class="att-rbtn" title="Cài lại bộ lọc">
                <i class="fa-solid fa-rotate-right"></i>
            </button>
        </div>

        <!-- Table View -->
        <div class="att-tw">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; background:var(--po-bd2)">
                        <th style="padding:14px 12px; width:40px"><input type="checkbox"></th>
                        <th style="padding:14px 12px; width:100px">Mã NV</th>
                        <th style="padding:14px 12px">Họ và tên</th>
                        <th style="padding:14px 12px">Phòng ban</th>
                        <th style="padding:14px 12px">Ca làm việc</th>
                        <th style="padding:14px 12px; width:100px">Check-in</th>
                        <th style="padding:14px 12px; width:100px">Check-out</th>
                        <th style="padding:14px 12px; width:100px">Tổng giờ</th>
                        <th style="padding:14px 12px; width:100px">Tăng ca</th>
                        <th style="padding:14px 12px; width:140px">Trạng thái</th>
                        <th style="padding:14px 12px; width:120px; text-align:center">Hành động</th>
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
                                    <span class="st-pill st-ok">Đúng giờ</span>
                                @elseif($row->status === 'Đi trễ')
                                    <span class="st-pill st-late">Đi trễ</span>
                                @elseif($row->status === 'Tăng ca')
                                    <span class="st-pill st-ot">Tăng ca</span>
                                @elseif($row->status === 'Nghỉ phép')
                                    <span class="st-pill st-leave">Nghỉ phép</span>
                                @elseif($row->status === 'Vắng mặt')
                                    <span class="st-pill st-absent">Vắng mặt</span>
                                @else
                                    <span class="st-pill" style="background:var(--po-bd2); color:var(--po-su);">{{ $row->status }}</span>
                                @endif
                            </td>
                            <td style="padding:12px 12px; text-align:center">
                                <div style="display:inline-flex; gap:5px">
                                    <!-- Xem chi tiết (Edit) -->
                                    <a href="{{ \App\Filament\Resources\TimekeepingResource::getUrl('edit', ['record' => $row]) }}" class="abt" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <!-- Chỉnh sửa -->
                                    <a href="{{ \App\Filament\Resources\TimekeepingResource::getUrl('edit', ['record' => $row]) }}" class="abt" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>

                                    <!-- Xóa chấm công -->
                                    <button wire:click="deleteTimekeeping({{ $row->id }})" wire:confirm="Bạn có chắc chắn muốn xóa bản ghi chấm công này?" class="abt" title="Xóa chấm công">
                                        <i class="fa-solid fa-trash"></i>
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
                                <div style="font-weight:700; color:var(--po-tx)">Không tìm thấy dữ liệu chấm công nào</div>
                                <div style="font-size:12px; color:var(--po-mu)">Hãy thử điều chỉnh bộ lọc hoặc chọn ngày làm việc khác.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination -->
        @if($timekeepingsList->hasPages())
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
                    Hiển thị <strong>{{ $timekeepingsList->firstItem() }}</strong> đến <strong>{{ $timekeepingsList->lastItem() }}</strong> trong tổng số <strong>{{ number_format($timekeepingsList->total(), 0, ',', '.') }}</strong> bản ghi
                </div>
                <div class="po-pagination" style="display:flex; align-items:center; gap:12px">
                    <select wire:model.live="perPage" class="po-select" style="min-width:7rem;height:2.1rem;padding:0 .5rem;border-radius:.5rem; border:1px solid var(--po-bd); outline:none; background:var(--po-wh); color:var(--po-tx)">
                        <option value="10">10 dòng/trang</option>
                        <option value="20">20 dòng/trang</option>
                        <option value="50">50 dòng/trang</option>
                    </select>

                    <nav role="navigation" aria-label="Pagination Navigation" style="display:flex; align-items:center; gap:4px">
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
                </div>
            </div>
        @endif
    </div>
</div>
