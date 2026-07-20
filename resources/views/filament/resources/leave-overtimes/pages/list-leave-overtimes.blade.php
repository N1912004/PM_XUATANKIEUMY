<div class="emp-page">
    @include('filament.resources.leave-overtimes.partials.styles')

    @php
        $itemsList = $this->leaveOvertimes();
        $depts = $this->getDepartments();
        $types = $this->getTypes();

        $activeFiltersCount = 0;
        if ($this->search !== '') $activeFiltersCount++;
        if ($this->monthFilter !== '') $activeFiltersCount++;
        if ($this->departmentFilter !== '') $activeFiltersCount++;
        if ($this->typeFilter !== '') $activeFiltersCount++;
        if ($this->statusFilter !== '') $activeFiltersCount++;

        $avatarColors = ['#267DC1', '#059669', '#D97706', '#7C3AED', '#EA580C', '#EC4899'];
    @endphp

    @if (session()->has('message'))
        <div style="background:var(--po-gn-s); color:var(--po-gn-t); padding:12px 16px; border-radius:8px; border:1px solid var(--po-gn); margin-bottom:16px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('message') }}
        </div>
    @endif

    <!-- Header Section -->
    <div class="emp-head" style="margin-bottom: 14px;">
        <div>
            <h1 class="emp-title">{{ __('leave_overtime.navigation') }}</h1>
            <p class="emp-subtitle">{{ __('leave_overtime.ui.subtitle') }}</p>
        </div>
        <div class="emp-actions">
            <button wire:click="exportLeaveOvertimes" class="emp-btn">
                <i class="fa-solid fa-download" style="font-size: 13px;"></i>
                {{ __('leave_overtime.actions.export') }}
            </button>
            <a href="{{ \App\Filament\Resources\LeaveOvertimeResource::getUrl('create') }}" class="emp-btn emp-btn-primary">
                <i class="fa-solid fa-plus"></i>
                {{ __('leave_overtime.actions.create') }}
            </a>
        </div>
    </div>

    <!-- Tabs chuyển đổi -->
    <div class="lv-tabs">
        <button wire:click="switchTab('all')" class="lv-tab {{ $activeTab === 'all' ? 'active' : '' }}">
            {{ __('leave_overtime.tabs.all') }}
        </button>
        <button wire:click="switchTab('history')" class="lv-tab {{ $activeTab === 'history' ? 'active' : '' }}">
            {{ __('leave_overtime.tabs.history') }}
        </button>
    </div>

    <!-- Main Card containing filter toolbar and table -->
    <div class="tcard">
        <!-- Filter Toolbar -->
        <div class="lv-bar">
            <div class="lv-srch">
                <input wire:model.live.debounce.250ms="search" type="text" placeholder="{{ __('leave_overtime.ui.search_placeholder') }}">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            
            <div class="lv-date">
                <i class="fa-regular fa-calendar" style="color:var(--fa);font-size:12px"></i>
                <input wire:model.live="monthFilter" type="month" style="border:none;outline:none;font-size:12px;color:var(--tx);background:transparent;cursor:pointer">
            </div>

            <select wire:model.live="departmentFilter" class="lv-sel">
                <option value="">{{ __('leave_overtime.ui.department') }}</option>
                @foreach($depts as $deptId => $deptName)
                    <option value="{{ $deptId }}">{{ $deptName }}</option>
                @endforeach
            </select>

            <select wire:model.live="typeFilter" class="lv-sel">
                <option value="">{{ __('leave_overtime.fields.type') }}</option>
                @foreach($types as $typeId => $typeName)
                    <option value="{{ $typeId }}">{{ $typeName }}</option>
                @endforeach
            </select>

            @if($activeTab === 'all')
                <select wire:model.live="statusFilter" class="lv-sel">
                    <option value="">{{ __('leave_overtime.ui.status') }}</option>
                    <option value="pending">{{ __('leave_overtime.status.pending') }}</option>
                    <option value="approved">{{ __('leave_overtime.status.approved') }}</option>
                    <option value="rejected">{{ __('leave_overtime.status.rejected') }}</option>
                    <option value="cancelled">{{ __('leave_overtime.status.cancelled') }}</option>
                </select>
            @else
                <select wire:model.live="statusFilter" class="lv-sel">
                    <option value="">{{ __('leave_overtime.ui.status') }}</option>
                    <option value="approved">{{ __('leave_overtime.status.approved') }}</option>
                    <option value="rejected">{{ __('leave_overtime.status.rejected') }}</option>
                    <option value="cancelled">{{ __('leave_overtime.status.cancelled') }}</option>
                </select>
            @endif

            <div class="lv-sp"></div>

            <button class="lv-fbtn" style="height:34px">
                <i class="fa-solid fa-sliders"></i> {{ __('leave_overtime.ui.filters') }}
                @if($activeFiltersCount > 0)
                    <span class="lv-fdot">{{ $activeFiltersCount }}</span>
                @endif
            </button>

            <button wire:click="resetFilters" class="lv-rbtn" title="{{ __('leave_overtime.ui.reset_filters') }}">
                <i class="fa-solid fa-rotate-right"></i>
            </button>
        </div>

        <!-- Table View -->
        <div class="tw">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; background:var(--po-bd2)">
                        <th style="padding:14px 12px; width:40px"><input type="checkbox" aria-label="{{ __('leave_overtime.ui.select_all') }}"></th>
                        <th style="padding:14px 12px; width:100px">{{ __('leave_overtime.ui.employee_code') }}</th>
                        <th style="padding:14px 12px">{{ __('leave_overtime.ui.full_name') }}</th>
                        <th style="padding:14px 12px">{{ __('leave_overtime.ui.department') }}</th>
                        <th style="padding:14px 12px">{{ __('leave_overtime.fields.type') }}</th>
                        <th style="padding:14px 12px">{{ __('leave_overtime.ui.applied_date') }}</th>
                        <th style="padding:14px 12px; width:110px">{{ __('leave_overtime.fields.duration') }}</th>
                        <th style="padding:14px 12px">{{ __('leave_overtime.ui.reason') }}</th>
                        <th style="padding:14px 12px">{{ __('leave_overtime.fields.approver') }}</th>
                        <th style="padding:14px 12px; width:130px">{{ __('leave_overtime.ui.status') }}</th>
                        <th style="padding:14px 12px; width:120px; text-align:center">{{ __('leave_overtime.ui.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itemsList as $row)
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

                            // Tạo avatar cho người duyệt
                            $appColor = $row->approver ? $avatarColors[$row->approver->id % count($avatarColors)] : '#64748B';
                            $appInitials = '';
                            if ($row->approver) {
                                $appWords = explode(' ', $row->approver->name);
                                if (count($appWords) >= 2) {
                                    $appInitials = mb_substr($appWords[0], 0, 1) . mb_substr($appWords[count($appWords)-1], 0, 1);
                                } else {
                                    $appInitials = mb_substr($row->approver->name, 0, 2);
                                }
                                $appInitials = mb_strtoupper($appInitials);
                            }

                            // Style loại yêu cầu
                            $typeClass = 'req-other';
                            $typeName = $row->leaveType?->name ?? '';
                            if (str_contains($typeName, 'Nghỉ phép năm')) $typeClass = 'req-annual';
                            elseif (str_contains($typeName, 'Nghỉ phép bệnh')) $typeClass = 'req-sick';
                            elseif (str_contains($typeName, 'Nghỉ không lương')) $typeClass = 'req-unpaid';
                            elseif (str_contains($typeName, 'Tăng ca')) $typeClass = 'req-ot';
                        @endphp
                        <tr style="border-bottom:1px solid var(--po-bd2); color:var(--po-tx)" class="emp-row">
                            <td style="padding:12px 12px;"><input type="checkbox" aria-label="{{ __('leave_overtime.ui.select_request', ['code' => $row->employee?->code]) }}"></td>
                            <td style="padding:12px 12px; font-weight:700; color:var(--po-mu)">{{ $row->employee?->code }}</td>
                            <td style="padding:12px 12px;">
                                <div style="display:flex; align-items:center; gap:10px">
                                    @if($row->employee && $row->employee->avatar_url && filter_var($row->employee->avatar_url, FILTER_VALIDATE_URL))
                                            <img src="{{ $row->employee->avatar_url }}" alt="{{ $row->employee->name }}" style="width:34px; height:34px; border-radius:50%; object-fit:cover;">
                                    @elseif($row->employee && $row->employee->avatar_url)
                                            <img src="{{ asset('storage/' . $row->employee->avatar_url) }}" alt="{{ $row->employee->name }}" style="width:34px; height:34px; border-radius:50%; object-fit:cover;">
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
                                <span class="req-badge {{ $typeClass }}">{{ $row->leaveType?->name }}</span>
                            </td>
                            <td style="padding:12px 12px;">
                                @php
                                    $dateStr = $row->start_date?->format('d/m/Y');
                                    if ($row->end_date && ! $row->end_date->equalTo($row->start_date)) {
                                        $dateStr .= ' - ' . $row->end_date->format('d/m/Y');
                                    }
                                @endphp
                                <div style="font-weight:600; color:var(--po-tx)">{{ $dateStr }}</div>
                                @if($row->start_date)
                                    <div style="font-size:10.5px; color:var(--po-mu); margin-top:2px">
                                        ({{ __('leave_overtime.ui.weekday_prefix') }} {{ __('leave_overtime.weekdays.' . $row->start_date->dayOfWeek) }})
                                    </div>
                                @endif
                            </td>
                            <td style="padding:12px 12px; font-weight:700; color:var(--po-tx)">{{ $row->duration_text }}</td>
                            <td style="padding:12px 12px; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap" title="{{ $row->reason }}">{{ $row->reason }}</td>
                            <td style="padding:12px 12px;">
                                @if($row->approver)
                                    <div style="display:flex; align-items:center; gap:8px">
                                        @if($row->approver->avatar_url && filter_var($row->approver->avatar_url, FILTER_VALIDATE_URL))
                                            <img src="{{ $row->approver->avatar_url }}" alt="{{ $row->approver->name }}" style="width:26px; height:26px; border-radius:50%; object-fit:cover;">
                                        @elseif($row->approver->avatar_url)
                                            <img src="{{ asset('storage/' . $row->approver->avatar_url) }}" alt="{{ $row->approver->name }}" style="width:26px; height:26px; border-radius:50%; object-fit:cover;">
                                        @else
                                            <div style="width:26px; height:26px; border-radius:50%; background:{{ $appColor }}1A; color:{{ $appColor }}; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:10px">
                                                {{ $appInitials }}
                                            </div>
                                        @endif
                                        <div>
                                            <div style="font-weight:600; color:var(--po-tx); font-size:12px">{{ $row->approver->name }}</div>
                                            <div style="font-size:10px; color:var(--po-mu)">{{ $row->approver->position?->name }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span style="color:var(--po-fa)">{{ __('leave_overtime.ui.none') }}</span>
                                @endif
                            </td>
                            <td style="padding:12px 12px;">
                                @if($row->status === 'pending')
                                    <span class="st-pill st-late">{{ __('leave_overtime.status.pending') }}</span>
                                @elseif($row->status === 'approved')
                                    <span class="st-pill st-ok">{{ __('leave_overtime.status.approved') }}</span>
                                @elseif($row->status === 'rejected')
                                    <span class="st-pill st-absent">{{ __('leave_overtime.status.rejected') }}</span>
                                @elseif($row->status === 'cancelled')
                                    <span class="st-pill" style="background:var(--po-bd2); color:var(--po-su); border-color:var(--po-bd)">{{ __('leave_overtime.status.cancelled') }}</span>
                                @else
                                    <span class="st-pill" style="background:var(--po-bd2); color:var(--po-su);">{{ $row->status }}</span>
                                @endif
                            </td>
                            <td style="padding:12px 12px; text-align:center">
                                <div style="display:inline-flex; gap:5px">
                                    <!-- Xem chi tiết (Edit) -->
                                    <a href="{{ \App\Filament\Resources\LeaveOvertimeResource::getUrl('edit', ['record' => $row]) }}" class="abt" title="{{ __('leave_overtime.actions.view') }}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <!-- Chỉnh sửa -->
                                    <a href="{{ \App\Filament\Resources\LeaveOvertimeResource::getUrl('edit', ['record' => $row]) }}" class="abt" title="{{ __('leave_overtime.actions.edit') }}">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>

                                    <!-- Xóa yêu cầu -->
                                    <button wire:click="deleteLeaveOvertime({{ $row->id }})" wire:confirm="{{ __('leave_overtime.ui.confirm_delete') }}" class="abt" title="{{ __('leave_overtime.ui.delete') }}">
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
                                <div style="font-weight:700; color:var(--po-tx)">{{ __('leave_overtime.ui.empty') }}</div>
                                <div style="font-size:12px; color:var(--po-mu)">{{ __('leave_overtime.ui.empty_hint') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination (hiển thị cả khi chưa đủ 1 trang để thấy tổng số dòng) -->
        @if($itemsList->total() > 0)
            @php
                $currentPage = $itemsList->currentPage();
                $lastPage = $itemsList->lastPage();
                $pageWindow = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                    ->filter(fn ($p) => $p >= 1 && $p <= $lastPage)
                    ->unique()
                    ->sort()
                    ->values();
            @endphp
            <div class="po-footer" style="border-top:1px solid var(--po-bd2); padding:14px 18px; display:flex; justify-content:space-between; align-items:center; font-size:12.5px; color:var(--po-mu)">
                <div>
                    {!! __('leave_overtime.ui.pagination', ['from' => '<strong>'.$itemsList->firstItem().'</strong>', 'to' => '<strong>'.$itemsList->lastItem().'</strong>', 'total' => '<strong>'.number_format($itemsList->total(), 0, ',', '.').'</strong>']) !!}
                </div>
                <div class="po-pagination" style="display:flex; align-items:center; gap:12px">
                    <select wire:model.live="perPage" class="po-select" style="min-width:7rem;height:2.1rem;padding:0 .5rem;border-radius:.5rem; border:1px solid var(--po-bd); outline:none; background:var(--po-wh); color:var(--po-tx)">
                        <option value="10">{{ __('leave_overtime.ui.rows_per_page', ['count' => 10]) }}</option>
                        <option value="20">{{ __('leave_overtime.ui.rows_per_page', ['count' => 20]) }}</option>
                        <option value="50">{{ __('leave_overtime.ui.rows_per_page', ['count' => 50]) }}</option>
                    </select>

                    @if($itemsList->hasPages())
                    <nav role="navigation" aria-label="{{ __('leave_overtime.ui.pagination_navigation') }}" style="display:flex; align-items:center; gap:4px">
                        {{-- Previous --}}
                        @if ($itemsList->onFirstPage())
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
                        @if ($itemsList->hasMorePages())
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
</div>
