<?php

namespace App\Filament\Resources\SupplierResource\Concerns;

use App\Models\Supplier;
use App\Models\SupplierPriceLog;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Hồ sơ NCC (hợp đồng/chứng nhận ATTP kèm ngày hết hạn, file đính kèm) và
 * lịch sử chỉnh đơn giá NCC ↔ nguyên liệu — dùng chung cho trang Create/Edit Supplier.
 */
trait ManagesSupplierDocuments
{
    /**
     * Hồ sơ NCC: [['name' => ..., 'expires_at' => ..., 'file_path' => ...], ...]
     *
     * @var array<int, array<string, mixed>>
     */
    public array $documents = [];

    /** @var array<int, TemporaryUploadedFile|null> */
    public array $documentUploads = [];

    public function addDocument(): void
    {
        $this->documents[] = ['name' => '', 'expires_at' => null, 'file_path' => null];
    }

    public function removeDocument(int $index): void
    {
        unset($this->documents[$index], $this->documentUploads[$index]);
        $this->documents = array_values($this->documents);
        $this->documentUploads = array_values($this->documentUploads);
    }

    /**
     * Lưu file hồ sơ vừa upload và trả về mảng documents sạch để ghi vào cột JSON.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function processDocuments(): array
    {
        $clean = [];
        foreach ($this->documents as $index => $doc) {
            $name = trim((string) ($doc['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $upload = $this->documentUploads[$index] ?? null;
            if ($upload) {
                $this->validate(
                    ["documentUploads.{$index}" => 'file|mimes:jpg,jpeg,png,webp,pdf|max:5120'],
                    ["documentUploads.{$index}.mimes" => 'Hồ sơ chỉ nhận ảnh hoặc PDF.', "documentUploads.{$index}.max" => 'Hồ sơ tối đa 5MB.'],
                );
                $doc['file_path'] = $upload->store('supplier-documents', 'public');
            }

            $clean[] = [
                'name' => $name,
                'expires_at' => $doc['expires_at'] ?: null,
                'file_path' => $doc['file_path'] ?? null,
            ];
        }

        return $clean;
    }

    /**
     * Ghi lịch sử giá NCC ↔ nguyên liệu: so pivot cũ với giá mới, log các dòng thay đổi.
     *
     * @param  array<int|string, array<string, mixed>>  $syncData
     */
    protected function logPriceChanges(Supplier $supplier, array $syncData): void
    {
        $oldPrices = DB::table('ingredient_supplier')
            ->where('supplier_id', $supplier->id)
            ->pluck('reference_price', 'ingredient_id');

        foreach ($syncData as $ingredientId => $pivotData) {
            $newPrice = (float) $pivotData['reference_price'];
            $oldPrice = $oldPrices->has($ingredientId) ? (float) $oldPrices->get($ingredientId) : null;

            if ($oldPrice === null || $oldPrice !== $newPrice) {
                SupplierPriceLog::create([
                    'supplier_id' => $supplier->id,
                    'ingredient_id' => $ingredientId,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'user_id' => auth()->id(),
                ]);
            }
        }
    }
}
