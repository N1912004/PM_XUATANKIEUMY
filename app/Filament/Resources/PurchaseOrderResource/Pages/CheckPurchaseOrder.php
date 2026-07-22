<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class CheckPurchaseOrder extends Page
{
    protected static string $resource = PurchaseOrderResource::class;

    protected static string $view = 'filament.resources.purchase-orders.pages.check-purchase-order';

    public PurchaseOrder $record;

    public int $activePoId = 0;

    public array $receivedQuantities = [];

    public array $itemNotes = [];

    public function mount(PurchaseOrder $record): void
    {
        $this->record = $record->load(['supplier', 'kitchen', 'items.ingredient']);
        abort_unless(PurchaseOrderResource::canView($this->record), 403);

        $this->activePoId = $this->record->id;

        foreach ($this->record->items as $item) {
            $this->receivedQuantities[$item->id] = $item->quantity_received > 0 ? (float) $item->quantity_received : (float) $item->quantity_ordered;
            $this->itemNotes[$item->id] = $item->note ?? '';
        }
    }

    public function getRelatedPOsProperty()
    {
        return PurchaseOrder::with(['supplier', 'items.ingredient'])
            ->withCount('items')
            ->where('kitchen_id', $this->record->kitchen_id)
            ->where('estimated_delivery_date', $this->record->estimated_delivery_date)
            ->get();
    }

    public function getActiveOrderProperty(): PurchaseOrder
    {
        if ($this->activePoId === $this->record->id) {
            return $this->record;
        }

        return PurchaseOrder::with(['supplier', 'items.ingredient'])->find($this->activePoId) ?? $this->record;
    }

    public function switchPo(int $poId): void
    {
        $po = PurchaseOrder::with(['supplier', 'items.ingredient'])->find($poId);
        if ($po && PurchaseOrderResource::canView($po)) {
            $this->activePoId = $po->id;
            foreach ($po->items as $item) {
                if (! isset($this->receivedQuantities[$item->id])) {
                    $this->receivedQuantities[$item->id] = $item->quantity_received > 0 ? (float) $item->quantity_received : (float) $item->quantity_ordered;
                    $this->itemNotes[$item->id] = $item->note ?? '';
                }
            }
        }
    }

    public function completeCheck()
    {
        abort_unless(PurchaseOrderResource::canEdit($this->record), 403);

        $relatedPOs = $this->getRelatedPOsProperty();

        DB::transaction(function () use ($relatedPOs) {
            foreach ($relatedPOs as $po) {
                foreach ($po->items as $item) {
                    if (isset($this->receivedQuantities[$item->id])) {
                        $item->update([
                            'quantity_received' => (float) $this->receivedQuantities[$item->id],
                            'note' => $this->itemNotes[$item->id] ?? null,
                        ]);
                    }
                }

                $po->update([
                    'status' => 'done',
                    'stocked_at' => $po->stocked_at ?? now(),
                ]);
            }
        });

        Notification::make()
            ->title('Hoàn thành kiểm hàng!')
            ->body('Đã cập nhật số lượng thực nhận và ghi nhận hoàn thành đơn đặt hàng.')
            ->success()
            ->send();

        return redirect()->to(PurchaseOrderResource::getUrl('index'));
    }
}
