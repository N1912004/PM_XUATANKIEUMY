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

        $avatarColors = ['#1267E8', '#059669', '#D97706', '#7C3AED', '#EA580C', '#EC4899'];
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
            <h1 class="emp-title">Nghỉ phép &amp; Tăng ca</h1>
            <p class="emp-subtitle">Quản lý yêu cầu nghỉ phép, làm thêm giờ và trạng thái phê duyệt của nhân viên</p>
        </div>
        <div class="emp-actions">
            <button wire:click="exportLeaveOvertimes" class="emp-btn">
                <i class="fa-solid fa-download" style="font-size: 13px;"></i>
                Xuất dữ liệu
            </button>
            <a href="{{ \App\Filament\Resources\LeaveOvertimeResource::getUrl('create') }}" class="emp-btn emp-btn-primary">
                <i class="fa-solid fa-plus"></i>
                Tạo yêu cầu
            </a>
        </div>
    </div>

    <!-- Tabs chuyển đổi -->
    <div class="lv-tabs">
        <button wire:click="switchTab('all')" class="lv-tab {{ $activeTab === 'all' ? 'active' : '' }}">
            Tất cả yêu cầu
        </button>
        <button wire:click="switchTab('history')" class="lv-tab {{ $activeTab === 'history' ? 'active' : '' }}">
            Lịch sử phê duyệt
        </button>
    </div>

    <!-- Main Card containing filter toolbar and table -->
    <div class="tcard">
        <!-- Filter Toolbar -->
        <div class="lv-bar">
            <div class="lv-srch">
                <input wire:model.live.debounce.250ms="search" type="text" placeholder="Tìm kiếm nhân viên...">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            
            <div class="lv-date">
                <i class="fa-regular fa-calendar" style="color:var(--fa);font-size:12px"></i>
                <input wire:model.live="monthFilter" type="month" style="border:none;outline:none;font-size:12px;color:var(--tx);background:transparent;cursor:pointer">
            </div>

            <select wire:model.live="departmentFilter" class="lv-sel">
                <option value="">Phòng ban</option>
                @foreach($depts as $dept)
                    <option value="{{ $dept }}">{{ $dept }}</option>
                @endforeach
            </select>

            <select wire:model.live="typeFilter" class="lv-sel">
                <option value="">Loại yêu cầu</option>
                @foreach($types as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>

            @if($activeTab === 'all')
                <select wire:model.live="statusFilter" class="lv-sel">
                    <option value="">Trạng thái</option>
                    <option value="pending">Chờ duyệt</option>
                    <option value="approved">Đã duyệt</option>
                    <option value="rejected">Từ chối</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            @else
                <select wire:model.live="statusFilter" class="lv-sel">
                    <option value="">Trạng thái</option>
                    <option value="approved">Đã duyệt</option>
                    <option value="rejected">Từ chối</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            @endif

            <div class="lv-sp"></div>

            <button class="lv-fbtn" style="height:34px">
                <i class="fa-solid fa-sliders"></i> Bộ lọc
                @if($activeFiltersCount > 0)
                    <span class="lv-fdot">{{ $activeFiltersCount }}</span>
                @endif
            </button>

            <button wire:click="resetFilters" class="lv-rbtn" title="Cài lại bộ lọc">
                <i class="fa-solid fa-rotate-right"></i>
            </button>
        </div>

        <!-- Table View -->
        <div class="tw">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; background:var(--po-bd2)">
                        <th style="padding:14px 12px; width:40px"><input type="checkbox"></th>
                        <th style="padding:14px 12px; width:100px">Mã NV</th>
                        <th style="padding:14px 12px">Họ và tên</th>
                        <th style="padding:14px 12px">Phòng ban</th>
                        <th style="padding:14px 12px">Loại yêu cầu</th>
                        <th style="padding:14px 12px">Thời gian / Ngày áp dụng</th>
                        <th style="padding:14px 12px; width:110px">Số ngày / Số giờ</th>
                        <th style="padding:14px 12px">Lý do</th>
                        <th style="padding:14px 12px">Người duyệt</th>
                        <th style="padding:14px 12px; width:130px">Trạng thái</th>
                        <th style="padding:14px 12px; width:120px; text-align:center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itemsList as $row)
                        @php
                            $rowColor = $row->employee ? $avatarColors[$row->employee->id % count($avatarColors)] : '#1267E8';
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
                            if (str_contains($row->type, 'Nghỉ phép năm')) $typeClass = 'req-annual';
                            elseif (str_contains($row->type, 'Nghỉ phép bệnh')) $typeClass = 'req-sick';
                            elseif (str_contains($row->type, 'Nghỉ không lương')) $typeClass = 'req-unpaid';
                            elseif (str_contains($row->type, 'Tăng ca')) $typeClass = 'req-ot';
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
                            <td style="padding:12px 12px;">{{ $row->employee?->department }}</td>
                            <td style="padding:12px 12px;">
                                <span class="req-badge {{ $typeClass }}">{{ $row->type }}</span>
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
                                        (Thứ {{ ['Chủ Nhật', 'Hai', 'Ba', 'Tư', 'Năm', 'Sáu', 'Bảy'][$row->start_date->dayOfWeek] }})
                                    </div>
                                @endif
                            </td>
                            <td style="padding:12px 12px; font-weight:700; color:var(--po-tx)">{{ $row->duration_text }}</td>
                            <td style="padding:12px 12px; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap" title="{{ $row->reason }}">{{ $row->reason ?: '—' }}</td>
                            <td style="padding:12px 12px;">
                                @if($row->approver)
                                    <div style="display:flex; align-items:center; gap:8px">
                                        @if($row->approver->avatar_url && filter_var($row->approver->avatar_url, FILTER_VALIDATE_URL))
                                            <img src="{{ $row->approver->avatar_url }}" style="width:26px; height:26px; border-radius:50%; object-fit:cover;">
                                        @elseif($row->approver->avatar_url)
                                            <img src="{{ asset('storage/' . $row->approver->avatar_url) }}" style="width:26px; height:26px; border-radius:50%; object-fit:cover;">
                                        @else
                                            <div style="width:26px; height:26px; border-radius:50%; background:{{ $appColor }}1A; color:{{ $appColor }}; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:10px">
                                                {{ $appInitials }}
                                            </div>
                                        @endif
                                        <div>
                                            <div style="font-weight:600; color:var(--po-tx); font-size:12px">{{ $row->approver->name }}</div>
                                            <div style="font-size:10px; color:var(--po-mu)">{{ $row->approver->position }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span style="color:var(--po-fa)">Chưa có</span>
                                @endif
                            </td>
                            <td style="padding:12px 12px;">
                                @if($row->status === 'pending')
                                    <span class="st-pill st-late">Chờ duyệt</span>
                                @elseif($row->status === 'approved')
                                    <span class="st-pill st-ok">Đã duyệt</span>
                                @elseif($row->status === 'rejected')
                                    <span class="st-pill st-absent">Từ chối</span>
                                @elseif($row->status === 'cancelled')
                                    <span class="st-pill" style="background:var(--po-bd2); color:var(--po-su); border-color:var(--po-bd)">Đã hủy</span>
                                @else
                                    <span class="st-pill" style="background:var(--po-bd2); color:var(--po-su);">{{ $row->status }}</span>
                                @endif
                            </td>
                            <td style="padding:12px 12px; text-align:center">
                                <div style="display:inline-flex; gap:5px">
                                    <!-- Xem chi tiết (Edit) -->
                                    <a href="{{ \App\Filament\Resources\LeaveOvertimeResource::getUrl('edit', ['record' => $row]) }}" class="abt" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <!-- Chỉnh sửa -->
                                    <a href="{{ \App\Filament\Resources\LeaveOvertimeResource::getUrl('edit', ['record' => $row]) }}" class="abt" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>

                                    <!-- Xóa yêu cầu -->
                                    <button wire:click="deleteLeaveOvertime({{ $row->id }})" wire:confirm="Bạn có chắc chắn muốn xóa yêu cầu này?" class="abt" title="Xóa yêu cầu">
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
                                <div style="font-weight:700; color:var(--po-tx)">Không tìm thấy yêu cầu nghỉ phép hay tăng ca nào</div>
                                <div style="font-size:12px; color:var(--po-mu)">Hãy thử điều chỉnh bộ lọc hoặc từ khóa tìm kiếm khác.</div>
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
                    Hiển thị <strong>{{ $itemsList->firstItem() }}</strong> đến <strong>{{ $itemsList->lastItem() }}</strong> trong tổng số <strong>{{ number_format($itemsList->total(), 0, ',', '.') }}</strong> yêu cầu
                </div>
                <div class="po-pagination" style="display:flex; align-items:center; gap:12px">
                    <select wire:model.live="perPage" class="po-select" style="min-width:7rem;height:2.1rem;padding:0 .5rem;border-radius:.5rem; border:1px solid var(--po-bd); outline:none; background:var(--po-wh); color:var(--po-tx)">
                        <option value="10">10 dòng/trang</option>
                        <option value="20">20 dòng/trang</option>
                        <option value="50">50 dòng/trang</option>
                    </select>

                    @if($itemsList->hasPages())
                    <nav role="navigation" aria-label="Pagination Navigation" style="display:flex; align-items:center; gap:4px">
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
