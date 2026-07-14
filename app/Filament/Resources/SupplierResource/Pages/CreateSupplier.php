<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Filament\Resources\SupplierResource\Concerns\ManagesSupplierDocuments;
use App\Filament\Resources\SupplierResource\Concerns\ManagesSupplierIngredients;
use App\Models\Ingredient;
use App\Models\Supplier;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class CreateSupplier extends Page
{
    use ManagesSupplierDocuments;
    use ManagesSupplierIngredients;
    use WithFileUploads;

    protected static string $resource = SupplierResource::class;

    protected static string $view = 'filament.resources.suppliers.pages.form-supplier';

    public string $name = '';

    public string $code = '';

    public string $phone = '';

    public string $email = '';

    public string $type = '';

    public bool $status = true;

    public string $notes = '';

    public string $ingredientSearch = '';

    /** @var array<int|string, bool> */
    public array $selectedIngredients = [];

    /** @var array<int|string, string|int|float|null> */
    public array $ingredientCosts = [];

    public function mount(): void
    {
        $this->code = $this->nextSupplierCode();
    }

    public function saveDraft(): void
    {
        $this->status = false;
        $this->save();
    }

    public function save(): void
    {
        abort_unless(SupplierResource::canCreate(), 403);

        // Chuẩn hoá dữ liệu trước khi validate để tăng trải nghiệm người dùng (UX)
        $this->code = strtoupper(trim($this->code));
        $this->phone = preg_replace('/[.\-\s]/', '', $this->phone);
        if ($this->email) {
            $this->email = strtolower(trim($this->email));
        }

        $data = $this->validate($this->rules(), $this->messages(), $this->validationAttributes());
        $data['documents'] = $this->processDocuments();

        $supplier = Supplier::query()->create($data);
        $this->syncIngredients($supplier);

        Notification::make()
            ->title('Đã lưu nhà cung cấp')
            ->success()
            ->send();

        $this->redirect(SupplierResource::getUrl('index'));
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $selected = collect($this->selectedIngredients)->filter()->keys();

        return [
            'code' => $this->code ?: '--',
            'type' => $this->type ?: '--',
            'ingredients' => $selected->count(),
            'total' => $selected->sum(fn ($id): float => (float) ($this->ingredientCosts[$id] ?? 0)),
        ];
    }

    /**
     * @return array<int, Ingredient>
     */
    public function ingredients(): array
    {
        return Ingredient::query()
            ->when($this->ingredientSearch !== '', function ($query): void {
                $search = mb_strtolower($this->ingredientSearch);
                $query->where(function ($query) use ($search): void {
                    $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                });
            })
            ->orderBy('id')
            ->limit(100) // bảng chọn nguyên liệu render lại mỗi keystroke — giới hạn và dùng ô tìm kiếm để thu hẹp
            ->get()
            ->all();
    }

    protected function nextSupplierCode(): string
    {
        return 'NCC'.str_pad((string) ((Supplier::query()->max('id') ?? 0) + 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('suppliers', 'code'),
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^(0|\+84|84)[235789][0-9]{8,9}$/',
            ],
            'email' => [
                'nullable',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z][a-zA-Z0-9.-]*\.[a-zA-Z]{2,}$/',
                function ($attribute, $value, $fail) {
                    $domain = strtolower(substr(strrchr($value, '@'), 1));
                    $typoDomains = ['1gmail.com', 'gamil.com', 'gmail.con', 'yaho.com', 'hotamil.com', 'outlok.com'];
                    if (in_array($domain, $typoDomains)) {
                        $fail('Địa chỉ email chứa tên miền không hợp lệ hoặc sai chính tả.');
                    }
                },
            ],
            'type' => ['required', 'string', 'max:255'],
            'status' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'code.regex' => 'Mã nhà cung cấp chỉ được chứa chữ cái không dấu, chữ số, dấu gạch ngang (-) và gạch dưới (_).',
            'phone.regex' => 'Số điện thoại không đúng định dạng Việt Nam (phải gồm 10-11 số, bắt đầu bằng 0, 84 hoặc +84).',
            'email.regex' => 'Địa chỉ email không đúng định dạng (tên miền sau ký tự @ phải bắt đầu bằng chữ cái).',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => 'tên nhà cung cấp',
            'code' => 'mã nhà cung cấp',
            'phone' => 'số điện thoại',
            'email' => 'địa chỉ email',
            'type' => 'loại thực phẩm',
        ];
    }

    protected function syncIngredients(Supplier $supplier): void
    {
        $syncData = [];
        foreach ($this->selectedIngredients as $ingredientId => $selected) {
            if ($selected) {
                $syncData[$ingredientId] = [
                    'reference_price' => (float) ($this->ingredientCosts[$ingredientId] ?? 0),
                ];
            }
        }
        // Log lịch sử giá TRƯỚC khi sync (cần đọc pivot cũ để so giá cũ → mới)
        $this->logPriceChanges($supplier, $syncData);

        $supplier->ingredients()->sync($syncData);

        // Đồng bộ ngược cột supplier_id và reference_price ở bảng ingredients để tương thích ngược.
        foreach ($syncData as $ingredientId => $pivotData) {
            // Update qua model instance để hook đổi giá của Ingredient chạy
            // (đưa các recipe liên quan về 'pending' khi giá tham chiếu thay đổi)
            $ingredient = Ingredient::find($ingredientId);
            $ingredient?->update([
                'supplier_id' => $supplier->id,
                'reference_price' => $pivotData['reference_price'],
            ]);
        }
    }

    /**
     * Lấy các Loại Nguyên liệu từ danh sách nguyên liệu đã chọn.
     *
     * @return array<string, string>
     */
    public function getAvailableTypes(): array
    {
        $selectedIds = collect($this->selectedIngredients)
            ->filter()
            ->keys();

        if ($selectedIds->isEmpty()) {
            return [];
        }

        $types = Ingredient::query()
            ->whereIn('id', $selectedIds)
            ->with('typeRelation')
            ->get()
            ->pluck('type')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $options = [];
        foreach ($types as $type) {
            $options[$type] = $type;
        }

        return $options;
    }

    public function updatedSelectedIngredients(): void
    {
        $types = array_keys($this->getAvailableTypes());
        $this->type = implode(', ', $types);

        // Khởi tạo key trong mảng ingredientCosts cho các nguyên liệu mới được chọn
        // để Livewire 3 đồng bộ hoàn chỉnh dữ liệu từ Alpine qua @entangle
        foreach ($this->selectedIngredients as $id => $selected) {
            if ($selected && ! isset($this->ingredientCosts[$id])) {
                $this->ingredientCosts[$id] = (float) (Ingredient::find($id)?->reference_price ?? 0);
            }
        }
    }
}
