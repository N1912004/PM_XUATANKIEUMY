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
                <h1 class="emp-title">{{ $isEdit ? 'Chỉnh sửa yêu cầu' : 'Tạo yêu cầu mới' }}</h1>
                <p class="emp-subtitle">
                    {{ $isEdit ? 'Cập nhật chi tiết yêu cầu nghỉ phép hoặc tăng ca của nhân viên' : 'Vui lòng nhập thông tin để gửi yêu cầu nghỉ phép hoặc tăng ca' }}
                </p>
            </div>
            <div>
                <a href="{{ \App\Filament\Resources\LeaveOvertimeResource::getUrl('index') }}" class="emp-btn" style="background:#fff; border:1px solid var(--po-bd); color:var(--po-tx)">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>

        <!-- Sub Tabs (Chỉ cho chuyển tab khi tạo mới) -->
        <div class="lf-tabs" style="margin-bottom: 20px;">
            @if($isEdit)
                <button type="button" class="lf-tab active" style="cursor: default">
                    @if($formTab === 'leave')
                        <i class="fa-regular fa-calendar"></i> Nghỉ phép
                    @else
                        <i class="fa-regular fa-clock"></i> Tăng ca
                    @endif
                </button>
            @else
                <button type="button" wire:click="switchFormTab('leave')" class="lf-tab {{ $formTab === 'leave' ? 'active' : '' }}">
                    <i class="fa-regular fa-calendar"></i> Nghỉ phép
                </button>
                <button type="button" wire:click="switchFormTab('ot')" class="lf-tab {{ $formTab === 'ot' ? 'active' : '' }}">
                    <i class="fa-regular fa-clock"></i> Tăng ca
                </button>
            @endif
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

        <form wire:submit.prevent="save">
            <div class="lf-layout">
                <!-- LEFT FORM -->
                <div class="fstack" style="display:flex; flex-direction:column; gap:16px">
                    
                    @if($formTab === 'leave')
                        <!-- LEAVE FORM -->
                        <div class="fc">
                            <div class="fch" style="background: var(--po-pu-s); color: var(--po-pu);">
                                <div class="fci"><i class="fa-regular fa-calendar"></i></div>
                                <div class="fct">1. Thông tin nghỉ phép</div>
                            </div>

                            <div class="fg fg2" style="margin-bottom:14px">
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
                                    <label>Loại nghỉ phép <span class="req">*</span></label>
                                    <select wire:model="type" class="ctrl" required>
                                        <option value="Nghỉ phép năm">Nghỉ phép năm</option>
                                        <option value="Nghỉ phép bệnh">Nghỉ phép bệnh</option>
                                        <option value="Nghỉ không lương">Nghỉ không lương</option>
                                        <option value="Nghỉ thai sản">Nghỉ thai sản</option>
                                    </select>
                                </div>
                            </div>

                            <div class="fg fg2" style="margin-bottom:14px">
                                <div class="field">
                                    <label>Thời gian nghỉ <span class="req">*</span></label>
                                    <div style="display:flex; align-items:center; gap:8px">
                                        <input wire:model="start_date" type="date" class="ctrl" required style="flex:1">
                                        <span style="color:var(--po-mu)">đến</span>
                                        <input wire:model="end_date" type="date" class="ctrl" required style="flex:1">
                                    </div>
                                </div>

                                <div class="field">
                                    <label>Số ngày nghỉ</label>
                                    <div style="display:flex; align-items:center; gap:8px">
                                        <input wire:model="duration_text" type="text" class="ctrl" placeholder="VD: 3 ngày" style="flex:1">
                                    </div>
                                </div>
                            </div>

                            <div class="field" style="margin-bottom:14px">
                                <label style="display:inline-flex; align-items:center; gap:8px; font-weight:500; cursor:pointer">
                                    <input wire:model="no_count_leave" type="checkbox" style="width:16px; height:16px; border-radius:4px; border:1px solid var(--po-bd)">
                                    Nghỉ không tính phép
                                </label>
                            </div>

                            <div class="fg fg2" style="margin-bottom:14px">
                                <div class="field">
                                    <label>Thời gian bàn giao công việc</label>
                                    <input wire:model="handover_time" type="datetime-local" class="ctrl">
                                </div>
                            </div>

                            <div class="field">
                                <label>Lý do nghỉ <span class="req">*</span></label>
                                <textarea wire:model="reason" class="ctrl" placeholder="Nhập lý do nghỉ phép" rows="4" required maxlength="200"></textarea>
                                <div style="display:flex; justify-content:flex-end; font-size:11px; color:var(--po-fa); margin-top:4px">
                                    <span>{{ strlen($reason) }}/200</span>
                                </div>
                            </div>
                        </div>

                        <div class="fc">
                            <div class="fch">
                                <div class="fci" style="background:var(--po-bl-s);color:var(--po-bl)"><i class="fa-regular fa-address-book"></i></div>
                                <div class="fct">2. Thông tin liên hệ khi cần</div>
                            </div>

                            <div class="fg fg2" style="margin-bottom:14px">
                                <div class="field">
                                    <label>Người liên hệ</label>
                                    <input wire:model="contact_name" type="text" class="ctrl" placeholder="Nhập tên người liên hệ">
                                </div>
                                <div class="field">
                                    <label>Số điện thoại</label>
                                    <input wire:model="contact_phone" type="tel" class="ctrl" placeholder="Nhập số điện thoại">
                                </div>
                            </div>

                            <div class="field">
                                <label>Ghi chú thêm (nếu có)</label>
                                <textarea wire:model="notes" class="ctrl" placeholder="Nhập ghi chú thêm" rows="3" maxlength="200"></textarea>
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
                                <div class="fct">1. Thông tin tăng ca</div>
                            </div>

                            <div class="fg fg3" style="margin-bottom:14px">
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
                                    <label>Loại tăng ca <span class="req">*</span></label>
                                    <select wire:model="type" class="ctrl" required>
                                        <option value="Tăng ca ngày thường">Tăng ca ngày thường</option>
                                        <option value="Tăng ca cuối tuần">Tăng ca cuối tuần</option>
                                        <option value="Tăng ca ngày lễ">Tăng ca ngày lễ</option>
                                    </select>
                                </div>

                                <div class="field">
                                    <label>Ngày tăng ca <span class="req">*</span></label>
                                    <input wire:model="start_date" type="date" class="ctrl" required>
                                </div>
                            </div>

                            <div class="fg fg2" style="margin-bottom:14px">
                                <div class="field">
                                    <label>Thời gian tăng ca <span class="req">*</span></label>
                                    <div style="display:flex; align-items:center; gap:8px">
                                        <input wire:model="ot_start_time" type="time" class="ctrl" required style="flex:1">
                                        <span style="color:var(--po-mu)">đến</span>
                                        <input wire:model="ot_end_time" type="time" class="ctrl" required style="flex:1">
                                    </div>
                                </div>

                                <div class="field">
                                    <label>Số giờ tăng ca</label>
                                    <input wire:model="duration_text" type="text" class="ctrl" placeholder="VD: 3 giờ">
                                </div>
                            </div>

                            <div class="fg fg2" style="margin-bottom:14px">
                                <div class="field">
                                    <label>Địa điểm / khu vực làm việc</label>
                                    <select wire:model="ot_location" class="ctrl">
                                        <option value="Bếp trung tâm - Khu A">Bếp trung tâm - Khu A</option>
                                        <option value="Kho nguyên liệu">Kho nguyên liệu</option>
                                        <option value="Van phòng HQ">Van phòng HQ</option>
                                    </select>
                                </div>

                                <div class="field">
                                    <label>Người quản lý duyệt <span class="req">*</span></label>
                                    <select wire:model="approver_id" class="ctrl" required>
                                        <option value="">Chọn người duyệt</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="field" style="margin-bottom:14px">
                                <label>Lý do tăng ca <span class="req">*</span></label>
                                <textarea wire:model="reason" class="ctrl" placeholder="Lý do tăng ca..." rows="3" required></textarea>
                            </div>

                            <div class="field" style="margin-bottom:14px">
                                <label>Công việc thực hiện</label>
                                <textarea wire:model="ot_work_description" class="ctrl" placeholder="Mô tả công việc sẽ thực hiện..." rows="3"></textarea>
                            </div>

                            <div class="field" style="margin-bottom:14px">
                                <label>Đính kèm file</label>
                                <div class="lf-upload">
                                    <div class="lf-upload-ico"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                                    <span>Kéo thả file vào đây hoặc <span style="color:var(--po-bl);font-weight:700;text-decoration:underline">nhấn để chọn file</span></span>
                                    <small>PDF, XLSX, JPG, PNG tối da 5MB</small>
                                </div>
                            </div>
                        </div>

                        <div class="fc">
                            <div class="fch">
                                <div class="fci" style="background:var(--po-bl-s);color:var(--po-bl)"><i class="fa-regular fa-handshake"></i></div>
                                <div class="fct">2. Xác nhận &amp; bàn giao</div>
                            </div>

                            <div class="fg fg2" style="margin-bottom:14px">
                                <div class="field">
                                    <label>Người phối hợp</label>
                                    <input wire:model="co_worker" type="text" class="ctrl" placeholder="Nhập tên người phối hợp">
                                </div>
                                <div class="field">
                                    <label>Ghi chú thêm</label>
                                    <input wire:model="notes" type="text" class="ctrl" placeholder="Ghi chú thêm...">
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Phê duyệt trạng thái (Hiện khi edit hoặc admin) -->
                    @if($isEdit)
                        <div class="fc">
                            <div class="fch" style="background: var(--po-gn-s); color: var(--po-gn-t);">
                                <div class="fci"><i class="fa-solid fa-circle-check"></i></div>
                                <div class="fct">Phê duyệt &amp; Trạng thái yêu cầu</div>
                            </div>
                            <div class="fg fg2">
                                <div class="field">
                                    <label>Người duyệt <span class="req">*</span></label>
                                    <select wire:model="approver_id" class="ctrl" required>
                                        <option value="">Chọn người duyệt</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Trạng thái phê duyệt <span class="req">*</span></label>
                                    <select wire:model="status" class="ctrl" required>
                                        <option value="Chờ duyệt">Chờ duyệt</option>
                                        <option value="Đã duyệt">Đã duyệt</option>
                                        <option value="Từ chối">Từ chối</option>
                                        <option value="Đã hủy">Đã hủy</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endif

                </div><!-- /left form -->

                <!-- RIGHT SUMMARY -->
                <div style="display:flex; flex-direction:column; gap:16px">
                    <div class="lf-sum">
                        <div class="lf-sum-ttl">Tóm tắt yêu cầu</div>
                        <div class="lf-sum-row">
                            <span class="lf-sum-k">Loại yêu cầu</span>
                            <span class="lf-sum-v" style="font-weight:700; color:var(--po-bl)">{{ $type }}</span>
                        </div>
                        <div class="lf-sum-row">
                            <span class="lf-sum-k">Thời gian áp dụng</span>
                            <span class="lf-sum-v">
                                {{ $start_date ? date('d/m/Y', strtotime($start_date)) : '—' }}
                                @if($formTab === 'leave' && $end_date)
                                    – {{ date('d/m/Y', strtotime($end_date)) }}
                                @endif
                            </span>
                        </div>
                        <div class="lf-sum-row">
                            <span class="lf-sum-k">{{ $formTab === 'leave' ? 'Số ngày nghỉ' : 'Số giờ tăng ca' }}</span>
                            <span class="lf-sum-v" style="font-weight:700">{{ $duration_text ?: '—' }}</span>
                        </div>
                        
                        @if($formTab === 'leave')
                            <div class="lf-sum-row">
                                <span class="lf-sum-k">Thời gian bàn giao</span>
                                <span class="lf-sum-v">
                                    {{ $handover_time ? date('d/m/Y H:i', strtotime($handover_time)) : '—' }}
                                </span>
                            </div>
                            <div class="lf-sum-row">
                                <span class="lf-sum-k">Người liên hệ</span>
                                <span class="lf-sum-v">
                                    {{ $contact_name ?: '—' }} {{ $contact_phone ? '(' . $contact_phone . ')' : '' }}
                                </span>
                            </div>
                        @else
                            <div class="lf-sum-row">
                                <span class="lf-sum-k">Giờ tăng ca</span>
                                <span class="lf-sum-v">{{ $ot_start_time }} – {{ $ot_end_time }}</span>
                            </div>
                            <div class="lf-sum-row">
                                <span class="lf-sum-k">Địa điểm</span>
                                <span class="lf-sum-v">{{ $ot_location }}</span>
                            </div>
                        @endif

                        <div class="lf-sum-row" style="border-bottom:none">
                            <span class="lf-sum-k">Lý do</span>
                            <span class="lf-sum-v" style="max-height:80px; overflow-y:auto; font-style:italic">
                                {{ $reason ?: '—' }}
                            </span>
                        </div>
                    </div>

                    @if($formTab === 'leave')
                        <div class="lf-quota">
                            <div class="lf-quota-ttl">Số dư phép năm</div>
                            <div class="lf-quota-row">
                                <span class="lf-quota-k">Tổng số ngày phép</span>
                                <span class="lf-quota-v">12 ngày</span>
                            </div>
                            <div class="lf-quota-row">
                                <span class="lf-quota-k">Đã sử dụng</span>
                                <span class="lf-quota-v">3 ngày</span>
                            </div>
                            <div class="lf-quota-row" style="border-bottom:none">
                                <span class="lf-quota-k">Số dư còn lại</span>
                                <span class="lf-quota-v" style="color:var(--po-bl); font-weight:800">9 ngày</span>
                            </div>
                        </div>
                    @endif

                    <div class="lf-notice">
                        <div class="lf-notice-ttl">Lưu ý</div>
                        <ul class="lf-rule">
                            <li><i class="fa-solid fa-circle" style="font-size:5px; color:var(--po-mu)"></i> Yêu cầu sẽ được gửi trực tiếp đến người quản lý được chỉ định để phê duyệt.</li>
                            <li><i class="fa-solid fa-circle" style="font-size:5px; color:var(--po-mu)"></i> Vui lòng hoàn thành bàn giao công việc cần thiết trước thời gian áp dụng yêu cầu.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Footer Action Bar -->
            <div class="ffoot" style="display:flex; justify-content:flex-end; align-items:center; background:#F8FAFC; border-top:1px solid var(--po-bd2); padding:16px 20px; border-radius:12px; margin-top:20px; gap:8px; box-shadow:var(--po-sh2)">
                <a href="{{ \App\Filament\Resources\LeaveOvertimeResource::getUrl('index') }}" class="emp-btn" style="background:#fff; border:1px solid var(--po-bd); color:var(--po-tx)">
                    Hủy bỏ
                </a>
                <button type="submit" class="emp-btn emp-btn-primary">
                    <i class="fa-regular fa-paper-plane"></i> Gửi yêu cầu
                </button>
            </div>
        </form>
    </div>
</div>
