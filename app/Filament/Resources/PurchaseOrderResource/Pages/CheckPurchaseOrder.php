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
                    // Ô số thực nhận để TRỐNG khi chưa kiểm — người kiểm nhập tay số hàng
                    // thực giao. KHÔNG prefill = số đặt (che mất mặt hàng chưa kiểm và làm
                    // nhập kho theo số đặt thay vì số thực nhận). Chỉ giữ giá trị nếu PO
                    // đã từng kiểm/nhập kho trước đó (quantity_received > 0).
                    $this->receivedQuantities[$item->id] = $item->quantity_received > 0 ? (float) $item->quantity_received : '';
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
                    // Ô trống khi chưa kiểm (xem chú thích ở mount()) — không prefill số đặt.
                    $this->receivedQuantities[$item->id] = $item->quantity_received > 0 ? (float) $item->quantity_received : '';
                    $this->itemNotes[$item->id] = $item->receive_note ?? '';
                }
            }
        }
    }

    /**
     * Một item được coi là "đã nhập" khi ô số thực nhận có giá trị (khác rỗng/null).
     * Số 0 hợp lệ (hàng không giao = nhận 0kg), chỉ ô để trống mới là "chưa kiểm".
     */
    protected function isItemChecked(int $itemId): bool
    {
        $val = $this->receivedQuantities[$itemId] ?? null;

        return $val !== null && $val !== '';
    }

    public function completeCheck()
    {
        abort_unless(PurchaseOrderResource::canEdit($this->record), 403);
        $this->assertKitchenAccess((int) $this->record->kitchen_id);

        $relatedPOs = $this->getRelatedPOsProperty();

        // Phân loại PO trước khi mutate: chỉ chốt PO đã nhập ĐỦ số thực nhận cho mọi mặt hàng.
        // PO chưa nhập gì → bỏ qua (chưa kiểm). PO nhập dở dang → chặn cả phiên, báo lỗi.
        $poToFinalize = [];
        $pendingCount = 0;   // số PO còn có thể kiểm (chưa nhập kho) trong đợt

        foreach ($relatedPOs as $po) {
            // PO đã nhập kho rồi thì bỏ qua: số thực nhận đã chốt vào sổ kho,
            // sửa lại ở đây sẽ làm chứng từ lệch tồn kho thực tế.
            if ($po->stocked_at !== null) {
                continue;
            }

            $total = $po->items->count();
            if ($total === 0) {
                continue;
            }

            $pendingCount++;

            $checked = $po->items->filter(fn ($item) => $this->isItemChecked((int) $item->id))->count();

            if ($checked === 0) {
                // Chưa nhập mặt hàng nào của NCC này — coi như chưa kiểm, không chốt.
                continue;
            }

            if ($checked < $total) {
                // Nhập dở dang: chặn toàn bộ phiên, không chốt PO nào để tránh nhập kho thiếu.
                Notification::make()
                    ->title(__('purchase_order.validation.check_incomplete_title'))
                    ->body(__('purchase_order.validation.check_incomplete_body', [
                        'supplier' => $po->supplier?->name ?? '—',
                        'count' => $total - $checked,
                    ]))
                    ->danger()
                    ->send();

                return;
            }

            $poToFinalize[] = $po;
        }

        if (empty($poToFinalize)) {
            // Không có PO nào chưa nhập kho → cả đợt đã hoàn thành, không kiểm lại được.
            if ($pendingCount === 0) {
                Notification::make()
                    ->title(__('purchase_order.validation.check_all_done_title'))
                    ->body(__('purchase_order.validation.check_all_done_body'))
                    ->info()
                    ->send();

                return redirect()->to(PurchaseOrderResource::getUrl('index'));
            }

            Notification::make()
                ->title(__('purchase_order.validation.check_nothing_title'))
                ->body(__('purchase_order.validation.check_nothing_body'))
                ->warning()
                ->send();

            return;
        }

        DB::transaction(function () use ($poToFinalize) {
            foreach ($poToFinalize as $po) {
                foreach ($po->items as $item) {
                    $item->update([
                        'quantity_received' => (float) $this->receivedQuantities[$item->id],
                        'receive_note' => trim((string) ($this->itemNotes[$item->id] ?? '')) ?: null,
                    ]);
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
