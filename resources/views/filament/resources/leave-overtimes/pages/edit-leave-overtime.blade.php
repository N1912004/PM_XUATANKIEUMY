<div>
    @include('filament.resources.leave-overtimes.partials.styles')

    @php
        $isEdit = isset($this->record);
        $employees = $this->getEmployees();
    @endphp

    <div class="emp-page" style="padding: 0 !important; background: transparent !important;">
        <!-- Header Section -->
        <div class="emp-head" style="margin-bottom: 20px;">
            <div>
                <h1 class="emp-title">{{ $isEdit ? __('leave_overtime.ui.edit_title') : __('leave_overtime.ui.create_title') }}</h1>
                <p class="emp-subtitle">
                    {{ $isEdit ? __('leave_overtime.ui.edit_description') : __('leave_overtime.ui.create_description') }}
                </p>
            </div>
            <div>
                <a href="{{ \App\Filament\Resources\LeaveOvertimeResource::getUrl('index') }}" class="emp-btn" style="background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-tx)">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('leave_overtime.ui.back') }}
                </a>
            </div>
        </div>

        <!-- Sub Tabs (Chỉ cho chuyển tab khi tạo mới) -->
        <div class="lf-tabs" style="margin-bottom: 20px;">
            @if($isEdit)
                <button type="button" class="lf-tab active" style="cursor: default">
                    @if($formTab === 'leave')
                        <i class="fa-regular fa-calendar"></i> {{ __('leave_overtime.ui.leave') }}
                    @else
                        <i class="fa-regular fa-clock"></i> {{ __('leave_overtime.ui.overtime') }}
                    @endif
                </button>
            @else
                <button type="button" wire:click="switchFormTab('leave')" class="lf-tab {{ $formTab === 'leave' ? 'active' : '' }}">
                    <i class="fa-regular fa-calendar"></i> {{ __('leave_overtime.ui.leave') }}
                </button>
                <button type="button" wire:click="switchFormTab('ot')" class="lf-tab {{ $formTab === 'ot' ? 'active' : '' }}">
                    <i class="fa-regular fa-clock"></i> {{ __('leave_overtime.ui.overtime') }}
                </button>
            @endif
        </div>

        @if ($errors->any())
            <div style="background:var(--po-rd-s); color:var(--po-rd-t); padding:12px 16px; border-radius:8px; border:1px solid var(--po-rd); margin-bottom:16px; font-size:13px; font-weight:600">
                <div style="font-weight:700; margin-bottom:4px"><i class="fa-solid fa-triangle-exclamation"></i> {{ __('leave_overtime.ui.form_error') }}</div>
                <ul style="list-style-type:disc; padding-left:20px">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form wire:submit.prevent="save">
            <div class="lf-layout">
                <!-- LEFT FORM -->
                <div class="fstack" style="display:flex; flex-direction:column; gap:16px">
                    
                    @if($formTab === 'leave')
                        <!-- LEAVE FORM -->
                        <div class="fc">
                            <div class="fch" style="background: var(--po-pu-s); color: var(--po-pu);">
                                <div class="fci"><i class="fa-regular fa-calendar"></i></div>
                                <div class="fct">{{ __('leave_overtime.ui.leave_info') }}</div>
                            </div>

                            <div class="fg fg2" style="margin-bottom:14px">
                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.employee') }} <span class="req">*</span></label>
                                    <select wire:model="employee_id" class="ctrl" required>
                                        <option value="">{{ __('leave_overtime.ui.select_employee') }}</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->code }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.leave_type') }} <span class="req">*</span></label>
                                    <select wire:model="leave_type_id" class="ctrl" required>
                                        @foreach(\App\Models\LeaveType::options(false) as $optId => $optName)
                                            <option value="{{ $optId }}">{{ $optName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="fg fg2" style="margin-bottom:14px">
                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.leave_time') }} <span class="req">*</span></label>
                                    <div style="display:flex; align-items:center; gap:8px">
                                        <input wire:model="start_date" type="date" class="ctrl" required style="flex:1">
                                        <span style="color:var(--po-mu)">{{ __('leave_overtime.ui.to') }}</span>
                                        <input wire:model="end_date" type="date" class="ctrl" required style="flex:1">
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.leave_days') }}</label>
                                    <div style="display:flex; align-items:center; gap:8px">
                                        <input wire:model="duration_text" type="text" class="ctrl" placeholder="{{ __('leave_overtime.ui.leave_duration_placeholder') }}" style="flex:1">
                                    </div>
                                </div>
                            </div>

                            <div class="field" style="margin-bottom:14px">
                                <label style="display:inline-flex; align-items:center; gap:8px; font-weight:500; cursor:pointer">
                                    <input wire:model="no_count_leave" type="checkbox" style="width:16px; height:16px; border-radius:4px; border:1px solid var(--po-bd)">
                                    {{ __('leave_overtime.ui.unpaid') }}
                                </label>
                            </div>

                            <div class="fg fg2" style="margin-bottom:14px">
                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.handover_time') }}</label>
                                    <input wire:model="handover_time" type="datetime-local" class="ctrl">
                                </div>
                            </div>

                            <div class="field">
                                <label>{{ __('leave_overtime.ui.leave_reason') }} <span class="req">*</span></label>
                                <textarea wire:model="reason" class="ctrl" placeholder="{{ __('leave_overtime.ui.leave_reason_placeholder') }}" rows="4" required maxlength="200"></textarea>
                                <div style="display:flex; justify-content:flex-end; font-size:11px; color:var(--po-fa); margin-top:4px">
                                    <span>{{ strlen($reason) }}/200</span>
                                </div>
                            </div>
                        </div>

                        <div class="fc">
                            <div class="fch">
                                <div class="fci" style="background:var(--po-bl-s);color:var(--po-bl)"><i class="fa-regular fa-address-book"></i></div>
                                <div class="fct">{{ __('leave_overtime.ui.contact_info') }}</div>
                            </div>

                            <div class="fg fg2" style="margin-bottom:14px">
                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.contact') }}</label>
                                    <input wire:model="contact_name" type="text" class="ctrl" placeholder="{{ __('leave_overtime.ui.contact_placeholder') }}">
                                </div>
                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.phone') }}</label>
                                    <input wire:model="contact_phone" type="tel" class="ctrl" placeholder="{{ __('leave_overtime.ui.phone_placeholder') }}">
                                </div>
                            </div>

                            <div class="field">
                                <label>{{ __('leave_overtime.ui.optional_notes') }}</label>
                                <textarea wire:model="notes" class="ctrl" placeholder="{{ __('leave_overtime.ui.notes_placeholder') }}" rows="3" maxlength="200"></textarea>
                                <div style="display:flex; justify-content:flex-end; font-size:11px; color:var(--po-fa); margin-top:4px">
                                    <span>{{ strlen($notes) }}/200</span>
                                </div>
                            </div>
                        </div>

                    @else
                        <!-- OT FORM -->
                        <div class="fc">
                            <div class="fch" style="background: var(--po-bl-s); color: var(--po-bl);">
                                <div class="fci"><i class="fa-regular fa-clock"></i></div>
                                <div class="fct">{{ __('leave_overtime.ui.overtime_info') }}</div>
                            </div>

                            <div class="fg fg3" style="margin-bottom:14px">
                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.employee') }} <span class="req">*</span></label>
                                    <select wire:model="employee_id" class="ctrl" required>
                                        <option value="">{{ __('leave_overtime.ui.select_employee') }}</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->code }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.overtime_type') }} <span class="req">*</span></label>
                                    <select wire:model="leave_type_id" class="ctrl" required>
                                        @foreach(\App\Models\LeaveType::options(true) as $optId => $optName)
                                            <option value="{{ $optId }}">{{ $optName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.overtime_date') }} <span class="req">*</span></label>
                                    <input wire:model="start_date" type="date" class="ctrl" required>
                                </div>
                            </div>

                            <div class="fg fg2" style="margin-bottom:14px">
                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.overtime_time') }} <span class="req">*</span></label>
                                    <div style="display:flex; align-items:center; gap:8px">
                                        <input wire:model="ot_start_time" type="time" class="ctrl" required style="flex:1">
                                        <span style="color:var(--po-mu)">{{ __('leave_overtime.ui.to') }}</span>
                                        <input wire:model="ot_end_time" type="time" class="ctrl" required style="flex:1">
                                    </div>
                                </div>

                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.overtime_hours') }}</label>
                                    <input wire:model="duration_text" type="text" class="ctrl" placeholder="{{ __('leave_overtime.ui.overtime_duration_placeholder') }}">
                                </div>
                            </div>

                            <div class="fg fg2" style="margin-bottom:14px">
                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.location') }}</label>
                                    <select wire:model="ot_location" class="ctrl">
                                        <option value="Bếp trung tâm - Khu A">{{ __('leave_overtime.locations.central_kitchen_a') }}</option>
                                        <option value="Kho nguyên liệu">{{ __('leave_overtime.locations.ingredient_warehouse') }}</option>
                                        <option value="Van phòng HQ">{{ __('leave_overtime.locations.head_office') }}</option>
                                    </select>
                                </div>

                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.approver') }} <span class="req">*</span></label>
                                    <select wire:model="approver_id" class="ctrl" required>
                                        <option value="">{{ __('leave_overtime.ui.select_approver') }}</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="field" style="margin-bottom:14px">
                                <label>{{ __('leave_overtime.ui.overtime_reason') }} <span class="req">*</span></label>
                                <textarea wire:model="reason" class="ctrl" placeholder="{{ __('leave_overtime.ui.overtime_reason_placeholder') }}" rows="3" required></textarea>
                            </div>

                            <div class="field" style="margin-bottom:14px">
                                <label>{{ __('leave_overtime.ui.work') }}</label>
                                <textarea wire:model="ot_work_description" class="ctrl" placeholder="{{ __('leave_overtime.ui.work_placeholder') }}" rows="3"></textarea>
                            </div>

                            <div class="field" style="margin-bottom:14px">
                                <label>{{ __('leave_overtime.ui.attachment') }}</label>
                                <div class="lf-upload">
                                    <div class="lf-upload-ico"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                                    <span>{{ __('leave_overtime.ui.file_drag') }} <span style="color:var(--po-bl);font-weight:700;text-decoration:underline">{{ __('leave_overtime.ui.file_choose') }}</span></span>
                                    <small>{{ __('leave_overtime.ui.file_types') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="fc">
                            <div class="fch">
                                <div class="fci" style="background:var(--po-bl-s);color:var(--po-bl)"><i class="fa-regular fa-handshake"></i></div>
                                <div class="fct">{{ __('leave_overtime.ui.confirmation') }}</div>
                            </div>

                            <div class="fg fg2" style="margin-bottom:14px">
                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.coworker') }}</label>
                                    <input wire:model="co_worker" type="text" class="ctrl" placeholder="{{ __('leave_overtime.ui.coworker_placeholder') }}">
                                </div>
                                <div class="field">
                                    <label>{{ __('leave_overtime.ui.notes') }}</label>
                                    <input wire:model="notes" type="text" class="ctrl" placeholder="{{ __('leave_overtime.ui.notes_placeholder') }}">
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Phê duyệt trạng thái (Hiện khi edit hoặc admin) -->
                    @if($isEdit)
                        <div class="fc">
                            <div class="fch" style="background: var(--po-gn-s); color: var(--po-gn-t);">
                                <div class="fci"><i class="fa-solid fa-circle-check"></i></div>
                                <div class="fct">{{ __('leave_overtime.ui.approval') }}</div>
                            </div>
                            <div class="fg fg2">
                                <div class="field">
                                    <label>{{ __('leave_overtime.fields.approver') }} <span class="req">*</span></label>
                                    <select wire:model="approver_id" class="ctrl" required>
                                        <option value="">{{ __('leave_overtime.ui.select_approver') }}</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field">
                                    <label>{{ __('leave_overtime.fields.status') }} <span class="req">*</span></label>
                                    <select wire:model="status" class="ctrl" required>
                                        <option value="pending">{{ __('leave_overtime.status.pending') }}</option>
                                        <option value="approved">{{ __('leave_overtime.status.approved') }}</option>
                                        <option value="rejected">{{ __('leave_overtime.status.rejected') }}</option>
                                        <option value="cancelled">{{ __('leave_overtime.status.cancelled') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endif

                </div><!-- /left form -->

                <!-- RIGHT SUMMARY -->
                <div style="display:flex; flex-direction:column; gap:16px">
                    <div class="lf-sum">
                        <div class="lf-sum-ttl">{{ __('leave_overtime.ui.summary') }}</div>
                        <div class="lf-sum-row">
                            <span class="lf-sum-k">{{ __('leave_overtime.fields.type') }}</span>
                            <span class="lf-sum-v" style="font-weight:700; color:var(--po-bl)">{{ \App\Models\LeaveType::find($leave_type_id)?->name }}</span>
                        </div>
                        <div class="lf-sum-row">
                            <span class="lf-sum-k">{{ __('leave_overtime.ui.applicable_time') }}</span>
                            <span class="lf-sum-v">
                                {{ $start_date ? date('d/m/Y', strtotime($start_date)) : '—' }}
                                @if($formTab === 'leave' && $end_date)
                                    – {{ date('d/m/Y', strtotime($end_date)) }}
                                @endif
                            </span>
                        </div>
                        <div class="lf-sum-row">
                            <span class="lf-sum-k">{{ $formTab === 'leave' ? __('leave_overtime.ui.leave_days') : __('leave_overtime.ui.overtime_hours') }}</span>
                            <span class="lf-sum-v" style="font-weight:700">{{ $duration_text ?: '—' }}</span>
                        </div>
                        
                        @if($formTab === 'leave')
                            <div class="lf-sum-row">
                                <span class="lf-sum-k">{{ __('leave_overtime.ui.handover_time_short') }}</span>
                                <span class="lf-sum-v">
                                    {{ $handover_time ? date('d/m/Y H:i', strtotime($handover_time)) : '—' }}
                                </span>
                            </div>
                            <div class="lf-sum-row">
                                <span class="lf-sum-k">{{ __('leave_overtime.ui.contact') }}</span>
                                <span class="lf-sum-v">
                                    {{ $contact_name ?: '—' }} {{ $contact_phone ? '(' . $contact_phone . ')' : '' }}
                                </span>
                            </div>
                        @else
                            <div class="lf-sum-row">
                                <span class="lf-sum-k">{{ __('leave_overtime.ui.overtime_period') }}</span>
                                <span class="lf-sum-v">{{ $ot_start_time }} – {{ $ot_end_time }}</span>
                            </div>
                            <div class="lf-sum-row">
                                <span class="lf-sum-k">{{ __('leave_overtime.ui.location_short') }}</span>
                                <span class="lf-sum-v">{{ $ot_location }}</span>
                            </div>
                        @endif

                        <div class="lf-sum-row" style="border-bottom:none">
                            <span class="lf-sum-k">{{ __('leave_overtime.ui.reason') }}</span>
                            <span class="lf-sum-v" style="max-height:80px; overflow-y:auto; font-style:italic">
                                {{ $reason ?: '—' }}
                            </span>
                        </div>
                    </div>

                    @if($formTab === 'leave')
                        <div class="lf-quota">
                            <div class="lf-quota-ttl">{{ __('leave_overtime.ui.annual_balance') }}</div>
                            <div class="lf-quota-row">
                                <span class="lf-quota-k">{{ __('leave_overtime.ui.annual_total') }}</span>
                                <span class="lf-quota-v">{{ __('leave_overtime.ui.days_count', ['count' => 12]) }}</span>
                            </div>
                            <div class="lf-quota-row">
                                <span class="lf-quota-k">{{ __('leave_overtime.ui.used') }}</span>
                                <span class="lf-quota-v">{{ __('leave_overtime.ui.days_count', ['count' => 3]) }}</span>
                            </div>
                            <div class="lf-quota-row" style="border-bottom:none">
                                <span class="lf-quota-k">{{ __('leave_overtime.ui.remaining') }}</span>
                                <span class="lf-quota-v" style="color:var(--po-bl); font-weight:800">{{ __('leave_overtime.ui.days_count', ['count' => 9]) }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="lf-notice">
                        <div class="lf-notice-ttl">{{ __('leave_overtime.ui.notice') }}</div>
                        <ul class="lf-rule">
                            <li><i class="fa-solid fa-circle" style="font-size:5px; color:var(--po-mu)"></i> {{ __('leave_overtime.ui.notice_approval') }}</li>
                            <li><i class="fa-solid fa-circle" style="font-size:5px; color:var(--po-mu)"></i> {{ __('leave_overtime.ui.notice_handover') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Footer Action Bar -->
            <div class="ffoot" style="display:flex; justify-content:flex-end; align-items:center; background:var(--po-bd2); border-top:1px solid var(--po-bd2); padding:16px 20px; border-radius:12px; margin-top:20px; gap:8px; box-shadow:var(--po-sh2)">
                <a href="{{ \App\Filament\Resources\LeaveOvertimeResource::getUrl('index') }}" class="emp-btn" style="background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-tx)">
                    {{ __('leave_overtime.actions.cancel') }}
                </a>
                <button type="submit" class="emp-btn emp-btn-primary">
                    <i class="fa-regular fa-paper-plane"></i> {{ __('leave_overtime.ui.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
