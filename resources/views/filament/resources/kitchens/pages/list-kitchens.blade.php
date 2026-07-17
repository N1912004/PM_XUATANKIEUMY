<div class="emp-page">
    @include('filament.resources.areas.partials.styles')

    @php
        $stats = $this->getStats();
        $kitchens = $this->kitchens();
        $allAreas = \App\Models\Area::orderBy('name')->get();
        $createUrl = \App\Filament\Resources\KitchenResource::getUrl('create');
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

    <!-- Header Section -->
    <div class="emp-head" style="margin-bottom: 14px;">
        <div>
            <h1 class="emp-title">Nhà ăn / bếp</h1>
            <p class="emp-subtitle">Quản lý các nhà ăn / bếp sản xuất trực thuộc từng khu vực vận hành</p>
        </div>
        <div class="emp-actions">
            <a href="{{ $createUrl }}" wire:navigate class="emp-btn emp-btn-primary">
                <i class="fa-solid fa-plus"></i>
                Thêm nhà ăn / bếp
            </a>
        </div>
    </div>

    <!-- 4 KPIs Stats -->
    <div class="krow" style="grid-template-columns:repeat(4,1fr); margin-bottom: 16px;">
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
            <div class="knote">Cơ sở khả dụng</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-b"><i class="fa-solid fa-map-location-dot"></i></div></div>
            <div class="kval">{{ $stats['total_areas'] }}</div>
            <div class="klbl">Khu vực</div>
            <div class="knote">Đang quản lý</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-p"><i class="fa-solid fa-user-tie"></i></div></div>
            <div class="kval">{{ $stats['managers'] }}</div>
            <div class="klbl">Quản lý nhà bếp</div>
            <div class="knote">Theo cơ sở</div>
        </div>
    </div>

    <!-- ==========================================
         DANH SÁCH NHÀ ĂN / BẾP
         ========================================== -->
    <div class="tcard" style="margin-bottom:14px">
        <!-- Search & Quick Filters -->
        <div class="tbar">
            <div class="tsbox" style="height:38px; min-width:260px; max-width:360px">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input wire:model.live.debounce.250ms="kitchenSearch" type="text" placeholder="Tìm nhà ăn / bếp...">
            </div>

            <div style="display:flex; gap:6px; margin-left:12px">
                <select wire:model.live="kitchenAreaFilter" class="lv-sel" style="height:34px">
                    <option value="">Tất cả khu vực</option>
                    @foreach($allAreas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="kitchenTypeFilter" class="lv-sel" style="height:34px">
                    <option value="">Tất cả loại</option>
                    @foreach($this->kitchenTypeOptions() as $ktId => $ktName)
                        <option value="{{ $ktId }}">{{ $ktName }}</option>
                    @endforeach
                </select>

                <select wire:model.live="kitchenStatusFilter" class="lv-sel" style="height:34px">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active">Đang hoạt động</option>
                    <option value="paused">Tạm dừng</option>
                    <option value="maintenance">Bảo trì</option>
                </select>
            </div>

            <div class="tsp"></div>
            <button wire:click="resetFilters" class="fbtn">
                <i class="fa-solid fa-filter-circle-xmark"></i> Xóa lọc
            </button>
        </div>

        <!-- Kitchen List Table -->
        <div class="tw">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; background:var(--po-bd2)">
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
                        <tr style="border-bottom:1px solid var(--po-bd2); color:var(--po-tx)" class="emp-row">
                            <td style="padding:12px 14px; font-weight:600; color:var(--po-mu)">{{ ($kitchens->currentPage() - 1) * $kitchens->perPage() + $index + 1 }}</td>
                            <td style="padding:12px 14px; font-weight:700">{{ $row->name }}</td>
                            <td style="padding:12px 14px; font-weight:600; color:var(--po-mu)">{{ $row->area?->name }}</td>
                            <td style="padding:12px 14px;">{{ $row->kitchenType?->name }}</td>
                            <td style="padding:12px 14px; text-align:right; font-weight:700; color:var(--po-bl)">
                                {{ number_format($row->capacity, 0, ',', '.') }} suất/ngày
                            </td>
                            <td style="padding:12px 14px; font-weight:600">{{ $row->manager?->name }}</td>
                            <td style="padding:12px 14px;">
                                @if($row->status === 'active')
                                    <span class="st-pill st-ok">Đang hoạt động</span>
                                @elseif($row->status === 'paused')
                                    <span class="st-pill st-late">Tạm dừng</span>
                                @else
                                    <span class="st-pill" style="background:var(--po-bd2); color:var(--po-su); border-color:var(--po-bd)">Bảo trì</span>
                                @endif
                            </td>
                            <td style="padding:12px 14px; text-align:center">
                                <div style="display:inline-flex; gap:6px">
                                    <a href="{{ \App\Filament\Resources\KitchenResource::getUrl('edit', ['record' => $row->id]) }}" wire:navigate class="abt" title="Sửa">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>
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
        @if($kitchens->hasPages())
            <div style="padding: 10px 16px; border-top: 1px solid var(--po-bd2); background: var(--po-bd2);">
                {{ $kitchens->links() }}
            </div>
        @endif
    </div>
</div>
