<div class="sup-page">
    @include('filament.resources.suppliers.partials.styles')
    @php
        $formCrumb = isset($this->supplierId) ? 'Sửa nhà cung cấp' : 'Thêm nhà cung cấp';
        $formTitle = isset($this->supplierId) ? 'Sửa nhà cung cấp' : 'Thêm nhà cung cấp';
        $ingredients = $this->ingredients();
        $summary = $this->summary();
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
                            <label class="sup-label">Mã NCC <span class="sup-required">*</span></label>
                            <input wire:model="code" type="text" class="sup-input" placeholder="Nhập mã nhà cung cấp" required>
                            @error('code') <span class="sup-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="sup-field">
                            <label class="sup-label">Số điện thoại <span class="sup-required">*</span></label>
                            <input wire:model="phone" type="tel" class="sup-input" placeholder="Nhập số điện thoại" required>
                            @error('phone') <span class="sup-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="sup-field">
                            <label class="sup-label">Email</label>
                            <input wire:model="email" type="email" class="sup-input" placeholder="Nhập email">
                            @error('email') <span class="sup-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="sup-field sup-field-full">
                            <label class="sup-label">Loại thực phẩm cung cấp <span class="sup-required">*</span></label>
                            <div class="sup-input" style="display:flex; flex-wrap:wrap; gap:6px; padding:8px 12px; min-height:42px; background:var(--sup-bg); border:1.5px solid var(--sup-bd2); border-radius:.5rem; align-items:center">
                                @php
                                    $selectedTypes = array_filter(explode(', ', $this->type));
                                @endphp
                                @forelse($selectedTypes as $sType)
                                    <span style="background:var(--po-bl-s); color:var(--po-bl); padding:4px 10px; border-radius:9999px; font-size:12px; font-weight:700">
                                        {{ $sType }}
                                    </span>
                                @empty
                                    <span style="color:var(--sup-mu); font-size:13px">
                                        Chưa có loại thực phẩm nào (Tự động cập nhật khi tích chọn nguyên liệu ở dưới)
                                    </span>
                                @endforelse
                            </div>
                            <input type="hidden" wire:model="type" required>
                            @error('type') <span class="sup-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="sup-field sup-field-full">
                            <label class="sup-label">Ghi chú</label>
                            <textarea wire:model="notes" class="sup-input" rows="3" placeholder="Ghi chú thêm về nhà cung cấp (không bắt buộc)"></textarea>
                            @error('notes') <span class="sup-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Hồ sơ NCC: hợp đồng, chứng nhận ATTP... kèm ngày hết hạn -->
                <div class="sup-card">
                    <div class="sup-card-title" style="display:flex; align-items:center; justify-content:space-between">
                        <span>Hồ sơ nhà cung cấp (hợp đồng, chứng nhận ATTP...)</span>
                        <button type="button" wire:click="addDocument"
                            style="background:var(--po-bl-s); color:var(--po-bl); border:none; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer">
                            <i class="fa-solid fa-plus"></i> Thêm hồ sơ
                        </button>
                    </div>

                    @forelse($documents as $index => $doc)
                        <div style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap; padding:10px 0; border-bottom:1px dashed var(--sup-bd2)">
                            <div class="sup-field" style="flex:2; min-width:180px">
                                <label class="sup-label">Tên hồ sơ</label>
                                <input wire:model="documents.{{ $index }}.name" type="text" class="sup-input" placeholder="VD: Chứng nhận ATTP 2026">
                            </div>
                            <div class="sup-field" style="min-width:150px">
                                <label class="sup-label">Ngày hết hạn</label>
                                <input wire:model="documents.{{ $index }}.expires_at" type="date" class="sup-input">
                            </div>
                            <div class="sup-field" style="flex:2; min-width:200px">
                                <div style="display:flex; justify-content:space-between; align-items:center; gap:8px">
                                    <label class="sup-label">File đính kèm (ảnh/PDF, ≤5MB)</label>
                                    @if(!empty($doc['file_path']))
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($doc['file_path']) }}" target="_blank"
                                            style="font-size:11.5px; color:var(--po-bl); font-weight:700; text-decoration:none; white-space:nowrap">
                                            <i class="fa-solid fa-paperclip"></i> Xem file hiện tại
                                        </a>
                                    @endif
                                </div>
                                <input wire:model="documentUploads.{{ $index }}" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf" class="sup-input" style="padding:7px">
                                @error('documentUploads.'.$index) <span class="sup-error">{{ $message }}</span> @enderror
                            </div>
                            <button type="button" wire:click="removeDocument({{ $index }})"
                                style="background:#fee2e2; color:#dc2626; border:none; width:3rem; height:3rem; border-radius:.7rem; cursor:pointer; flex-shrink:0; display:flex; align-items:center; justify-content:center">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    @empty
                        <div style="color:var(--sup-mu); font-size:12.5px; font-style:italic; padding:6px 0">
                            Chưa có hồ sơ nào. Bấm "Thêm hồ sơ" để khai báo hợp đồng/chứng nhận kèm ngày hết hạn.
                        </div>
                    @endforelse
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

                    {{-- Thống kê: đã tích bao nhiêu / còn bao nhiêu chưa hiện (danh mục có thể hàng trăm dòng) --}}
                    @php
                        $chosenCount = collect($selectedIngredients)->filter()->count();
                        $matchCount = $this->ingredientMatchCount();
                        $hiddenCount = max(0, $matchCount - count($ingredients));
                    @endphp
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:8px; font-size:12.5px; color:var(--sup-mu)">
                        <span>
                            Đã chọn <strong style="color:var(--sup-bl)">{{ $chosenCount }}</strong> nguyên liệu
                            @if($chosenCount > 0)
                                <span style="font-style:italic">— các mục đã chọn luôn nằm ở đầu bảng</span>
                            @endif
                        </span>
                        @if($hiddenCount > 0)
                            <span>Còn <strong>{{ number_format($hiddenCount) }}</strong> nguyên liệu chưa hiện — gõ tìm kiếm để thu hẹp</span>
                        @endif
                    </div>

                    {{-- Khung cuộn: danh sách dài không kéo trang phình ra (xem ảnh phản hồi) --}}
                    <div class="sup-table-wrap" style="border:1.5px solid var(--sup-bd2);border-radius:.65rem;overflow:auto;max-height:420px">
                        <table class="sup-table">
                            <thead class="sup-thead-sticky">
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
                                            <input type="text"
                                                   x-data="{
                                                       rawVal: @entangle('ingredientCosts.' . $ing->id),
                                                       {{-- VND không có số lẻ: chỉ nhận chữ số, hiển thị kiểu VN (dấu chấm nghìn) — đồng bộ với form Nguyên liệu --}}
                                                       get formatted() {
                                                           if (this.rawVal === undefined || this.rawVal === null || this.rawVal === '') return '';
                                                           let digits = String(Math.round(Number(this.rawVal) || 0));
                                                           return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                                       },
                                                       set formatted(val) {
                                                           let clean = String(val).replace(/[^0-9]/g, '');
                                                           this.rawVal = clean === '' ? null : Number(clean);
                                                       }
                                                   }"
                                                   x-model="formatted"
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
