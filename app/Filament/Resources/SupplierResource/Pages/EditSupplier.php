<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Filament\Resources\SupplierResource\Concerns\ManagesSupplierDocuments;
use App\Models\Ingredient;
use App\Models\IngredientType;
use App\Models\Supplier;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class EditSupplier extends Page
{
    /** Số dòng nguyên liệu (chưa tích) hiển thị tối đa — bảng render lại mỗi keystroke. */
    protected const INGREDIENT_LIST_LIMIT = 100;

    use ManagesSupplierDocuments;
    use WithFileUploads;

    protected static string $resource = SupplierResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('supplier.breadcrumb.home'),
            SupplierResource::getUrl('index') => __('supplier.breadcrumb.list'),
            __('supplier.breadcrumb.edit'),
        ];
    }

    protected static string $view = 'filament.resources.suppliers.pages.form-supplier';

    public int $supplierId;

    public string $name = '';

    public string $code = '';

    public string $phone = '';

    public string $email = '';

    public bool $status = true;

    public string $notes = '';

    public string $ingredientSearch = '';

    /** @var array<int|string, bool> */
    public array $selectedIngredients = [];

    /** @var array<int|string, string|int|float|null> */
    public array $ingredientCosts = [];

    public function mount(int|string $record): void
    {
        $supplier = Supplier::query()->with(['ingredients'])->findOrFail($record);

        $this->supplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->code = $supplier->code;
        $this->phone = (string) $supplier->phone;
        $this->email = (string) $supplier->email;
        $this->status = (bool) $supplier->status;
        $this->notes = (string) $supplier->notes;
        $this->documents = $supplier->documents ?? [];

        foreach ($supplier->ingredients as $ingredient) {
            $this->selectedIngredients[$ingredient->id] = true;
            $this->ingredientCosts[$ingredient->id] = (float) $ingredient->pivot->reference_price;
        }
    }

    public function saveDraft(): void
    {
        $this->status = false;
        $this->save();
    }

    public function save(): void
    {
        $supplier = Supplier::query()->findOrFail($this->supplierId);
        abort_unless(SupplierResource::canEdit($supplier), 403);

        // Chuẩn hoá dữ liệu trước khi validate để tăng trải nghiệm người dùng (UX)
        $this->code = strtoupper(trim($this->code));
        $this->phone = preg_replace('/[.\-\s]/', '', $this->phone);
        if ($this->email) {
            $this->email = strtolower(trim($this->email));
        }

        $data = $this->validate($this->rules(), $this->messages(), $this->validationAttributes());
        $data['documents'] = $this->processDocuments();

        $supplier->update($data);
        $this->syncIngredients($supplier);
        // Loại thực phẩm suy trực tiếp từ loại của các nguyên liệu đã tích (không chọn tay).
        $supplier->ingredientTypes()->sync($this->derivedTypeIds());

        Notification::make()
            ->title(__('supplier.notifications.saved'))
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
            'type' => implode(', ', $this->derivedTypeNames()) ?: '--',
            'ingredients' => $selected->count(),
            'total' => $selected->sum(fn ($id): float => (float) ($this->ingredientCosts[$id] ?? 0)),
        ];
    }

    /**
     * Danh sách nguyên liệu hiển thị trong bảng chọn.
     *
     * Nguyên liệu ĐÃ TÍCH luôn đứng đầu và luôn có mặt (kể cả khi đang gõ tìm kiếm hoặc khi
     * nằm ngoài giới hạn) — nếu không, tích xong gõ tìm cái khác là dòng đã chọn biến mất
     * và người dùng tưởng bị mất. Phần còn lại giới hạn số dòng vì bảng render lại mỗi keystroke.
     *
     * @return array<int, Ingredient>
     */
    public function ingredients(): array
    {
        $chosenIds = collect($this->selectedIngredients)->filter()->keys()->all();

        $chosen = $chosenIds === []
            ? collect()
            : Ingredient::whereIn('id', $chosenIds)->orderBy('name')->get();

        $others = Ingredient::query()
            ->when($chosenIds !== [], fn ($query) => $query->whereNotIn('id', $chosenIds))
            ->when($this->ingredientSearch !== '', function ($query): void {
                $search = mb_strtolower($this->ingredientSearch);
                $query->where(function ($query) use ($search): void {
                    $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                });
            })
            ->orderBy('name')
            ->limit(self::INGREDIENT_LIST_LIMIT)
            ->get();

        return $chosen->concat($others)->all();
    }

    /** Tổng số nguyên liệu khớp bộ lọc (để báo cho người dùng biết còn bao nhiêu chưa hiện). */
    public function ingredientMatchCount(): int
    {
        return Ingredient::query()
            ->when($this->ingredientSearch !== '', function ($query): void {
                $search = mb_strtolower($this->ingredientSearch);
                $query->where(function ($query) use ($search): void {
                    $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                });
            })
            ->count();
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
                Rule::unique('suppliers', 'code')->ignore($this->supplierId),
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
                        $fail(__('supplier.validation.email_typo'));
                    }
                },
            ],
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
            'code.regex' => __('supplier.validation.code_format'),
            'phone.regex' => __('supplier.validation.phone_format'),
            'email.regex' => __('supplier.validation.email_format'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => __('supplier.attributes.name'),
            'code' => __('supplier.attributes.code'),
            'phone' => __('supplier.attributes.phone'),
            'email' => __('supplier.attributes.email'),
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
        Ingredient::query()
            ->where('supplier_id', $supplier->id)
            ->whereNotIn('id', array_keys($syncData))
            ->update([
                'supplier_id' => null,
                'reference_price' => 0,
            ]);

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
     * ID các loại thực phẩm suy ra từ loại (ingredient_type_id) của những nguyên liệu đã tích —
     * NCC không chọn loại thủ công, loại phản ánh đúng nguyên liệu NCC cung cấp.
     *
     * @return array<int, int>
     */
    protected function derivedTypeIds(): array
    {
        $chosenIds = collect($this->selectedIngredients)->filter()->keys();

        if ($chosenIds->isEmpty()) {
            return [];
        }

        return Ingredient::query()
            ->whereIn('id', $chosenIds)
            ->whereNotNull('ingredient_type_id')
            ->pluck('ingredient_type_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Loại thực phẩm cung cấp (chỉ hiển thị) — tên các loại suy từ nguyên liệu đã tích.
     *
     * @return array<int, string>
     */
    public function derivedTypeNames(): array
    {
        $ids = $this->derivedTypeIds();

        if ($ids === []) {
            return [];
        }

        return IngredientType::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * Tương thích ngược: trả về danh sách loại thực phẩm (suy từ nguyên liệu đã tích).
     *
     * @return array<int, string>
     */
    public function typeOptions(): array
    {
        return $this->derivedTypeNames();
    }

    public function updatedSelectedIngredients(): void
    {
        // Khởi tạo key trong mảng ingredientCosts cho các nguyên liệu mới được chọn
        // để Livewire 3 đồng bộ hoàn chỉnh dữ liệu từ Alpine qua @entangle
        foreach ($this->selectedIngredients as $id => $selected) {
            if ($selected && ! isset($this->ingredientCosts[$id])) {
                $this->ingredientCosts[$id] = (float) (Ingredient::find($id)?->reference_price ?? 0);
            }
        }
    }
}
