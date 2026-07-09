<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Models\Ingredient;
use App\Models\Supplier;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Validation\Rule;

class EditSupplier extends Page
{
    protected static string $resource = SupplierResource::class;

    protected static string $view = 'filament.resources.suppliers.pages.form-supplier';

    public int $supplierId;

    public string $name = '';

    public string $code = '';

    public string $phone = '';

    public string $email = '';

    public string $type = '';

    public bool $status = true;

    public string $ingredientSearch = '';

    /** @var array<int|string, bool> */
    public array $selectedIngredients = [];

    /** @var array<int|string, string|int|float|null> */
    public array $ingredientCosts = [];

    public function mount(int|string $record): void
    {
        $supplier = Supplier::query()->with('ingredients')->findOrFail($record);

        $this->supplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->code = $supplier->code;
        $this->phone = (string) $supplier->phone;
        $this->email = (string) $supplier->email;
        $this->type = $supplier->type;
        $this->status = (bool) $supplier->status;

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
        $data = $this->validate($this->rules());
        $supplier = Supplier::query()->findOrFail($this->supplierId);

        $supplier->update($data);
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
            ->get()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('suppliers', 'code')->ignore($this->supplierId)],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'status' => ['boolean'],
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
            Ingredient::query()
                ->whereKey($ingredientId)
                ->update([
                    'supplier_id' => $supplier->id,
                    'reference_price' => $pivotData['reference_price'],
                ]);
        }
    }
}
