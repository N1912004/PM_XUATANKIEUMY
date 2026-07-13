<?php

namespace App\Filament\Resources\IngredientResource\Pages;

use App\Exports\IngredientsExport;
use App\Filament\Resources\IngredientResource;
use App\Imports\IngredientsImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class ListIngredients extends ListRecords
{
    protected static string $resource = IngredientResource::class;

    public function getTitle(): string
    {
        return __('ingredient.navigation.ingredient_plural');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import_excel')
                ->label(__('ingredient.actions.import'))
                ->icon('heroicon-o-document-arrow-up')
                ->color('gray')
                ->outlined()
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
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->outlined()
                ->action(function () {
                    $filename = __('ingredient.excel.filename').'-'.now()->format('Ymd-His').'.xlsx';

                    return Excel::download(
                        new IngredientsExport($this->getFilteredTableQuery()),
                        $filename
                    );
                }),
            Actions\CreateAction::make()
                ->label(__('ingredient.actions.create'))
                ->icon('heroicon-o-plus'),
        ];
    }

    /**
     * Kết quả chạy thử, memo hoá theo đường dẫn tệp: Placeholder có thể được
     * render nhiều lần trong một request, không nên đọc lại file Excel mỗi lần.
     *
     * @var array<string, IngredientsImport>
     */
    protected array $previewCache = [];

    /**
     * Xem trước: chạy đúng luồng import thật trong một transaction rồi rollback,
     * nên con số hiển thị là kết quả thật chứ không phải ước lượng.
     */
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

    /** Chạy import thật rồi huỷ bỏ mọi thay đổi — không ghi gì vào CSDL. */
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

    /**
     * Báo cáo kết quả import: số dòng thêm mới / cập nhật, và liệt kê rõ
     * từng dòng bị bỏ qua (trùng mã, thiếu dữ liệu, lỗi ghi).
     */
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
            // Notification không chứa nổi hàng nghìn dòng — 30 lý do đầu là đủ,
            // danh sách đầy đủ đã soát được ở bước Xem trước
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

        // Filament sanitize rồi render body dạng HTML, nên xuống dòng bằng <br>;
        // nội dung lấy từ Excel nên escape từng dòng trước khi ghép.
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

    protected function getHeaderWidgets(): array
    {
        return [
            IngredientStatsOverview::class,
        ];
    }
}
