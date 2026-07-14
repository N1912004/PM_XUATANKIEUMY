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

        return $this->orders->map(function (PurchaseOrder $order) use (&$used): PurchaseOrderTemplateExport {
            // Tên sheet Excel: tối đa 31 ký tự, không được trùng nhau trong cùng file
            $base = mb_substr($order->supplier?->name ?: $order->code, 0, 25);
            $name = $base;
            $i = 2;

            while (in_array($name, $used, true)) {
                $name = mb_substr($base, 0, 22).' ('.$i++.')';
            }

            $used[] = $name;

            return new PurchaseOrderTemplateExport($order, $name);
        })->all();
    }
}
