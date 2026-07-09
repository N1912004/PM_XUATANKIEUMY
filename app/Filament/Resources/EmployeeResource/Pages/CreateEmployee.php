<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\Area;
use App\Models\Employee;
use App\Models\Kitchen;
use Filament\Resources\Pages\Page;
use Livewire\WithFileUploads;

class CreateEmployee extends Page
{
    use WithFileUploads;

    protected static string $resource = EmployeeResource::class;

    protected static string $view = 'filament.resources.employees.pages.edit-employee';

    // Form fields
    public $code;

    public $name;

    public $email;

    public $phone;

    public $department;

    public $position;

    public $area_id;

    public $kitchen_id;

    public $start_date;

    public $status = 'Đang làm việc';

    public $avatar_url;

    // Detailed fields
    public $gender = 'Nam';

    public $dob;

    public $id_card;

    public $id_card_date;

    public $id_card_place;

    public $marital_status;

    public $nationality = 'Việt Nam';

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

    public bool $createAnother = false;

    public function mount(): void
    {
        $this->start_date = now()->toDateString();
        $this->dob = now()->subYears(20)->toDateString();
        $this->id_card_date = now()->subYears(2)->toDateString();
        $this->documents = [];
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
        abort_unless(EmployeeResource::canCreate(), 403);

        $this->validate([
            'code' => 'required|unique:employees,code',
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'department' => 'required',
            'position' => 'required',
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
            'department.required' => 'Phòng ban là bắt buộc.',
            'position.required' => 'Chức vụ là bắt buộc.',
            'area_id.required' => 'Khu vực là bắt buộc.',
            'start_date.required' => 'Ngày vào làm là bắt buộc.',
            'avatarFile.image' => 'Ảnh đại diện phải là tệp hình ảnh (JPG, PNG, WebP).',
            'avatarFile.mimes' => 'Ảnh đại diện phải là tệp hình ảnh (JPG, PNG, WebP).',
            'avatarFile.max' => 'Ảnh đại diện tối đa 2MB.',
            'uploadedDocFiles.*.mimes' => 'Tệp đính kèm phải là PDF, Word hoặc hình ảnh.',
            'uploadedDocFiles.*.max' => 'Tệp đính kèm tối đa 5MB.',
            'documents.*.expired_at.date' => 'Ngày hết hạn hồ sơ không đúng định dạng.',
        ]);

        $avatarPath = null;
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

        Employee::create([
            'code' => $this->code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'department' => $this->department,
            'position' => $this->position,
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

        session()->flash('message', 'Thêm mới nhân viên thành công!');

        if ($this->createAnother) {
            return redirect($this->getResource()::getUrl('create'));
        }

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
        return Employee::where('status', 'Đang làm việc')->get();
    }
}
