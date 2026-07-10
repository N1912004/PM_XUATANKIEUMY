<div class="emp-page">
    @include('filament.resources.food-safety-audits.partials.styles')

    @php
        $stats = $this->getStats();
        $auditItems = $this->getAuditItems();
    @endphp

    @if (session()->has('message'))
        <div style="background:#ECFDF5; color:#065F46; padding:12px 16px; border-radius:8px; border:1px solid #A7F3D0; margin-bottom:16px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px">
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
            <label>Cơ sở / địa điểm</label>
            <input wire:model.live="canteen" class="ctrl" placeholder="Canteen Summit">
        </div>
        <div class="field" style="min-width:180px">
            <label>Người kiểm tra</label>
            <input wire:model.live="inspector" class="ctrl" placeholder="Nguyễn Văn An">
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
        <button wire:click="exportCSV" class="emp-btn" style="height:36px">
            <i class="fa-solid fa-file-excel" style="color:var(--po-gn)"></i> Xuất bước đang chọn
        </button>
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
                        <th colspan="{{ $activeStep === 'Bước 1' ? 10 : ($activeStep === 'Bước 2' ? 8 : ($activeStep === 'Bước 3' ? 7 : 8)) }}" style="background:#F8FAFC; text-align:center; padding:12px">
                            <div style="font-size:13px; font-weight:800; color:var(--po-tx)">
                                CN NHƠN TRẠCH - CÔNG TY TNHH DỊCH VỤ CJ CATERING VIỆT NAM
                            </div>
                            <div style="font-size:11px; font-weight:600; color:var(--po-mu); margin-top:2px">
                                ĐỊA CHỈ: TỔ 15, ẤP 2 XÃ LONG THỌ, HUYỆN NHƠN TRẠCH, TỈNH ĐỒNG NAI
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="{{ $activeStep === 'Bước 1' ? 4 : ($activeStep === 'Bước 2' ? 3 : ($activeStep === 'Bước 3' ? 3 : 3)) }}" style="text-align:left; background:#fff; font-weight:700">
                            📍 ĐỊA ĐIỂM KIỂM TRA: {{ $canteen }}
                        </th>
                        <th colspan="{{ $activeStep === 'Bước 1' ? 6 : ($activeStep === 'Bước 2' ? 5 : ($activeStep === 'Bước 3' ? 4 : 5)) }}" style="text-align:right; background:#fff; font-weight:700">
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
                            <!-- Step 1 Rows -->
                            @if(isset($item['loai']) && str_starts_with($item['name'], 'I.'))
                                <tr>
                                    <td colspan="10" class="byt-group-title">{{ $item['name'] }}</td>
                                </tr>
                            @else
                                <tr class="emp-row">
                                    <td class="text-center font-bold" style="color:var(--po-mu)">{{ $index + 1 }}</td>
                                    <td class="font-bold">{{ $item['name'] }}</td>
                                    <td class="text-center">{{ $item['time'] ?? '05:00' }}</td>
                                    <td class="text-right font-bold" style="color:var(--po-bl)">
                                        @if(isset($item['unit']) && ($item['unit'] === 'Quả' || $item['unit'] === 'Trái' || $item['unit'] === 'Cái'))
                                            {{ number_format($item['quantity'], 0) }} {{ $item['unit'] }}
                                        @else
                                            {{ number_format(($item['quantity'] ?? 0) / 1000, 2, ',', '.') }} kg
                                        @endif
                                    </td>
                                    <td>{{ $item['supplier'] ?? 'Cơ sở tự do' }}</td>
                                    <td class="text-center">{{ $item['invoice'] ?? '—' }}</td>
                                    <td class="text-center">
                                        @if(($item['vet_check'] ?? '') === 'Đạt')
                                            <span class="px-2 py-0.5 rounded bg-green-50 text-green-700 font-bold" style="font-size:11px">Đạt</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="px-2 py-0.5 rounded bg-green-50 text-green-700 font-bold" style="font-size:11px">Đạt</span>
                                    </td>
                                    <td class="text-center">{{ $item['quick_test'] ?? '—' }}</td>
                                    <td style="color:var(--po-mu); font-style:italic">{{ $item['notes'] ?? 'Cảm quan tốt, sạch sẽ' }}</td>
                                </tr>
                            @endif
                        @elseif($activeStep === 'Bước 2')
                            <!-- Step 2 Rows -->
                            <tr class="emp-row">
                                <td class="text-center font-bold" style="color:var(--po-mu)">{{ $index + 1 }}</td>
                                <td class="font-bold">{{ $item['name'] }}</td>
                                <td class="text-center">{{ $item['time'] ?: '07:00 - 09:30' }}</td>
                                <td class="text-center">
                                    <span class="px-2 py-0.5 rounded bg-green-50 text-green-700 font-bold" style="font-size:11px">Đạt</span>
                                </td>
                                <td class="text-center font-bold" style="color:var(--po-gn)">{{ $item['temp'] ?: '75°C' }}</td>
                                <td class="font-bold">{{ $item['cook'] ?: 'Lê Hoàng Cường' }}</td>
                                <td>{{ $item['kitchen'] ?: 'Bếp nấu chính' }}</td>
                                <td style="color:var(--po-mu); font-style:italic">{{ $item['notes'] ?: 'Chín đều, đạt màu sắc' }}</td>
                            </tr>
                        @elseif($activeStep === 'Bước 3')
                            <!-- Step 3 Rows -->
                            <tr class="emp-row">
                                <td class="text-center font-bold" style="color:var(--po-mu)">{{ $index + 1 }}</td>
                                <td class="font-bold">{{ $item['name'] }}</td>
                                <td class="text-center">{{ $item['time'] ?: '10:30 - 11:00' }}</td>
                                <td class="text-center">
                                    <span class="px-2 py-0.5 rounded bg-green-50 text-green-700 font-bold" style="font-size:11px">Đạt</span>
                                </td>
                                <td class="text-center font-bold" style="color:var(--po-bl)">{{ $item['sample_kept'] ?: 'Có lưu mẫu' }}</td>
                                <td class="text-center font-bold" style="color:var(--po-gn)">{{ $item['temp'] ?: '65°C' }}</td>
                                <td style="color:var(--po-mu); font-style:italic">{{ $item['notes'] ?: 'Nóng sốt, khay sạch' }}</td>
                            </tr>
                        @elseif($activeStep === 'Lưu mẫu')
                            <!-- Keep Sample Rows -->
                            <tr class="emp-row">
                                <td class="text-center font-bold" style="color:var(--po-mu)">{{ $index + 1 }}</td>
                                <td class="font-bold">{{ $item['name'] }}</td>
                                <td class="text-center">{{ $item['time'] ?: '10:30' }}</td>
                                <td class="text-center font-bold">{{ $item['quantity'] ?: '150g' }}</td>
                                <td class="text-center font-mono font-bold" style="color:var(--po-pu)">{{ $item['sample_code'] ?: 'M-20260518-01' }}</td>
                                <td class="text-center font-bold" style="color:var(--po-gn)">{{ $item['temp'] ?: '2-8°C' }}</td>
                                <td class="font-bold">{{ $item['staff'] ?: 'Lê Hoàng Cường' }}</td>
                                <td style="color:var(--po-mu); font-style:italic">{{ $item['notes'] ?: 'Hộp inox tiệt trùng' }}</td>
                            </tr>
                        @elseif($activeStep === 'Hủy mẫu')
                            <!-- Discard Sample Rows -->
                            <tr class="emp-row">
                                <td class="text-center font-bold" style="color:var(--po-mu)">{{ $index + 1 }}</td>
                                <td class="font-bold">{{ $item['name'] }}</td>
                                <td class="text-center">{{ $item['time'] ?: '10:30' }}</td>
                                <td class="text-center">{{ $item['retention'] ?: '24 giờ' }}</td>
                                <td class="text-center font-bold" style="color:var(--po-gn)">{{ $item['status'] ?: 'Bình thường' }}</td>
                                <td class="font-bold">{{ $item['staff'] ?: 'Lê Hoàng Cường' }}</td>
                                <td style="color:var(--po-mu); font-style:italic">{{ $item['notes'] ?: 'Không mùi vị lạ, tiêu hủy' }}</td>
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
                            <td colspan="4" style="text-align:left; border-top:1.5px solid #94A3B8; padding:8px 10px">
                                <strong>GHI CHÚ:</strong>
                            </td>
                            <td colspan="3" style="text-align:center; border-top:1.5px solid #94A3B8; padding:8px 10px">
                                <strong>Đại diện nhà ăn</strong>
                            </td>
                            <td colspan="3" style="text-align:center; border-top:1.5px solid #94A3B8; padding:8px 10px">
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
</div>
