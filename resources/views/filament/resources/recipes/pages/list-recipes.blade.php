<div class="po-page">
    @include('filament.resources.recipes.partials.styles')

    @php
        $recipesList = $this->recipes();
        $prices = $this->priceOptions();
        $types = $this->typeOptions();
    @endphp

    <!-- Page Head -->
    <div class="ph">
        <div class="ph-l">
            <h1>Ngân hàng thực đơn</h1>
            <p>Quản lý món ăn theo từng mức giá và cost nguyên liệu trên 1 phần</p>
        </div>
        <div class="ph-r">
            <label class="btn" style="cursor:pointer">
                <i class="fa-solid fa-file-import"></i>
                {{ $importFile ? 'Đã chọn file' : 'Chọn file định lượng' }}
                <input type="file" wire:model="importFile" accept=".xlsx,.xls" style="display:none">
            </label>
            @if($importFile)
                <button type="button" wire:click="importRecipes" wire:loading.attr="disabled" class="btn btn-p">
                    <i class="fa-solid fa-upload"></i>
                    Nhập Excel
                </button>
            @endif
            @error('importFile') <span style="color:#dc2626; font-size:11.5px; align-self:center">{{ $message }}</span> @enderror
            <a href="{{ route('recipes.export-file') }}" class="btn">
                <i class="fa-solid fa-download"></i>
                Xuất dữ liệu
            </a>
            <a href="{{ \App\Filament\Resources\RecipeResource::getUrl('create') }}" class="btn btn-p">
                <i class="fa-solid fa-plus"></i>
                Thêm món ăn
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="mn-bar">
        <div class="mn-srch">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input wire:model.live.debounce.250ms="search" type="text" placeholder="Tìm kiếm theo tên món ăn hoặc mã món...">
        </div>

        <select wire:model.live="priceFilter" class="mn-sel">
            <option value="">Mức giá / Đơn giá suất ăn</option>
            @foreach($prices as $val => $lbl)
                <option value="{{ $val }}">{{ $lbl }}</option>
            @endforeach
        </select>

        <select wire:model.live="typeFilter" class="mn-sel">
            <option value="">Nhóm món</option>
            @foreach($types as $val => $lbl)
                <option value="{{ $val }}">{{ $lbl }}</option>
            @endforeach
        </select>

        <select wire:model.live="statusFilter" class="mn-sel">
            <option value="">Trạng thái</option>
            <option value="active">Đang áp dụng</option>
            <option value="pending">Chờ rà soát</option>
            <option value="inactive">Ngừng áp dụng</option>
        </select>

        <button wire:click="resetFilters" class="mn-fbtn" title="Cài lại bộ lọc">
            <i class="fa-solid fa-sliders"></i>
            Bộ lọc
        </button>
        <span wire:click="resetFilters" class="mn-clr">Xóa bộ lọc</span>
    </div>

    <!-- Info Banner -->
    <div class="mn-info">
        <i class="fa-solid fa-circle-info"></i>
        Đơn giá nguyên liệu được lấy từ module Nguyên liệu / Nhà cung cấp và dùng để tự động tính cost nguyên liệu trên 1 phần.
    </div>

    <!-- Recipes Main Table -->
    <div class="mn-card">
        <div style="overflow-x:auto">
            <table class="mn-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Mã món</th>
                        <th>Tên món ăn</th>
                        <th>Nhóm món</th>
                        <th>Mức giá suất ăn</th>
                        <th>Đơn giá suất ăn</th>
                        <th>Số nguyên liệu</th>
                        <th>Tổng định lượng / phần</th>
                        <th>Tổng cost nguyên liệu / phần</th>
                        <th>Trạng thái</th>
                        <th>Cập nhật</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recipesList as $recipe)
                        @php
                            $isExpanded = $expandedRecipeId === $recipe->id;
                            $recipeCost = $recipe->effectiveCostPerPortion();
                            $recipeWeight = $recipe->ingredients->sum('pivot.quantity_per_portion');
                            $ingredientsCount = $recipe->ingredients->count();

                            // Chọn class badge cho loại món
                            $typeClass = match ($recipe->type) {
                                'Món mặn' => 'mg-man',
                                'Món xào' => 'mg-xao',
                                'Món canh' => 'mg-canh',
                                'Món chiên' => 'mg-chien',
                                default => 'mg-man',
                            };
                            
                            // Chọn class cho trạng thái
                            $statusClass = match ($recipe->status) {
                                'active' => 'ms-active',
                                'pending' => 'ms-review',
                                'inactive' => 'ms-inactive',
                                default => 'ms-inactive',
                            };
                            $statusText = match ($recipe->status) {
                                'active' => 'Đang hoạt động',
                                'pending' => 'Chờ rà soát',
                                'inactive' => 'Ngừng hoạt động',
                                default => $recipe->status,
                            };
                        @endphp
                        <tr class="{{ $isExpanded ? 'mn-row-sel' : '' }}">
                            <td>
                                <button type="button" wire:click="toggleExpand({{ $recipe->id }})" class="mn-expand-btn {{ $isExpanded ? 'open' : '' }}">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </td>
                            <td><span class="mn-code">{{ $recipe->code }}</span></td>
                            <td><span class="mn-name">{{ $recipe->name }}</span></td>
                            <td><span class="mn-group-pill {{ $typeClass }}">{{ $recipe->type }}</span></td>
                            <td><span class="mn-price">{{ number_format($recipe->price_level, 0, ',', '.') }} d</span></td>
                            <td><span class="mn-price">{{ number_format($recipe->actual_price, 0, ',', '.') }} d</span></td>
                            <td><span class="mn-num">{{ $ingredientsCount }}</span></td>
                            <td><span class="mn-kg">{{ str_replace('.', ',', round($recipeWeight, 2)) }} kg</span></td>
                            <td>
                                <span class="mn-cost" style="{{ $recipe->cost_override !== null ? 'color:var(--or)' : '' }}">
                                    {{ number_format($recipeCost, 0, ',', '.') }} d
                                </span>
                                @if($recipe->cost_override !== null)
                                    <span style="display:block;font-size:10px;color:var(--or)">Đã điều chỉnh</span>
                                @endif
                            </td>
                            <td><span class="{{ $statusClass }}">{{ $statusText }}</span></td>
                            <td><span class="mn-date">{{ $recipe->updated_at->format('d/m/Y H:i') }}</span></td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <!-- Xem chi tiết -->
                                    <a href="{{ \App\Filament\Resources\RecipeResource::getUrl('view', ['record' => $recipe]) }}" class="abt" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <!-- Chỉnh sửa -->
                                    <a href="{{ \App\Filament\Resources\RecipeResource::getUrl('edit', ['record' => $recipe]) }}" class="abt" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <!-- Xóa món -->
                                    <button type="button" wire:click="deleteRecipe({{ $recipe->id }})" wire:confirm="Bạn có chắc chắn muốn xóa món ăn này?" class="abt abt-danger" title="Xóa món ăn">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Accordion Detail Row -->
                        @if($isExpanded)
                            <tr class="mn-expand-row">
                                <td colspan="12">
                                    <div class="mn-sub">
                                        <div class="mn-sub-inner">
                                            <!-- Sub table -->
                                            <div class="mn-sub-table-wrap">
                                                <div class="mn-sub-ttl">Chi tiết nguyên liệu của {{ $recipe->name }}</div>
                                                <table class="mn-sub-table">
                                                    <thead>
                                                        <tr>
                                                            <th style="width:50px">STT</th>
                                                            <th>Nguyên liệu</th>
                                                            <th>Định lượng (kg) / 1 phần</th>
                                                            <th>Đơn giá nguyên liệu</th>
                                                            <th>Thành tiền</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($recipe->ingredients as $index => $ing)
                                                            @php
                                                                $qty = $ing->pivot->quantity_per_portion;
                                                                $price = $ing->reference_price;
                                                                $total = $qty * $price;
                                                            @endphp
                                                            <tr>
                                                                <td style="font-weight:600;color:var(--mu)">{{ $index + 1 }}</td>
                                                                <td style="font-weight:600">{{ $ing->name }}</td>
                                                                <td>{{ (float) $qty }} kg</td>
                                                                <td>{{ number_format($price, 0, ',', '.') }} đ</td>
                                                                <td style="font-weight:700;color:var(--or)">{{ number_format($total, 0, ',', '.') }} đ</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" style="text-align:center;color:var(--fa)">Món ăn này chưa khai báo định mức nguyên liệu.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Summary block right -->
                                            <div class="mn-sub-summary">
                                                <div class="mn-sub-sum-lbl">Tổng định lượng / phần:</div>
                                                <div class="mn-sub-sum-val">{{ str_replace('.', ',', round($recipeWeight, 2)) }} kg</div>
                                                
                                                <div class="mn-sub-cost-lbl">Tổng cost đơn giá trên 1 phần:</div>
                                                <div class="mn-sub-cost-val">{{ number_format($recipeCost, 0, ',', '.') }} đ</div>
                                                
                                                @if($recipe->cost_override !== null)
                                                    <div style="margin-top:10px;padding:6px 8px;background:var(--or-s);border:1px solid #fed7aa;border-radius:6px;font-size:11px;color:var(--or-t)">
                                                        <strong>Lý do điều chỉnh:</strong><br>
                                                        {{ $recipe->recipeCostLogs->first()?->reason ?? 'Không ghi nhận lý do' }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="12" style="text-align:center;padding:30px;color:var(--mu)">Không tìm thấy món ăn nào khớp với bộ lọc.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer Pagination -->
        @if($recipesList->hasPages())
            @php
                $currentPage = $recipesList->currentPage();
                $lastPage = $recipesList->lastPage();
                $pageWindow = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                    ->filter(fn ($p) => $p >= 1 && $p <= $lastPage)
                    ->unique()
                    ->sort()
                    ->values();
            @endphp
            <div class="tf">
                <div>
                    Hiển thị <strong>{{ $recipesList->firstItem() }}</strong> đến <strong>{{ $recipesList->lastItem() }}</strong> trong tổng số <strong>{{ number_format($recipesList->total(), 0, ',', '.') }}</strong> món ăn
                </div>
                <div class="pgwrap">
                    <span style="font-size:12px;color:var(--mu)">Số dòng mỗi trang</span>
                    <select wire:model.live="perPage" class="pgsel">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    <div class="pgbs">
                        {{-- Previous --}}
                        @if ($recipesList->onFirstPage())
                            <button type="button" class="pgb" disabled style="opacity: 0.5;">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                        @else
                            <button type="button" wire:click="previousPage" class="pgb">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                        @endif

                        {{-- Windowed page numbers --}}
                        @foreach ($pageWindow as $i => $page)
                            @if ($i > 0 && $page - $pageWindow[$i - 1] > 1)
                                <span class="pgdot">...</span>
                            @endif
                            @if ($page == $currentPage)
                                <button type="button" class="pgb cur">{{ $page }}</button>
                            @else
                                <button type="button" wire:click="gotoPage({{ $page }})" class="pgb">{{ $page }}</button>
                            @endif
                        @endforeach

                        {{-- Next --}}
                        @if ($recipesList->hasMorePages())
                            <button type="button" wire:click="nextPage" class="pgb">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        @else
                            <button type="button" class="pgb" disabled style="opacity: 0.5;">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
