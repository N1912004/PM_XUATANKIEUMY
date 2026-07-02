<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Top filter and actions bar -->
        <div class="p-6 bg-white rounded-xl border border-gray-150 shadow-sm dark:bg-gray-900 dark:border-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-6">
                <!-- Date picker & navigation -->
                <div class="flex items-center gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Ngày kiểm thực</label>
                        <input type="date" wire:model.live="date" 
                               class="rounded-lg border-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white font-semibold text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Cơ sở / địa điểm</label>
                        <select wire:model.live="canteen" 
                                class="rounded-lg border-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white text-sm font-semibold">
                            <option value="Canteen Summit">Canteen Summit</option>
                            <option value="Bếp chính Nhơn Trạch">Bếp chính Nhơn Trạch</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Người kiểm tra</label>
                        <select wire:model.live="inspector" 
                                class="rounded-lg border-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white text-sm font-semibold">
                            <option value="Nguyễn Văn An">Nguyễn Văn An</option>
                            <option value="Trần Thị Bình">Trần Thị Bình</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-4 sm:mt-0">
                    <button class="px-4 py-2 text-sm font-semibold rounded-lg bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-950/20 dark:text-green-400 flex items-center gap-2 border border-green-150 dark:border-green-900/30">
                        📥 Xuất toàn bộ Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Steps Tabs -->
        <div class="flex border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-2 rounded-xl border">
            @foreach(['Bước 1', 'Bước 2', 'Bước 3', 'Bước 4', 'Bước 5'] as $step)
                <button wire:click="$set('activeStep', '{{ $step }}')"
                        class="flex-1 py-3 text-sm font-bold rounded-lg transition-all {{ $activeStep === $step ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">
                    {{ $step }}
                </button>
            @endforeach
        </div>

        <!-- Stats Grid -->
        @php $stats = $this->getStats(); @endphp
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="p-3 mr-4 text-blue-500 bg-blue-50 rounded-lg dark:bg-blue-900/20 text-xl font-bold">
                    🥬
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['ingredients'] }}</p>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Nguyên liệu B1 (Từ món ăn trong ngày)</p>
                </div>
            </div>

            <div class="flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="p-3 mr-4 text-green-500 bg-green-50 rounded-lg dark:bg-green-900/20 text-xl font-bold">
                    🍜
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['dishes'] }}</p>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Món ăn phân theo ca</p>
                </div>
            </div>

            <div class="flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="p-3 mr-4 text-orange-500 bg-orange-50 rounded-lg dark:bg-orange-900/20 text-xl font-bold">
                    👥
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ number_format($stats['portions']) }}</p>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Tổng suất theo tổng món</p>
                </div>
            </div>

            <div class="flex items-center p-4 bg-white rounded-xl border border-gray-100 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="p-3 mr-4 text-purple-500 bg-purple-50 rounded-lg dark:bg-purple-900/20 text-xl font-bold">
                    📄
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['forms'] }}</p>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Biểu mẫu lưu trữ (B1 đến B5)</p>
                </div>
            </div>
        </div>

        <!-- Official Ministry of Health Report View -->
        <div class="bg-white rounded-xl border border-gray-150 shadow-sm dark:bg-gray-900 dark:border-gray-800 overflow-hidden">
            <!-- Header Block inside table style -->
            <div class="p-6 bg-gray-50 border-b border-gray-100 dark:bg-gray-800/30 dark:border-gray-800 text-center space-y-2">
                <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                    CN NHƠN TRẠCH - CÔNG TY TNHH DỊCH VỤ CJ CATERING VIỆT NAM
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    ĐỊA CHỈ: TỔ 15, ẤP 2 XÃ LONG THỌ, HUYỆN NHƠN TRẠCH, TỈNH ĐỒNG NAI
                </p>
                <div class="flex justify-center gap-6 pt-2 text-xs font-semibold text-gray-700 dark:text-gray-300">
                    <span>📍 ĐỊA ĐIỂM KIỂM TRA: <strong class="text-primary-600">{{ $canteen }}</strong></span>
                    <span>👤 NGƯỜI KIỂM TRA: <strong class="text-primary-600">{{ $inspector }} ( THỦ CÔNG )</strong></span>
                </div>
                <div class="mt-4 inline-block bg-primary-50 text-primary-700 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider dark:bg-primary-950/20 dark:text-primary-400 border border-primary-100 dark:border-primary-900/30">
                    {{ $activeStep }}: KIỂM TRA TRƯỚC KHI CHẾ BIẾN THỨC ĂN — BAN HÀNH: QĐ 1246/2017-BYT
                </div>
            </div>

            <!-- Table content -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-150/50 dark:bg-gray-800/80 text-gray-700 dark:text-gray-350 font-bold border-b border-gray-200 dark:border-gray-700">
                            <th class="p-3 w-12 text-center border-r border-gray-200 dark:border-gray-700">TT</th>
                            <th class="p-3 border-r border-gray-200 dark:border-gray-700">TÊN THỰC PHẨM</th>
                            <th class="p-3 w-28 border-r border-gray-200 dark:border-gray-700 text-center">THỜI GIAN NHẬP</th>
                            <th class="p-3 w-28 border-r border-gray-200 dark:border-gray-700 text-right">KHỐI LƯỢNG (KG)</th>
                            <th class="p-3 border-r border-gray-200 dark:border-gray-700">NƠI CUNG CẤP THỰC PHẨM</th>
                            <th class="p-3 w-28 border-r border-gray-200 dark:border-gray-700 text-center">CHỨNG TỪ, HÓA ĐƠN</th>
                            <th class="p-3 w-20 border-r border-gray-200 dark:border-gray-700 text-center">ĐK VS THÚ Y</th>
                            <th class="p-3 w-20 border-r border-gray-200 dark:border-gray-700 text-center">CẢM QUAN (Đ/K)</th>
                            <th class="p-3 w-20 border-r border-gray-200 dark:border-gray-700 text-center">TEST NHANH</th>
                            <th class="p-3">BIỆN PHÁP XỬ LÝ / GHI CHÚ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        <!-- Group I -->
                        <tr class="bg-blue-50/30 dark:bg-blue-950/10 font-bold text-blue-700 dark:text-blue-400">
                            <td colspan="10" class="p-3">I. Thực phẩm tươi sống, đông lạnh: thịt, cá, gà... v.v.</td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 font-semibold">1</td>
                            <td class="p-3 border-r border-gray-200 dark:border-gray-700 font-bold text-gray-900 dark:text-white">Vịt bọng</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">5h00</td>
                            <td class="p-3 text-right border-r border-gray-200 dark:border-gray-700 font-bold">30.000</td>
                            <td class="p-3 border-r border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-450">Công ty TNHH Thực phẩm Hưng Thịnh</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">HĐ-HT-2026</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700"><span class="px-2 py-0.5 rounded bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 text-xxs font-bold">Đạt</span></td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700"><span class="px-2 py-0.5 rounded bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 text-xxs font-bold">Đạt</span></td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">—</td>
                            <td class="p-3 text-gray-400 italic">Cảm quan tươi tốt, bảo quản lạnh</td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 font-semibold">2</td>
                            <td class="p-3 border-r border-gray-200 dark:border-gray-700 font-bold text-gray-900 dark:text-white">Trứng gà tươi</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">5h00</td>
                            <td class="p-3 text-right border-r border-gray-200 dark:border-gray-700 font-bold">360.000</td>
                            <td class="p-3 border-r border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-450">Công ty TNHH Thực phẩm Hưng Thịnh</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">HĐ-HT-2027</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700"><span class="px-2 py-0.5 rounded bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 text-xxs font-bold">Đạt</span></td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700"><span class="px-2 py-0.5 rounded bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 text-xxs font-bold">Đạt</span></td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">—</td>
                            <td class="p-3 text-gray-400 italic">Trứng sạch, không nứt vỡ</td>
                        </tr>

                        <!-- Group II -->
                        <tr class="bg-green-50/30 dark:bg-green-950/10 font-bold text-green-700 dark:text-green-400">
                            <td colspan="10" class="p-3">II. Rau củ, quả, trái cây, các loại...</td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 font-semibold">3</td>
                            <td class="p-3 border-r border-gray-200 dark:border-gray-700 font-bold text-gray-900 dark:text-white">Su su quả</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">5h15</td>
                            <td class="p-3 text-right border-r border-gray-200 dark:border-gray-700 font-bold">30.000</td>
                            <td class="p-3 border-r border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-450">Cơ sở Rau sạch Xanh Việt</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">HĐ-XV-592</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">—</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700"><span class="px-2 py-0.5 rounded bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 text-xxs font-bold">Đạt</span></td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">—</td>
                            <td class="p-3 text-gray-400 italic">Tươi ngon, không héo dập</td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 font-semibold">4</td>
                            <td class="p-3 border-r border-gray-200 dark:border-gray-700 font-bold text-gray-900 dark:text-white">Cải xanh</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">5h15</td>
                            <td class="p-3 text-right border-r border-gray-200 dark:border-gray-700 font-bold">7.200</td>
                            <td class="p-3 border-r border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-450">Cơ sở Rau sạch Xanh Việt</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">HĐ-XV-593</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">—</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700"><span class="px-2 py-0.5 rounded bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 text-xxs font-bold">Đạt</span></td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">—</td>
                            <td class="p-3 text-gray-400 italic">Rau xanh, không có sâu bệnh</td>
                        </tr>

                        <!-- Group III -->
                        <tr class="bg-yellow-50/30 dark:bg-yellow-950/10 font-bold text-yellow-700 dark:text-yellow-400">
                            <td colspan="10" class="p-3">III. Bún, đậu hũ, thực phẩm chế biến sẵn...</td>
                        </tr>
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 font-semibold">5</td>
                            <td class="p-3 border-r border-gray-200 dark:border-gray-700 font-bold text-gray-900 dark:text-white">Đậu hũ miếng</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">5h30</td>
                            <td class="p-3 text-right border-r border-gray-200 dark:border-gray-700 font-bold">75.000</td>
                            <td class="p-3 border-r border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-450">Công ty TNHH Thực phẩm Hưng Thịnh</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">HĐ-HT-2028</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">—</td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700"><span class="px-2 py-0.5 rounded bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 text-xxs font-bold">Đạt</span></td>
                            <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">—</td>
                            <td class="p-3 text-gray-400 italic">Miếng đậu chắc mịn, mùi thơm nhẹ đặc trưng</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
