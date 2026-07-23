<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class CheckPurchaseOrder extends Page
{
    protected static string $resource = PurchaseOrderResource::class;

    protected static string $view = 'filament.resources.purchase-orders.pages.check-purchase-order';

    public function getHeading(): string
    {
        return '';
    }

    public function getHeader(): ?View
    {
        return null;
    }

    public PurchaseOrder $record;

    public int $activePoId = 0;

    public array $receivedQuantities = [];

    public array $itemNotes = [];

    public function mount(PurchaseOrder $record): void
    {
        $this->record = $record->load(['supplier', 'kitchen', 'items.ingredient']);
        abort_unless(PurchaseOrderResource::canView($this->record), 403);
        $this->assertKitchenAccess((int) $this->record->kitchen_id);

        $this->activePoId = $this->record->id;

        foreach ($this->getRelatedPOsProperty() as $po) {
            foreach ($po->items as $item) {
                if (! isset($this->receivedQuantities[$item->id])) {
                    $this->receivedQuantities[$item->id] = $item->quantity_received > 0 ? (float) $item->quantity_received : (float) $item->quantity_ordered;
                    $this->itemNotes[$item->id] = $item->receive_note ?? '';
                }
            }
        }
    }

    /**
     * PO id/kitchen đến từ client — user thường chỉ được thao tác trên bếp của mình.
     */
    protected function assertKitchenAccess(int $kitchenId): void
    {
        $user = auth()->user();
        if (! $user || $user->hasRole([User::superAdminRole(), 'Quản trị viên'])) {
            return;
        }

        $ownKitchenId = $user->currentKitchenId();
        abort_unless($ownKitchenId && $kitchenId === (int) $ownKitchenId, 403);
    }

    public function getRelatedPOsProperty()
    {
        // Chỉ các PO đã gửi NCC / đang kiểm / đã xong — không kéo PO nháp hoặc đã hủy
        // vào phiên kiểm hàng (completeCheck sẽ ép chúng sang 'done' sai nghiệp vụ).
        return PurchaseOrder::with(['supplier', 'items.ingredient'])
            ->withCount('items')
            ->where('kitchen_id', $this->record->kitchen_id)
            ->where('estimated_delivery_date', $this->record->estimated_delivery_date)
            ->whereIn('status', ['sent', 'checking', 'done'])
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
        // Chỉ nhận PO thuộc đúng nhóm liên quan (cùng bếp + ngày giao) — poId là dữ liệu client.
        $po = $this->getRelatedPOsProperty()->firstWhere('id', $poId);
        if ($po && PurchaseOrderResource::canView($po)) {
            $this->activePoId = $po->id;
            foreach ($po->items as $item) {
                if (! isset($this->receivedQuantities[$item->id])) {
                    $this->receivedQuantities[$item->id] = $item->quantity_received > 0 ? (float) $item->quantity_received : (float) $item->quantity_ordered;
                    $this->itemNotes[$item->id] = $item->receive_note ?? '';
                }
            }
        }
    }

    public function completeCheck()
    {
        abort_unless(PurchaseOrderResource::canEdit($this->record), 403);
        $this->assertKitchenAccess((int) $this->record->kitchen_id);

        $relatedPOs = $this->getRelatedPOsProperty();

        DB::transaction(function () use ($relatedPOs) {
            foreach ($relatedPOs as $po) {
                // PO đã nhập kho rồi thì bỏ qua: số thực nhận đã chốt vào sổ kho,
                // sửa lại ở đây sẽ làm chứng từ lệch tồn kho thực tế.
                if ($po->stocked_at !== null) {
                    continue;
                }

                foreach ($po->items as $item) {
                    if (isset($this->receivedQuantities[$item->id])) {
                        $item->update([
                            'quantity_received' => (float) $this->receivedQuantities[$item->id],
                            'receive_note' => trim((string) ($this->itemNotes[$item->id] ?? '')) ?: null,
                        ]);
                    }
                }

                // KHÔNG set stocked_at ở đây — hook PurchaseOrder::booted() cần
                // stocked_at IS NULL để tự nhập kho đúng 1 lần khi status chuyển 'done'
                // (hook tự claim cờ stocked_at atomic bên trong).
                $po->update(['status' => 'done']);
            }
        });

        Notification::make()
            ->title(__('purchase_order.notifications.check_completed_title'))
            ->body(__('purchase_order.notifications.check_completed_body'))
            ->success()
            ->send();

        return redirect()->to(PurchaseOrderResource::getUrl('index'));
    }
}
