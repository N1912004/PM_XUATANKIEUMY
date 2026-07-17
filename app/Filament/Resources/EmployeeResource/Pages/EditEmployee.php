<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\Area;
use App\Models\Employee;
use App\Models\Kitchen;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Livewire\WithFileUploads;

class EditEmployee extends Page
{
    use InteractsWithRecord;
    use WithFileUploads;

    protected static string $resource = EmployeeResource::class;

    protected static string $view = 'filament.resources.employees.pages.edit-employee';

    // Form fields
    public $code;

    public $name;

    public $email;

    public $phone;

    public $department_id;

    public $position_id;

    public $area_id;

    public $kitchen_id;

    public $start_date;

    public $status;

    public $avatar_url;

    // Detailed fields
    public $gender;

    public $dob;

    public $id_card;

    public $id_card_date;

    public $id_card_place;

    public $marital_status;

    public $nationality;

    public $ethnic;

    public $religion;

    public $permanent_address;

    public $temporary_address;

    // Emergency contact
    public $emergency_contact_name;

    public $emergency_contact_phone;

    public $emergency_contact_relation;

    // Job details
    public $sub_department;

    public $level;

    public $work_type;

    public $manager_id;

    // Files upload
    public $avatarFile;

    public array $documents = [];

    public $uploadedDocFiles = []; // index => TemporaryUploadedFile

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->code = $this->record->code;
        $this->name = $this->record->name;
        $this->email = $this->record->email;
        $this->phone = $this->record->phone;
        $this->department_id = $this->record->department_id;
        $this->position_id = $this->record->position_id;
        $this->area_id = $this->record->area_id;
        $this->kitchen_id = $this->record->kitchen_id;
        $this->start_date = $this->record->start_date ? $this->record->start_date->toDateString() : null;
        $this->status = $this->record->status;
        $this->avatar_url = $this->record->avatar_url;

        $this->gender = $this->record->gender ?: 'Nam';
        $this->dob = $this->record->dob ? $this->record->dob->toDateString() : null;
        $this->id_card = $this->record->id_card;
        $this->id_card_date = $this->record->id_card_date ? $this->record->id_card_date->toDateString() : null;
        $this->id_card_place = $this->record->id_card_place;
        $this->marital_status = $this->record->marital_status;
        $this->nationality = $this->record->nationality ?: 'Việt Nam';
        $this->ethnic = $this->record->ethnic;
        $this->religion = $this->record->religion;
        $this->permanent_address = $this->record->permanent_address;
        $this->temporary_address = $this->record->temporary_address;

        $this->emergency_contact_name = $this->record->emergency_contact_name;
        $this->emergency_contact_phone = $this->record->emergency_contact_phone;
        $this->emergency_contact_relation = $this->record->emergency_contact_relation;

        $this->sub_department = $this->record->sub_department;
        $this->level = $this->record->level;
        $this->work_type = $this->record->work_type;
        $this->manager_id = $this->record->manager_id;

        $this->documents = $this->record->documents ?: [];
    }

    public function addDocument()
    {
        $this->documents[] = [
            'name' => '',
            'file_path' => '',
            'expired_at' => now()->addYear()->toDateString(),
        ];
    }

    public function removeDocument($index)
    {
        unset($this->documents[$index]);
        unset($this->uploadedDocFiles[$index]);
        $this->documents = array_values($this->documents);

        $newFiles = [];
        $i = 0;
        foreach ($this->uploadedDocFiles as $file) {
            $newFiles[$i++] = $file;
        }
        $this->uploadedDocFiles = $newFiles;
    }

    public function save()
    {
        abort_unless(EmployeeResource::canEdit($this->record), 403);

        $this->validate([
            'code' => 'required|unique:employees,code,'.$this->record->id,
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'department_id' => 'required',
            'position_id' => 'required',
            'area_id' => 'required',
            'start_date' => 'required|date',
            'status' => 'required',
            'dob' => 'nullable|date',
            'id_card_date' => 'nullable|date',
            'avatarFile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'uploadedDocFiles.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,webp|max:5120',
            'documents.*.expired_at' => 'nullable|date',
        ], [
            'code.required' => 'Mã nhân viên là bắt buộc.',
            'code.unique' => 'Mã nhân viên đã tồn tại.',
            'name.required' => 'Họ và tên là bắt buộc.',
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email không đúng định dạng.',
            'phone.required' => 'Số điện thoại là bắt buộc.',
            'department_id.required' => 'Phòng ban là bắt buộc.',
            'position_id.required' => 'Chức vụ là bắt buộc.',
            'area_id.required' => 'Khu vực là bắt buộc.',
            'start_date.required' => 'Ngày vào làm là bắt buộc.',
            'avatarFile.image' => 'Ảnh đại diện phải là tệp hình ảnh (JPG, PNG, WebP).',
            'avatarFile.mimes' => 'Ảnh đại diện phải là tệp hình ảnh (JPG, PNG, WebP).',
            'avatarFile.max' => 'Ảnh đại diện tối đa 2MB.',
            'uploadedDocFiles.*.mimes' => 'Tệp đính kèm phải là PDF, Word hoặc hình ảnh.',
            'uploadedDocFiles.*.max' => 'Tệp đính kèm tối đa 5MB.',
            'documents.*.expired_at.date' => 'Ngày hết hạn hồ sơ không đúng định dạng.',
        ]);

        $avatarPath = $this->avatar_url;
        if ($this->avatarFile) {
            $avatarPath = $this->avatarFile->store('avatars', 'public');
        }

        $processedDocs = [];
        foreach ($this->documents as $index => $doc) {
            $filePath = $doc['file_path'];
            if (isset($this->uploadedDocFiles[$index])) {
                $filePath = $this->uploadedDocFiles[$index]->store('employee_docs', 'public');
            }
            $processedDocs[] = [
                'name' => $doc['name'] ?: 'Tài liệu không tên',
                'file_path' => $filePath,
                'expired_at' => $doc['expired_at'],
            ];
        }

        $this->record->update([
            'code' => $this->code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'department_id' => $this->department_id,
            'position_id' => $this->position_id,
            'area_id' => $this->area_id,
            'kitchen_id' => $this->kitchen_id,
            'start_date' => $this->start_date,
            'status' => $this->status,
            'avatar_url' => $avatarPath,
            'gender' => $this->gender,
            'dob' => $this->dob,
            'id_card' => $this->id_card,
            'id_card_date' => $this->id_card_date,
            'id_card_place' => $this->id_card_place,
            'marital_status' => $this->marital_status,
            'nationality' => $this->nationality,
            'ethnic' => $this->ethnic,
            'religion' => $this->religion,
            'permanent_address' => $this->permanent_address,
            'temporary_address' => $this->temporary_address,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'emergency_contact_relation' => $this->emergency_contact_relation,
            'sub_department' => $this->sub_department,
            'level' => $this->level,
            'work_type' => $this->work_type,
            'manager_id' => $this->manager_id,
            'documents' => $processedDocs,
        ]);

        session()->flash('message', 'Cập nhật nhân viên thành công!');

        return redirect($this->getResource()::getUrl('index'));
    }

    public function getAreas()
    {
        return Area::all();
    }

    public function getKitchens()
    {
        return Kitchen::when($this->area_id, fn ($q) => $q->where('area_id', $this->area_id))->get();
    }

    public function getManagers()
    {
        return Employee::where('status', 'working')
            ->where('id', '!=', $this->record->id)
            ->get();
    }
}
