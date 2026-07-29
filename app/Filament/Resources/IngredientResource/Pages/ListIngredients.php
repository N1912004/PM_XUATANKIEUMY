<?php

namespace App\Filament\Resources\IngredientResource\Pages;

use App\Exports\IngredientsExport;
use App\Filament\Resources\IngredientResource;
use App\Imports\IngredientsImport;
use App\Models\Ingredient;
use App\Models\IngredientType;
use App\Models\Supplier;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListIngredients extends Page
{
    use WithPagination;

    protected static string $resource = IngredientResource::class;

    protected static string $view = 'filament.resources.ingredients.pages.list-ingredients';

    public string $search = '';

    public string $typeFilter = '';

    public string $trashedFilter = '';

    public int $perPage = 10;

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'trashedFilter' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTrashedFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->trashedFilter = '';
        $this->resetPage();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import_excel')
                ->label(__('ingredient.actions.import'))
                ->icon(new HtmlString('<i class="fa-solid fa-file-arrow-up" style="color:#1267E8;font-size:15px"></i>'))
                ->color('gray')
                ->outlined()
                ->extraAttributes(['class' => 'btn-import-excel'])
                ->modalSubmitActionLabel(__('ingredient.import.confirm'))
                ->steps([
                    Step::make(__('ingredient.import.step_upload'))
                        ->icon('heroicon-o-document-arrow-up')
                        ->schema([
                            FileUpload::make('excel_file')
                                ->label(__('ingredient.actions.import_file'))
                                ->acceptedFileTypes([
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'application/vnd.ms-excel',
                                ])
                                ->required()
                                ->disk('local')
                                ->directory('imports'),
                        ]),
                    Step::make(__('ingredient.import.step_preview'))
                        ->icon('heroicon-o-eye')
                        ->description(__('ingredient.import.step_preview_desc'))
                        ->schema([
                            Placeholder::make('preview')
                                ->hiddenLabel()
                                ->content(fn (Get $get) => $this->renderImportPreview($get('excel_file'))),
                        ]),
                ])
                ->action(function (array $data) {
                    $disk = Storage::disk('local');
                    $filePath = $disk->path($data['excel_file']);

                    try {
                        $import = new IngredientsImport;
                        Excel::import($import, $filePath);

                        $this->notifyImportResult($import);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title(__('ingredient.actions.import_error'))
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    } finally {
                        $disk->delete($data['excel_file']);
                    }
                }),
            Actions\Action::make('export_excel')
                ->label(__('ingredient.actions.export'))
                ->icon(new HtmlString('<i class="fa-solid fa-file-excel" style="color:#059669;font-size:15px"></i>'))
                ->color('gray')
                ->outlined()
                ->extraAttributes(['class' => 'btn-export-excel'])
                ->action(function () {
                    $filename = __('ingredient.excel.filename').'-'.now()->format('Ymd-His').'.xlsx';

                    return Excel::download(
                        new IngredientsExport($this->baseQuery()),
                        $filename
                    );
                }),
            Actions\CreateAction::make()
                ->label(__('ingredient.actions.create'))
                ->icon('heroicon-o-plus'),
        ];
    }

    /** @var array<string, IngredientsImport> */
    protected array $previewCache = [];

    protected function renderImportPreview(mixed $state): Htmlable
    {
        $file = is_array($state) ? Arr::first($state) : $state;

        $path = match (true) {
            $file instanceof TemporaryUploadedFile => $file->getRealPath(),
            is_string($file) && $file !== '' => Storage::disk('local')->path($file),
            default => null,
        };

        if ($path === null || ! is_file($path)) {
            return new HtmlString(e(__('ingredient.import.preview_unavailable')));
        }

        try {
            $import = $this->previewCache[$path] ??= $this->dryRun($path);
        } catch (\Throwable $e) {
            return new HtmlString(e(__('ingredient.import.preview_failed', ['message' => $e->getMessage()])));
        }

        return view('filament.resources.ingredients.partials.import-preview', ['import' => $import]);
    }

    protected function dryRun(string $path): IngredientsImport
    {
        $import = new IngredientsImport;

        DB::beginTransaction();

        try {
            Excel::import($import, $path);
        } finally {
            DB::rollBack();
        }

        return $import;
    }

    protected function notifyImportResult(IngredientsImport $import): void
    {
        $lines = [];
        $created = $import->countOf(IngredientsImport::CREATED);
        $updated = $import->countOf(IngredientsImport::UPDATED);
        $skipped = $import->skippedMessages();

        if ($created > 0) {
            $lines[] = __('ingredient.import.created', ['count' => $created]);
        }

        if ($updated > 0) {
            $lines[] = __('ingredient.import.updated', ['count' => $updated]);
        }

        if ($skipped !== []) {
            $lines[] = __('ingredient.import.skipped', ['count' => count($skipped)]);
            $lines = array_merge($lines, array_slice($skipped, 0, 30));
            if (count($skipped) > 30) {
                $lines[] = __('ingredient.import.preview_more', ['count' => count($skipped) - 30]);
            }
        }

        if ($import->successCount() === 0 && $skipped === []) {
            Notification::make()
                ->title(__('ingredient.import.empty'))
                ->warning()
                ->send();

            return;
        }

        $notification = Notification::make()
            ->body(implode('<br>', array_map('e', $lines)));

        if ($skipped === []) {
            $notification->title(__('ingredient.actions.import_success'))->success();
        } elseif ($import->successCount() > 0) {
            $notification->title(__('ingredient.import.partial'))->warning()->persistent();
        } else {
            $notification->title(__('ingredient.import.none'))->danger()->persistent();
        }

        $notification->send();
    }

    public function deleteIngredient(int $id): void
    {
        $ingredient = Ingredient::withTrashed()->find($id);

        if (! $ingredient) {
            return;
        }

        abort_unless(IngredientResource::canDelete($ingredient), 403);

        $ingredient->delete();

        Notification::make()
            ->title(__('ingredient.notifications.deleted', ['name' => $ingredient->name]))
            ->success()
            ->send();

        $this->resetPage();
    }

    public function restoreIngredient(int $id): void
    {
        $ingredient = Ingredient::onlyTrashed()->find($id);

        if (! $ingredient) {
            return;
        }

        abort_unless(IngredientResource::canRestore($ingredient), 403);

        $ingredient->restore();

        Notification::make()
            ->title(__('ingredient.notifications.restored', ['name' => $ingredient->name]))
            ->success()
            ->send();

        $this->resetPage();
    }

    public function forceDeleteIngredient(int $id): void
    {
        $ingredient = Ingredient::onlyTrashed()->find($id);

        if (! $ingredient) {
            return;
        }

        abort_unless(IngredientResource::canForceDelete($ingredient), 403);

        $ingredient->forceDelete();

        Notification::make()
            ->title(__('ingredient.notifications.force_deleted', ['name' => $ingredient->name]))
            ->success()
            ->send();

        $this->resetPage();
    }

    public function exportExcel(): BinaryFileResponse
    {
        abort_unless(IngredientResource::canViewAny(), 403);

        $query = $this->baseQuery()->orderByDesc('id');
        $filename = __('ingredient.excel.filename').'-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new IngredientsExport($query), $filename);
    }

    public function ingredients(): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->with(['typeRelation', 'unitRelation', 'supplier'])
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }

    public function stats(): array
    {
        return [
            'total' => Ingredient::query()->count(),
            'active' => Ingredient::query()->where('reference_price', '>', 0)->count(),
            'types' => IngredientType::query()->count(),
            'suppliers' => Supplier::query()->count(),
        ];
    }

    public function typeOptions(): array
    {
        return IngredientType::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    protected function baseQuery()
    {
        return Ingredient::query()
            ->when($this->trashedFilter === 'with', fn ($q) => $q->withTrashed())
            ->when($this->trashedFilter === 'only', fn ($q) => $q->onlyTrashed())
            ->when($this->search !== '', function ($query): void {
                $search = mb_strtolower($this->search);
                $query->where(function ($q) use ($search): void {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($this->typeFilter !== '', fn ($q) => $q->where('ingredient_type_id', $this->typeFilter));
    }
}
