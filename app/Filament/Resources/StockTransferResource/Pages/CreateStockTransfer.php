<?php

namespace App\Filament\Resources\StockTransferResource\Pages;

use App\Filament\Resources\StockTransferResource;
use App\Models\StockTransfer;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateStockTransfer extends CreateRecord
{
    protected static string $resource = StockTransferResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = $this->generateCode();
        $data['status'] = StockTransfer::STATUS_IN_TRANSIT;
        $data['created_by'] = Filament::auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Đóng băng lượng tồn tại bếp xuất ngay khi phiếu được tạo.
        $this->record->load('items')->freezeSourceStock();
    }

    protected function generateCode(): string
    {
        do {
            $code = 'CK-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        } while (StockTransfer::where('code', $code)->exists());

        return $code;
    }
}
