<div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @include('filament.resources.employees.partials.styles')

    @php
        $isEdit = isset($this->record);
        $areas = $this->getAreas();
        $kitchens = $this->getKitchens();
        $managers = $this->getManagers();
    @endphp

    <div class="emp-page" style="padding: 0 !important; background: transparent !important;">
        <!-- Header Section -->
        <div class="emp-head" style="margin-bottom: 20px;">
            <div>
                <h1 class="emp-title">{{ $isEdit ? 'Chỉnh sửa nhân viên' : 'Thêm nhân viên' }}</h1>
                <p class="emp-subtitle">
                    {{ $isEdit ? 'Cập nhật thông tin và hồ sơ nhân viên trong hệ thống' : 'Nhập thông tin để tạo hồ sơ nhân viên mới trong hệ thống' }}
                </p>
            </div>
            <div>
                <a href="{{ \App\Filament\Resources\EmployeeResource::getUrl('index') }}" class="emp-btn" style="background:#fff; border:1px solid var(--po-bd); color:var(--po-tx)">
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
            <!-- SECTION 1: THÔNG TIN CÁ NHÂN -->
            <div class="fc">
                <div class="fch">
                    <div class="fci" style="background:var(--bl-s);color:var(--bl)">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="fct">Thông tin cá nhân</div>
                </div>
                <div class="play">
                    <!-- Avatar Upload Column -->
                    <div class="pcol">
                        <div class="pring">
                            @if ($avatarFile)
                                <img src="{{ $avatarFile->temporaryUrl() }}" style="width:100px; height:100px; border-radius:50%; object-fit:cover;">
                            @elseif ($avatar_url && filter_var($avatar_url, FILTER_VALIDATE_URL))
                                <img src="{{ $avatar_url }}" style="width:100px; height:100px; border-radius:50%; object-fit:cover;">
                            @elseif ($avatar_url)
                                <img src="{{ asset('storage/' . $avatar_url) }}" style="width:100px; height:100px; border-radius:50%; object-fit:cover;">
                            @else
                                <i class="fa-solid fa-user" style="font-size:44px;color:#CBD5E1"></i>
                            @endif
                            <label class="pcam" for="avInput">
                                <i class="fa-solid fa-camera"></i>
                            </label>
                            <input id="avInput" type="file" wire:model="avatarFile" accept="image/png,image/jpeg,image/webp" hidden>
                        </div>
                        <div class="phint">
                            Ảnh hồ sơ cá nhân<br>
                            <span>JPG, PNG, WebP tối đa 2MB</span>
                        </div>
                        <div wire:loading wire:target="avatarFile" style="font-size:11px; color:var(--po-bl); font-weight:600; margin-top:5px">
                            Đang tải ảnh lên...
                        </div>
                    </div>

                    <!-- Fields Grid (3 columns) -->
                    <div class="fg fg3">
                        <div class="field sp2">
                            <label>Họ và tên <span class="req">*</span></label>
                            <input wire:model="name" class="ctrl" type="text" placeholder="Họ tên đầy đủ" required>
                        </div>
                        <div class="field">
                            <label>Mã nhân viên <span class="req">*</span></label>
                            <input wire:model="code" class="ctrl" type="text" placeholder="Mã số định danh nội bộ" required>
                        </div>
                        <div class="field">
                            <label>Giới tính <span class="req">*</span></label>
                            <select wire:model="gender" class="ctrl" required>
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Ngày sinh <span class="req">*</span></label>
                            <input wire:model="dob" class="ctrl" type="date" required>
                        </div>
                        <div class="field">
                            <label>Số CCCD / CMND <span class="req">*</span></label>
                            <input wire:model="id_card" class="ctrl" type="text" placeholder="Số giấy tờ tùy thân" required>
                        </div>
                        <div class="field">
                            <label>Ngày cấp</label>
                            <input wire:model="id_card_date" class="ctrl" type="date">
                        </div>
                        <div class="field">
                            <label>Nơi cấp</label>
                            <input wire:model="id_card_place" class="ctrl" type="text" placeholder="Cơ quan cấp CCCD/CMND">
                        </div>
                        <div class="field">
                            <label>Số điện thoại <span class="req">*</span></label>
                            <input wire:model="phone" class="ctrl" type="tel" placeholder="Liên hệ cá nhân" required>
                        </div>
                        <div class="field">
                            <label>Email <span class="req">*</span></label>
                            <input wire:model="email" class="ctrl" type="email" placeholder="Nhập email" required>
                        </div>
                        <div class="field">
                            <label>Tình trạng hôn nhân</label>
                            <select wire:model="marital_status" class="ctrl">
                                <option value="">Chọn tình trạng</option>
                                <option value="Độc thân">Độc thân</option>
                                <option value="Kết hôn">Kết hôn</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Quốc tịch</label>
                            <select wire:model="nationality" class="ctrl">
                                <option value="Việt Nam">Việt Nam</option>
                                <option value="Nước ngoài">Nước ngoài</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Dân tộc</label>
                            <input wire:model="ethnic" class="ctrl" type="text" placeholder="Theo hồ sơ (VD: Kinh)">
                        </div>
                        <div class="field">
                            <label>Tôn giáo</label>
                            <input wire:model="religion" class="ctrl" type="text" placeholder="Theo hồ sơ (nếu có)">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: ĐỊA CHỈ & LIÊN HỆ KHẨN CẤP -->
            <div class="fc">
                <div class="fch">
                    <div class="fci" style="background:var(--am-s);color:var(--am)">
                        <i class="fa-solid fa-address-book"></i>
                    </div>
                    <div class="fct">Địa chỉ &amp; liên hệ khẩn cấp</div>
                </div>
                <div class="fg fg3">
                    <div class="field sp2">
                        <label>Địa chỉ thường trú</label>
                        <input wire:model="permanent_address" class="ctrl" type="text" placeholder="Theo hộ khẩu / CCCD">
                    </div>
                    <div class="field">
                        <label>Địa chỉ tạm trú</label>
                        <input wire:model="temporary_address" class="ctrl" type="text" placeholder="Nơi ở hiện tại">
                    </div>
                    <div class="field">
                        <label>Người liên hệ khẩn cấp</label>
                        <input wire:model="emergency_contact_name" class="ctrl" type="text" placeholder="Họ tên">
                    </div>
                    <div class="field">
                        <label>SĐT liên hệ khẩn cấp</label>
                        <input wire:model="emergency_contact_phone" class="ctrl" type="tel" placeholder="Số điện thoại">
                    </div>
                    <div class="field">
                        <label>Quan hệ</label>
                        <input wire:model="emergency_contact_relation" class="ctrl" type="text" placeholder="Cha / Mẹ / Vợ / Chồng...">
                    </div>
                </div>
            </div>

            <!-- SECTION 3: THÔNG TIN CÔNG VIỆC -->
            <div class="fc">
                <div class="fch">
                    <div class="fci" style="background:var(--gn-s);color:var(--gn)">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                    <div class="fct">Thông tin công việc</div>
                </div>
                <div class="fg fg4">
                    <div class="field">
                        <label>Phòng ban <span class="req">*</span></label>
                        <select wire:model="department" class="ctrl" required>
                            <option value="">Chọn phòng ban</option>
                            <option value="Nhân sự">Nhân sự</option>
                            <option value="Kế toán">Kế toán</option>
                            <option value="Kho">Kho</option>
                            <option value="Sản xuất">Sản xuất</option>
                            <option value="IT">IT</option>
                            <option value="Kinh doanh">Kinh doanh</option>
                            <option value="Chăm sóc KH">Chăm sóc KH</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Bộ phận</label>
                        <input wire:model="sub_department" class="ctrl" type="text" placeholder="VD: PNS">
                    </div>
                    <div class="field">
                        <label>Chức vụ <span class="req">*</span></label>
                        <input wire:model="position" class="ctrl" type="text" placeholder="VD: Chuyên viên, Tổ trưởng..." required>
                    </div>
                    <div class="field">
                        <label>Cấp bậc</label>
                        <select wire:model="level" class="ctrl">
                            <option value="">Chọn cấp bậc</option>
                            <option value="Nhân viên">Nhân viên</option>
                            <option value="Chuyên viên">Chuyên viên</option>
                            <option value="Tổ trưởng">Tổ trưởng</option>
                            <option value="Quản lý">Quản lý</option>
                            <option value="Trưởng phòng">Trưởng phòng</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Hình thức làm việc</label>
                        <select wire:model="work_type" class="ctrl">
                            <option value="">Chọn hình thức</option>
                            <option value="Toàn thời gian">Toàn thời gian</option>
                            <option value="Bán thời gian">Bán thời gian</option>
                            <option value="Thời vụ">Thời vụ</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Khu vực làm việc <span class="req">*</span></label>
                        <select wire:model.live="area_id" class="ctrl" required>
                            <option value="">Chọn khu vực</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Bếp ăn trực thuộc</label>
                        <select wire:model="kitchen_id" class="ctrl">
                            <option value="">Không có / Văn phòng</option>
                            @foreach($kitchens as $kitchen)
                                <option value="{{ $kitchen->id }}">{{ $kitchen->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Ngày vào làm <span class="req">*</span></label>
                        <input wire:model="start_date" class="ctrl" type="date" required>
                    </div>
                    <div class="field">
                        <label>Quản lý trực tiếp</label>
                        <select wire:model="manager_id" class="ctrl">
                            <option value="">Chọn quản lý</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}">{{ $manager->name }} ({{ $manager->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Trạng thái nhân viên <span class="req">*</span></label>
                        <select wire:model="status" class="ctrl" required>
                            <option value="Đang làm việc">Đang làm việc</option>
                            <option value="Nghỉ phép">Nghỉ phép</option>
                            <option value="Nghỉ việc">Nghỉ việc</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: THEO DÕI HỒ SƠ -->
            <div class="fc">
                <div class="fch">
                    <div class="fci" style="background:var(--pu-s);color:var(--pu)">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <div class="fct">Theo dõi hồ sơ</div>
                </div>
                <div style="font-size:12px;color:var(--po-mu);margin-bottom:12px;display:flex;align-items:flex-start;gap:6px;line-height:1.5">
                    <i class="fa-solid fa-circle-info" style="color:var(--po-bl);margin-top:2px"></i>
                    Nhập tên hồ sơ, đính kèm tệp và chọn ngày hết hạn để theo dõi (VD: Hợp đồng làm việc / Hopdonglamviec.pdf / 29/06/2026).
                </div>
                
                <table class="mf-ing-table" style="width:100%; border-collapse:collapse; font-size:13px; table-layout:fixed;">
                    <thead>
                        <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; text-align:left; background:#F8FAFC">
                            <th style="padding:10px 8px; width:50px; text-align:center">STT</th>
                            <th style="padding:10px 8px; width:30%">Tên hồ sơ</th>
                            <th style="padding:10px 8px; width:40%">Tệp đính kèm</th>
                            <th style="padding:10px 8px; width:170px">Ngày hết hạn</th>
                            <th style="padding:10px 8px; width:70px; text-align:center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $idx => $doc)
                            <tr style="border-bottom:1px solid #F1F5F9" wire:key="doc-row-{{ $idx }}">
                                <td style="padding:10px 8px; text-align:center; color:var(--po-mu)">{{ $idx + 1 }}</td>
                                <td style="padding:10px 8px">
                                    <input type="text" wire:model="documents.{{ $idx }}.name" class="ctrl" style="height:32px; border-radius:6px; padding:0 8px; font-size:13px; width:100%" placeholder="Tên hồ sơ (VD: Hợp đồng lao động)">
                                </td>
                                <td style="padding:10px 8px">
                                    <div style="display:flex; align-items:center; gap:8px; overflow:hidden">
                                        <!-- Chọn File đính kèm -->
                                        <input type="file" id="docFile_{{ $idx }}" wire:model="uploadedDocFiles.{{ $idx }}" accept=".pdf,.doc,.docx,.jpg,.png,.webp" class="hidden" style="display:none">
                                        <label for="docFile_{{ $idx }}" class="emp-btn" style="background:#fff; border:1px solid var(--po-bd); color:#475569; padding:4px 10px; font-size:11.5px; border-radius:6px; cursor:pointer; font-weight:600; display:inline-flex; align-items:center; gap:4px; flex-shrink:0">
                                            <i class="fa-solid fa-paperclip"></i> Chọn tệp
                                        </label>

                                        <!-- Trạng thái file đã có hoặc file tạm -->
                                        @if (isset($uploadedDocFiles[$idx]))
                                            <span style="font-size:11.5px; color:#059669; font-weight:600; display:inline-flex; align-items:center; gap:3px; max-width:calc(100% - 90px)">
                                                <i class="fa-solid fa-file-circle-check" style="flex-shrink:0"></i> 
                                                <span title="{{ $uploadedDocFiles[$idx]->getClientOriginalName() }}" style="display:inline-block; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:middle">
                                                    {{ $uploadedDocFiles[$idx]->getClientOriginalName() }}
                                                </span>
                                            </span>
                                        @elseif (!empty($doc['file_path']))
                                            @php
                                                $fileName = basename($doc['file_path']);
                                                if (str_contains($fileName, 'employee_docs/')) {
                                                    $fileName = str_replace('employee_docs/', '', $fileName);
                                                }
                                            @endphp
                                            <a href="{{ asset('storage/' . $doc['file_path']) }}" target="_blank" style="font-size:11.5px; color:var(--po-bl); font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:3px; max-width:calc(100% - 90px)">
                                                <i class="fa-solid fa-file-pdf" style="flex-shrink:0"></i> 
                                                <span title="{{ $fileName }}" style="display:inline-block; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:middle">
                                                    {{ $fileName }}
                                                </span>
                                            </a>
                                        @else
                                            <span style="font-size:11.5px; color:var(--po-mu)">Chưa có tệp</span>
                                        @endif

                                        <div wire:loading wire:target="uploadedDocFiles.{{ $idx }}" style="font-size:10px; color:var(--po-bl)">
                                            Đang tải...
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:10px 8px">
                                    <input type="date" wire:model="documents.{{ $idx }}.expired_at" class="ctrl" style="height:32px; border-radius:6px; padding:0 8px; font-size:13px; width:100%">
                                </td>
                                <td style="padding:10px 8px; text-align:center">
                                    <button type="button" wire:click="removeDocument({{ $idx }})" class="abt" style="margin:0 auto; color:var(--po-rd)" title="Xóa tài liệu này">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" wire:click.prevent="addDocument" class="emp-btn" style="margin-top:12px; background:#fff; border:1px solid var(--po-bd); color:#475569; padding:6px 12px; font-size:12px; border-radius:8px">
                    <i class="fa-solid fa-plus"></i> Thêm hồ sơ
                </button>
            </div>

            <!-- Footer Action Bar -->
            <div class="ffoot" style="display:flex; justify-content:space-between; align-items:center; background:#F8FAFC; border-top:1px solid var(--po-bd2); padding:16px 20px; border-radius:0 0 12px 12px; margin-top:20px">
                <div>
                    @if(!$isEdit)
                        <label class="smore" style="font-size:13px; font-weight:600; color:var(--po-tx); display:inline-flex; align-items:center; gap:6px; cursor:pointer">
                            <input type="checkbox" wire:model="createAnother" style="border-radius:4px; border:1px solid var(--po-bd)"> Tạo thêm sau khi lưu
                        </label>
                    @endif
                </div>
                <div style="display:flex; gap:8px">
                    <a href="{{ \App\Filament\Resources\EmployeeResource::getUrl('index') }}" class="emp-btn" style="background:#fff; border:1px solid var(--po-bd); color:var(--po-tx)">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="emp-btn emp-btn-primary">
                        <i class="fa-regular fa-floppy-disk"></i> Lưu nhân viên
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
