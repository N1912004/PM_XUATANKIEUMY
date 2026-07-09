<div class="emp-page">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @include('filament.resources.areas.partials.styles')

    @php
        $stats = $this->getStats();
        $areas = $this->areas();
        $kitchens = $this->kitchens();
        $employees = $this->getEmployees();
    @endphp

    @if (session()->has('message'))
        <div style="background:#ECFDF5; color:#065F46; padding:12px 16px; border-radius:8px; border:1px solid #A7F3D0; margin-bottom:16px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div style="background:#FEF2F2; color:#991B1B; padding:12px 16px; border-radius:8px; border:1px solid #FCA5A5; margin-bottom:16px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px">
            <i class="fa-solid fa-triangle-exclamation"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Header Section -->
    <div class="emp-head" style="margin-bottom: 14px;">
        <div>
            <h1 class="emp-title">Khu vực & nhà ăn</h1>
            <p class="emp-subtitle">Quản lý các khu vực vận hành và các nhà ăn / bếp sản xuất trực thuộc từng khu vực</p>
        </div>
        <div class="emp-actions">
            <button wire:click="resetAreaForm(); resetKitchenForm();" class="emp-btn">
                <i class="fa-solid fa-rotate-left"></i>
                Làm mới
            </button>
            @if($activeTab === 'area')
                <button wire:click="resetAreaForm" class="emp-btn emp-btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    Thêm khu vực
                </button>
            @else
                <button wire:click="resetKitchenForm" class="emp-btn emp-btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    Thêm nhà ăn
                </button>
            @endif
        </div>
    </div>

    <!-- 4 KPIs Stats -->
    <div class="krow" style="grid-template-columns:repeat(4,1fr); margin-bottom: 16px;">
        <div class="kcard">
            <div class="ktop"><div class="kico ki-b"><i class="fa-solid fa-map-location-dot"></i></div></div>
            <div class="kval">{{ $stats['total_areas'] }}</div>
            <div class="klbl">Khu vực</div>
            <div class="knote">Đang quản lý</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-g"><i class="fa-solid fa-utensils"></i></div></div>
            <div class="kval">{{ $stats['total_kitchens'] }}</div>
            <div class="klbl">Nhà ăn / bếp</div>
            <div class="knote">Tổng cơ sở sản xuất</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-o"><i class="fa-solid fa-circle-check"></i></div></div>
            <div class="kval">{{ $stats['active_kitchens'] }}</div>
            <div class="klbl">Đang hoạt động</div>
            <div class="knote">Khu vực khả dụng</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-p"><i class="fa-solid fa-user-tie"></i></div></div>
            <div class="kval">{{ $stats['managers'] }}</div>
            <div class="klbl">Quản lý phụ trách</div>
            <div class="knote">Theo khu vực</div>
        </div>
    </div>

    <!-- Tabs chuyển đổi -->
    <div class="area-tabs">
        <button wire:click="switchTab('area')" class="area-tab {{ $activeTab === 'area' ? 'active' : '' }}">
            <i class="fa-solid fa-map-location-dot"></i> Khu vực
        </button>
        <button wire:click="switchTab('canteen')" class="area-tab {{ $activeTab === 'canteen' ? 'active' : '' }}">
            <i class="fa-solid fa-utensils"></i> Nhà ăn / bếp
        </button>
    </div>

    @if($activeTab === 'area')
        <!-- ==========================================
             PANE: KHU VỰC
             ========================================== -->
        <div style="display:grid; grid-template-columns:360px 1fr; gap:14px; align-items:start">
            <!-- Left CRUD Form -->
            <div class="tcard">
                <div class="tbar">
                    <div style="font-size:14px; font-weight:800; color:var(--tx)">
                        {{ $areaId ? 'Chỉnh sửa khu vực' : 'Thêm / sửa khu vực' }}
                    </div>
                </div>
                <form wire:submit.prevent="saveArea" style="padding:16px; display:grid; gap:12px">
                    <div class="field">
                        <label>Tên khu vực <span class="req">*</span></label>
                        <input wire:model="areaName" class="ctrl" placeholder="VD: Đồng Nai / Hồ Chí Minh" required>
                    </div>
                    <div class="field">
                        <label>Mã khu vực <span class="req">*</span></label>
                        <input wire:model="areaCode" class="ctrl" placeholder="VD: KV-DN" required {{ $areaId ? 'readonly' : '' }}>
                    </div>
                    <div class="field">
                        <label>Quản lý phụ trách</label>
                        <select wire:model="areaManagerId" class="ctrl">
                            <option value="">Chọn quản lý</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Trạng thái <span class="req">*</span></label>
                        <select wire:model="areaStatus" class="ctrl" required>
                            <option value="Đang hoạt động">Đang hoạt động</option>
                            <option value="Tạm dừng">Tạm dừng</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Ghi chú</label>
                        <textarea wire:model="areaNotes" class="ctrl" placeholder="Phạm vi vận hành, ca sản xuất, khách hàng chính..." rows="3"></textarea>
                    </div>
                    
                    <div style="display:flex; gap:8px; margin-top:4px">
                        @if($areaId)
                            <button type="button" wire:click="resetAreaForm" class="emp-btn" style="flex:1">Hủy</button>
                        @endif
                        <button type="submit" class="emp-btn emp-btn-primary" style="flex:1; justify-content:center">
                            <i class="fa-regular fa-floppy-disk"></i> Lưu khu vực
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right List Table -->
            <div class="tcard">
                <div class="tbar">
                    <div class="tsbox" style="height:38px; min-width:260px; max-width:360px">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input wire:model.live.debounce.250ms="areaSearch" type="text" placeholder="Tìm khu vực...">
                    </div>
                    <div class="tsp"></div>
                    <button wire:click="resetFilters" class="fbtn">
                        <i class="fa-solid fa-filter-circle-xmark"></i> Xóa chọn
                    </button>
                </div>
                <div class="tw">
                    <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px">
                        <thead>
                            <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; background:#F8FAFC">
                                <th style="padding:12px 14px; width:100px">Mã</th>
                                <th style="padding:12px 14px">Khu vực</th>
                                <th style="padding:12px 14px">Quản lý</th>
                                <th style="padding:12px 14px; text-align:center; width:90px">Nhà ăn</th>
                                <th style="padding:12px 14px; width:140px">Trạng thái</th>
                                <th style="padding:12px 14px; text-align:center; width:100px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($areas as $row)
                                <tr style="border-bottom:1px solid #F1F5F9; color:var(--po-tx)" class="emp-row">
                                    <td style="padding:12px 14px; font-weight:700; color:var(--po-mu)">{{ $row->code }}</td>
                                    <td style="padding:12px 14px;">
                                        <div style="font-weight:700; color:var(--po-tx)">{{ $row->name }}</div>
                                        <div style="font-size:11px; color:var(--po-mu); margin-top:2px">{{ $row->notes ?: 'Chưa có ghi chú' }}</div>
                                    </td>
                                    <td style="padding:12px 14px; font-weight:600">{{ $row->manager?->name ?: '—' }}</td>
                                    <td style="padding:12px 14px; text-align:center; font-weight:800; font-size:15px; color:var(--po-bl)">
                                        {{ $row->kitchens->count() }}
                                    </td>
                                    <td style="padding:12px 14px;">
                                        @if($row->status === 'Đang hoạt động')
                                            <span class="st-pill st-ok">Đang hoạt động</span>
                                        @else
                                            <span class="st-pill st-late">Tạm dừng</span>
                                        @endif
                                    </td>
                                    <td style="padding:12px 14px; text-align:center">
                                        <div style="display:inline-flex; gap:6px">
                                            <button wire:click="editArea({{ $row->id }})" class="abt" title="Sửa">
                                                <i class="fa-solid fa-pencil"></i>
                                            </button>
                                            <button wire:click="deleteArea({{ $row->id }})" wire:confirm="Bạn có chắc chắn muốn xóa khu vực này?" class="abt" title="Xóa">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding:32px; text-align:center; color:var(--po-mu)">
                                        Không tìm thấy khu vực nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <!-- ==========================================
             PANE: NHÀ ĂN / BẾP
             ========================================== -->
        <!-- CRUD & Filter Card -->
        <div class="tcard" style="margin-bottom:14px">
            <div class="tbar" style="border-bottom:1px solid var(--po-bd2)">
                <div>
                    <div style="font-size:14px; font-weight:800; color:var(--tx)" id="areaCanteenTitle">
                        {{ $kitchenId ? 'Chỉnh sửa nhà ăn / bếp' : 'Thêm mới nhà ăn / bếp' }}
                    </div>
                    <div style="font-size:11.5px; color:var(--po-mu); margin-top:2px">
                        Vui lòng điền thông tin và bấm nút Lưu để hoàn tất.
                    </div>
                </div>
            </div>
            
            <!-- Inline Form -->
            <form wire:submit.prevent="saveKitchen" style="padding:14px 16px; background:#FAFBFC; display:grid; grid-template-columns:repeat(6,1fr); gap:10px; border-bottom:1.5px solid var(--po-bd2)">
                <select wire:model="kitchenAreaId" class="ctrl" required>
                    <option value="">Khu vực *</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }} ({{ $area->code }})</option>
                    @endforeach
                </select>

                <input wire:model="kitchenName" class="ctrl" placeholder="Tên nhà ăn / bếp *" required>

                <select wire:model="kitchenType" class="ctrl" required>
                    <option value="Bếp sản xuất">Bếp sản xuất</option>
                    <option value="Điểm chia suất">Điểm chia suất</option>
                    <option value="Nhà ăn phục vụ">Nhà ăn phục vụ</option>
                </select>

                <input wire:model="kitchenCapacity" class="ctrl" type="number" placeholder="Công suất / ngày *" min="0" required>

                <select wire:model="kitchenManagerId" class="ctrl">
                    <option value="">Phụ trách</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>

                <div style="display:flex; gap:6px">
                    <select wire:model="kitchenStatus" class="ctrl" required style="flex:1">
                        <option value="Đang hoạt động">Đang hoạt động</option>
                        <option value="Tạm dừng">Tạm dừng</option>
                        <option value="Bảo trì">Bảo trì</option>
                    </select>
                    <button type="submit" class="emp-btn emp-btn-primary" style="height:36px; padding:0 12px" title="Lưu lại">
                        <i class="fa-regular fa-floppy-disk"></i>
                    </button>
                    @if($kitchenId)
                        <button type="button" wire:click="resetKitchenForm" class="emp-btn" style="height:36px; padding:0 12px" title="Hủy">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    @endif
                </div>
            </form>

            <!-- Search & Quick Filters -->
            <div class="tbar">
                <div class="tsbox" style="height:38px; min-width:260px; max-width:360px">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input wire:model.live.debounce.250ms="kitchenSearch" type="text" placeholder="Tìm nhà ăn / bếp...">
                </div>

                <div style="display:flex; gap:6px; margin-left:12px">
                    <select wire:model.live="kitchenAreaFilter" class="lv-sel" style="height:34px">
                        <option value="">Tất cả khu vực</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="kitchenTypeFilter" class="lv-sel" style="height:34px">
                        <option value="">Tất cả loại</option>
                        <option value="Bếp sản xuất">Bếp sản xuất</option>
                        <option value="Điểm chia suất">Điểm chia suất</option>
                        <option value="Nhà ăn phục vụ">Nhà ăn phục vụ</option>
                    </select>

                    <select wire:model.live="kitchenStatusFilter" class="lv-sel" style="height:34px">
                        <option value="">Tất cả trạng thái</option>
                        <option value="Đang hoạt động">Đang hoạt động</option>
                        <option value="Tạm dừng">Tạm dừng</option>
                        <option value="Bảo trì">Bảo trì</option>
                    </select>
                </div>

                <div class="tsp"></div>
                <button wire:click="resetFilters" class="fbtn">
                    <i class="fa-solid fa-filter-circle-xmark"></i> Xóa nhập liệu
                </button>
            </div>

            <!-- Kitchen List Table -->
            <div class="tw">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px">
                    <thead>
                        <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; background:#F8FAFC">
                            <th style="padding:12px 14px; width:60px">#</th>
                            <th style="padding:12px 14px">Nhà ăn / bếp</th>
                            <th style="padding:12px 14px">Khu vực</th>
                            <th style="padding:12px 14px">Loại</th>
                            <th style="padding:12px 14px; text-align:right">Công suất</th>
                            <th style="padding:12px 14px">Phụ trách</th>
                            <th style="padding:12px 14px; width:140px">Trạng thái</th>
                            <th style="padding:12px 14px; text-align:center; width:100px">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kitchens as $index => $row)
                            <tr style="border-bottom:1px solid #F1F5F9; color:var(--po-tx)" class="emp-row">
                                <td style="padding:12px 14px; font-weight:600; color:var(--po-mu)">{{ $index + 1 }}</td>
                                <td style="padding:12px 14px; font-weight:700">{{ $row->name }}</td>
                                <td style="padding:12px 14px; font-weight:600; color:var(--po-mu)">{{ $row->area?->name }}</td>
                                <td style="padding:12px 14px;">{{ $row->type }}</td>
                                <td style="padding:12px 14px; text-align:right; font-weight:700; color:var(--po-bl)">
                                    {{ number_format($row->capacity, 0, ',', '.') }} suất/ngày
                                </td>
                                <td style="padding:12px 14px; font-weight:600">{{ $row->manager?->name ?: '—' }}</td>
                                <td style="padding:12px 14px;">
                                    @if($row->status === 'Đang hoạt động')
                                        <span class="st-pill st-ok">Đang hoạt động</span>
                                    @elseif($row->status === 'Tạm dừng')
                                        <span class="st-pill st-late">Tạm dừng</span>
                                    @else
                                        <span class="st-pill" style="background:#F1F5F9; color:#64748B; border-color:#CBD5E1">Bảo trì</span>
                                    @endif
                                </td>
                                <td style="padding:12px 14px; text-align:center">
                                    <div style="display:inline-flex; gap:6px">
                                        <button wire:click="editKitchen({{ $row->id }})" class="abt" title="Sửa">
                                            <i class="fa-solid fa-pencil"></i>
                                        </button>
                                        <button wire:click="deleteKitchen({{ $row->id }})" wire:confirm="Bạn có chắc chắn muốn xóa nhà ăn/bếp này?" class="abt" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="padding:32px; text-align:center; color:var(--po-mu)">
                                    Không tìm thấy nhà ăn hay bếp sản xuất nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
