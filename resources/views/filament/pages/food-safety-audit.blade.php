<div class="emp-page w-full space-y-6">
    @include('filament.resources.food-safety-audits.partials.styles')

    @php
        $stats = $this->getStats();
        $sheet = $this->getSheetView();
        $auditItems = $sheet['items'];
        $colspan = $sheet['columns'];
        $companyTitle = mb_strtoupper($canteen).' - '.mb_strtoupper($sheet['companyName']);
        $excelTemplateHtml = $this->getExcelTemplateHtml();
    @endphp

    @if (session()->has('message'))
        <div class="fsa-alert">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('message') }}
        </div>
    @endif

    <div class="emp-head" style="margin-bottom:14px">
        <div>
            <h1 class="emp-title">{{ __('food_safety.page.title') }}</h1>
            <p class="emp-subtitle">{{ __('food_safety.page.subtitle') }}</p>
        </div>
        <div class="emp-actions">
            <button wire:click="exportExcel" class="emp-btn emp-btn-primary" aria-label="{{ __('food_safety.accessibility.export_excel') }}">
                <i class="fa-solid fa-file-excel"></i>
                {{ __('food_safety.actions.export_excel') }}
            </button>
        </div>
    </div>

    <div class="filter-card" style="margin-bottom:16px">
        <div class="field" style="min-width:180px">
            <label>{{ __('food_safety.filters.date') }}</label>
            <input type="date" wire:model.live="date" class="ctrl">
        </div>
        <div class="field" style="min-width:150px">
            <label>{{ __('food_safety.filters.shift') }}</label>
            <select wire:model.live="selectedShift" class="ctrl">
                <option value="">{{ __('food_safety.filters.all_shifts') }}</option>
                @foreach(\App\Models\Shift::all() as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field" style="min-width:200px">
            <label>{{ __('food_safety.filters.location') }}</label>
            <input value="{{ $canteen }}" class="ctrl" readonly style="background:var(--po-bd2); cursor:not-allowed">
        </div>
        <div class="field" style="min-width:220px">
            <label>{{ __('food_safety.filters.inspector') }}</label>
            <div x-data="{
                open: false,
                search: '',
                selected: @entangle('inspector').live,
                options: {{ json_encode(array_values($this->getInspectorOptions())) }},
                get filteredOptions() {
                    if (!this.search) return this.options;
                    let keyword = this.search.toLowerCase();
                    return this.options.filter(name => name.toLowerCase().includes(keyword));
                }
            }" class="relative w-full">
                <div @click="open = !open" class="ctrl flex items-center justify-between cursor-pointer" role="combobox" tabindex="0" :aria-expanded="open" aria-label="{{ __('food_safety.accessibility.inspector_picker') }}" style="background:var(--po-wh); min-height:38px; border:1.5px solid var(--po-line); padding:6px 12px; border-radius:8px">
                    <span x-text="selected ? selected : @js(__('food_safety.placeholders.select_employee'))" style="font-weight:600; color:var(--po-tx)"></span>
                    <i class="fa-solid fa-chevron-down" style="font-size:11px; color:var(--po-mu)"></i>
                </div>
                <div x-show="open" @click.away="open = false" class="absolute left-0 mt-1 w-full rounded-lg shadow-lg z-50 p-2" style="display:none; max-height:280px; overflow-y:auto; border:1px solid var(--po-line); box-shadow:0 10px 25px rgba(15,35,70,.15); background:var(--po-wh);">
                    <input type="text" x-model="search" placeholder="{{ __('food_safety.placeholders.search_employee') }}" class="ctrl w-full mb-2" style="height:32px; padding:4px 8px; font-size:13px; border:1px solid var(--po-line); border-radius:6px; outline:none">
                    <div class="flex flex-col gap-1">
                        <div @click="selected = ''; open = false; search = ''" class="fsa-dropdown-item px-3 py-1.5 rounded cursor-pointer text-sm font-semibold transition italic" style="color:var(--po-mu)">
                            {{ __('food_safety.actions.clear_selection') }}
                        </div>
                        <template x-for="name in filteredOptions" :key="name">
                            <div @click="selected = name; open = false; search = ''"
                                class="fsa-dropdown-item px-3 py-1.5 rounded cursor-pointer text-sm font-semibold transition"
                                :style="selected === name ? 'background:rgba(18,86,196,.15); color:var(--po-bl);' : ''"
                                x-text="name">
                            </div>
                        </template>
                        <div x-show="filteredOptions.length === 0" class="text-center py-3 text-xs font-semibold" style="color:var(--po-mu)">
                            {{ __('food_safety.empty.no_employee_results') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="font-size:12.5px; color:var(--po-mu); padding-bottom:9px; font-weight:600">
            {{ __('food_safety.labels.filter_summary', ['date' => $sheet['dateText'], 'dishes' => $stats['dishes'], 'ingredients' => $stats['ingredients']]) }}
        </div>
    </div>

    <div class="area-tabs">
        @foreach($this->getStepTabs() as $tab)
            <button wire:click="$set('activeStep', '{{ $tab['key'] }}')" class="area-tab {{ $activeStep === $tab['key'] ? 'active' : '' }}" aria-pressed="{{ $activeStep === $tab['key'] ? 'true' : 'false' }}">
                <i class="fa-solid {{ $tab['icon'] }}"></i>
                {{ $tab['label'] }}
                <span class="fsa-tab-sheet">{{ $tab['sheet'] }}</span>
            </button>
        @endforeach
        <div class="tsp"></div>
        @if($activeStep === 'Lưu mẫu')
            <button type="button" onclick="window.print()" class="emp-btn" aria-label="{{ __('food_safety.accessibility.print_labels') }}" style="height:36px">
                <i class="fa-solid fa-tags" style="color:var(--po-bl)"></i>
                {{ __('food_safety.actions.print_labels') }}
            </button>
        @endif
    </div>

    <div class="krow" style="grid-template-columns:repeat(4,1fr); margin-bottom:16px">
        <div class="kcard">
            <div class="ktop"><div class="kico ki-b"><i class="fa-solid fa-seedling"></i></div></div>
            <div class="kval">{{ $stats['ingredients'] }}</div>
            <div class="klbl">{{ __('food_safety.kpi.ingredients_b1') }}</div>
            <div class="knote">{{ __('food_safety.kpi.from_daily_dishes') }}</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-g"><i class="fa-solid fa-bowl-food"></i></div></div>
            <div class="kval">{{ $stats['dishes'] }}</div>
            <div class="klbl">{{ __('food_safety.kpi.dishes') }}</div>
            <div class="knote">{{ __('food_safety.kpi.by_shift') }}</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-o"><i class="fa-solid fa-users"></i></div></div>
            <div class="kval">{{ number_format($stats['portions']) }}</div>
            <div class="klbl">{{ __('food_safety.kpi.total_portions') }}</div>
            <div class="knote">{{ __('food_safety.kpi.by_dish') }}</div>
        </div>
        <div class="kcard">
            <div class="ktop"><div class="kico ki-p"><i class="fa-solid fa-file-excel"></i></div></div>
            <div class="kval">{{ $stats['forms'] }}</div>
            <div class="klbl">{{ __('food_safety.kpi.forms') }}</div>
            <div class="knote">{{ __('food_safety.kpi.b1_to_b5') }}</div>
        </div>
    </div>

    <div class="tcard">
        @if($excelTemplateHtml !== '')
            <div class="fsa-template-wrap fsa-template-{{ $sheet['sheet'] }}">
                {!! $excelTemplateHtml !!}
            </div>
        @else
        <div class="tw fsa-sheet-wrap">
            <table class="byt-table fsa-sheet-table">
                <thead>
                    @if($activeStep === 'Bước 1')
                        <tr>
                            <th colspan="4" class="fsa-meta-cell">Thời gian kiểm tra: {{ $sheet['dateText'] }}</th>
                            <th colspan="6" rowspan="2" class="fsa-company">
                                <div>{{ $companyTitle }}</div>
                                <div>Địa chỉ: {{ $sheet['companyAddress'] }}</div>
                            </th>
                            <th colspan="3" class="fsa-blank"></th>
                        </tr>
                        <tr>
                            <th colspan="4" class="fsa-meta-cell">Địa điểm kiểm tra: {{ $canteen }}</th>
                            <th colspan="3" class="fsa-blank"></th>
                        </tr>
                        <tr>
                            <th colspan="4" class="fsa-meta-cell">Người kiểm tra: {{ $inspector ?: '...' }}</th>
                            <th colspan="6" class="fsa-report-title">{{ $sheet['title'] }}</th>
                            <th colspan="3" class="fsa-issued">Ban hành: QĐ 1246/2017-BYT</th>
                        </tr>
                    @else
                        <tr>
                            <th colspan="{{ $colspan }}" class="fsa-company">
                                <div>{{ $companyTitle }}</div>
                                <div>Địa chỉ: {{ $sheet['companyAddress'] }}</div>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="{{ $colspan }}" class="fsa-report-title">{{ $sheet['title'] }}</th>
                        </tr>
                        <tr>
                            <th colspan="{{ max(1, intdiv($colspan, 3)) }}" class="fsa-meta-cell">Thời gian kiểm tra: {{ $sheet['dateText'] }}</th>
                            <th colspan="{{ max(1, intdiv($colspan, 3)) }}" class="fsa-meta-cell">Địa điểm kiểm tra: {{ $canteen }}</th>
                            <th colspan="{{ $colspan - (max(1, intdiv($colspan, 3)) * 2) }}" class="fsa-meta-cell">Người kiểm tra: {{ $inspector ?: '...' }}</th>
                        </tr>
                        <tr>
                            <th colspan="{{ $colspan }}" class="fsa-issued">Ban hành: QĐ 1246/2017-BYT</th>
                        </tr>
                    @endif
                    @foreach($sheet['headRows'] as $headRow)
                        <tr>
                            @foreach($headRow as $cell)
                                <th
                                    @if(($cell['colspan'] ?? 1) > 1) colspan="{{ $cell['colspan'] }}" @endif
                                    @if(($cell['rowspan'] ?? 1) > 1) rowspan="{{ $cell['rowspan'] }}" @endif
                                    @if(!empty($cell['width'])) style="width:{{ $cell['width'] }}" @endif
                                >
                                    {!! nl2br(e($cell['text'])) !!}
                                </th>
                            @endforeach
                        </tr>
                    @endforeach
                </thead>
                <tbody>
                    @forelse($sheet['rows'] as $row)
                        @if($row['type'] === 'group')
                            <tr>
                                <td colspan="{{ $row['colspan'] }}" class="byt-group-title">{{ $row['label'] }}</td>
                            </tr>
                        @else
                            <tr class="emp-row">
                                @foreach($row['cells'] as $cell)
                                    <td class="{{ $cell['class'] }}">{{ $cell['value'] }}</td>
                                @endforeach
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="{{ $colspan }}" class="fsa-empty">{{ __('food_safety.empty.no_audit_data') }}</td>
                        </tr>
                    @endforelse

                    <tr class="byt-sign-title">
                        @if($activeStep === 'Bước 1')
                            <td colspan="{{ max(1, intdiv($colspan, 3)) }}" class="fsa-note">GHI CHÚ:<br>Đ: Đạt<br>K: Không đạt<br>NT: Như trên</td>
                            <td colspan="{{ max(1, intdiv($colspan, 3)) }}" class="text-center">Đại diện nhà ăn</td>
                            <td colspan="{{ $colspan - (max(1, intdiv($colspan, 3)) * 2) }}" class="text-center">Người kiểm tra</td>
                        @elseif(in_array($activeStep, ['Bước 2', 'Bước 3'], true))
                            <td colspan="{{ max(1, intdiv($colspan, 3)) }}" class="fsa-note">Ghi chú:<br>Đ: Đạt<br>K: Không đạt</td>
                            <td colspan="{{ max(1, intdiv($colspan, 3)) }}" class="text-center">Nhân viên kiểm soát</td>
                            <td colspan="{{ $colspan - (max(1, intdiv($colspan, 3)) * 2) }}" class="text-center">Nhân viên giám sát</td>
                        @else
                            <td colspan="{{ max(1, intdiv($colspan, 3)) }}" class="fsa-note">Ghi chú:<br>Đ: Đạt<br>K: Không đạt</td>
                            <td colspan="{{ max(1, intdiv($colspan, 3)) }}" class="text-center">Nhân viên kiểm tra</td>
                            <td colspan="{{ $colspan - (max(1, intdiv($colspan, 3)) * 2) }}" class="text-center">Đại diện công ty</td>
                        @endif
                    </tr>
                    <tr class="byt-sign-text">
                        <td colspan="{{ max(1, intdiv($colspan, 3)) }}"></td>
                        <td colspan="{{ max(1, intdiv($colspan, 3)) }}" class="text-center font-bold">{{ $inspector ?: '...' }}</td>
                        <td colspan="{{ $colspan - (max(1, intdiv($colspan, 3)) * 2) }}" class="text-center font-bold"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    </div>

    @if($activeStep === 'Lưu mẫu')
        <div class="fsa-print-labels" style="display:none">
            @foreach($auditItems as $item)
                <div class="fsa-label">
                    <div class="fsa-label-head">TEM LƯU MẪU THỨC ĂN · {{ $canteen }}</div>
                    <table class="fsa-label-table">
                        <tr><td>Món ăn:</td><td><strong>{{ $item['name'] }}</strong></td></tr>
                        <tr><td>Ca:</td><td><strong>{{ $item['shift'] ?? '—' }}</strong></td></tr>
                        <tr><td>Mã mẫu:</td><td><strong>{{ $item['sample_code'] ?: '—' }}</strong></td></tr>
                        <tr><td>Ngày:</td><td>{{ $sheet['dateText'] }}</td></tr>
                        <tr><td>Giờ lưu:</td><td>{{ $item['time'] ?: '—' }}</td></tr>
                        <tr><td>KL mẫu:</td><td>{{ $item['sample_amount'] ?? '100g' }}</td></tr>
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
                    top: 0;
                    left: 0;
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
                .fsa-label-table td { padding: .6mm 0; vertical-align: top; }
                .fsa-label-table td:first-child { width: 38%; color: #333; }
            }
        </style>
    @endif
</div>
