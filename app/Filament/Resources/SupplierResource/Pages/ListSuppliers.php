<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Models\Ingredient;
use App\Models\Supplier;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListSuppliers extends Page
{
    use WithPagination;

    protected static string $resource = SupplierResource::class;

    protected static string $view = 'filament.resources.suppliers.pages.list-suppliers';

    public string $search = '';

    public string $typeFilter = '';

    public string $statusFilter = '';

    public int $perPage = 10;

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
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
        $this->typeFilter = '';
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

    public function exportExcel(): StreamedResponse
    {
        abort_unless(SupplierResource::canViewAny(), 403);

        $fileName = 'nha-cung-cap-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, ['Mã NCC', 'Tên NCC', 'Số điện thoại', 'Email', 'Loại TP', 'Số nguyên liệu', 'Trạng thái']);

            $this->baseQuery()
                ->withCount('ingredients')
                ->orderBy('id')
                ->chunk(100, function ($suppliers) use ($output): void {
                    foreach ($suppliers as $supplier) {
                        fputcsv($output, [
                            $supplier->code,
                            $supplier->name,
                            $supplier->phone,
                            $supplier->email,
                            $supplier->type,
                            $supplier->ingredients_count,
                            $supplier->status ? 'Đang hoạt động' : 'Tạm khóa',
                        ]);
                    }
                });

            fclose($output);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function suppliers(): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->withCount('ingredients')
            ->orderBy('id')
            ->paginate($this->perPage);
    }

    /**
     * @return array<int, string>
     */
    public function typeOptions(): array
    {
        return Supplier::query()
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type', 'type')
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
            ->when($this->typeFilter !== '', fn ($query) => $query->where('type', $this->typeFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter === 'active'));
    }
}
