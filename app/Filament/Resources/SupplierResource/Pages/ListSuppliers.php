<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Exports\SuppliersExport;
use App\Filament\Resources\SupplierResource;
use App\Models\Ingredient;
use App\Models\IngredientType;
use App\Models\Supplier;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListSuppliers extends Page
{
    use WithPagination;

    protected static string $resource = SupplierResource::class;

    protected static string $view = 'filament.resources.suppliers.pages.list-suppliers';

    public string $search = '';

    /** @var array<int, string> Chọn nhiều loại; NCC khớp nếu chứa BẤT KỲ loại nào đã chọn. */
    public array $typeFilter = [];

    public string $statusFilter = '';

    public int $perPage = 10;

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => []],
        'statusFilter' => ['except' => ''],
    ];

    public function updatedSearch(): void
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

    public function resetFilters(): void
    {
        $this->search = '';
        $this->typeFilter = [];
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function deleteSupplier(int $supplierId): void
    {
        $supplier = Supplier::query()->find($supplierId);

        if (! $supplier) {
            return;
        }

        abort_unless(SupplierResource::canDelete($supplier), 403);

        $supplier->delete();

        Notification::make()
            ->title('Đã xóa nhà cung cấp')
            ->success()
            ->send();

        $this->resetPage();
    }

    public function exportExcel(): BinaryFileResponse
    {
        abort_unless(SupplierResource::canViewAny(), 403);

        // Xuất đúng danh sách đang hiển thị: dùng lại baseQuery() nên mọi bộ lọc
        // (tìm kiếm, loại, trạng thái) và phạm vi quyền đều được giữ nguyên.
        $query = $this->baseQuery()
            ->withCount('ingredients')
            ->orderBy('id');

        $fileName = 'nha-cung-cap-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new SuppliersExport($query), $fileName);
    }

    public function suppliers(): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->withCount('ingredients')
            ->orderBy('id')
            ->paginate($this->perPage);
    }

    /**
     * Nguồn loại cho bộ lọc: danh mục `ingredient_types` (dùng chung với Nguyên
     * liệu). Loại cũ của NCC đã được chuẩn hoá vào danh mục nên chỉ cần đọc đây.
     *
     * @return array<int, string>
     */
    public function typeOptions(): array
    {
        return IngredientType::query()
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        return [
            'suppliers' => Supplier::query()->count(),
            'types' => Supplier::query()->whereNotNull('type')->distinct('type')->count('type'),
            'ingredients' => Ingredient::query()->whereNotNull('supplier_id')->count(),
            'quotes' => Ingredient::query()->whereNotNull('supplier_id')->where('reference_price', '>', 0)->count(),
        ];
    }

    protected function baseQuery()
    {
        return Supplier::query()
            ->when($this->search !== '', function ($query): void {
                $search = mb_strtolower($this->search);
                $query->where(function ($query) use ($search): void {
                    $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($this->typeFilter !== [], fn ($query) => $query->whereHas(
                'ingredientTypes',
                fn ($query) => $query->whereIn('name', $this->typeFilter)
            ))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter === 'active'));
    }
}
