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
                <h1 class="emp-title">{{ $isEdit ? 'Chỉnh sửa bản ghi chấm công' : 'Thêm bản ghi chấm công' }}</h1>
                <p class="emp-subtitle">
                    {{ $isEdit ? 'Cập nhật thông tin chi tiết giờ công của nhân viên' : 'Tạo mới bản ghi chấm công thủ công cho nhân viên' }}
                </p>
            </div>
            <div>
                <a href="{{ \App\Filament\Resources\TimekeepingResource::getUrl('index') }}" class="emp-btn" style="background:#fff; border:1px solid var(--po-bd); color:var(--po-tx)">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div style="background:#FEF2F2; color:#991B1B; padding:12px 16px; border-radius:8px; border:1px solid #FCA5A5; margin-bottom:16px; font-size:13px; font-weight:600">
                <div style="font-weight:700; margin-bottom:4px"><i class="fa-solid fa-triangle-exclamation"></i> Có lỗi xảy ra, vui lòng kiểm tra lại:</div>
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
                    <div class="fct">Thông tin chi tiết chấm công</div>
                </div>

                <div class="fg fg3">
                    <div class="field">
                        <label>Nhân viên <span class="req">*</span></label>
                        <select wire:model="employee_id" class="ctrl" required>
                            <option value="">Chọn nhân viên</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Ngày làm việc <span class="req">*</span></label>
                        <input wire:model="date" class="ctrl" type="date" required>
                    </div>

                    <div class="field">
                        <label>Ca làm việc <span class="req">*</span></label>
                        <select wire:model="shift_id" class="ctrl" required>
                            <option value="">Chọn ca</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->time_range }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Giờ vào (Check-in)</label>
                        <input wire:model="check_in" class="ctrl" type="text" placeholder="HH:MM (VD: 07:01)">
                    </div>

                    <div class="field">
                        <label>Giờ ra (Check-out)</label>
                        <input wire:model="check_out" class="ctrl" type="text" placeholder="HH:MM (VD: 16:05)">
                    </div>

                    <div class="field">
                        <label>Số giờ tăng ca</label>
                        <input wire:model="overtime_hours" class="ctrl" type="text" placeholder="VD: 0h, 1h30">
                    </div>

                    <div class="field">
                        <label>Trạng thái <span class="req">*</span></label>
                        <select wire:model="status" class="ctrl" required>
                            <option value="Đúng giờ">Đúng giờ</option>
                            <option value="Đi trễ">Đi trễ</option>
                            <option value="Tăng ca">Tăng ca</option>
                            <option value="Nghỉ phép">Nghỉ phép</option>
                            <option value="Vắng mặt">Vắng mặt</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Footer Action Bar -->
            <div class="ffoot" style="display:flex; justify-content:flex-end; align-items:center; background:#F8FAFC; border-top:1px solid var(--po-bd2); padding:16px 20px; border-radius:0 0 12px 12px; margin-top:20px; gap:8px">
                <a href="{{ \App\Filament\Resources\TimekeepingResource::getUrl('index') }}" class="emp-btn" style="background:#fff; border:1px solid var(--po-bd); color:var(--po-tx)">
                    Hủy bỏ
                </a>
                <button type="submit" class="emp-btn emp-btn-primary">
                    <i class="fa-regular fa-floppy-disk"></i> Lưu bản ghi
                </button>
            </div>
        </form>
    </div>
</div>
