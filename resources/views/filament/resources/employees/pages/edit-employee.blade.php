<div>
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
                <h1 class="emp-title">{{ $isEdit ? __('employee.edit_title') : __('employee.create_title') }}</h1>
                <p class="emp-subtitle">
{{ $isEdit ? __('employee.edit_description') : __('employee.create_description') }}
                </p>
            </div>
            <div>
                <a href="{{ \App\Filament\Resources\EmployeeResource::getUrl('index') }}" class="emp-btn" style="background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-tx)">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('employee.ui.back') }}
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div style="background:var(--po-rd-s); color:var(--po-rd-t); padding:12px 16px; border-radius:8px; border:1px solid var(--po-rd); margin-bottom:16px; font-size:13px; font-weight:600">
                <div style="font-weight:700; margin-bottom:4px"><i class="fa-solid fa-triangle-exclamation"></i> {{ __('employee.ui.form_error') }}</div>
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
                    <div class="fct">{{ __('employee.sections.personal') }}</div>
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
                                <i class="fa-solid fa-user" style="font-size:44px;color:var(--po-fa)"></i>
                            @endif
                            <label class="pcam" for="avInput">
                                <i class="fa-solid fa-camera"></i>
                            </label>
                            <input id="avInput" type="file" wire:model="avatarFile" accept="image/png,image/jpeg,image/webp" hidden>
                        </div>
                        <div class="phint">
{{ __('employee.fields.avatar') }}<br>
<span>{{ __('employee.upload.compact_hint') }}</span>
                        </div>
                        <div wire:loading wire:target="avatarFile" style="font-size:11px; color:var(--po-bl); font-weight:600; margin-top:5px">
{{ __('employee.upload.uploading') }}
                        </div>
                    </div>

                    <!-- Fields Grid (3 columns) -->
                    <div class="fg fg3">
                        <div class="field sp2">
                            <label>{{ __('employee.fields.name') }} <span class="req">*</span></label>
                            <input wire:model="name" class="ctrl" type="text" placeholder="{{ __('employee.ui.full_name') }}" required>
                        </div>
                        <div class="field">
                            <label>{{ __('employee.fields.code') }} <span class="req">*</span></label>
                            <input wire:model="code" class="ctrl" type="text" placeholder="{{ __('employee.ui.internal_code') }}" required>
                        </div>
                        <div class="field">
                            <label>{{ __('employee.fields.gender') }} <span class="req">*</span></label>
                            <select wire:model="gender" class="ctrl" required>
                                <option value="Nam">{{ __('employee.options.male') }}</option>
                                <option value="Nữ">{{ __('employee.options.female') }}</option>
                                <option value="Khác">{{ __('employee.options.other') }}</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>{{ __('employee.fields.birth_date') }} <span class="req">*</span></label>
                            <input wire:model="dob" class="ctrl" type="date" required>
                        </div>
                        <div class="field">
                            <label>{{ __('employee.fields.identity_number') }} <span class="req">*</span></label>
                            <input wire:model="id_card" class="ctrl" type="text" placeholder="{{ __('employee.placeholders.identity_number') }}" required>
                        </div>
                        <div class="field">
<label>{{ __('employee.fields.issue_date') }}</label>
                            <input wire:model="id_card_date" class="ctrl" type="date">
                        </div>
                        <div class="field">
<label>{{ __('employee.fields.issue_place') }}</label>
                            <input wire:model="id_card_place" class="ctrl" type="text" placeholder="{{ __('employee.ui.id_issue_place') }}">
                        </div>
                        <div class="field">
                            <label>{{ __('employee.fields.phone') }} <span class="req">*</span></label>
                            <input wire:model="phone" class="ctrl" type="tel" placeholder="{{ __('employee.placeholders.phone') }}" required>
                        </div>
                        <div class="field">
                            <label>{{ __('employee.fields.email') }} <span class="req">*</span></label>
                            <input wire:model="email" class="ctrl" type="email" placeholder="{{ __('employee.placeholders.email') }}" required>
                        </div>
                        <div class="field">
<label>{{ __('employee.fields.marital_status') }}</label>
                            <select wire:model="marital_status" class="ctrl">
                                <option value="">{{ __('employee.ui.select_marital') }}</option>
                                <option value="Độc thân">{{ __('employee.options.single') }}</option>
                                <option value="Kết hôn">{{ __('employee.options.married') }}</option>
                                <option value="Khác">{{ __('employee.options.other') }}</option>
                            </select>
                        </div>
                        <div class="field">
<label>{{ __('employee.fields.nationality') }}</label>
                            <select wire:model="nationality" class="ctrl">
                                <option value="Việt Nam">{{ __('employee.options.vietnam') }}</option>
                                <option value="Nước ngoài">{{ __('employee.options.foreign') }}</option>
                            </select>
                        </div>
                        <div class="field">
<label>{{ __('employee.fields.ethnicity') }}</label>
                            <input wire:model="ethnic" class="ctrl" type="text" placeholder="{{ __('employee.ui.ethnicity_hint') }}">
                        </div>
                        <div class="field">
