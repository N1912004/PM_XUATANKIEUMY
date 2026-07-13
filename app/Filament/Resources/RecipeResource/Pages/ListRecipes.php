<?php

namespace App\Filament\Resources\RecipeResource\Pages;

use App\Filament\Resources\RecipeResource;
use App\Models\Recipe;
use App\Models\RecipeType;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;

class ListRecipes extends Page
{
    use WithPagination;

    protected static string $resource = RecipeResource::class;

    protected static string $view = 'filament.resources.recipes.pages.list-recipes';

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
