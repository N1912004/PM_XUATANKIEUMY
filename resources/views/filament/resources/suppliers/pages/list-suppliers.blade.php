<div class="sup-page">
    @include('filament.resources.suppliers.partials.styles')
    @php
        $statsData = $this->stats();
        $suppliersList = $this->suppliers();
    @endphp

    <div class="sup-head">
        <div>
            <h1 class="sup-title">Nhà cung cấp</h1>
            <p class="sup-subtitle">Quản lý thông tin NCC, loại thực phẩm cung cấp và bảng giá theo từng nguyên liệu</p>
        </div>
        <div class="sup-actions">
            <button wire:click="exportExcel" class="sup-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--sup-gn)">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
                Xuất Excel
            </button>
            <a href="{{ \App\Filament\Resources\SupplierResource::getUrl('create') }}" class="sup-btn sup-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Thêm NCC
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="sup-kpis">
        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-blue">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="3" width="15" height="13" rx="2" ry="2"/>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                    <circle cx="5.5" cy="18.5" r="2.5"/>
                    <circle cx="18.5" cy="18.5" r="2.5"/>
                </svg>
            </div>
            <div class="sup-kpi-value">{{ $statsData['suppliers'] }}</div>
            <div class="sup-kpi-label">Tổng NCC</div>
            <div class="sup-kpi-note">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                Đang quản lý
            </div>
        </div>

        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                </svg>
            </div>
            <div class="sup-kpi-value">{{ $statsData['types'] }}</div>
            <div class="sup-kpi-label">Loại thực phẩm</div>
            <div class="sup-kpi-note">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                Theo nhóm cung cấp
            </div>
        </div>

        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-green">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>
            <div class="sup-kpi-value">{{ $statsData['ingredients'] }}</div>
            <div class="sup-kpi-label">Nguyên liệu liên kết</div>
            <div class="sup-kpi-note">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                NCC ↔ nguyên liệu
            </div>
        </div>

        <div class="sup-kpi">
            <div class="sup-kpi-icon sup-ico-purple">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="sup-kpi-value">{{ $statsData['quotes'] }}</div>
            <div class="sup-kpi-label">Báo giá đã nhập</div>
            <div class="sup-kpi-note">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                Theo từng nguyên liệu
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="sup-card sup-table-card">
        <div class="sup-toolbar">
            <div class="sup-search">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm tên NCC, SĐT hoặc email...">
            </div>

            <div class="sup-filter">
                <label>Loại TP cung cấp</label>
                <select wire:model.live="typeFilter" class="sup-select">
                    <option value="">Tất cả</option>
                    @foreach($this->typeOptions() as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sup-filter">
                <label>Trạng thái</label>
                <select wire:model.live="statusFilter" class="sup-select">
                    <option value="">Tất cả</option>
                    <option value="active">Đang hoạt động</option>
                    <option value="inactive">Tạm khóa</option>
                </select>
            </div>

            <div class="sup-spacer"></div>

            @if($search !== '' || $typeFilter !== '' || $statusFilter !== '')
                <button wire:click="resetFilters" class="sup-btn sup-row-danger" style="margin-right: 6px">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Xóa lọc
                </button>
            @endif

            <a href="{{ \App\Filament\Resources\SupplierResource::getUrl('create') }}" class="sup-btn sup-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Thêm NCC
            </a>
        </div>

        <div class="sup-table-wrap">
            <table class="sup-table">
                <thead>
                    <tr>
                        <th style="width:56px;text-align:center">STT</th>
                        <th style="width:110px">Mã NCC</th>
                        <th>Tên NCC</th>
                        <th style="width:130px">Số điện thoại</th>
                        <th>Email</th>
                        <th style="width:180px">Loại thực phẩm cung cấp</th>
                        <th style="width:130px;text-align:center">SL nguyên liệu</th>
                        <th style="width:140px">Trạng thái</th>
                        <th style="text-align:center;width:120px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliersList as $index => $supplier)
                        <tr>
                            <td style="text-align:center" class="sup-muted">{{ ($suppliersList->currentPage() - 1) * $suppliersList->perPage() + $index + 1 }}</td>
                            <td><span style="font-size:12px;font-weight:600;color:var(--sup-mu)">{{ $supplier->code }}</span></td>
                            <td class="sup-name">{{ $supplier->name }}</td>
                            <td>{{ $supplier->phone }}</td>
                            <td>{{ $supplier->email ?: '--' }}</td>
                            <td>{{ $supplier->type }}</td>
                            <td class="sup-link-num">{{ $supplier->ingredients_count }}</td>
                            <td>
                                @if($supplier->status)
                                    <span class="spill s-ok">Đang hoạt động</span>
                                @else
                                    <span class="spill s-qt">Tạm khóa</span>
                                @endif
                            </td>
                            <td style="text-align:center">
                                <div class="sup-row-actions" style="justify-content:center">
                                    <a href="{{ \App\Filament\Resources\SupplierResource::getUrl('edit', ['record' => $supplier->id]) }}" class="sup-row-action" title="Xem" style="color: var(--sup-mu)">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>
                                    <a href="{{ \App\Filament\Resources\SupplierResource::getUrl('edit', ['record' => $supplier->id]) }}" class="sup-row-action" title="Chỉnh sửa" style="color: var(--sup-mu)">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    <button wire:click="deleteSupplier({{ $supplier->id }})" wire:confirm="Bạn có chắc chắn muốn xóa nhà cung cấp này không?" class="sup-row-action sup-row-danger" title="Xóa">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:30px;color:var(--sup-mu)">
                                Không tìm thấy nhà cung cấp nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliersList->hasPages())
            <div class="sup-footer">
                <div>
                    Hiển thị {{ $suppliersList->firstItem() }}-{{ $suppliersList->lastItem() }} trên {{ $suppliersList->total() }} nhà cung cấp
                </div>
                <div class="sup-pagination">
                    <select wire:model.live="perPage" class="sup-select" style="min-width:7rem;height:2rem;padding:0 .5rem;border-radius:.5rem">
                        <option value="10">10 / trang</option>
                        <option value="20">20 / trang</option>
                        <option value="50">50 / trang</option>
                    </select>

                    <nav role="navigation" aria-label="Pagination Navigation">
                        {{-- Previous Page Link --}}
                        @if ($suppliersList->onFirstPage())
                            <span aria-disabled="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="15 18 9 12 15 6"/></svg>
                            </span>
                        @else
                            <button type="button" wire:click="previousPage" class="sup-small-btn" rel="prev">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="15 18 9 12 15 6"/></svg>
                            </button>
                        @endif

                        {{-- Pagination Elements (dạng cửa sổ: 1 … n-1 n n+1 … cuối) --}}
                        @php
                            $supCurrentPage = $suppliersList->currentPage();
                            $supLastPage = $suppliersList->lastPage();
                            $supPageWindow = collect([1, $supCurrentPage - 1, $supCurrentPage, $supCurrentPage + 1, $supLastPage])
                                ->filter(fn ($p) => $p >= 1 && $p <= $supLastPage)
                                ->unique()
                                ->sort()
                                ->values();
                        @endphp
                        @foreach ($supPageWindow as $i => $page)
                            @if ($i > 0 && $page - $supPageWindow[$i - 1] > 1)
                                <span aria-hidden="true" style="padding:0 4px">…</span>
                            @endif
                            @if ($page == $supCurrentPage)
                                <span aria-current="page">
                                    <span>{{ $page }}</span>
                                </span>
                            @else
                                <button type="button" wire:click="gotoPage({{ $page }})" class="sup-small-btn">{{ $page }}</button>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($suppliersList->hasMorePages())
                            <button type="button" wire:click="nextPage" class="sup-small-btn" rel="next">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="9 18 15 12 9 6"/></svg>
                            </button>
                        @else
                            <span aria-disabled="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="9 18 15 12 9 6"/></svg>
                            </span>
                        @endif
                    </nav>
                </div>
            </div>
        @endif
    </div>
</div>
