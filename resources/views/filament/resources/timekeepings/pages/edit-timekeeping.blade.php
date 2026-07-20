<div>
    @include('filament.resources.timekeepings.partials.styles')

    @php
        $isEdit = isset($this->record);
        $employees = $this->getEmployees();
        $shifts = $this->getShifts();
    @endphp

    <div class="emp-page" style="padding: 0 !important; background: transparent !important;">
        <!-- Header Section -->
        <div class="emp-head" style="margin-bottom: 20px;">
            <div>
                <h1 class="emp-title">{{ $isEdit ? __('timekeeping.ui.edit_title') : __('timekeeping.ui.create_title') }}</h1>
                <p class="emp-subtitle">
                    {{ $isEdit ? __('timekeeping.ui.edit_description') : __('timekeeping.ui.create_description') }}
                </p>
            </div>
            <div>
                <a href="{{ \App\Filament\Resources\TimekeepingResource::getUrl('index') }}" class="emp-btn" style="background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-tx)">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('timekeeping.ui.back') }}
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div style="background:var(--po-rd-s); color:var(--po-rd-t); padding:12px 16px; border-radius:8px; border:1px solid var(--po-rd); margin-bottom:16px; font-size:13px; font-weight:600">
                <div style="font-weight:700; margin-bottom:4px"><i class="fa-solid fa-triangle-exclamation"></i> {{ __('timekeeping.ui.form_error') }}</div>
                <ul style="list-style-type:disc; padding-left:20px">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form wire:submit.prevent="save" class="fstack">
            <!-- SECTION: THÔNG TIN CHI TIẾT GIỜ CÔNG -->
            <div class="fc">
                <div class="fch">
                    <div class="fci" style="background:var(--po-bl-s);color:var(--po-bl)">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <div class="fct">{{ __('timekeeping.ui.details') }}</div>
                </div>

                <div class="fg fg3">
                    <div class="field">
                        <label>{{ __('timekeeping.fields.employee') }} <span class="req">*</span></label>
                        <select wire:model="employee_id" class="ctrl" required>
                            <option value="">{{ __('timekeeping.fields.employee') }}</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>{{ __('timekeeping.fields.date') }} <span class="req">*</span></label>
                        <input wire:model="date" class="ctrl" type="date" required>
                    </div>

                    <div class="field">
                        <label>{{ __('timekeeping.fields.shift') }} <span class="req">*</span></label>
                        <select wire:model="shift_id" class="ctrl" required>
                            <option value="">{{ __('timekeeping.fields.shift') }}</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->time_range }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>{{ __('timekeeping.fields.check_in') }}</label>
                        <input wire:model="check_in" class="ctrl" type="text" placeholder="HH:MM (VD: 07:01)">
                    </div>

                    <div class="field">
                        <label>{{ __('timekeeping.fields.check_out') }}</label>
                        <input wire:model="check_out" class="ctrl" type="text" placeholder="HH:MM (VD: 16:05)">
                    </div>

                    <div class="field">
                        <label>{{ __('timekeeping.fields.overtime') }}</label>
                        <input wire:model="overtime_hours" class="ctrl" type="text" placeholder="VD: 0h, 1h30">
                    </div>

                    <div class="field">
                        <label>{{ __('timekeeping.fields.status') }} <span class="req">*</span></label>
                        <select wire:model="status" class="ctrl" required>
                            <option value="Đúng giờ">{{ __('timekeeping.status.on_time') }}</option>
                            <option value="Đi trễ">{{ __('timekeeping.status.late') }}</option>
                            <option value="Tăng ca">{{ __('timekeeping.status.overtime') }}</option>
                            <option value="Nghỉ phép">{{ __('timekeeping.status.leave') }}</option>
                            <option value="Vắng mặt">{{ __('timekeeping.status.absent') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Footer Action Bar -->
            <div class="ffoot" style="display:flex; justify-content:flex-end; align-items:center; background:var(--po-bd2); border-top:1px solid var(--po-bd2); padding:16px 20px; border-radius:0 0 12px 12px; margin-top:20px; gap:8px">
                <a href="{{ \App\Filament\Resources\TimekeepingResource::getUrl('index') }}" class="emp-btn" style="background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-tx)">
                    {{ __('timekeeping.actions.cancel') }}
                </a>
                <button type="submit" class="emp-btn emp-btn-primary">
                    <i class="fa-regular fa-floppy-disk"></i> {{ __('timekeeping.ui.save') }}
                </button>
            </div>
        </form>
    </div>
</div>
