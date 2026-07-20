<?php

namespace App\Filament\Resources\RecipeResource\Pages;

use App\Exports\RecipeExport;
use App\Filament\Resources\RecipeResource;
use App\Imports\RecipesImport;
use App\Models\Recipe;
use App\Models\RecipeType;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ListRecipes extends Page
{
    use WithPagination;

    protected static string $resource = RecipeResource::class;

    protected static string $view = 'filament.resources.recipes.pages.list-recipes';

    public string $search = '';

    public string $priceFilter = '';

    public string $typeFilter = '';

    public string $statusFilter = '';

    public string $trashedFilter = '';

    public array $selectedRecipes = [];

    public int $perPage = 10;

    public ?int $expandedRecipeId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'priceFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'trashedFilter' => ['except' => ''],
    ];

    public function resetSelection(): void
    {
        $this->selectedRecipes = [];
    }

    public function updatedSearch(): void
    {
        $this->resetSelection();
        $this->resetPage();
    }

    public function updatedPriceFilter(): void
    {
        $this->resetSelection();
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetSelection();
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetSelection();
        $this->resetPage();
    }

    public function updatedTrashedFilter(): void
    {
        $this->resetSelection();
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetSelection();
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->priceFilter = '';
        $this->typeFilter = '';
        $this->statusFilter = '';
        $this->trashedFilter = '';
        $this->resetSelection();
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
            ->title(__('recipe.notifications.deleted'))
            ->success()
            ->send();

        $this->resetPage();
    }

    public function restoreRecipe(int $recipeId): void
    {
        $recipe = Recipe::withTrashed()->find($recipeId);

        if (! $recipe) {
            return;
        }

        abort_unless(RecipeResource::canRestore($recipe), 403);

        $recipe->restore();

        Notification::make()
            ->title(__('recipe.notifications.restored'))
            ->success()
            ->send();

        $this->resetPage();
    }

    public function forceDeleteRecipe(int $recipeId): void
    {
        $recipe = Recipe::withTrashed()->find($recipeId);

        if (! $recipe) {
            return;
        }

        abort_unless(RecipeResource::canForceDelete($recipe), 403);

        $recipe->ingredients()->detach();
        $recipe->forceDelete();

        Notification::make()
            ->title(__('recipe.notifications.force_deleted'))
            ->success()
            ->send();

        $this->resetPage();
    }

    public function selectPage(array $ids, bool $checked): void
    {
        if ($checked) {
            $this->selectedRecipes = array_unique(array_merge($this->selectedRecipes, $ids));
        } else {
            $this->selectedRecipes = array_diff($this->selectedRecipes, $ids);
        }
    }

    public function bulkDelete(): void
    {
        if (empty($this->selectedRecipes)) {
            return;
        }

        $recipes = Recipe::query()->whereIn('id', $this->selectedRecipes)->get();
        $count = 0;
        foreach ($recipes as $recipe) {
            if (RecipeResource::canDelete($recipe)) {
                $recipe->delete();
                $count++;
            }
        }

        Notification::make()
            ->title(__('recipe.notifications.bulk_deleted', ['count' => $count]))
            ->success()
            ->send();

        $this->resetSelection();
        $this->resetPage();
    }

    public function bulkRestore(): void
    {
        if (empty($this->selectedRecipes)) {
            return;
        }

        $recipes = Recipe::onlyTrashed()->whereIn('id', $this->selectedRecipes)->get();
        $count = 0;
        foreach ($recipes as $recipe) {
            if (RecipeResource::canRestore($recipe)) {
                $recipe->restore();
                $count++;
            }
        }

        Notification::make()
            ->title(__('recipe.notifications.bulk_restored', ['count' => $count]))
            ->success()
            ->send();

        $this->resetSelection();
        $this->resetPage();
    }

    public function bulkForceDelete(): void
    {
        if (empty($this->selectedRecipes)) {
            return;
        }

        $recipes = Recipe::withTrashed()->whereIn('id', $this->selectedRecipes)->get();
        $count = 0;
        foreach ($recipes as $recipe) {
            if (RecipeResource::canForceDelete($recipe)) {
                $recipe->ingredients()->detach();
                $recipe->forceDelete();
                $count++;
            }
        }

        Notification::make()
            ->title(__('recipe.notifications.bulk_force_deleted', ['count' => $count]))
            ->success()
            ->send();

        $this->resetSelection();
        $this->resetPage();
    }

    /**
     * Import món ăn theo file mẫu "ĐỊNH LƯỢNG MÓN ĂN.xlsx" — wizard 2 bước
     * (tải file → XEM TRƯỚC kết quả bằng dry-run rollback), cùng pattern trang Nguyên liệu.
     */
    public function importAction(): Actions\Action
    {
        return Actions\Action::make('import')
            ->label(__('recipe.import.action'))
            ->icon('heroicon-o-document-arrow-up')
            ->color('gray')
            ->visible(fn (): bool => RecipeResource::canCreate())
            ->modalSubmitActionLabel(__('recipe.import.confirm'))
            ->steps([
                Step::make('Tải tệp lên')
                    ->icon('heroicon-o-document-arrow-up')
                    ->schema([
                        FileUpload::make('excel_file')
                            ->label(__('recipe.import.file_label'))
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->required()
                            ->disk('local')
                            ->directory('imports'),
                    ]),
                Step::make('Xem trước')
                    ->icon('heroicon-o-eye')
                    ->description(__('recipe.import.preview_description'))
                    ->schema([
                        Placeholder::make('preview')
                            ->hiddenLabel()
                            ->content(fn (Get $get) => $this->renderImportPreview($get('excel_file'))),
                    ]),
            ])
            ->action(function (array $data): void {
                $disk = Storage::disk('local');
                $filePath = $disk->path($data['excel_file']);

                try {
                    $import = new RecipesImport;
                    Excel::import($import, $filePath);

                    $this->notifyImportResult($import);
                    $this->resetPage();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title(__('recipe.import.error_title'))
                        ->body(e($e->getMessage()))
                        ->danger()
                        ->persistent()
                        ->send();
                } finally {
                    $disk->delete($data['excel_file']);
                }
            });
    }

    /** Xuất ngân hàng món ăn ra .xlsx — cùng pattern nút xuất của trang Nguyên liệu. */
    public function exportAction(): Actions\Action
    {
        return Actions\Action::make('export')
            ->label(__('recipe.actions.export'))
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray')
            ->action(fn () => Excel::download(
                new RecipeExport($this->buildRecipesQuery()),
                'ngan-hang-thuc-don-'.now()->format('Ymd-His').'.xlsx',
            ));
    }

    /** Nút Thêm món ăn dạng Filament Action để đồng bộ giao diện */
    public function createAction(): Actions\Action
    {
        return Actions\Action::make('create')
            ->label(__('recipe.actions.add'))
            ->icon('heroicon-o-plus')
            ->color('primary')
            ->url(RecipeResource::getUrl('create'));
    }

    /**
     * Kết quả chạy thử, memo hoá theo đường dẫn tệp — Placeholder có thể render
     * nhiều lần trong một request, không đọc lại file Excel mỗi lần.
     *
     * @var array<string, RecipesImport>
     */
    protected array $previewCache = [];

    /** Xem trước: chạy đúng luồng import thật trong transaction rồi rollback. */
    protected function renderImportPreview(mixed $state): Htmlable
    {
        $file = is_array($state) ? Arr::first($state) : $state;

        $path = match (true) {
            $file instanceof TemporaryUploadedFile => $file->getRealPath(),
            is_string($file) && $file !== '' => Storage::disk('local')->path($file),
            default => null,
        };

        if ($path === null || ! is_file($path)) {
            return new HtmlString(e(__('recipe.import.no_preview_file')));
        }

        try {
            $import = $this->previewCache[$path] ??= $this->dryRun($path);
        } catch (\Throwable $e) {
            return new HtmlString(e(__('recipe.import.cannot_read_file', ['message' => $e->getMessage()])));
        }

        return view('filament.resources.recipes.partials.import-preview', ['import' => $import]);
    }

    /** Chạy import thật rồi huỷ bỏ mọi thay đổi — không ghi gì vào CSDL. */
    protected function dryRun(string $path): RecipesImport
    {
        $import = new RecipesImport;

        DB::beginTransaction();

        try {
            Excel::import($import, $path);
        } finally {
            DB::rollBack();
        }

        return $import;
    }

    /** Báo cáo kết quả: số món thêm mới / cập nhật, liệt kê rõ món bị bỏ qua. */
    protected function notifyImportResult(RecipesImport $import): void
    {
        $created = $import->countOf(RecipesImport::CREATED);
        $updated = $import->countOf(RecipesImport::UPDATED);
        $skipped = $import->skippedMessages();

        $lines = [];
        if ($created > 0) {
            $lines[] = __('recipe.import.created_count', ['count' => $created]);
        }
        if ($updated > 0) {
            $lines[] = __('recipe.import.updated_count', ['count' => $updated]);
        }
        if ($skipped !== []) {
            // Notification không chứa nổi hàng nghìn dòng — liệt kê 30 lý do đầu,
            // danh sách đầy đủ đã soát được ở bước Xem trước
            $lines[] = __('recipe.import.skipped_count', ['count' => count($skipped)]);
            $lines = array_merge($lines, array_slice($skipped, 0, 30));
            if (count($skipped) > 30) {
                $lines[] = __('recipe.import.more_skipped', ['count' => count($skipped) - 30]);
            }
        }

        if ($created + $updated === 0 && $skipped === []) {
            Notification::make()->title(__('recipe.import.empty'))->warning()->send();

            return;
        }

        // Filament render body dạng HTML — xuống dòng bằng <br>, escape từng dòng từ Excel
        $notification = Notification::make()->body(implode('<br>', array_map('e', $lines)));

        if ($skipped === []) {
            $notification->title(__('recipe.import.success'))->success();
        } elseif ($created + $updated > 0) {
            $notification->title(__('recipe.import.partial'))->warning()->persistent();
        } else {
            $notification->title(__('recipe.import.failed'))->danger()->persistent();
        }

        $notification->send();
    }

    public function buildRecipesQuery(): Builder
    {
        $query = Recipe::query();

        if ($this->trashedFilter === 'with') {
            $query->withTrashed();
        } elseif ($this->trashedFilter === 'only') {
            $query->onlyTrashed();
        }

        return $query
            ->with(['ingredients', 'recipeType'])
            ->when($this->search !== '', function ($query): void {
                $search = mb_strtolower($this->search);
                $query->where(function ($q) use ($search): void {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($this->priceFilter !== '', fn ($query) => $query->where('standard_price_per_portion', $this->priceFilter))
            ->when($this->typeFilter !== '', function ($query): void {
                // Lọc theo cột type tương thích ngược (qua bảng recipe_types hoặc fallback old_type)
                $query->where(function ($q): void {
                    $q->whereHas('recipeType', fn ($sub) => $sub->where('name', $this->typeFilter))
                        ->orWhere('old_type', $this->typeFilter);
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter));
    }

    public function recipes(): LengthAwarePaginator
    {
        return $this->buildRecipesQuery()
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
