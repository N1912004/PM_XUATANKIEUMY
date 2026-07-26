<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Models\Ingredient;
use App\Models\IngredientType;
use App\Models\Supplier;
use Filament\Resources\Pages\Page;

class ViewSupplier extends Page
{
    protected static string $resource = SupplierResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            '#' => __('supplier.breadcrumb.home'),
            SupplierResource::getUrl('index') => __('supplier.breadcrumb.list'),
            __('supplier.breadcrumb.view'),
        ];
    }

    protected static string $view = 'filament.resources.suppliers.pages.view-supplier';

    public int $supplierId;

    public string $name = '';

    public string $code = '';

    public string $phone = '';

    public string $email = '';

    public string $type = '';

    public bool $status = true;

    public string $notes = '';

    /** @var array<int, array{name?: string, expires_at?: string, attachment?: string}> */
    public array $documents = [];

    public string $ingredientSearch = '';

    /** @var array<int|string, bool> */
    public array $selectedIngredients = [];

    /** @var array<int|string, float> */
    public array $ingredientCosts = [];

    public function mount(int|string $record): void
    {
        $supplier = Supplier::query()->with(['ingredients'])->findOrFail($record);
        abort_unless(SupplierResource::canView($supplier), 403);

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

        // Đảm bảo tải đủ các nguyên liệu gắn supplier_id trực tiếp (tương thích hoàn toàn với dữ liệu import/cũ)
        $directIngredients = Ingredient::where('supplier_id', $supplier->id)->get();
        foreach ($directIngredients as $ingredient) {
            if (! isset($this->selectedIngredients[$ingredient->id])) {
                $this->selectedIngredients[$ingredient->id] = true;
                $this->ingredientCosts[$ingredient->id] = (float) $ingredient->reference_price;
            }
        }

        $this->type = implode(', ', $this->derivedTypeNames());
    }

    /**
     * @return array<int, string>
     */
    public function derivedTypeNames(): array
    {
        $chosenIds = collect($this->selectedIngredients)->filter()->keys();

        if ($chosenIds->isEmpty()) {
            return [];
        }

        return IngredientType::query()
            ->whereIn('id', $chosenIds)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function typeOptions(): array
    {
        return $this->derivedTypeNames();
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
     * @return array<int, Ingredient>
     */
    public function ingredients(): array
    {
        $chosenIds = collect($this->selectedIngredients)->filter()->keys()->all();

        if ($chosenIds === []) {
            return [];
        }

        return Ingredient::whereIn('id', $chosenIds)
            ->when($this->ingredientSearch !== '', function ($query): void {
                $search = mb_strtolower($this->ingredientSearch);
                $query->where(function ($query) use ($search): void {
                    $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                });
            })
            ->orderBy('name')
            ->get()
            ->all();
    }
}
