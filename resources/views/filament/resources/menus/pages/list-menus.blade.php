<div class="emp-page">
    @include('filament.resources.menus.partials.styles')

    @php
        $stats = $this->getStats();
        $menusList = $this->menus();
        $kitchens = $this->getKitchens();
        $shifts = $this->getShifts();
        $recipes = $this->getRecipes();
    @endphp

    @if (session()->has('message'))
        <div style="background:#ECFDF5; color:#065F46; padding:12px 16px; border-radius:8px; border:1px solid #A7F3D0; margin-bottom:16px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px">
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
                <h1 class="emp-title">Lập thực đơn</h1>
                <p class="emp-subtitle">Quản lý thực đơn tuần / ngày theo từng công ty khách và ca phục vụ</p>
            </div>
            <div class="emp-actions">
                <button wire:click="loadWeekMenu({{ $kitchens->first()?->id ?? 1 }}, '{{ now()->startOfWeek()->toDateString() }}')" class="emp-btn">
                    <i class="fa-regular fa-calendar-week"></i>
                    Tạo thực đơn tuần
                </button>
                <button wire:click="loadDayMenu({{ $kitchens->first()?->id ?? 1 }}, '{{ now()->toDateString() }}')" class="emp-btn emp-btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    Tạo thực đơn ngày
                </button>
            </div>
        </div>

        <!-- 4 KPIs Stats -->
        <div class="mp-krow" style="margin-bottom: 16px;">
            <div class="mp-kcard">
                <div class="mp-kico" style="background:#EBF3FF; color:var(--po-bl)"><i class="fa-regular fa-calendar-week"></i></div>
                <div>
                    <div class="mp-klbl">Thực đơn tuần đang chạy</div>
                    <div class="mp-kval">{{ $stats['total_active_weeks'] }}</div>
                </div>
            </div>
            <div class="mp-kcard">
                <div class="mp-kico" style="background:#ECFDF5; color:var(--po-gn)"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <div class="mp-klbl">Đã gửi khách tháng này</div>
                    <div class="mp-kval">{{ $stats['sent_month'] }}</div>
                </div>
            </div>
            <div class="mp-kcard">
                <div class="mp-kico" style="background:#FFF7ED; color:var(--po-or)"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div>
                    <div class="mp-klbl">Chờ xác nhận</div>
                    <div class="mp-kval">{{ $stats['pending'] }}</div>
                </div>
            </div>
            <div class="mp-kcard">
                <div class="mp-kico" style="background:#FEF3C7; color:#78350F"><i class="fa-solid fa-lock"></i></div>
                <div>
                    <div class="mp-klbl">Đã chốt tháng này</div>
                    <div class="mp-kval">{{ $stats['locked_month'] }}</div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mp-bar" style="margin-bottom: 14px;">
            <div class="mp-srch">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input wire:model.live.debounce.250ms="search" type="text" placeholder="Tìm kiếm theo công ty, tuần...">
            </div>
            <select wire:model.live="typeFilter" class="mp-sel">
                <option value="">Tất cả loại</option>
                <option value="week">Thực đơn tuần</option>
                <option value="day">Thực đơn ngày</option>
            </select>
            <select wire:model.live="statusFilter" class="mp-sel">
                <option value="">Tất cả trạng thái</option>
                <option value="draft">Nháp</option>
                <option value="sent">Đã gửi khách</option>
                <option value="locked">Đã chốt</option>
            </select>
            <select wire:model.live="monthFilter" class="mp-sel">
                @foreach($this->getMonthOptions() as $ym => $label)
                    <option value="{{ $ym }}">{{ $label }}</option>
                @endforeach
            </select>
            <div class="tsp"></div>
            <button wire:click="resetFilters" class="att-rbtn" title="Cài lại bộ lọc">
                <i class="fa-solid fa-rotate-right"></i>
            </button>
        </div>

        <!-- Locked Edit Warning Panel -->
        <div class="mp-locked-panel" style="margin-bottom: 16px;">
            <div class="mp-locked-panel-hd">
                <div>
                    <div class="mp-locked-panel-title">
                        <i class="fa-solid fa-lock-open"></i>
                        Thực đơn đã chốt vẫn có thể chỉnh sửa
                    </div>
                    <div class="mp-locked-panel-sub">
                        Chọn tuần đã chốt, chọn ngày cần sửa, cập nhật món ăn rồi bấm Lưu thay đổi. Trạng thái vẫn giữ là Đã chốt.
                    </div>
                </div>
                <span class="ms-locked">Đã chốt</span>
            </div>
            <div class="mp-locked-tools">
                <div class="dv-field" style="min-width:260px">
                    <label>Danh sách thực đơn đã chốt</label>
                    <select class="dv-sel" id="mpLockedMenuSelect">
                        @php
                            $lockedMenus = $this->getLockedMenus();
                        @endphp
                        @forelse($lockedMenus as $lm)
                            <option value="{{ $lm->kitchen_id }}_{{ $lm->date->toDateString() }}">
                                Ngày {{ $lm->date->format('d/m/Y') }} · {{ $lm->kitchen?->name }}
                            </option>
                        @empty
                            <option value="">Không có thực đơn đã chốt</option>
                        @endforelse
                    </select>
                </div>
                <button type="button" onclick="const val = document.getElementById('mpLockedMenuSelect').value.split('_'); if(val.length === 2) { @this.loadDayMenu(val[0], val[1]); }" class="btn btn-p">
                    <i class="fa-solid fa-pen-to-square"></i> Mở chỉnh sửa
                </button>
                <button type="button" onclick="const val = document.getElementById('mpLockedMenuSelect').value.split('_'); if(val.length === 2) { @this.loadWeekMenu(val[0], val[1]); }" class="btn">
                    <i class="fa-regular fa-calendar-week"></i> Xem thực đơn tuần
                </button>
            </div>
        </div>

        <!-- List cards -->
        <div class="mp-card-list">
            @forelse($menusList as $row)
                <div wire:click="{{ $row['type'] === 'week' ? "loadWeekMenu({$row['kitchen_id']}, '{$row['start_date']}')" : "loadDayMenu({$row['kitchen_id']}, '{$row['start_date']}')" }}" class="mp-item">
                    <div class="mp-item-ico" style="{{ $row['type'] === 'week' ? 'background:#EBF3FF;color:var(--po-bl)' : 'background:#F5F3FF;color:var(--po-pu)' }}">
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
                            <span class="mp-item-tag"><i class="fa-regular fa-clock"></i>Áp dụng: {{ date('d/m/Y', strtotime($row['start_date'])) }}</span>
                        </div>
                    </div>
                    <div class="mp-item-right" wire:click.stop>
                        @if($row['status'] === 'locked')
                            <span class="ms-locked">Đã chốt</span>
                        @elseif($row['status'] === 'sent')
                            <span class="ms-sent">Đã gửi khách</span>
                        @else
                            <span class="ms-draft">Nháp</span>
                        @endif

                        <div class="mp-item-actions" style="margin-top: 8px">
                            <!-- Xem chi tiết -->
                            <button wire:click="{{ $row['type'] === 'week' ? "loadWeekMenu({$row['kitchen_id']}, '{$row['start_date']}')" : "loadDayMenu({$row['kitchen_id']}, '{$row['start_date']}')" }}" class="abt" title="Chỉnh sửa">
                                <i class="fa-solid fa-pencil"></i>
                            </button>
                            <!-- Xuất Excel -->
                            <button class="abt" title="Xuất Excel" wire:click="exportMenus({{ $row['kitchen_id'] }}, '{{ $row['start_date'] }}', '{{ $row['end_date'] }}')">
                                <i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div style="background:#fff; border:1px solid var(--po-bd); border-radius:12px; padding:40px; text-align:center; color:var(--po-mu)">
                    <i class="fa-regular fa-calendar" style="font-size:32px; opacity:.3; margin-bottom:8px"></i>
                    <div style="font-weight:700; color:var(--po-tx)">Không tìm thấy thực đơn nào</div>
                    <div style="font-size:12px">Hãy thử đổi tháng hoặc từ khóa tìm kiếm.</div>
                </div>
            @endforelse
        </div>

        @if($menusList->hasPages() || $menusList->total() > 10)
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-top:14px; font-size:12px; color:var(--po-mu)">
                <div>
                    Hiển thị {{ $menusList->firstItem() ?? 0 }}-{{ $menusList->lastItem() ?? 0 }} trên {{ $menusList->total() }} thực đơn
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <select wire:model.live="perPage" style="height:30px; border:1px solid var(--po-bd); border-radius:6px; padding:0 8px; font-size:12px; background:transparent;">
                        <option value="10">10 / trang</option>
                        <option value="20">20 / trang</option>
                        <option value="50">50 / trang</option>
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
                <h1 class="emp-title">Lập thực đơn tuần mới</h1>
                <p class="emp-subtitle">Thiết lập món ăn và công suất suất ăn cho từng thứ trong tuần</p>
            </div>
            <div class="emp-actions">
                <button wire:click="switchView('list')" class="emp-btn"><i class="fa-solid fa-arrow-left"></i> Quay lại</button>
                <button type="button" class="emp-btn" wire:click="exportWeekForm"><i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i> Xuất Excel</button>
                <button wire:click="saveWeekMenu('draft')" class="emp-btn"><i class="fa-regular fa-floppy-disk"></i> Lưu nháp</button>
                <button wire:click="saveWeekMenu('sent')" class="emp-btn"><i class="fa-regular fa-paper-plane"></i> Gửi xác nhận</button>
                <button wire:click="saveWeekMenu('locked')" class="emp-btn emp-btn-primary"><i class="fa-solid fa-lock"></i> Chốt thực đơn</button>
            </div>
        </div>

        <!-- Form settings -->
        <div class="tcard" style="padding:16px; margin-bottom:14px; background:#FAFBFC; display:flex; gap:12px; flex-wrap:wrap">
            <div class="field" style="min-width:240px">
                <label>Nhà ăn / Bếp ăn *</label>
                <select wire:model="weekKitchenId" class="ctrl" required>
                    @foreach($kitchens as $kit)
                        <option value="{{ $kit->id }}">{{ $kit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="min-width:200px">
                <label>Ngày bắt đầu tuần (Thứ 2) *</label>
                <input wire:model="weekStartDate" type="date" class="ctrl" required>
            </div>
        </div>

        <!-- Grid Matrix Table -->
        <div class="tcard" style="overflow-x:auto">
            <table class="grid-table" style="width:100%; border-collapse:collapse; min-width:900px">
                <thead>
                    <tr style="background:#1267E8; color:#fff">
                        <th style="padding:12px 14px; text-align:left; width:120px">CA / THỨ</th>
                        @for($d = 0; $d < 6; $d++)
                            @php
                                $dayDate = \Illuminate\Support\Carbon::parse($this->weekStartDate)->addDays($d);
                            @endphp
                            <th style="padding:12px 14px; text-align:center">
                                Thứ {{ $d + 2 }}
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
                            <td style="padding:14px; font-weight:800; background:#F8FAFC; color:var(--po-tx)">
                                {{ $shift->name }}
                                <div style="font-size:10px; font-weight:500; color:var(--po-mu); margin-top:2px">
                                    {{ $shift->time_range }}
                                </div>
                            </td>
                            @for($d = 0; $d < 6; $d++)
                                <td style="padding:10px; text-align:center; background:#fff">
                                    <div style="display:flex; flex-direction:column; gap:6px">
                                        <!-- Món ăn select -->
                                        <select wire:model="weekGrid.{{ $d }}.{{ $shift->id }}" class="ctrl" style="font-size:12px; height:32px">
                                            <option value="">Chọn món...</option>
                                            @foreach($recipes as $rec)
                                                <option value="{{ $rec->id }}">{{ $rec->name }}</option>
                                            @endforeach
                                        </select>
                                        <!-- Suất ăn input -->
                                        <div style="display:flex; align-items:center; gap:4px">
                                            <input wire:model="weekPortions.{{ $d }}.{{ $shift->id }}" type="number" class="ctrl" style="font-size:11.5px; height:28px; text-align:center; padding:0 4px" placeholder="Suất">
                                            <span style="font-size:10px; color:var(--po-fa)">suất</span>
                                        </div>
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
                <h1 class="emp-title">Thực đơn ngày – {{ $dayDate ? date('d/m/Y', strtotime($dayDate)) : '' }}</h1>
                <p class="emp-subtitle">Khai báo chi tiết thực đơn và số lượng suất ăn cụ thể theo từng ca ăn trong ngày</p>
            </div>
            <div class="emp-actions">
                <button wire:click="switchView('list')" class="emp-btn"><i class="fa-solid fa-arrow-left"></i> Quay lại</button>
                <button type="button" class="emp-btn" wire:click="exportDayForm"><i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i> Xuất Excel</button>
                <button wire:click="saveDayMenu('draft')" class="emp-btn"><i class="fa-regular fa-floppy-disk"></i> Lưu nháp</button>
                <button wire:click="saveDayMenu('locked')" class="emp-btn emp-btn-primary"><i class="fa-solid fa-lock"></i> Chốt thực đơn</button>
            </div>
        </div>

        <!-- Settings form -->
        <div class="tcard" style="padding:16px; margin-bottom:14px; background:#FAFBFC; display:flex; gap:12px; flex-wrap:wrap">
            <div class="field" style="min-width:240px">
                <label>Nhà ăn / Bếp ăn *</label>
                <select wire:model="dayKitchenId" class="ctrl" required>
                    @foreach($kitchens as $kit)
                        <option value="{{ $kit->id }}">{{ $kit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="min-width:200px">
                <label>Ngày lập thực đơn *</label>
                <input wire:model="dayDate" type="date" class="ctrl" required>
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
                                    <span style="font-size:12px; font-weight:700; color:var(--po-mu); width:60px">Món {{ $index + 1 }}</span>
                                    <select wire:model="dayItems.{{ $shiftId }}.recipes.{{ $index }}.recipe_id" class="ctrl" style="flex:1">
                                        <option value="">Chọn món ăn...</option>
                                        @foreach($recipes as $rec)
                                            <option value="{{ $rec->id }}">{{ $rec->name }}</option>
                                        @endforeach
                                    </select>
                                    <input wire:model="dayItems.{{ $shiftId }}.recipes.{{ $index }}.portions" type="number" class="ctrl" placeholder="Suất" style="width:100px; text-align:center">
                                    <span style="font-size:12.5px; color:var(--po-mu)">suất</span>
                                    <button type="button" wire:click="removeRecipeFromShift({{ $shiftId }}, {{ $index }})" class="abt" title="Xóa món" style="border-color:var(--po-rd-s); color:var(--po-rd)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <div style="margin-top:14px; display:flex; justify-content:flex-start">
                            <button type="button" wire:click="addRecipeToShift({{ $shiftId }})" class="emp-btn" style="height:32px; font-size:12px">
                                <i class="fa-solid fa-plus"></i> Thêm món ăn
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Right: Summary & Rules -->
            <div style="display:flex; flex-direction:column; gap:16px">
                <div class="lf-sum">
                    <div class="lf-sum-ttl">Tóm tắt thực đơn ngày</div>
                    <div class="lf-sum-row">
                        <span class="lf-sum-k">Ngày áp dụng</span>
                        <span class="lf-sum-v" style="font-weight:700">{{ $dayDate ? date('d/m/Y', strtotime($dayDate)) : '—' }}</span>
                    </div>
                    <div class="lf-sum-row">
                        <span class="lf-sum-k">Bếp ăn</span>
                        <span class="lf-sum-v">{{ \App\Models\Kitchen::find($dayKitchenId)?->name ?: '—' }}</span>
                    </div>
                    <div class="lf-sum-row" style="border-bottom:none">
                        <span class="lf-sum-k">Ca phục vụ</span>
                        <span class="lf-sum-v" style="font-weight:700; color:var(--po-bl)">
                            @php
                                $activeShifts = [];
                                foreach($dayItems as $sId => $sData) {
                                    $hasRecipe = collect($sData['recipes'])->contains(fn($r) => !empty($r['recipe_id']));
                                    if($hasRecipe) $activeShifts[] = $sData['shift_name'];
                                }
                                echo empty($activeShifts) ? 'Chưa có' : implode(' / ', $activeShifts);
                            @endphp
                        </span>
                    </div>
                </div>

                <div class="lf-notice">
                    <div class="lf-notice-ttl">Lưu ý khi chốt</div>
                    <ul class="lf-rule">
                        <li><i class="fa-solid fa-circle" style="font-size:5px; color:var(--po-mu)"></i> Thực đơn sau khi chốt sẽ được gửi đến bộ phận kho để chuẩn bị xuất nguyên liệu.</li>
                        <li><i class="fa-solid fa-circle" style="font-size:5px; color:var(--po-mu)"></i> Mọi thay đổi sau khi chốt sẽ được lưu vết lịch sử chi tiết (Audit log).</li>
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>
