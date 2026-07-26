<?php

namespace App\Filament\Pages;

use App\Filament\Resources\PurchaseOrderResource;

/**
 * Bước "Tạo đơn đặt hàng" của List hàng, tách thành URL riêng /admin/list-hang/create.
 *
 * Trước đây bước này chỉ là `$mode = 'create_po'` bên trong ListHang nên URL không đổi:
 * F5 hay chia sẻ link đều rơi về danh sách. Page này dùng lại toàn bộ logic của ListHang
 * (kế thừa) và chỉ khác ở slug + mode khởi tạo.
 */
class ListHangCreate extends ListHang
{
    protected static ?string $slug = 'list-hang/create';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return __('list_hang.po.title');
    }

    public static function canAccess(): bool
    {
        return PurchaseOrderResource::canCreate();
    }

    public function mount(): void
    {
        parent::mount();

        // Vào thẳng bước phân NCC; ngày/ca lấy từ query nếu người dùng đi từ trang danh sách.
        $this->mode = 'create_po';

        $date = request()->query('date');
        if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1) {
            $this->date = $date;
            $this->poDate = $date;
            $this->poSourceFrom = $date;
            $this->poSourceTo = $date;
        }

        $shifts = request()->query('shifts');
        if (is_string($shifts) && $shifts !== '') {
            $ids = array_values(array_filter(array_map('intval', explode(',', $shifts))));
            if ($ids !== []) {
                $this->selectedShifts = $ids;
                $this->poSelectedShifts = $ids;
            }
        }

        $this->loadPOIngredients();
    }
}
