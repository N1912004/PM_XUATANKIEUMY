<div class="sup-page">
    @include('filament.resources.suppliers.partials.styles')
    @php
        $formCrumb = isset($this->supplierId) ? 'Sửa nhà cung cấp' : 'Thêm nhà cung cấp';
        $formTitle = isset($this->supplierId) ? 'Sửa nhà cung cấp' : 'Thêm nhà cung cấp';
        $summary = $this->summary();
        $ingredients = $this->ingredients();
    @endphp

    <div class="sup-breadcrumb">
        <a href="{{ url('/admin') }}">Xuất ăn</a>
        <span>/</span>
        <a href="{{ \App\Filament\Resources\SupplierResource::getUrl('index') }}">Nhà cung cấp</a>
        <span>/</span>
        <span style="color:var(--sup-mu)">{{ $formCrumb }}</span>
    </div>

    <div class="sup-head" style="margin-bottom:1.5rem">
        <div>
            <h1 class="sup-title">{{ $formTitle }}</h1>
            <p class="sup-subtitle">Khai báo thông tin NCC và thiết lập bảng giá nguyên liệu mà NCC đó cung cấp</p>
        </div>
    </div>

    <form wire:submit.prevent="save">
        <div class="sup-form-layout">
            <div class="sup-form-stack">
                <!-- Info Section -->
                <div class="sup-card">
                    <div class="sup-card-title">Thông tin nhà cung cấp</div>
                    
                    <div class="sup-grid">
                        <div class="sup-field">
                            <label class="sup-label">Tên NCC <span class="sup-required">*</span></label>
                            <input wire:model="name" type="text" class="sup-input" placeholder="Nhập tên nhà cung cấp" required>
                            @error('name') <span class="sup-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="sup-field">
                            <label class="sup-label">Mã NCC</label>
                            <input wire:model="code" type="text" class="sup-input" placeholder="Nhập mã nhà cung cấp" required>
                            @error('code') <span class="sup-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="sup-field">
                            <label class="sup-label">Số điện thoại <span class="sup-required">*</span></label>
                            <input wire:model="phone" type="text" class="sup-input" placeholder="Nhập số điện thoại" required>
                            @error('phone') <span class="sup-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="sup-field">
                            <label class="sup-label">Email</label>
                            <input wire:model="email" type="email" class="sup-input" placeholder="Nhập email">
                            @error('email') <span class="sup-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="sup-field sup-field-full">
                            <label class="sup-label">Loại thực phẩm cung cấp <span class="sup-required">*</span></label>
                            <select wire:model="type" class="sup-select" required>
                                <option value="">Chọn loại thực phẩm cung cấp</option>
                                <option value="Thịt">Thịt</option>
                                <option value="Rau củ">Rau củ</option>
                                <option value="Thực phẩm khô">Thực phẩm khô</option>
                                <option value="Gia vị">Gia vị</option>
                                <option value="Hải sản">Hải sản</option>
                                <option value="Tổng hợp">Tổng hợp</option>
                            </select>
                            @error('type') <span class="sup-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Ingredient Mapping Section -->
                <div class="sup-card">
                    <div class="sup-ingredient-head">
                        <div class="sup-card-title" style="margin:0;border:0;padding:0">Nguyên liệu NCC cung cấp & đơn giá</div>
                        <span class="sup-help">Chọn nguyên liệu nào thì nhập chi phí/đơn giá cho nguyên liệu đó</span>
                    </div>

                    <div class="sup-search">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input wire:model.live.debounce.250ms="ingredientSearch" type="text" placeholder="Tìm nguyên liệu theo tên hoặc mã...">
                    </div>

                    <div class="sup-table-wrap" style="border:1.5px solid var(--sup-bd2);border-radius:.65rem;overflow-x:auto">
                        <table class="sup-table">
                            <thead>
                                <tr>
                                    <th style="width:60px;text-align:center">CHỌN</th>
                                    <th style="width:90px">MÃ NL</th>
                                    <th>TÊN NGUYÊN LIỆU</th>
                                    <th style="width:80px">ĐƠN VỊ</th>
                                    <th style="width:110px">LOẠI NL</th>
                                    <th style="width:160px;text-align:right">CHI PHÍ NCC CUNG CẤP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ingredients as $ing)
                                    <tr>
                                        <td style="text-align:center">
                                            <input type="checkbox" wire:model.live="selectedIngredients.{{ $ing->id }}" class="sup-check">
                                        </td>
                                        <td style="font-weight:700;color:var(--sup-bl)">{{ $ing->code }}</td>
                                        <td class="sup-name">{{ $ing->name }}</td>
                                        <td>{{ $ing->unit }}</td>
                                        <td>{{ $ing->type }}</td>
                                        <td>
                                            <input type="number" step="any" wire:model.live="ingredientCosts.{{ $ing->id }}" 
                                                   class="sup-input sup-cost" placeholder="Nhập chi phí"
                                                   @if(!($selectedIngredients[$ing->id] ?? false)) disabled @endif>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align:center;padding:24px;color:var(--sup-mu)">
                                            Không tìm thấy nguyên liệu nào.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="sup-info">
                        <div class="sup-info-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                            Nguyên tắc dữ liệu
                        </div>
                        Một NCC có thể cung cấp nhiều nguyên liệu. Mỗi nguyên liệu được chọn sẽ cần nhập <strong>chi phí/đơn giá</strong> tương ứng để dùng cho các nghiệp vụ list hàng, đặt hàng và đối chiếu chi phí sau này.
                    </div>
                </div>
            </div>

            <div class="sup-side">
                <!-- Status settings -->
                <div class="sup-card">
                    <div class="sup-card-title">Thiết lập nhanh</div>
                    <div class="sup-toggle-row">
                        <span>Trạng thái</span>
                        <label class="sup-switch">
                            <input type="checkbox" wire:model="status">
                            <span class="sup-switch-track"></span>
                            <span style="font-size:13px;font-weight:700">{{ $status ? 'Đang hoạt động' : 'Tạm khóa' }}</span>
                        </label>
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="sup-card">
                    <div class="sup-card-title">Tóm tắt thông tin</div>
                    
                    <div class="sup-summary-row">
                        <span class="sup-summary-key">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--sup-bl)">
                                <line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/>
                            </svg>
                            Mã NCC
                        </span>
                        <span class="sup-summary-value">{{ $summary['code'] }}</span>
                    </div>

                    <div class="sup-summary-row">
                        <span class="sup-summary-key">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--sup-bl)">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>
                            </svg>
                            Loại TP
                        </span>
                        <span class="sup-summary-value">{{ $summary['type'] }}</span>
                    </div>

                    <div class="sup-summary-row">
                        <span class="sup-summary-key">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--sup-bl)">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                            Số nguyên liệu
                        </span>
                        <span class="sup-summary-value">{{ $summary['ingredients'] }}</span>
                    </div>

                    <div class="sup-summary-row">
                        <span class="sup-summary-key">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--sup-bl)">
                                <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                            Tổng báo giá
                        </span>
                        <span class="sup-summary-value">{{ number_format($summary['total']) }} đ</span>
                    </div>

                    <div class="sup-info" style="margin-top:1.5rem">
                        <div class="sup-info-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                            Lưu ý
                        </div>
                        Vui lòng nhập thông tin chính của NCC và chọn đúng nguyên liệu kèm chi phí để hệ thống dùng xuyên suốt cho các nghiệp vụ tiếp theo.
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="sup-bottom-bar">
            <a href="{{ \App\Filament\Resources\SupplierResource::getUrl('index') }}" class="sup-btn">Hủy</a>
            <button type="button" wire:click="saveDraft" class="sup-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                Lưu nháp
            </button>
            <button type="submit" class="sup-btn sup-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                </svg>
                Lưu nhà cung cấp
            </button>
        </div>
    </form>
</div>
