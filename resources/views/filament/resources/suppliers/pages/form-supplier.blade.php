<div class="sup-page w-full space-y-6">
    @include('filament.resources.suppliers.partials.styles')
    @php
        $formCrumb = isset($this->supplierId) ? __('supplier.form.edit_title') : __('supplier.form.create_title');
        $formTitle = $formCrumb;
        $ingredients = $this->ingredients();
        $summary = $this->summary();
    @endphp

    <div class="sup-breadcrumb">
        <a href="{{ url('/admin') }}">{{ __('supplier.breadcrumb.home') }}</a>
        <span>/</span>
        <a href="{{ \App\Filament\Resources\SupplierResource::getUrl('index') }}">{{ __('supplier.navigation.label') }}</a>
        <span>/</span>
        <span style="color:var(--sup-mu)">{{ $formCrumb }}</span>
    </div>

    <div class="sup-head" style="margin-bottom:1.5rem">
        <div>
            <h1 class="sup-title">{{ $formTitle }}</h1>
            <p class="sup-subtitle">{{ __('supplier.form.subtitle') }}</p>
        </div>
    </div>

    <form wire:submit.prevent="save">
        <div class="sup-form-layout">
            <div class="sup-form-stack">
                <!-- Info Section -->
                <div class="sup-card">
                    <div class="sup-card-title">{{ __('supplier.form.information') }}</div>
                    
                    <div class="sup-grid">
                        <div class="sup-field">
                            <label class="sup-label">{{ __('supplier.fields.name_short') }} <span class="sup-required">*</span></label>
                            <input wire:model="name" type="text" class="sup-input" placeholder="{{ __('supplier.placeholders.name') }}" required>
                            @error('name') <span class="sup-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="sup-field">
                            <label class="sup-label">{{ __('supplier.fields.code_short') }} <span class="sup-required">*</span></label>
                            <input wire:model="code" type="text" class="sup-input" placeholder="{{ __('supplier.placeholders.code') }}" required>
                            @error('code') <span class="sup-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="sup-field">
                            <label class="sup-label">{{ __('supplier.fields.phone') }} <span class="sup-required">*</span></label>
                            <input wire:model="phone" type="tel" class="sup-input" placeholder="{{ __('supplier.placeholders.phone') }}" required>
                            @error('phone') <span class="sup-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="sup-field">
                            <label class="sup-label">Email</label>
                            <input wire:model="email" type="email" class="sup-input" placeholder="{{ __('supplier.placeholders.email_short') }}">
                            @error('email') <span class="sup-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="sup-field sup-field-full">
                            <label class="sup-label">{{ __('supplier.fields.food_types') }} <span class="sup-required">*</span></label>
                            {{-- Read-only: loại suy trực tiếp từ nguyên liệu đã tích ở bảng dưới, không chọn tay --}}
                            @php $derivedTypes = $this->derivedTypeNames(); @endphp
                            <div style="display:flex; flex-wrap:wrap; gap:8px; padding:10px 12px; min-height:42px; height:auto !important; background:var(--sup-bg); border:1.5px solid var(--sup-bd2); border-radius:.7rem; align-items:center">
                                @forelse($derivedTypes as $typeName)
                                    <span class="sup-type-pill"><i class="fa-solid fa-tag" style="font-size:11px"></i> {{ $typeName }}</span>
                                @empty
                                    <span style="color:var(--sup-mu); font-size:13px">
                                        {{ __('supplier.empty.no_food_types') }}
                                    </span>
                                @endforelse
                            </div>
                        </div>

                        <div class="sup-field sup-field-full">
                            <label class="sup-label">{{ __('supplier.fields.notes') }}</label>
                            <textarea wire:model="notes" class="sup-input" rows="3" placeholder="{{ __('supplier.placeholders.notes_optional') }}"></textarea>
                            @error('notes') <span class="sup-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Hồ sơ NCC: hợp đồng, chứng nhận ATTP... kèm ngày hết hạn -->
                <div class="sup-card">
                    <div class="sup-card-title" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem">
                        <span>{{ __('supplier.documents.title') }}</span>
                        <button type="button" wire:click="addDocument"
                            style="background:var(--po-bl-s); color:var(--po-bl); border:none; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer">
                            <i class="fa-solid fa-plus"></i> {{ __('supplier.documents.add') }}
                        </button>
                    </div>

                    @forelse($documents as $index => $doc)
                        <div style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap; padding:10px 0; border-bottom:1px dashed var(--sup-bd2)">
                            <div class="sup-field" style="flex:1 1 140px; min-width:120px">
                                <label class="sup-label">{{ __('supplier.documents.name') }}</label>
                                <input wire:model="documents.{{ $index }}.name" type="text" class="sup-input" placeholder="{{ __('supplier.documents.name_example') }}">
                            </div>
                            <div class="sup-field" style="flex:1 1 120px; min-width:110px">
                                <label class="sup-label">{{ __('supplier.documents.expires_at') }}</label>
                                <input wire:model="documents.{{ $index }}.expires_at" type="date" class="sup-input">
                            </div>
                            <div class="sup-field" style="flex:1 1 140px; min-width:120px">
                                <div style="display:flex; justify-content:space-between; align-items:center; gap:8px">
                                    <label class="sup-label">{{ __('supplier.documents.attachment') }}</label>
                                    @if(!empty($doc['file_path']))
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($doc['file_path']) }}" target="_blank"
                                            style="font-size:11.5px; color:var(--po-bl); font-weight:700; text-decoration:none; white-space:nowrap">
                                            <i class="fa-solid fa-paperclip"></i> {{ __('supplier.documents.view_current') }}
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
                            {{ __('supplier.documents.empty') }}
                        </div>
                    @endforelse
                </div>

                <!-- Ingredient Mapping Section -->
                <div class="sup-card">
                    <div class="sup-ingredient-head">
                        <div class="sup-card-title" style="margin:0;border:0;padding:0">{{ __('supplier.ingredients.title') }}</div>
                        <span class="sup-help">{{ __('supplier.ingredients.help') }}</span>
                    </div>

                    <div class="sup-search">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input wire:model.live.debounce.250ms="ingredientSearch" type="text" placeholder="{{ __('supplier.ingredients.search') }}">
                    </div>

                    {{-- Thống kê: đã tích bao nhiêu / còn bao nhiêu chưa hiện (danh mục có thể hàng trăm dòng) --}}
                    @php
                        $chosenCount = collect($selectedIngredients)->filter()->count();
                        $matchCount = $this->ingredientMatchCount();
                        $hiddenCount = max(0, $matchCount - count($ingredients));
                    @endphp
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:8px; font-size:12.5px; color:var(--sup-mu)">
                        <span>
                            {{ __('supplier.ingredients.selected_prefix') }} <strong style="color:var(--sup-bl)">{{ $chosenCount }}</strong> {{ __('supplier.ingredients.selected_suffix') }}
                            @if($chosenCount > 0)
                                <span style="font-style:italic">{{ __('supplier.ingredients.selected_first') }}</span>
                            @endif
                        </span>
                        @if($hiddenCount > 0)
                            <span>{{ __('supplier.ingredients.hidden', ['count' => number_format($hiddenCount)]) }}</span>
                        @endif
                    </div>

                    {{-- Khung cuộn: danh sách dài không kéo trang phình ra (xem ảnh phản hồi) --}}
                    <div class="sup-table-wrap" style="border:1.5px solid var(--sup-bd2);border-radius:.65rem;overflow:auto;max-height:420px">
                        <table class="sup-table">
                            <thead class="sup-thead-sticky">
                                <tr>
                                    <th style="width:60px;text-align:center">{{ __('supplier.ingredients.select') }}</th>
                                    <th style="width:90px">{{ __('supplier.ingredients.code') }}</th>
                                    <th>{{ __('supplier.ingredients.name') }}</th>
                                    <th style="width:80px">{{ __('supplier.ingredients.unit') }}</th>
                                    <th style="width:110px">{{ __('supplier.ingredients.type') }}</th>
                                    <th style="width:160px;text-align:right">{{ __('supplier.ingredients.cost') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ingredients as $ing)
                                    <tr wire:key="sup-ing-row-{{ $ing->id }}">
                                        <td style="text-align:center">
                                            <input type="checkbox" wire:model.live="selectedIngredients.{{ $ing->id }}" wire:key="sup-ing-chk-{{ $ing->id }}" class="sup-check">
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
                                                   class="sup-input sup-cost" placeholder="{{ __('supplier.ingredients.cost_placeholder') }}"
                                                   @if(!($selectedIngredients[$ing->id] ?? false)) disabled @endif>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align:center;padding:24px;color:var(--sup-mu)">
                                            {{ __('supplier.ingredients.empty') }}
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
                            {{ __('supplier.ingredients.data_principle') }}
                        </div>
                        {!! __('supplier.ingredients.data_principle_text') !!}
                    </div>
                </div>
            </div>

            <div class="sup-side">
                <!-- Status settings -->
                <div class="sup-card">
                    <div class="sup-card-title">{{ __('supplier.form.quick_settings') }}</div>
                    <div class="sup-toggle-row">
                        <span>{{ __('supplier.fields.status') }}</span>
                        <label class="sup-switch">
                            <input type="checkbox" wire:model="status">
                            <span class="sup-switch-track"></span>
                            <span style="font-size:13px;font-weight:700">{{ $status ? __('supplier.status.active') : __('supplier.status.locked') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="sup-card">
                    <div class="sup-card-title">{{ __('supplier.summary.title') }}</div>
                    
                    <div class="sup-summary-row">
                        <span class="sup-summary-key">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--sup-bl)">
                                <line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/>
                            </svg>
                            {{ __('supplier.summary.code') }}
                        </span>
                        <span class="sup-summary-value">{{ $summary['code'] }}</span>
                    </div>

                    <div class="sup-summary-row">
                        <span class="sup-summary-key">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--sup-bl)">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>
                            </svg>
                            {{ __('supplier.summary.type') }}
                        </span>
                        <span class="sup-summary-value">{{ $summary['type'] }}</span>
                    </div>

                    <div class="sup-summary-row">
                        <span class="sup-summary-key">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--sup-bl)">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                            {{ __('supplier.summary.ingredient_count') }}
                        </span>
                        <span class="sup-summary-value">{{ $summary['ingredients'] }}</span>
                    </div>

                    <div class="sup-summary-row">
                        <span class="sup-summary-key">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--sup-bl)">
                                <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                            {{ __('supplier.summary.total_quote') }}
                        </span>
                        <span class="sup-summary-value">{{ __('supplier.currency.amount', ['value' => number_format($summary['total'])]) }}</span>
                    </div>

                    <div class="sup-info" style="margin-top:1.5rem">
                        <div class="sup-info-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                            {{ __('supplier.summary.note') }}
                        </div>
                        {{ __('supplier.summary.note_text') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="sup-bottom-bar">
            <a href="{{ \App\Filament\Resources\SupplierResource::getUrl('index') }}" class="sup-btn">{{ __('supplier.actions.cancel') }}</a>
            <button type="submit" class="sup-btn sup-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                </svg>
                {{ __('supplier.actions.save') }}
            </button>
        </div>
    </form>
</div>
