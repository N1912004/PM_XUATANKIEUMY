<div class="emp-page">
    @include('filament.resources.food-safety-audits.partials.styles')

    @php
        $stats = $this->getStats();
        $auditItems = $this->getAuditItems();
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
            <h1 class="emp-title">Kiểm thực 3 bước</h1>
            <p class="emp-subtitle">Tạo biểu mẫu kiểm thực theo ngày, lấy dữ liệu từ thực đơn đã lập và xuất Excel theo mẫu B1-B3</p>
        </div>
        <div class="emp-actions">
            <!-- Nút Tạo dữ liệu (Hủy mẫu / Reset) -->
            <button wire:click="$set('date', '2026-05-18')" class="emp-btn emp-btn-danger">
                <i class="fa-solid fa-ban"></i>
                Hủy mẫu
            </button>
            <!-- Nút Xuất Excel (Lưu mẫu / Xuất toàn bộ) -->
            <button wire:click="exportExcel" class="emp-btn emp-btn-primary">
                <i class="fa-solid fa-floppy-disk"></i>
                Lưu mẫu
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="filter-card" style="margin-bottom: 16px;">
        <div class="field" style="min-width:180px">
            <label>Ngày kiểm thực</label>
            <input type="date" wire:model.live="date" class="ctrl">
        </div>
        <div class="field" style="min-width:150px">
            <label>Ca phục vụ</label>
            <select wire:model.live="selectedShift" class="ctrl">
                <option value="">Tất cả ca</option>
                @foreach(\App\Models\Shift::all() as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field" style="min-width:200px">
            <label>Cơ sở / địa điểm (theo bếp)</label>
            <input value="{{ $canteen }}" class="ctrl" readonly style="background:var(--po-bd2); cursor:not-allowed" title="Tự nhận theo bếp của tài khoản đăng nhập">
        </div>
        <div class="field" style="min-width:220px">
            <label>Người kiểm tra</label>
            <div x-data="{
                open: false,
                search: '',
                selected: @entangle('inspector'),
                options: {{ json_encode(array_values($this->getInspectorOptions())) }},
                get filteredOptions() {
                    if (!this.search) return this.options;
                    let s = this.search.toLowerCase();
                    return this.options.filter(name => name.toLowerCase().includes(s));
                }
            }" class="relative w-full">
                <!-- Input hiển thị + Trigger -->
                <div @click="open = !open" class="ctrl flex items-center justify-between cursor-pointer" style="background:#fff; min-height:38px; border: 1.5px solid var(--po-line); padding: 6px 12px; border-radius: 8px;">
                    <span x-text="selected ? selected : '— Chọn nhân viên —'" style="font-weight:600;"></span>
                    <i class="fa-solid fa-chevron-down" style="font-size:11px; color:var(--po-mu);"></i>
                </div>
                
                <!-- Dropdown với ô Search -->
                <div x-show="open" @click.away="open = false" class="absolute left-0 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg z-50 p-2" style="display:none; max-height:280px; overflow-y:auto; border: 1px solid var(--po-line); box-shadow: 0 10px 25px rgba(15, 35, 70, 0.15);">
                    <input type="text" x-model="search" placeholder="Tìm kiếm nhân viên..." class="ctrl w-full mb-2" style="height:32px; padding:4px 8px; font-size:13px; border: 1px solid var(--po-line); border-radius:6px; outline:none;">
                    <div class="flex flex-col gap-1">
                        <!-- Nút để bỏ chọn (reset filter) -->
                        <div @click="selected = ''; open = false; search = ''" 
                             class="px-3 py-1.5 rounded cursor-pointer text-sm font-semibold hover:bg-gray-100 transition text-gray-500 italic">
                             — Bỏ chọn —
                        </div>
                        <template x-for="name in filteredOptions" :key="name">
                            <div @click="selected = name; open = false; search = ''" 
                                 class="px-3 py-1.5 rounded cursor-pointer text-sm font-semibold hover:bg-gray-100 transition"
                                 :style="selected === name ? 'background:rgba(18, 86, 196, 0.08); color:var(--po-bl);' : 'color:var(--po-tx);'"
                                 x-text="name">
                            </div>
                        </template>
                        <div x-show="filteredOptions.length === 0" class="text-center py-3 text-xs text-gray-400 font-semibold">
                            Không tìm thấy kết quả
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="font-size:12.5px; color:var(--po-mu); padding-bottom:9px; font-weight:600">
            {{ date('d/m/Y', strtotime($date)) }} · {{ $stats['dishes'] }} món · {{ $stats['ingredients'] }} nguyên liệu
        </div>
    </div>

    <!-- Steps Tabs -->
    <div class="area-tabs">
        <button wire:click="$set('activeStep', 'Bước 1')" class="area-tab {{ $activeStep === 'Bước 1' ? 'active' : '' }}">
            <i class="fa-solid fa-clipboard-check"></i> Bước 1
        </button>
        <button wire:click="$set('activeStep', 'Bước 2')" class="area-tab {{ $activeStep === 'Bước 2' ? 'active' : '' }}">
            <i class="fa-solid fa-utensils"></i> Bước 2
        </button>
        <button wire:click="$set('activeStep', 'Bước 3')" class="area-tab {{ $activeStep === 'Bước 3' ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i> Bước 3
        </button>
        <button wire:click="$set('activeStep', 'Lưu mẫu')" class="area-tab {{ $activeStep === 'Lưu mẫu' ? 'active' : '' }}">
            <i class="fa-solid fa-box-archive"></i> Lưu mẫu
        </button>
        <button wire:click="$set('activeStep', 'Hủy mẫu')" class="area-tab {{ $activeStep === 'Hủy mẫu' ? 'active' : '' }}">
            <i class="fa-solid fa-ban"></i> Hủy mẫu
        </button>
        <div class="tsp"></div>
        @if($activeStep === 'Lưu mẫu')
            <button type="button" onclick="window.print()" class="emp-btn" style="height:36px">
                <i class="fa-solid fa-tags" style="color:var(--po-pu)"></i> In tem nhãn lưu mẫu
            </button>
        @endif
    </div>

    <!-- 4 KPIs Stats -->
    <div class="krow" style="grid-template-columns:repeat(4,1fr); margin-bottom: 16px;">
        <div class="kcard">
            <div class="ktop"><div class="kico ki-b"><i class="fa-solid fa-seedling"></i></div></div>
            <div class="kval">{{ $stats['ingredients'] }}</div>
            <div class="klbl">Nguyên liệu B1</div>
            <div class="knote">Từ món trong ngày</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-g"><i class="fa-solid fa-bowl-food"></i></div></div>
            <div class="kval">{{ $stats['dishes'] }}</div>
            <div class="klbl">Món ăn</div>
            <div class="knote">Phân theo ca</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-o"><i class="fa-solid fa-users"></i></div></div>
            <div class="kval">{{ number_format($stats['portions']) }}</div>
            <div class="klbl">Tổng suất</div>
            <div class="knote">Theo từng món</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-p"><i class="fa-solid fa-file-excel"></i></div></div>
            <div class="kval">{{ $stats['forms'] }}</div>
            <div class="klbl">Biểu mẫu</div>
            <div class="knote">B1 đến B5</div>
        </div>
    </div>

    <!-- Dynamic Official Ministry of Health Report View -->
    <div class="tcard">
        <div class="tbar">
            <div style="font-size:14px; font-weight:800; color:var(--po-tx)" id="ktStepTitle">
                @if($activeStep === 'Bước 1')
                    Bước 1: Kiểm tra trước khi chế biến thức ăn
                @elseif($activeStep === 'Bước 2')
                    Bước 2: Kiểm tra khi chế biến món ăn
                @elseif($activeStep === 'Bước 3')
                    Bước 3: Kiểm tra trước khi ăn
                @elseif($activeStep === 'Lưu mẫu')
                    Lưu mẫu: Theo dõi lưu thức ăn lưu trong 24 giờ
                @elseif($activeStep === 'Hủy mẫu')
                    Hủy mẫu: Theo dõi hủy thức ăn lưu hết hạn
                @endif
            </div>
            <div style="font-size:12.5px; color:var(--po-mu)" id="ktStepSub">
                @if($activeStep === 'Bước 1')
                    Danh sách nguyên liệu trong ngày theo mẫu B1
                @elseif($activeStep === 'Bước 2')
                    Danh sách món ăn phân theo ca và nguyên liệu chính
                @elseif($activeStep === 'Bước 3')
                    Món ăn, số suất, dụng cụ và cảm quan trước khi phục vụ
                @elseif($activeStep === 'Lưu mẫu')
                    Theo dõi lưu mẫu thức ăn ca chính / ca trưa
                @elseif($activeStep === 'Hủy mẫu')
                    Theo dõi hủy mẫu thức ăn các ca còn lại
                @endif
            </div>
        </div>

        <div class="tw" style="padding: 16px;">
            <table class="byt-table">
                <thead>
                    <!-- Hàng tiêu đề chung chuẩn Bộ Y tế -->
                    <tr>
                        <th colspan="{{ $activeStep === 'Bước 1' ? 10 : ($activeStep === 'Bước 2' ? 8 : ($activeStep === 'Bước 3' ? 7 : 8)) }}" style="background:var(--po-bd2); text-align:center; padding:12px">
                            @php
                                $companyName = \App\Models\Setting::get('company_name', 'CÔNG TY TNHH DỊCH VỤ CJ CATERING VIỆT NAM');
                                $companyAddress = \App\Models\Setting::get('company_address', 'TỔ 15, ẤP 2 XÃ LONG THỌ, HUYỆN NHƠN TRẠCH, TỈNH ĐỒNG NAI');
                                $headerTitle = strtoupper($canteen) . ' - ' . strtoupper($companyName);
                            @endphp
                            <div style="font-size:13px; font-weight:800; color:var(--po-tx)">
                                {{ $headerTitle }}
                            </div>
                            <div style="font-size:11px; font-weight:600; color:var(--po-mu); margin-top:2px">
                                ĐỊA CHỈ: {{ strtoupper($companyAddress) }}
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="{{ $activeStep === 'Bước 1' ? 4 : ($activeStep === 'Bước 2' ? 3 : ($activeStep === 'Bước 3' ? 3 : 3)) }}" style="text-align:left; background:var(--po-wh); font-weight:700">
                            📍 ĐỊA ĐIỂM KIỂM TRA: {{ $canteen }}
                        </th>
                        <th colspan="{{ $activeStep === 'Bước 1' ? 6 : ($activeStep === 'Bước 2' ? 5 : ($activeStep === 'Bước 3' ? 4 : 5)) }}" style="text-align:right; background:var(--po-wh); font-weight:700">
                            👤 NGƯỜI KIỂM TRA: {{ $inspector }}
                        </th>
                    </tr>
                    
                    <!-- Hàng tiêu đề cột cột chính -->
                    <tr style="text-transform:uppercase; font-size:11.5px">
                        @if($activeStep === 'Bước 1')
                            <th style="width:50px">TT</th>
                            <th>Tên thực phẩm</th>
                            <th style="width:110px">Thời gian nhập</th>
                            <th style="width:110px" class="text-right">Khối lượng (Kg)</th>
                            <th>Nơi cung cấp thực phẩm</th>
                            <th style="width:140px">Chứng từ, hóa đơn</th>
                            <th style="width:110px">Giấy ĐK VS Thú Y</th>
                            <th style="width:110px">Cảm quan (Đ/K)</th>
                            <th style="width:100px">Test nhanh</th>
                            <th>Biện pháp xử lý / Ghi chú</th>
                        @elseif($activeStep === 'Bước 2')
                            <th style="width:50px">TT</th>
                            <th>Tên món ăn chế biến</th>
                            <th style="width:140px">Giờ chế biến</th>
                            <th style="width:110px">Cảm quan (Đ/K)</th>
                            <th style="width:140px">Nhiệt độ trung tâm</th>
                            <th>Người thực hiện</th>
                            <th style="width:160px">Khu vực bếp nấu</th>
                            <th>Biện pháp xử lý / Ghi chú</th>
                        @elseif($activeStep === 'Bước 3')
                            <th style="width:50px">TT</th>
                            <th>Tên món ăn chia suất</th>
                            <th style="width:140px">Giờ chia suất</th>
                            <th style="width:110px">Cảm quan (Đ/K)</th>
                            <th style="width:160px">Lưu mẫu thức ăn</th>
                            <th style="width:140px">Nhiệt độ chia suất</th>
                            <th>Biện pháp xử lý / Ghi chú</th>
                        @elseif($activeStep === 'Lưu mẫu')
                            <th style="width:50px">TT</th>
                            <th>Tên món ăn lưu mẫu</th>
                            <th style="width:130px">Giờ lưu mẫu</th>
                            <th style="width:130px">Khối lượng mẫu</th>
                            <th style="width:150px">Mã số mẫu lưu</th>
                            <th style="width:130px">Nhiệt độ tủ lưu</th>
                            <th>Người thực hiện lưu</th>
                            <th>Biện pháp xử lý / Ghi chú</th>
                        @elseif($activeStep === 'Hủy mẫu')
                            <th style="width:50px">TT</th>
                            <th>Tên món ăn hủy mẫu</th>
                            <th style="width:140px">Giờ hủy mẫu</th>
                            <th style="width:140px">Thời gian lưu đủ</th>
                            <th style="width:160px">Tình trạng khi hủy</th>
                            <th>Người hủy mẫu</th>
                            <th>Biện pháp xử lý / Ghi chú</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditItems as $index => $item)
                        @if($activeStep === 'Bước 1')
                            {{-- Dòng tiêu đề nhóm khi đổi PHÂN LOẠI nguyên liệu (items đã sort theo type) --}}
                            @php $prevType = $index > 0 ? ($auditItems[$index - 1]['type'] ?? null) : null; @endphp
                            @if(($item['type'] ?? null) !== $prevType)
                                <tr>
                                    <td colspan="13" class="byt-group-title">{{ $item['type'] ?: 'Khác' }}</td>
                                </tr>
                            @endif
                            <!-- Step 1 Rows -->
                                <tr class="emp-row">
                                    <td class="text-center font-bold" style="color:var(--po-mu)">{{ $index + 1 }}</td>
                                    <td class="font-bold">{{ $item['name'] }}</td>
                                    <td class="text-center">{{ $item['time'] ?: '—' }}</td>
                                    <td class="text-right font-bold" style="color:var(--po-bl)">
                                        @if(isset($item['unit']) && ($item['unit'] === 'Quả' || $item['unit'] === 'Trái' || $item['unit'] === 'Cái'))
                                            {{ number_format($item['quantity'], 0) }} {{ $item['unit'] }}
                                        @else
                                            {{-- quantity ĐÃ là kg (suất × định lượng kg/suất) — không chia 1000 --}}
                                            {{ number_format($item['quantity'] ?? 0, 2, ',', '.') }} kg
                                        @endif
                                    </td>
                                    <td>{{ $item['supplier'] ?? 'Cơ sở tự do' }}</td>
                                    <td class="text-center">{{ $item['invoice'] ?? '—' }}</td>
                                    <td class="text-center">
                                        @if(($item['vet_check'] ?? '') === 'Đạt')
                                            <span class="px-2 py-0.5 rounded font-bold" style="font-size:11px; background:var(--po-gn-s); color:var(--po-gn)">Đạt</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(($item['quarantine'] ?? '') === 'Có')
                                            <span class="px-2 py-0.5 rounded font-bold" style="font-size:11px; background:var(--po-gn-s); color:var(--po-gn)">Có</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item['quick_test'] ?? '—' }}</td>
                                    <td style="color:var(--po-mu); font-style:italic">{{ $item['notes'] ?: '—' }}</td>
                                </tr>
                        @elseif($activeStep === 'Bước 2')
                            <!-- Step 2 Rows -->
                            <tr class="emp-row">
                                <td class="text-center font-bold" style="color:var(--po-mu)">{{ $index + 1 }}</td>
                                <td class="font-bold">{{ $item['name'] }}</td>
                                <td class="text-center">{{ $item['time'] ?: '—' }}</td>
                                <td class="text-center">
                                    @if($item['sensory'] === 'Không đạt')
                                        <span class="px-2 py-0.5 rounded font-bold" style="font-size:11px; background:var(--po-rd-s); color:var(--po-rd)">Không đạt</span>
                                    @elseif($item['sensory'] === 'Đạt')
                                        <span class="px-2 py-0.5 rounded font-bold" style="font-size:11px; background:var(--po-gn-s); color:var(--po-gn)">Đạt</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center font-bold" style="color:var(--po-gn)">{{ $item['temp'] ? $item['temp'] . '°C' : '—' }}</td>
                                <td class="font-bold">{{ $item['cook'] ?: '—' }}</td>
                                <td>{{ $item['kitchen'] ?: '—' }}</td>
                                <td style="color:var(--po-mu); font-style:italic">{{ $item['notes'] ?: '—' }}</td>
                            </tr>
                        @elseif($activeStep === 'Bước 3')
                            <!-- Step 3 Rows -->
                            <tr class="emp-row">
                                <td class="text-center font-bold" style="color:var(--po-mu)">{{ $index + 1 }}</td>
                                <td class="font-bold">{{ $item['name'] }}</td>
                                <td class="text-center">{{ $item['time'] ?: '—' }}</td>
                                <td class="text-center">
                                    @if($item['sensory'] === 'Không đạt')
                                        <span class="px-2 py-0.5 rounded font-bold" style="font-size:11px; background:var(--po-rd-s); color:var(--po-rd)">Không đạt</span>
                                    @elseif($item['sensory'] === 'Đạt')
                                        <span class="px-2 py-0.5 rounded font-bold" style="font-size:11px; background:var(--po-gn-s); color:var(--po-gn)">Đạt</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center font-bold" style="color:var(--po-bl)">{{ $item['sample_kept'] ?: '—' }}</td>
                                <td class="text-center font-bold" style="color:var(--po-gn)">{{ $item['temp'] ? $item['temp'] . '°C' : '—' }}</td>
                                <td style="color:var(--po-mu); font-style:italic">{{ $item['notes'] ?: '—' }}</td>
                            </tr>
                        @elseif($activeStep === 'Lưu mẫu')
                            <!-- Keep Sample Rows -->
                            <tr class="emp-row">
                                <td class="text-center font-bold" style="color:var(--po-mu)">{{ $index + 1 }}</td>
                                <td class="font-bold">{{ $item['name'] }}</td>
                                <td class="text-center">{{ $item['time'] ?: '—' }}</td>
                                <td class="text-center font-bold">
                                    @if($item['time'])
                                        {{ $item['quantity'] ?: '≥100g' }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center font-mono font-bold" style="color:var(--po-pu)">{{ $item['sample_code'] ?: '—' }}</td>
                                <td class="text-center font-bold" style="color:var(--po-gn)">{{ $item['temp'] ? $item['temp'] . '°C' : '—' }}</td>
                                <td class="font-bold">{{ $item['staff'] ?: '—' }}</td>
                                <td style="color:var(--po-mu); font-style:italic">{{ $item['notes'] ?: '—' }}</td>
                            </tr>
                        @elseif($activeStep === 'Hủy mẫu')
                            <!-- Discard Sample Rows -->
                            <tr class="emp-row">
                                <td class="text-center font-bold" style="color:var(--po-mu)">{{ $index + 1 }}</td>
                                <td class="font-bold">{{ $item['name'] }}</td>
                                <td class="text-center">{{ $item['time'] ?: '—' }}</td>
                                <td class="text-center">
                                    @if($item['time'])
                                        {{ $item['retention'] ?: '24 giờ' }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center font-bold" style="color:var(--po-gn)">{{ $item['status'] ?: '—' }}</td>
                                <td class="font-bold">{{ $item['staff'] ?: '—' }}</td>
                                <td style="color:var(--po-mu); font-style:italic">{{ $item['notes'] ?: '—' }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="{{ $activeStep === 'Bước 1' ? 10 : ($activeStep === 'Bước 2' ? 8 : ($activeStep === 'Bước 3' ? 7 : 8)) }}" style="padding:40px; text-align:center; color:var(--po-mu); font-style:italic">
                                Không tìm thấy dữ liệu kiểm thực phù hợp.
                            </td>
                        </tr>
                    @endforelse

                    <!-- Chữ ký xác nhận chân bảng theo biểu mẫu B1 -->
                    @if($activeStep === 'Bước 1' && !empty($auditItems))
                        <tr class="byt-sign-title">
                            <td colspan="4" style="text-align:left; border-top:1.5px solid var(--po-fa); padding:8px 10px">
                                <strong>GHI CHÚ:</strong>
                            </td>
                            <td colspan="3" style="text-align:center; border-top:1.5px solid var(--po-fa); padding:8px 10px">
                                <strong>Đại diện nhà ăn</strong>
                            </td>
                            <td colspan="3" style="text-align:center; border-top:1.5px solid var(--po-fa); padding:8px 10px">
                                <strong>Người kiểm tra</strong>
                            </td>
                        </tr>
                        <tr class="byt-sign-text">
                            <td colspan="4" style="text-align:left; padding:4px 10px">Đ: Đạt</td>
                            <td colspan="3" style="text-align:center; padding:4px 10px">—</td>
                            <td colspan="3" style="text-align:center; font-weight:700; color:var(--po-tx); padding:4px 10px">
                                {{ $inspector }}
                            </td>
                        </tr>
                        <tr class="byt-sign-text">
                            <td colspan="4" style="text-align:left; padding:4px 10px">K: Không đạt</td>
                            <td colspan="3" style="text-align:center; padding:4px 10px">—</td>
                            <td colspan="3" style="text-align:center; padding:4px 10px">—</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tem nhãn lưu mẫu: ẩn trên màn hình, chỉ hiện khi in (nút "In tem nhãn lưu mẫu" ở tab Lưu mẫu) --}}
    @if($activeStep === 'Lưu mẫu')
        <div class="fsa-print-labels" style="display:none">
            @foreach($auditItems as $item)
                <div class="fsa-label">
                    <div class="fsa-label-head">TEM LƯU MẪU THỨC ĂN — {{ $canteen }}</div>
                    <table class="fsa-label-table">
                        <tr><td>Món ăn:</td><td><strong>{{ $item['name'] }}</strong></td></tr>
                        <tr><td>Ca:</td><td><strong>{{ $item['shift'] ?? '—' }}</strong></td></tr>
                        <tr><td>Mã mẫu:</td><td><strong>{{ $item['sample_code'] ?: '—' }}</strong></td></tr>
                        <tr><td>Ngày:</td><td>{{ date('d/m/Y', strtotime($date)) }}</td></tr>
                        <tr><td>Giờ lưu:</td><td>{{ $item['time'] ?: '—' }}</td></tr>
                        <tr><td>KL mẫu:</td><td>{{ $item['quantity'] ?: '≥100g' }}</td></tr>
                        <tr><td>Nhiệt độ lưu:</td><td>{{ $item['temp'] ?: '2-8°C' }}</td></tr>
                        <tr><td>Người lưu:</td><td>{{ $item['staff'] ?: $inspector }}</td></tr>
                        <tr><td>Hủy sau:</td><td>24 giờ</td></tr>
                    </table>
                </div>
            @endforeach
        </div>

        <style>
            @media print {
                body * { visibility: hidden !important; }
                .fsa-print-labels, .fsa-print-labels * { visibility: visible !important; }
                .fsa-print-labels {
                    display: flex !important;
                    flex-wrap: wrap;
                    gap: 6mm;
                    position: absolute;
                    top: 0; left: 0;
                    width: 100%;
                    padding: 8mm;
                    background: #fff;
                }
                .fsa-label {
                    width: 62mm;
                    border: 1px solid #000;
                    border-radius: 2mm;
                    padding: 3mm;
                    page-break-inside: avoid;
                    font-size: 9pt;
                    color: #000;
                }
                .fsa-label-head {
                    font-weight: 700;
                    font-size: 8pt;
                    text-align: center;
                    border-bottom: 1px solid #000;
                    padding-bottom: 1.5mm;
                    margin-bottom: 1.5mm;
                }
                .fsa-label-table { width: 100%; border-collapse: collapse; }
                .fsa-label-table td { padding: 0.6mm 0; vertical-align: top; }
                .fsa-label-table td:first-child { width: 38%; color: #333; }
            }
        </style>
    @endif
</div>