<label>{{ __('employee.fields.religion') }}</label>
                            <input wire:model="religion" class="ctrl" type="text" placeholder="{{ __('employee.ui.religion_hint') }}">
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
                    <div class="fct">{{ __('employee.sections.address') }}</div>
                </div>
                <div class="fg fg3">
                    <div class="field sp2">
<label>{{ __('employee.fields.permanent_address') }}</label>
                        <input wire:model="permanent_address" class="ctrl" type="text" placeholder="{{ __('employee.ui.permanent_hint') }}">
                    </div>
                    <div class="field">
<label>{{ __('employee.fields.temporary_address') }}</label>
                        <input wire:model="temporary_address" class="ctrl" type="text" placeholder="{{ __('employee.ui.current_address') }}">
                    </div>
                    <div class="field">
<label>{{ __('employee.fields.emergency_contact') }}</label>
                        <input wire:model="emergency_contact_name" class="ctrl" type="text" placeholder="{{ __('employee.fields.name') }}">
                    </div>
                    <div class="field">
<label>{{ __('employee.fields.emergency_phone') }}</label>
                        <input wire:model="emergency_contact_phone" class="ctrl" type="tel" placeholder="{{ __('employee.fields.phone') }}">
                    </div>
                    <div class="field">
                        <label>{{ __('employee.ui.relationship') }}</label>
                        <input wire:model="emergency_contact_relation" class="ctrl" type="text" placeholder="{{ __('employee.ui.relationship_hint') }}">
                    </div>
                </div>
            </div>

            <!-- SECTION 3: THÔNG TIN CÔNG VIỆC -->
            <div class="fc">
                <div class="fch">
                    <div class="fci" style="background:var(--gn-s);color:var(--gn)">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                    <div class="fct">{{ __('employee.sections.work') }}</div>
                </div>
                <div class="fg fg4">
                    <div class="field">
                        <label>{{ __('employee.fields.department') }} <span class="req">*</span></label>
                        <select wire:model="department_id" class="ctrl" required>
                            <option value="">{{ __('employee.ui.select_department') }}</option>
                            @foreach(\App\Models\Department::options() as $optId => $optName)
                                <option value="{{ $optId }}">{{ $optName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>{{ __('employee.ui.division') }}</label>
                        <input wire:model="sub_department" class="ctrl" type="text" placeholder="{{ __('employee.ui.division_hint') }}">
                    </div>
                    <div class="field">
                        <label>{{ __('employee.fields.position') }} <span class="req">*</span></label>
                        <select wire:model="position_id" class="ctrl" required>
                            <option value="">{{ __('employee.ui.select_position') }}</option>
                            @foreach(\App\Models\Position::options() as $optId => $optName)
                                <option value="{{ $optId }}">{{ $optName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>{{ __('employee.ui.level') }}</label>
                        <select wire:model="level" class="ctrl">
                            <option value="">{{ __('employee.ui.select_level') }}</option>
                            <option value="Nhân viên">{{ __('employee.ui.employee_level') }}</option>
                            <option value="Chuyên viên">{{ __('employee.ui.specialist') }}</option>
                            <option value="Tổ trưởng">{{ __('employee.ui.team_leader') }}</option>
                            <option value="Quản lý">{{ __('employee.ui.manager') }}</option>
                            <option value="Trưởng phòng">{{ __('employee.ui.department_head') }}</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>{{ __('employee.ui.employment_type') }}</label>
                        <select wire:model="work_type" class="ctrl">
                            <option value="">{{ __('employee.ui.select_employment_type') }}</option>
                            <option value="Toàn thời gian">{{ __('employee.ui.full_time') }}</option>
                            <option value="Bán thời gian">{{ __('employee.ui.part_time') }}</option>
                            <option value="Thời vụ">{{ __('employee.ui.seasonal') }}</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>{{ __('employee.fields.area') }} <span class="req">*</span></label>
                        <select wire:model.live="area_id" class="ctrl" required>
                            <option value="">{{ __('employee.ui.select_area') }}</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>{{ __('employee.fields.kitchen') }}</label>
                        <select wire:model="kitchen_id" class="ctrl">
                            <option value="">{{ __('employee.ui.no_kitchen') }}</option>
                            @foreach($kitchens as $kitchen)
                                <option value="{{ $kitchen->id }}">{{ $kitchen->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>{{ __('employee.fields.start_date') }} <span class="req">*</span></label>
                        <input wire:model="start_date" class="ctrl" type="date" required>
                    </div>
                    <div class="field">
                        <label>{{ __('employee.ui.direct_manager') }}</label>
                        <select wire:model="manager_id" class="ctrl">
                            <option value="">{{ __('employee.ui.select_manager') }}</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}">{{ $manager->name }} ({{ $manager->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>{{ __('employee.ui.employee_status') }} <span class="req">*</span></label>
                        <select wire:model="status" class="ctrl" required>
                            <option value="working">{{ __('employee.status.working') }}</option>
                            <option value="on_leave">{{ __('employee.status.on_leave') }}</option>
                            <option value="resigned">{{ __('employee.status.resigned') }}</option>
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
                    <div class="fct">{{ __('employee.ui.document_tracking') }}</div>
                </div>
                <div style="font-size:12px;color:var(--po-mu);margin-bottom:12px;display:flex;align-items:flex-start;gap:6px;line-height:1.5">
                    <i class="fa-solid fa-circle-info" style="color:var(--po-bl);margin-top:2px"></i>
                    {{ __('employee.ui.document_help') }}
                </div>
                
                <table class="mf-ing-table" style="width:100%; border-collapse:collapse; font-size:13px; table-layout:fixed;">
                    <thead>
                        <tr style="border-bottom:1.5px solid var(--po-bd2); color:var(--po-mu); font-weight:700; text-transform:uppercase; font-size:11px; text-align:left; background:var(--po-bd2)">
                            <th style="padding:10px 8px; width:50px; text-align:center">{{ __('employee.table.index') }}</th>
                            <th style="padding:10px 8px; width:30%">{{ __('employee.ui.document_name') }}</th>
                            <th style="padding:10px 8px; width:40%">{{ __('employee.ui.attachment') }}</th>
                            <th style="padding:10px 8px; width:170px">{{ __('employee.ui.expiry_date') }}</th>
                            <th style="padding:10px 8px; width:70px; text-align:center">{{ __('employee.ui.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $idx => $doc)
                            <tr style="border-bottom:1px solid var(--po-bd2)" wire:key="doc-row-{{ $idx }}">
                                <td style="padding:10px 8px; text-align:center; color:var(--po-mu)">{{ $idx + 1 }}</td>
                                <td style="padding:10px 8px">
                                    <input type="text" wire:model="documents.{{ $idx }}.name" class="ctrl" style="height:32px; border-radius:6px; padding:0 8px; font-size:13px; width:100%" placeholder="{{ __('employee.ui.document_placeholder') }}">
                                </td>
                                <td style="padding:10px 8px">
                                    <div style="display:flex; align-items:center; gap:8px; overflow:hidden">
                                        <!-- Chọn File đính kèm -->
                                        <input type="file" id="docFile_{{ $idx }}" wire:model="uploadedDocFiles.{{ $idx }}" accept=".pdf,.doc,.docx,.jpg,.png,.webp" class="hidden" style="display:none">
                                        <label for="docFile_{{ $idx }}" class="emp-btn" style="background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-mu); padding:4px 10px; font-size:11.5px; border-radius:6px; cursor:pointer; font-weight:600; display:inline-flex; align-items:center; gap:4px; flex-shrink:0">
                                            <i class="fa-solid fa-paperclip"></i> {{ __('employee.ui.choose_file') }}
                                        </label>

                                        <!-- Trạng thái file đã có hoặc file tạm -->
                                        @if (isset($uploadedDocFiles[$idx]))
                                            <span style="font-size:11.5px; color:var(--po-gn); font-weight:600; display:inline-flex; align-items:center; gap:3px; max-width:calc(100% - 90px)">
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
                                            <span style="font-size:11.5px; color:var(--po-mu)">{{ __('employee.ui.no_file') }}</span>
                                        @endif

                                        <div wire:loading wire:target="uploadedDocFiles.{{ $idx }}" style="font-size:10px; color:var(--po-bl)">
                                            {{ __('employee.ui.uploading') }}
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:10px 8px">
                                    <input type="date" wire:model="documents.{{ $idx }}.expired_at" class="ctrl" style="height:32px; border-radius:6px; padding:0 8px; font-size:13px; width:100%">
                                </td>
                                <td style="padding:10px 8px; text-align:center">
                                    <button type="button" wire:click="removeDocument({{ $idx }})" class="abt" style="margin:0 auto; color:var(--po-rd)" title="{{ __('employee.ui.delete_document') }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" wire:click.prevent="addDocument" class="emp-btn" style="margin-top:12px; background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-mu); padding:6px 12px; font-size:12px; border-radius:8px">
                    <i class="fa-solid fa-plus"></i> {{ __('employee.ui.add_document') }}
                </button>
            </div>

            <!-- Footer Action Bar -->
            <div class="ffoot" style="display:flex; justify-content:space-between; align-items:center; background:var(--po-bd2); border-top:1px solid var(--po-bd2); padding:16px 20px; border-radius:0 0 12px 12px; margin-top:20px">
                <div>
                    @if(!$isEdit)
                        <label class="smore" style="font-size:13px; font-weight:600; color:var(--po-tx); display:inline-flex; align-items:center; gap:6px; cursor:pointer">
                            <input type="checkbox" wire:model="createAnother" style="border-radius:4px; border:1px solid var(--po-bd)"> {{ __('employee.ui.create_another') }}
                        </label>
                    @endif
                </div>
                <div style="display:flex; gap:8px">
                    <a href="{{ \App\Filament\Resources\EmployeeResource::getUrl('index') }}" class="emp-btn" style="background:var(--po-wh); border:1px solid var(--po-bd); color:var(--po-tx)">
{{ __('employee.actions.cancel') }}
                    </a>
                    <button type="submit" class="emp-btn emp-btn-primary">
                        <i class="fa-regular fa-floppy-disk"></i> {{ $isEdit ? __('employee.actions.update') : __('employee.actions.save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
