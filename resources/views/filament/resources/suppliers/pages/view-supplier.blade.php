<div class="sup-page">
    @include('filament.resources.suppliers.partials.styles')

    <!-- Header -->
    <div class="sup-head">
        <div>
            <h1 class="sup-title">{{ $name }} ({{ $code }})</h1>
            <p class="sup-subtitle">Xem thông tin chi tiết hồ sơ nhà cung cấp và bảng báo giá nguyên liệu</p>
        </div>
        <div class="sup-actions">
            <a href="{{ \App\Filament\Resources\SupplierResource::getUrl('index') }}" class="sup-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px">
                    <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                </svg>
                Quay lại
            </a>
            @if(\App\Filament\Resources\SupplierResource::canEdit(\App\Models\Supplier::find($supplierId)))
                <a href="{{ \App\Filament\Resources\SupplierResource::getUrl('edit', ['record' => $supplierId]) }}" class="sup-btn sup-btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Chỉnh sửa nhà cung cấp
                </a>
            @endif
        </div>
    </div>

    <!-- Form layout (Read-Only Detail View) -->
    <div class="sup-form-layout">
        <!-- Main stack -->
        <div class="sup-form-stack">
            <!-- Card 1: Thông tin NCC -->
            <div class="sup-card">
                <h2 class="sup-card-title">{{ __('supplier.form.information') }}</h2>
                <div class="sup-grid">
                    <div class="sup-field">
                        <label class="sup-label">{{ __('supplier.fields.name') }}</label>
                        <div class="sup-input" style="display:flex;align-items:center;background:var(--sup-bg);font-weight:700;color:var(--sup-tx)">{{ $name }}</div>
                    </div>
                    <div class="sup-field">
                        <label class="sup-label">{{ __('supplier.fields.code') }}</label>
                        <div class="sup-input" style="display:flex;align-items:center;background:var(--sup-bg);font-weight:700;color:var(--sup-bl)">{{ $code }}</div>
                    </div>
                    <div class="sup-field">
                        <label class="sup-label">{{ __('supplier.fields.phone') }}</label>
                        <div class="sup-input" style="display:flex;align-items:center;background:var(--sup-bg);color:var(--sup-tx)">{{ $phone ?: '--' }}</div>
                    </div>
                    <div class="sup-field">
                        <label class="sup-label">Email</label>
                        <div class="sup-input" style="display:flex;align-items:center;background:var(--sup-bg);color:var(--sup-tx)">{{ $email ?: '--' }}</div>
                    </div>

                    <div class="sup-field sup-field-full">
                        <label class="sup-label">{{ __('supplier.fields.food_types') }}</label>
                        @php $derivedTypes = $this->derivedTypeNames(); @endphp
                        <div style="display:flex; flex-wrap:wrap; gap:8px; padding:10px 12px; min-height:42px; height:auto !important; background:var(--sup-bg); border:1.5px solid var(--sup-bd2); border-radius:.7rem; align-items:center">
                            @forelse($derivedTypes as $typeName)
                                <span class="sup-type-pill"><i class="fa-solid fa-tag" style="font-size:11px"></i> {{ $typeName }}</span>
                            @empty
                                <span style="color:var(--sup-mu); font-size:13px">{{ __('supplier.empty.no_food_types') }}</span>
                            @endforelse
                        </div>
                    </div>

                    @if($notes)
                        <div class="sup-field sup-field-full">
                            <label class="sup-label">{{ __('supplier.fields.notes') }}</label>
                            <div style="padding:10px 14px; background:var(--sup-bg); border:1px solid var(--sup-bd2); border-radius:.7rem; font-size:14px; color:var(--sup-su); line-height:1.5">
                                {{ $notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card 2: Hồ sơ NCC -->
            @if(count($documents) > 0)
                <div class="sup-card">
                    <h2 class="sup-card-title">{{ __('supplier.documents.title') }}</h2>
                    <div style="display:flex; flex-direction:column; gap:10px">
                        @foreach($documents as $doc)
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:var(--sup-bg); border:1px solid var(--sup-bd2); border-radius:.7rem">
                                <div style="display:flex; align-items:center; gap:10px">
                                    <i class="fa-regular fa-file-lines" style="font-size:18px; color:var(--sup-bl)"></i>
                                    <div>
                                        <div style="font-weight:700; color:var(--sup-tx); font-size:14px">{{ $doc['name'] ?? '--' }}</div>
                                        @if(!empty($doc['expires_at']))
                                            @php
                                                $exp = \Carbon\Carbon::parse($doc['expires_at']);
                                                $isExpired = $exp->isPast();
                                                $isNear = !$isExpired && $exp->diffInDays(now()) <= 30;
                                            @endphp
                                            <div style="font-size:12px; color: {{ $isExpired ? 'var(--sup-rd)' : ($isNear ? 'var(--sup-or)' : 'var(--sup-mu)') }}">
                                                Hạn: {{ $exp->format('d/m/Y') }}
                                                @if($isExpired) (Đã hết hạn) @elseif($isNear) (Sắp hết hạn) @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if(!empty($doc['attachment']))
                                    <a href="{{ filter_var($doc['attachment'], FILTER_VALIDATE_URL) ? $doc['attachment'] : asset('storage/' . $doc['attachment']) }}" target="_blank" class="sup-btn" style="height:32px; padding:0 10px; font-size:12px">
                                        <i class="fa-solid fa-download"></i> Tải file
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Card 3: Bảng giá nguyên liệu -->
            <div class="sup-card">
                <div class="sup-ingredient-head">
                    <div>
                        <h2 class="sup-card-title" style="margin:0; border:none; padding:0">Báo giá nguyên liệu cung cấp</h2>
                        <p class="sup-help" style="margin-top:2px">Danh sách nguyên liệu mà nhà cung cấp này cung ứng</p>
                    </div>
                    <div style="width:240px">
                        <input wire:model.live.debounce.150ms="ingredientSearch" type="text" class="sup-input" placeholder="{{ __('supplier.placeholders.search_ingredient') }}" style="height:38px">
                    </div>
                </div>

                @php $ingredientsList = $this->ingredients(); @endphp
                <div style="max-height:450px; overflow-y:auto; border:1px solid var(--sup-bd2); border-radius:.7rem">
                    <table class="sup-table">
                        <thead class="sup-thead-sticky">
                            <tr>
                                <th style="width:50px;text-align:center">STT</th>
                                <th style="width:110px">{{ __('supplier.table.code') }}</th>
                                <th>{{ __('supplier.table.name') }}</th>
                                <th>Loại thực phẩm</th>
                                <th style="width:90px">ĐVT</th>
                                <th style="width:160px;text-align:right">Giá tham chiếu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ingredientsList as $idx => $ing)
                                <tr>
                                    <td style="text-align:center" class="sup-muted">{{ $idx + 1 }}</td>
                                    <td style="font-weight:700;color:var(--sup-bl)">{{ $ing->code }}</td>
                                    <td class="sup-name">{{ $ing->name }}</td>
                                    <td><span style="font-size:12px;color:var(--sup-su)">{{ $ing->typeRelation?->name ?? $ing->type }}</span></td>
                                    <td>{{ $ing->unit }}</td>
                                    <td style="text-align:right;font-weight:700;color:var(--sup-tx)">
                                        {{ number_format((float) ($ingredientCosts[$ing->id] ?? $ing->reference_price ?? 0), 0, ',', '.') }} đ / {{ $ing->unit }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center;padding:30px;color:var(--sup-mu)">
                                        Chưa có nguyên liệu nào được gán cho nhà cung cấp này.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar / Summary -->
        <div class="sup-side">
            <div class="sup-card">
                <h3 class="sup-card-title" style="font-size:0.9rem;margin-bottom:0.75rem">Trạng thái hoạt động</h3>
                <div style="display:flex;align-items:center;gap:8px">
                    @if($status)
                        <span class="spill s-ok">{{ __('supplier.status.active') }}</span>
                    @else
                        <span class="spill s-qt">{{ __('supplier.status.locked') }}</span>
                    @endif
                </div>
            </div>

            <div class="sup-card">
                <h3 class="sup-card-title">{{ __('supplier.summary.title') }}</h3>
                @php $sum = $this->summary(); @endphp
                <div style="display:flex;flex-direction:column;gap:12px">
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px">
                        <span style="color:var(--sup-mu)">Mã NCC</span>
                        <span style="font-weight:700;color:var(--sup-bl)">{{ $sum['code'] }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px">
                        <span style="color:var(--sup-mu)">Số nguyên liệu</span>
                        <span style="font-weight:700;color:var(--sup-tx)">{{ $sum['ingredients'] }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;padding-top:8px;border-top:1px dashed var(--sup-bd2)">
                        <span style="color:var(--sup-mu);font-weight:700">Tổng báo giá</span>
                        <span style="font-weight:800;color:var(--sup-bl);font-size:15px">{{ number_format($sum['total'], 0, ',', '.') }} đ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
