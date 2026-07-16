<div class="emp-page">
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
            <h1 class="emp-title">Nhân viên</h1>
            <p class="emp-subtitle">Quản lý thông tin và hồ sơ nhân viên trong công ty</p>
        </div>
        <div class="emp-actions">
            <button wire:click="exportEmployees" class="emp-btn">
                <i class="fa-solid fa-download" style="font-size: 13px;"></i>
                Xuất dữ liệu
            </button>
            <a href="{{ \App\Filament\Resources\EmployeeResource::getUrl('create') }}" class="emp-btn emp-btn-primary">
                <i class="fa-solid fa-plus"></i>
                Thêm nhân viên
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
                <div class="py-klbl">Tổng nhân viên</div>
                <div class="py-kval">{{ $statsData['total'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-gn-s);color:var(--po-gn)">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <div class="py-klbl">Đang làm việc</div>
                <div class="py-kval">{{ $statsData['working'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-or-s);color:var(--po-or)">
                <i class="fa-solid fa-umbrella-beach"></i>
            </div>
            <div>
                <div class="py-klbl">Nghỉ phép</div>
                <div class="py-kval">{{ $statsData['leave'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-pu-s, rgba(124,58,237,.18));color:#7C3AED">
                <i class="fa-solid fa-user-minus"></i>
            </div>
            <div>
                <div class="py-klbl">Nghỉ việc</div>
                <div class="py-kval">{{ $statsData['resign'] }}</div>
            </div>
        </div>

        <div class="py-kcard">
            <div class="py-kico" style="background:var(--po-rd-s);color:var(--po-rd)">
                <i class="fa-regular fa-clock"></i>
            </div>
            <div>
                <div class="py-klbl">Hồ sơ sắp hết hạn</div>
                <div class="py-kval">{{ $statsData['exp_docs'] }}</div>
                <span style="font-size:11px; color:var(--po-mu)">Đã hết hạn hoặc còn ≤ 30 ngày</span>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="mp-bar" style="margin-bottom:14px">
        <div class="mp-srch" style="max-width: 240px !important;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input wire:model.live.debounce.250ms="search" type="text" placeholder="Tìm kiếm nhân viên...">
        </div>

        <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:11px; font-weight:700; color:var(--po-mu); text-transform:uppercase">Phòng ban</span>
            <select wire:model.live="departmentFilter" class="mp-sel">
                <option value="">Tất cả</option>
                @foreach($depts as $dept)
                    <option value="{{ $dept }}">{{ $dept }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:11px; font-weight:700; color:var(--po-mu); text-transform:uppercase">Vị trí</span>
            <select wire:model.live="positionFilter" class="mp-sel">
                <option value="">Tất cả</option>
                @foreach($positions as $pos)
                    <option value="{{ $pos }}">{{ $pos }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:11px; font-weight:700; color:var(--po-mu); text-transform:uppercase">Khu vực</span>
            <select wire:model.live="areaFilter" class="mp-sel">
                <option value="">Tất cả</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:11px; font-weight:700; color:var(--po-mu); text-transform:uppercase">Trạng thái</span>
            <select wire:model.live="statusFilter" class="mp-sel">
                <option value="">Tất cả</option>
                <option value="working">Đang làm việc</option>
                <option value="on_leave">Nghỉ phép</option>
                <option value="resigned">Nghỉ việc</option>
            </select>
        </div>

        <div class="tsp"></div>

        <!-- Chỉ báo số bộ lọc đang áp dụng -->
        <div style="background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-tx); padding:6px 12px; font-size:12.5px; border-radius:8px; display:inline-flex; align-items:center; gap:6px">
            <i class="fa-solid fa-sliders" style="color:var(--po-mu)"></i> Bộ lọc
            @if($activeFiltersCount > 0)
                <span style="background:var(--po-bl); color:#fff; width:17px; height:17px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:10px; font-weight:700">
                    {{ $activeFiltersCount }}
                </span>
            @endif
        </div>

        <button wire:click="resetFilters" class="att-rbtn" title="Cài lại bộ lọc">
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
                        <th style="padding:14px 12px; width:140px">Mã nhân viên</th>
                        <th style="padding:14px 12px">Họ và tên</th>
                        <th style="padding:14px 12px">Phòng ban</th>
                        <th style="padding:14px 12px">Vị trí</th>
                        <th style="padding:14px 12px">Khu vực</th>
                        <th style="padding:14px 12px; width:130px">Ngày vào làm</th>
                        <th style="padding:14px 12px; width:160px">Trạng thái</th>
                        <th style="padding:14px 12px; width:120px; text-align:center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employeesList as $emp)
                        @php
                            $colors = ['#1267E8', '#059669', '#D97706', '#7C3AED', '#EA580C', '#EC4899'];
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
                            <td style="padding:12px 12px;">{{ $emp->department }}</td>
                            <td style="padding:12px 12px;">{{ $emp->position }}</td>
                            <td style="padding:12px 12px;">{{ $emp->area?->name }}</td>
                            <td style="padding:12px 12px;">{{ $emp->start_date ? $emp->start_date->format('d/m/Y') : '—' }}</td>
                            <td style="padding:12px 12px;">
                                @if($emp->status === 'working')
                                    <span class="es-badge es-working">
                                        <span class="es-dot"></span> Đang làm việc
                                    </span>
                                @elseif($emp->status === 'on_leave')
                                    <span class="es-badge es-leave">
                                        <span class="es-dot"></span> Nghỉ phép
                                    </span>
                                @elseif($emp->status === 'resigned')
                                    <span class="es-badge es-resign">
                                        <span class="es-dot"></span> Nghỉ việc
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
                                    <a href="{{ \App\Filament\Resources\EmployeeResource::getUrl('edit', ['record' => $emp]) }}" class="abt" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <!-- Chỉnh sửa -->
                                    <a href="{{ \App\Filament\Resources\EmployeeResource::getUrl('edit', ['record' => $emp]) }}" class="abt" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>

                                    <!-- Xóa nhân viên -->
                                    <button wire:click="deleteEmployee({{ $emp->id }})" wire:confirm="Bạn có chắc chắn muốn xóa nhân viên này?" class="abt" title="Xóa nhân viên">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
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
                                <div style="font-weight:700; color:var(--po-tx)">Không tìm thấy nhân viên nào</div>
                                <div style="font-size:12px; color:var(--po-mu)">Hãy thử điều chỉnh bộ lọc hoặc từ khóa tìm kiếm khác.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination -->
        @if($employeesList->hasPages())
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
                    Hiển thị <strong>{{ $employeesList->firstItem() }}</strong> đến <strong>{{ $employeesList->lastItem() }}</strong> trong tổng số <strong>{{ number_format($employeesList->total(), 0, ',', '.') }}</strong> nhân viên
                </div>
                <div class="po-pagination" style="display:flex; align-items:center; gap:12px">
                    <select wire:model.live="perPage" class="po-select" style="min-width:7rem;height:2.1rem;padding:0 .5rem;border-radius:.5rem; border:1px solid var(--po-bd); outline:none; background:var(--po-wh); color:var(--po-tx)">
                        <option value="10">10 dòng/trang</option>
                        <option value="20">20 dòng/trang</option>
                        <option value="50">50 dòng/trang</option>
                    </select>

                    <nav role="navigation" aria-label="Pagination Navigation" style="display:flex; align-items:center; gap:4px">
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
                </div>
            </div>
        @endif
    </div>
</div>
