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

        // Đếm số đơn của từng Nhà cung cấp trong đợt này
        $supplierCounts = $this->orders->groupBy(fn (PurchaseOrder $po) => $po->supplier_id ?: $po->code)->map->count();

        return $this->orders->map(function (PurchaseOrder $order) use (&$used, $supplierCounts): PurchaseOrderTemplateExport {
            $supplierName = $order->supplier?->name ?: $order->code;
            $supplierId = $order->supplier_id ?: $order->code;
            $hasMultiple = ($supplierCounts[$supplierId] ?? 1) > 1;

            // Nếu NCC có nhiều phiếu tách (P1/P2/P3), trích xuất mã phiếu từ mã đơn PO để nối vào tên sheet
            $suffix = '';
            if ($hasMultiple) {
                if (preg_match('/-(P[1-9]\d*)/i', $order->code, $matches)) {
                    $suffix = ' - '.strtoupper($matches[1]);
                } else {
                    $parts = explode('-', $order->code);
                    $suffix = ' - '.strtoupper(end($parts));
                }
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
