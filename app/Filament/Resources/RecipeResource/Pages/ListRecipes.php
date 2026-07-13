<?php

namespace App\Filament\Resources\RecipeResource\Pages;

use App\Filament\Resources\RecipeResource;
use App\Imports\RecipesImport;
use App\Models\Recipe;
use App\Models\RecipeType;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ListRecipes extends Page
{
    use WithFileUploads;
    use WithPagination;

    protected static string $resource = RecipeResource::class;

    protected static string $view = 'filament.resources.recipes.pages.list-recipes';

    /** File Excel định lượng món ăn chờ import (theo mẫu ĐỊNH LƯỢNG MÓN ĂN.xlsx) */
    public $importFile = null;

    public string $search = '';

    public string $priceFilter = '';

    public string $typeFilter = '';

    public string $statusFilter = '';

    public int $perPage = 10;

    public ?int $expandedRecipeId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'priceFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPriceFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->priceFilter = '';
        $this->typeFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function toggleExpand(int $recipeId): void
    {
        if ($this->expandedRecipeId === $recipeId) {
            $this->expandedRecipeId = null;
        } else {
            $this->expandedRecipeId = $recipeId;
        }
    }

    public function deleteRecipe(int $recipeId): void
    {
        $recipe = Recipe::query()->find($recipeId);

        if (! $recipe) {
            return;
        }

        abort_unless(RecipeResource::canDelete($recipe), 403);

        $recipe->delete();

        Notification::make()
            ->title('Đã xóa món ăn khỏi ngân hàng thực đơn')
            ->success()
            ->send();

        $this->resetPage();
    }

    /**
     * Import ngân hàng món ăn từ file Excel theo mẫu "ĐỊNH LƯỢNG MÓN ĂN.xlsx"
     * (mỗi món kèm bảng định mức gram — hệ thống tự quy đổi kg).
     */
    public function importRecipes(): void
    {
        abort_unless(RecipeResource::canCreate(), 403);

        $this->validate(
            ['importFile' => 'required|file|mimes:xlsx,xls|max:10240'],
            [
                'importFile.required' => 'Vui lòng chọn file Excel định lượng món ăn.',
                'importFile.mimes' => 'Chỉ nhận file .xlsx hoặc .xls.',
                'importFile.max' => 'File tối đa 10MB.',
            ],
        );

        $import = new RecipesImport;
        Excel::import($import, $this->importFile->getRealPath());

        $created = $import->countOf(RecipesImport::CREATED);
        $updated = $import->countOf(RecipesImport::UPDATED);
        $skipped = $import->countOf(RecipesImport::SKIPPED);

        $notification = Notification::make()
            ->title("Import xong: {$created} món mới, {$updated} món cập nhật".($skipped > 0 ? ", {$skipped} món bị bỏ qua" : ''))
            ->body($skipped > 0 ? implode('<br>', array_map('e', array_slice($import->skippedMessages(), 0, 5))) : null);

        $skipped > 0 ? $notification->warning()->persistent() : $notification->success();
        $notification->send();

        $this->importFile = null;
        $this->resetPage();
    }

    public function recipes(): LengthAwarePaginator
    {
        return Recipe::query()
            ->with(['ingredients', 'recipeType'])
            ->when($this->search !== '', function ($query): void {
                $search = mb_strtolower($this->search);
                $query->where(function ($q) use ($search): void {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($this->priceFilter !== '', fn ($query) => $query->where('price_level', $this->priceFilter))
            ->when($this->typeFilter !== '', function ($query): void {
                // Lọc theo cột type tương thích ngược (qua bảng recipe_types hoặc fallback old_type)
                $query->where(function ($q): void {
                    $q->whereHas('recipeType', fn ($sub) => $sub->where('name', $this->typeFilter))
                        ->orWhere('old_type', $this->typeFilter);
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);
    }

    public function priceOptions(): array
    {
        return [
            15000 => '15.000 đ',
            20000 => '20.000 đ',
            25000 => '25.000 đ',
            30000 => '30.000 đ',
            35000 => '35.000 đ',
            40000 => '40.000 đ',
            50000 => '50.000 đ',
        ];
    }

    public function typeOptions(): array
    {
        return RecipeType::pluck('name', 'name')->all();
    }
}
