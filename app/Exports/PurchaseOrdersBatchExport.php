<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Xuất TẤT CẢ đơn đặt hàng cùng đợt (cùng bếp + cùng ngày giao) ra MỘT file .xlsx:
 * mỗi nhà cung cấp là một sheet, dựng lại đúng "MẪU ĐƠN ĐẶT HÀNG.xlsx" bằng
 * PurchaseOrderTemplateExport — thay bản CSV cũ (một khối văn bản dồn nhiều đơn).
 */
class PurchaseOrdersBatchExport implements WithMultipleSheets
{
    use Exportable;

    /** @param Collection<int, PurchaseOrder> $orders */
    public function __construct(protected Collection $orders) {}

    /**
     * @return array<int, PurchaseOrderTemplateExport>
     */
    public function sheets(): array
    {
        $used = [];

        // Gom nhóm đơn theo Nhà cung cấp trong đợt này
        $groupedBySupplier = $this->orders->groupBy(fn (PurchaseOrder $po) => $po->supplier_id ?: $po->code);

        return $this->orders->map(function (PurchaseOrder $order) use (&$used, $groupedBySupplier): PurchaseOrderTemplateExport {
            $supplierName = $order->supplier?->name ?: $order->code;
            $supplierKey = $order->supplier_id ?: $order->code;
            $sameSupplierOrders = $groupedBySupplier->get($supplierKey, collect())->values();
            $hasMultiple = $sameSupplierOrders->count() > 1;

            // Nếu NCC có nhiều đơn/phiếu tách trong đợt, gắn thứ tự P1, P2, P3...
            $suffix = '';
            if ($hasMultiple) {
                $idx = $sameSupplierOrders->search(fn (PurchaseOrder $po) => $po->id === $order->id);
                $suffix = ' - P'.(($idx !== false ? $idx : 0) + 1);
            }

            $maxSupplierLen = 31 - mb_strlen($suffix);
            $base = mb_substr($supplierName, 0, max(10, $maxSupplierLen)).$suffix;
            $name = $base;
            $i = 2;

            while (in_array($name, $used, true)) {
                $suffixLoop = ' - P'.$i++;
                $maxLen = 31 - mb_strlen($suffixLoop);
                $name = mb_substr($supplierName, 0, max(10, $maxLen)).$suffixLoop;
            }

            $used[] = $name;

            return new PurchaseOrderTemplateExport($order, $name);
        })->all();
    }
}
