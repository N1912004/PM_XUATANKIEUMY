<?php

$translations = [
    'area_section' => 'Thông tin khu vực',
    'kitchen_section' => 'Thông tin nhà ăn / bếp',
    'kitchen_status' => ['active' => 'Đang hoạt động', 'paused' => 'Tạm dừng', 'maintenance' => 'Bảo trì'],
    'groups' => ['area_kitchen' => 'KHU VỰC & NHÀ ĂN', 'hr' => 'NHÂN SỰ', 'kitchen_operations' => 'VẬN HÀNH BẾP'],
    'common' => ['index' => 'STT', 'active' => 'Hoạt động', 'status' => 'TRẠNG THÁI', 'active_status' => 'Trạng thái hoạt động', 'notes' => 'Ghi chú', 'sort_order' => 'Thứ tự hiển thị', 'sort_order_upper' => 'THỨ TỰ', 'in_use' => 'Đang sử dụng', 'in_use_upper' => 'ĐANG DÙNG', 'created_at' => 'NGÀY TẠO'],
    'area' => ['label' => 'Khu vực', 'fields' => ['name' => 'Tên khu vực', 'code' => 'Mã khu vực', 'manager' => 'Quản lý phụ trách'], 'placeholders' => ['name' => 'VD: Đông Nai / Hồ Chí Minh', 'code' => 'VD: KV-DN', 'notes' => 'Phạm vi vận hành, ca sản xuất, khách hàng chính...'], 'table' => ['code' => 'MÃ', 'name' => 'KHU VỰC', 'manager' => 'QUẢN LÝ PHỤ TRÁCH', 'kitchens_count' => 'SỐ NHÀ ĂN / BẾP'], 'errors' => ['in_use' => 'Không thể xóa khu vực này vì vẫn còn nhà ăn/bếp trực thuộc.'], 'notifications' => ['deleted' => 'Xóa khu vực thành công.']],
    'kitchen' => ['label' => 'Nhà ăn / bếp', 'fields' => ['name' => 'Tên nhà ăn / bếp', 'area' => 'Thuộc khu vực', 'type' => 'Phân loại', 'capacity' => 'Công suất phục vụ (suất/ngày)', 'manager' => 'Quản lý nhà bếp'], 'placeholders' => ['name' => 'VD: Bếp chính Nhơn Trạch'], 'table' => ['name' => 'TÊN NHÀ BẾP / NHÀ ĂN', 'area' => 'KHU VỰC', 'type' => 'PHÂN LOẠI', 'capacity' => 'CÔNG SUẤT (SUẤT/NGÀY)', 'manager' => 'QUẢN LÝ'], 'notifications' => ['deleted' => 'Xóa nhà ăn/bếp thành công.']],
    'kitchen_type' => ['label' => 'Loại bếp / nhà ăn', 'fields' => ['name' => 'Tên loại'], 'placeholders' => ['name' => 'Nhập tên loại bếp / nhà ăn (VD: Bếp sản xuất, Nhà ăn phục vụ...)'], 'helpers' => ['active' => 'Tắt thì không còn xuất hiện ở các form chọn loại, dữ liệu cũ giữ nguyên.'], 'table' => ['name' => 'TÊN LOẠI', 'count' => 'SỐ NHÀ ĂN / BẾP'], 'errors' => ['in_use' => 'Không thể xóa loại này vì đang có :count nhà ăn/bếp sử dụng.', 'bulk_in_use' => 'Không thể xóa hàng loạt. Các loại sau đang được sử dụng: :names']],
    'department' => ['label' => 'Phòng ban', 'fields' => ['name' => 'Tên phòng ban'], 'placeholders' => ['name' => 'Nhập tên phòng ban (VD: Phòng hành chính, Phòng kỹ thuật...)'], 'helpers' => ['active' => 'Tắt thì không còn xuất hiện ở các form chọn phòng ban, dữ liệu cũ giữ nguyên.'], 'table' => ['name' => 'TÊN PHÒNG BAN', 'count' => 'SỐ NHÂN VIÊN'], 'errors' => ['in_use' => 'Không thể xóa phòng ban này vì đang có :count nhân viên trực thuộc.', 'bulk_in_use' => 'Không thể xóa hàng loạt. Các phòng ban sau đang có nhân viên: :names']],
    'position' => ['label' => 'Chức vụ', 'fields' => ['name' => 'Tên chức vụ'], 'placeholders' => ['name' => 'Nhập tên chức vụ (VD: Tổ trưởng bếp, Chuyên viên...)'], 'helpers' => ['active' => 'Tắt thì không còn xuất hiện ở các form chọn chức vụ, dữ liệu cũ giữ nguyên.'], 'table' => ['name' => 'TÊN CHỨC VỤ', 'count' => 'SỐ NHÂN VIÊN'], 'errors' => ['in_use' => 'Không thể xóa chức vụ này vì đang có :count nhân viên trực thuộc.', 'bulk_in_use' => 'Không thể xóa hàng loạt. Các chức vụ sau đang có nhân viên: :names']],
    'shift' => ['navigation' => 'Cấu hình ca làm việc', 'label' => 'Ca làm việc', 'fields' => ['name' => 'Tên ca', 'time_from' => 'Giờ bắt đầu', 'time_to' => 'Giờ kết thúc'], 'placeholders' => ['name' => 'Ví dụ: Ca 1'], 'helpers' => ['constraints' => 'Tối đa 12 giờ. Giờ kết thúc nhỏ hơn giờ bắt đầu là ca qua ngày.'], 'validation' => ['time_required' => 'Giờ bắt đầu và giờ kết thúc là bắt buộc.', 'different_times' => 'Giờ kết thúc phải khác giờ bắt đầu.', 'max_duration' => 'Thời lượng ca không được vượt quá 12 giờ.'], 'table' => ['time_range' => 'Khung giờ']],
];

