<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Xuất TẤT CẢ đơn đặt hàng cùng đợt (cùng bếp + cùng ngày giao) ra MỘT file .xlsx:
 * MỖI NHÀ CUNG CẤP LÀ MỘT SHEET — các phiếu tách (P1/P2/P3) của cùng một NCC được gộp
 * chung vào một sheet, dựng lại đúng "MẪU ĐƠN ĐẶT HÀNG.xlsx" bằng PurchaseOrderTemplateExport.
 */
class PurchaseOrdersBatchExport implements WithMultipleSheets
{
    use Exportable;

    /** Giới hạn độ dài tên sheet của Excel. */
    protected const SHEET_TITLE_MAX = 31;

    /** @param Collection<int, PurchaseOrder> $orders */
    public function __construct(protected Collection $orders) {}

    /**
     * @return array<int, PurchaseOrderTemplateExport>
     */
    public function sheets(): array
    {
        $used = [];

        // Gom theo NHÀ CUNG CẤP: mỗi NCC đúng một sheet.
        // Đơn chưa gán NCC không gộp lẫn nhau — mỗi đơn đứng riêng theo mã đơn.
        return $this->orders
            ->groupBy(fn (PurchaseOrder $po) => $po->supplier_id ? 'S'.$po->supplier_id : 'O'.$po->id)
            ->map(function (Collection $orders) use (&$used): PurchaseOrderTemplateExport {
                $first = $orders->first();
                $supplierName = $first->supplier?->name ?: $first->code;

                return new PurchaseOrderTemplateExport(
                    $orders->values(),
                    $this->uniqueSheetTitle($supplierName, $used),
                );
            })
            ->values()
            ->all();
    }

    /**
     * Tên sheet hợp lệ với Excel: thay ký tự cấm, cắt còn 31 ký tự, thêm hậu tố khi trùng.
     *
     * @param  array<int, string>  $used
     */
    protected function uniqueSheetTitle(string $name, array &$used): string
    {
        $clean = trim(preg_replace('#[\\\\/*?:\[\]]#u', '-', $name) ?? '');
        $clean = $clean !== '' ? $clean : 'NCC';

        $title = mb_substr($clean, 0, self::SHEET_TITLE_MAX);
        $i = 2;

        while (in_array($title, $used, true)) {
            $suffix = ' ('.$i++.')';
            $title = mb_substr($clean, 0, self::SHEET_TITLE_MAX - mb_strlen($suffix)).$suffix;
        }

        $used[] = $title;

        return $title;
    }
}
