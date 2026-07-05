<style>
    .btn-export-excel {
        background-color: #16a34a !important; /* bg-green-600 */
        color: #ffffff !important;
        border: 1px solid #15803d !important;
    }
    .btn-export-excel:hover {
        background-color: #15803d !important; /* bg-green-700 */
    }
    .btn-export-csv {
        background-color: #f0fdf4 !important; /* bg-green-50 */
        color: #15803d !important; /* text-green-700 */
        border: 1px solid #dcfce7 !important; /* border-green-200 */
    }
    .btn-export-csv:hover {
        background-color: #dcfce7 !important; /* bg-green-100 */
    }
    .dark .btn-export-csv {
        background-color: rgba(22, 163, 74, 0.1) !important;
        color: #4ade80 !important;
        border-color: rgba(22, 163, 74, 0.2) !important;
    }
    .dark .btn-export-csv:hover {
        background-color: rgba(22, 163, 74, 0.2) !important;
    }
</style>

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Top filter and actions bar -->
        <div class="p-6 bg-white rounded-xl border border-gray-150 shadow-sm dark:bg-gray-900 dark:border-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-6">
                <!-- Date picker & navigation -->
                <div class="flex flex-wrap items-center gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Ngày kiểm thực</label>
                        <input type="date" wire:model.live="date" 
                               class="rounded-lg border-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white font-semibold text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Ca phục vụ</label>
                        <select wire:model.live="selectedShift" 
                                class="rounded-lg border-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white text-sm font-semibold">
                            <option value="">Tất cả ca</option>
                            @foreach(\App\Models\Shift::all() as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Cơ sở / Địa điểm</label>
                        <select wire:model.live="canteen" 
                                class="rounded-lg border-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white text-sm font-semibold">
                            @foreach(\App\Models\Area::all() as $area)
                                <option value="{{ $area->name }}">{{ $area->name }}</option>
                            @endforeach
                            @foreach(\App\Models\Kitchen::all() as $kitchen)
                                <option value="{{ $kitchen->name }}">{{ $kitchen->name }}</option>
                            @endforeach
                            <option value="Canteen Summit">Canteen Summit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Người kiểm tra</label>
                        <select wire:model.live="inspector" 
                                class="rounded-lg border-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white text-sm font-semibold">
                            @foreach(\App\Models\Employee::all() as $emp)
                                <option value="{{ $emp->name }}">{{ $emp->name }}</option>
                            @endforeach
                            <option value="Nguyễn Văn An">Nguyễn Văn An</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-4 sm:mt-0">
                    <button wire:click="exportExcel" class="px-4 py-2 text-sm font-semibold rounded-lg btn-export-excel flex items-center gap-2 shadow-sm active:scale-95 transition-all">
                        📊 Xuất Excel (Biểu mẫu BYT)
                    </button>
                    <button wire:click="exportCSV" class="px-4 py-2 text-sm font-semibold rounded-lg btn-export-csv flex items-center gap-2 active:scale-95 transition-all">
                        📥 Xuất CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Steps Tabs -->
        <div class="flex border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-2 rounded-xl border">
            @foreach(['Bước 1', 'Bước 2', 'Bước 3', 'Lưu mẫu', 'Hủy mẫu'] as $step)
                <button wire:click="$set('activeStep', '{{ $step }}')"
                        class="flex-1 py-3 text-sm font-bold rounded-lg transition-all {{ $activeStep === $step ? 'text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}"
                        style="{{ $activeStep === $step ? 'background-color: rgb(var(--primary-600)) !important; color: white !important;' : '' }}">
                    {{ $step }}
                </button>
            @endforeach
        </div>

        <!-- Stats Grid -->
        @php $stats = $this->getStats(); @endphp
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="p-3 mr-4 text-blue-500 bg-blue-50 rounded-lg dark:bg-blue-900/20 text-xl font-bold">🥬</div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['ingredients'] }}</p>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Nguyên liệu (Từ thực đơn ngày)</p>
                </div>
            </div>

            <div class="flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="p-3 mr-4 text-green-500 bg-green-50 rounded-lg dark:bg-green-900/20 text-xl font-bold">🍜</div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['dishes'] }}</p>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Món ăn phục vụ</p>
                </div>
            </div>

            <div class="flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="p-3 mr-4 text-orange-500 bg-orange-50 rounded-lg dark:bg-orange-900/20 text-xl font-bold">👥</div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ number_format($stats['portions']) }}</p>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Tổng suất ăn dự kiến</p>
                </div>
            </div>

            <div class="flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="p-3 mr-4 text-purple-500 bg-purple-50 rounded-lg dark:bg-purple-900/20 text-xl font-bold">📄</div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['forms'] }}</p>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Biểu mẫu kiểm thực (QĐ 1246)</p>
                </div>
            </div>
        </div>

        <!-- Official Ministry of Health Report View -->
        <div class="bg-white rounded-xl border border-gray-150 shadow-sm dark:bg-gray-900 dark:border-gray-800 overflow-hidden">
            <!-- Header Block -->
            <div class="p-6 bg-gray-50 border-b border-gray-100 dark:bg-gray-800/30 dark:border-gray-800 text-center space-y-2">
                <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                    CN NHƠN TRẠCH - CÔNG TY TNHH DỊCH VỤ CJ CATERING VIỆT NAM
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    ĐỊA CHỈ: TỔ 15, ẤP 2 XÃ LONG THỌ, HUYỆN NHƠN TRẠCH, TỈNH ĐỒNG NAI
                </p>
                <div class="flex justify-center gap-6 pt-2 text-xs font-semibold text-gray-700 dark:text-gray-300">
                    <span>📍 ĐỊA ĐIỂM KIỂM TRA: <strong style="color: rgb(var(--primary-600)) !important;">{{ $canteen }}</strong></span>
                    <span>👤 NGƯỜI KIỂM TRA: <strong style="color: rgb(var(--primary-600)) !important;">{{ $inspector }}</strong></span>
                </div>
                <div class="mt-4 inline-block px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border"
                     style="background-color: rgba(var(--primary-600), 0.1) !important; color: rgb(var(--primary-600)) !important; border-color: rgba(var(--primary-600), 0.2) !important;">
                    {{ $activeStep }}: 
                    @if($activeStep === 'Bước 1')
                        KIỂM TRA TRƯỚC KHI CHẾ BIẾN THỨC ĂN (ĐẦU VÀO)
                    @elseif($activeStep === 'Bước 2')
                        KIỂM TRA TRONG QUÁ TRÌNH CHẾ BIẾN THỨC ĂN
                    @elseif($activeStep === 'Bước 3')
                        KIỂM TRA TRƯỚC KHI ĂN (BÀN GIAO / CHIA SUẤT)
                    @elseif($activeStep === 'Lưu mẫu')
                        THEO DÕI LƯU MẪU THỨC ĂN TRONG 24 GIỜ
                    @elseif($activeStep === 'Hủy mẫu')
                        THEO DÕI HỦY MẪU THỨC ĂN HẾT HẠN LƯU
                    @endif
                    — BAN HÀNH: QĐ 1246/2017-BYT
                </div>
            </div>

            <!-- Dynamic Table Content -->
            @php $auditItems = $this->getAuditItems(); @endphp
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-150/50 dark:bg-gray-800/80 text-gray-700 dark:text-gray-350 font-bold border-b border-gray-200 dark:border-gray-700">
                            @if($activeStep === 'Bước 1')
                                <th class="p-3 w-12 text-center border-r border-gray-200 dark:border-gray-700">TT</th>
                                <th class="p-3 border-r border-gray-200 dark:border-gray-700">TÊN THỰC PHẨM</th>
                                <th class="p-3 w-28 border-r border-gray-200 dark:border-gray-700 text-center">THỜI GIAN NHẬP</th>
                                <th class="p-3 w-28 border-r border-gray-200 dark:border-gray-700 text-right">KHỐI LƯỢNG (KG)</th>
                                <th class="p-3 border-r border-gray-200 dark:border-gray-700">NƠI CUNG CẤP THỰC PHẨM</th>
                                <th class="p-3 w-32 border-r border-gray-200 dark:border-gray-700 text-center">CHỨNG TỪ, HÓA ĐƠN</th>
                                <th class="p-3 w-24 border-r border-gray-200 dark:border-gray-700 text-center">ĐK VS THÚ Y</th>
                                <th class="p-3 w-24 border-r border-gray-200 dark:border-gray-700 text-center">CẢM QUAN (Đ/K)</th>
                                <th class="p-3 w-20 border-r border-gray-200 dark:border-gray-700 text-center">TEST NHANH</th>
                                <th class="p-3">BIỆN PHÁP XỬ LÝ / GHI CHÚ</th>
                            @elseif($activeStep === 'Bước 2')
                                <th class="p-3 w-12 text-center border-r border-gray-200 dark:border-gray-700">TT</th>
                                <th class="p-3 border-r border-gray-200 dark:border-gray-700">TÊN MÓN ĂN CHẾ BIẾN</th>
                                <th class="p-3 w-28 border-r border-gray-200 dark:border-gray-700 text-center">GIỜ CHẾ BIẾN</th>
                                <th class="p-3 w-24 border-r border-gray-200 dark:border-gray-700 text-center">CẢM QUAN (Đ/K)</th>
                                <th class="p-3 w-28 border-r border-gray-200 dark:border-gray-700 text-center">NHIỆT ĐỘ TRUNG TÂM</th>
                                <th class="p-3 border-r border-gray-200 dark:border-gray-700">NGƯỜI THỰC HIỆN</th>
                                <th class="p-3 w-36 border-r border-gray-200 dark:border-gray-700">KHU VỰC BẾP NẤU</th>
                                <th class="p-3">BIỆN PHÁP XỬ LÝ / GHI CHÚ</th>
                            @elseif($activeStep === 'Bước 3')
                                <th class="p-3 w-12 text-center border-r border-gray-200 dark:border-gray-700">TT</th>
                                <th class="p-3 border-r border-gray-200 dark:border-gray-700">TÊN MÓN ĂN CHIA SUẤT</th>
                                <th class="p-3 w-28 border-r border-gray-200 dark:border-gray-700 text-center">GIỜ CHIA SUẤT</th>
                                <th class="p-3 w-24 border-r border-gray-200 dark:border-gray-700 text-center">CẢM QUAN (Đ/K)</th>
                                <th class="p-3 w-32 border-r border-gray-200 dark:border-gray-700 text-center">LƯU MẪU THỨC ĂN</th>
                                <th class="p-3 w-28 border-r border-gray-200 dark:border-gray-700 text-center">NHIỆT ĐỘ CHIA SUẤT</th>
                                <th class="p-3">BIỆN PHÁP XỬ LÝ / GHI CHÚ</th>
                            @elseif($activeStep === 'Lưu mẫu')
                                <th class="p-3 w-12 text-center border-r border-gray-200 dark:border-gray-700">TT</th>
                                <th class="p-3 border-r border-gray-200 dark:border-gray-700">TÊN MÓN ĂN LƯU MẪU</th>
                                <th class="p-3 w-28 border-r border-gray-200 dark:border-gray-700 text-center">GIỜ LƯU MẪU</th>
                                <th class="p-3 w-28 border-r border-gray-200 dark:border-gray-700 text-center">KHỐI LƯỢNG MẪU</th>
                                <th class="p-3 w-40 border-r border-gray-200 dark:border-gray-700 text-center">MÃ SỐ MẪU LƯU</th>
                                <th class="p-3 w-28 border-r border-gray-200 dark:border-gray-700 text-center">NHIỆT ĐỘ TỦ LƯU</th>
                                <th class="p-3 border-r border-gray-200 dark:border-gray-700">NGƯỜI THỰC HIỆN LƯU</th>
                                <th class="p-3">BIỆN PHÁP XỬ LÝ / GHI CHÚ</th>
                            @elseif($activeStep === 'Hủy mẫu')
                                <th class="p-3 w-12 text-center border-r border-gray-200 dark:border-gray-700">TT</th>
                                <th class="p-3 border-r border-gray-200 dark:border-gray-700">TÊN MÓN ĂN HỦY MẪU</th>
                                <th class="p-3 w-28 border-r border-gray-200 dark:border-gray-700 text-center">GIỜ HỦY MẪU</th>
                                <th class="p-3 w-32 border-r border-gray-200 dark:border-gray-700 text-center">THỜI GIAN LƯU ĐỦ</th>
                                <th class="p-3 w-36 border-r border-gray-200 dark:border-gray-700 text-center">TÌNH TRẠNG KHI HỦY</th>
                                <th class="p-3 border-r border-gray-200 dark:border-gray-700">NGƯỜI HỦY MẪU</th>
                                <th class="p-3">BIỆN PHÁP XỬ LÝ / GHI CHÚ</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @if(empty($auditItems))
                            <tr>
                                <td colspan="10" class="p-8 text-center text-gray-500 font-semibold italic">
                                    Không tìm thấy dữ liệu thực đơn / nguyên liệu phù hợp với ngày và ca đã chọn để làm báo cáo kiểm thực.
                                </td>
                            </tr>
                        @else
                            @if($activeStep === 'Bước 1')
                                <!-- Dynamic Step 1 Rows -->
                                @foreach($auditItems as $index => $item)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 font-semibold">{{ $index + 1 }}</td>
                                        <td class="p-3 border-r border-gray-200 dark:border-gray-700 font-bold text-gray-900 dark:text-white">{{ $item['name'] }}</td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">{{ $item['time'] }}</td>
                                        <td class="p-3 text-right border-r border-gray-200 dark:border-gray-700 font-bold">
                                            @if($item['unit'] === 'Quả' || $item['unit'] === 'Trái' || $item['unit'] === 'Cái')
                                                {{ number_format($item['quantity'], 0) }} {{ $item['unit'] }}
                                            @else
                                                {{ number_format($item['quantity'] / 1000, 3, ',', '.') }} kg
                                            @endif
                                        </td>
                                        <td class="p-3 border-r border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-450">{{ $item['supplier'] }}</td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">{{ $item['invoice'] }}</td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">
                                            @if($item['vet_check'] === 'Đạt')
                                                <span class="px-2 py-0.5 rounded bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 text-xxs font-bold">Đạt</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">
                                            <span class="px-2 py-0.5 rounded bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 text-xxs font-bold">Đạt</span>
                                        </td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">{{ $item['quick_test'] }}</td>
                                        <td class="p-3 text-gray-500 italic">{{ $item['notes'] }}</td>
                                    </tr>
                                @endforeach
                            @elseif($activeStep === 'Bước 2')
                                <!-- Dynamic Step 2 Rows -->
                                @foreach($auditItems as $index => $item)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 font-semibold">{{ $index + 1 }}</td>
                                        <td class="p-3 border-r border-gray-200 dark:border-gray-700 font-bold text-gray-900 dark:text-white">{{ $item['name'] }}</td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">{{ $item['time'] }}</td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">
                                            <span class="px-2 py-0.5 rounded bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 text-xxs font-bold">Đạt</span>
                                        </td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 font-bold text-emerald-600">{{ $item['temp'] }}</td>
                                        <td class="p-3 border-r border-gray-200 dark:border-gray-700 font-medium">{{ $item['cook'] }}</td>
                                        <td class="p-3 border-r border-gray-200 dark:border-gray-700 text-gray-500">{{ $item['kitchen'] }}</td>
                                        <td class="p-3 text-gray-500 italic">{{ $item['notes'] }}</td>
                                    </tr>
                                @endforeach
                            @elseif($activeStep === 'Bước 3')
                                <!-- Dynamic Step 3 Rows -->
                                @foreach($auditItems as $index => $item)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 font-semibold">{{ $index + 1 }}</td>
                                        <td class="p-3 border-r border-gray-200 dark:border-gray-700 font-bold text-gray-900 dark:text-white">{{ $item['name'] }}</td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">{{ $item['time'] }}</td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">
                                            <span class="px-2 py-0.5 rounded bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 text-xxs font-bold">Đạt</span>
                                        </td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 font-semibold dark:text-primary-400" style="color: rgb(var(--primary-600));">{{ $item['sample_kept'] }}</td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 font-bold text-emerald-600">{{ $item['temp'] }}</td>
                                        <td class="p-3 text-gray-500 italic">{{ $item['notes'] }}</td>
                                    </tr>
                                @endforeach
                            @elseif($activeStep === 'Lưu mẫu')
                                <!-- Dynamic Keep Samples Rows -->
                                @foreach($auditItems as $index => $item)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 font-semibold">{{ $index + 1 }}</td>
                                        <td class="p-3 border-r border-gray-200 dark:border-gray-700 font-bold text-gray-900 dark:text-white">{{ $item['name'] }}</td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">{{ $item['time'] }}</td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 font-semibold">{{ $item['quantity'] }}</td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 font-mono font-bold text-purple-600">{{ $item['sample_code'] }}</td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 font-bold dark:text-primary-400" style="color: rgb(var(--primary-600));">{{ $item['temp'] }}</td>
                                        <td class="p-3 border-r border-gray-200 dark:border-gray-700 font-medium">{{ $item['staff'] }}</td>
                                        <td class="p-3 text-gray-500 italic">{{ $item['notes'] }}</td>
                                    </tr>
                                @endforeach
                            @elseif($activeStep === 'Hủy mẫu')
                                <!-- Dynamic Discard Samples Rows -->
                                @foreach($auditItems as $index => $item)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 font-semibold">{{ $index + 1 }}</td>
                                        <td class="p-3 border-r border-gray-200 dark:border-gray-700 font-bold text-gray-900 dark:text-white">{{ $item['name'] }}</td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">{{ $item['time'] }}</td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 font-semibold text-gray-600">{{ $item['retention'] }}</td>
                                        <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 font-bold text-green-600">{{ $item['status'] }}</td>
                                        <td class="p-3 border-r border-gray-200 dark:border-gray-700 font-medium">{{ $item['staff'] }}</td>
                                        <td class="p-3 text-gray-500 italic">{{ $item['notes'] }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