$translations['common'] += ['actions' => 'Thao tác', 'edit' => 'Sửa', 'delete' => 'Xóa', 'reset_filters' => 'Xóa lọc'];
$translations['area']['list'] = [
    'title' => 'Khu vực',
    'subtitle' => 'Quản lý các khu vực vận hành và các nhà ăn / bếp sản xuất trực thuộc từng khu vực',
    'create' => 'Thêm khu vực',
    'search_placeholder' => 'Tìm khu vực...',
    'no_notes' => 'Chưa có ghi chú',
    'confirm_delete' => 'Bạn có chắc chắn muốn xóa khu vực này?',
    'empty' => 'Không tìm thấy khu vực nào.',
    'kpi' => [
        'total_label' => 'Khu vực', 'total_note' => 'Đang quản lý',
        'active_label' => 'Đang hoạt động', 'active_note' => 'Khu vực khả dụng',
        'kitchens_label' => 'Nhà ăn / bếp', 'kitchens_note' => 'Tổng cơ sở trực thuộc',
        'managers_label' => 'Quản lý phụ trách', 'managers_note' => 'Theo khu vực',
    ],
    'columns' => ['code' => 'Mã', 'area' => 'Khu vực', 'manager' => 'Quản lý', 'kitchens' => 'Nhà ăn'],
];
$translations['kitchen']['list'] = [
    'title' => 'Nhà ăn / bếp',
    'subtitle' => 'Quản lý các nhà ăn / bếp sản xuất trực thuộc từng khu vực vận hành',
    'create' => 'Thêm nhà ăn / bếp',
    'search_placeholder' => 'Tìm nhà ăn / bếp...',
    'confirm_delete' => 'Bạn có chắc chắn muốn xóa nhà ăn/bếp này?',
    'empty' => 'Không tìm thấy nhà ăn hay bếp sản xuất nào.',
    'capacity_value' => ':count suất/ngày',
    'kpi' => [
        'total_label' => 'Nhà ăn / bếp', 'total_note' => 'Tổng cơ sở sản xuất',
        'active_label' => 'Đang hoạt động', 'active_note' => 'Cơ sở khả dụng',
        'areas_label' => 'Khu vực', 'areas_note' => 'Đang quản lý',
        'managers_label' => 'Quản lý nhà bếp', 'managers_note' => 'Theo cơ sở',
    ],
    'filters' => ['all_areas' => 'Tất cả khu vực', 'all_types' => 'Tất cả loại', 'all_statuses' => 'Tất cả trạng thái'],
    'columns' => ['kitchen' => 'Nhà ăn / bếp', 'area' => 'Khu vực', 'type' => 'Loại', 'capacity' => 'Công suất', 'manager' => 'Phụ trách'],
];

return $translations;
