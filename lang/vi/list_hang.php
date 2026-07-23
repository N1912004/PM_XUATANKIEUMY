<?php

return [
    'currency' => 'đ',
    'title' => 'Danh sách hàng', 'heading' => 'List hàng — :date', 'subtitle' => 'Danh sách nguyên liệu cần chuẩn bị theo ngày và ca', 'navigation' => ['group' => 'CUNG ỨNG & KHO'],
    'actions' => ['export' => 'Xuất Excel', 'print' => 'In danh sách', 'create_po' => 'Tạo đơn đặt hàng', 'today' => 'Hôm nay', 'back' => 'Quay lại', 'create_send' => 'Tạo & gửi đơn'],
    'filters' => ['week' => 'Tuần', 'in_period' => 'Trong kỳ'], 'stats' => ['shifts' => 'Ca phục vụ', 'portions' => 'Tổng suất ăn', 'dishes' => 'Món cần nấu', 'ingredients' => 'Loại nguyên liệu'],
    'table' => ['ingredient' => 'Nguyên liệu', 'ingredient_name' => 'Tên nguyên liệu', 'portions' => 'Số suất', 'quantity_g' => 'ĐL (g)', 'quantity_kg' => 'Số KG', 'order' => 'Đặt', 'dishes' => 'Thuộc món', 'demand' => 'Nhu cầu', 'stock' => 'Tồn kho', 'manual_quantity' => 'SL đặt tay', 'unit_price' => 'Đơn giá', 'total' => 'Thành tiền', 'supplier' => 'Nhà cung cấp', 'dish_total' => 'Tổng :dish'],
    'counts' => ['ingredients' => ':count nguyên liệu', 'portions' => ':count suất', 'dishes' => ':count món', 'items' => ':count mặt hàng'],
    'categories' => ['meat' => 'Thịt & Thủy hải sản', 'produce' => 'Rau củ quả & Nông sản', 'dry' => 'Hàng khô & Gia vị'],
    'po' => ['title' => 'Tạo đơn đặt hàng', 'subtitle' => 'Tổng hợp nguyên liệu từ list hàng → phân NCC → tạo đơn', 'steps' => ['select_list' => 'Chọn List hàng', 'assign_supplier' => 'Phân NCC & xác nhận', 'assign_supplier_description' => 'Gán nhà cung cấp', 'create' => 'Tạo đơn', 'create_description' => 'Xuất & gửi NCC'], 'fields' => ['order_date' => 'Ngày đặt hàng', 'source_from' => 'Nguồn từ ngày', 'source_to' => 'Nguồn đến ngày', 'shift' => 'Ca lấy nguyên liệu'], 'quick_supplier' => 'Gán nhanh NCC', 'select_supplier' => '-- Chọn NCC --', 'ordered_code' => 'Đã đặt · :code', 'group_total' => 'Tổng nhóm :group', 'grand_total' => 'TỔNG ĐƠN ĐẶT HÀNG – ĐẶT HÀNG :date', 'ordered_title' => 'Tổng :count đơn đã đặt cho nguyên liệu này trong ngày', 'other_orders' => '+:count đơn khác', 'selection_summary' => ':suppliers NCC · :selected/:total nguyên liệu'],
    'summary' => ['title' => 'Tóm tắt đơn hàng', 'scope' => 'Phạm vi', 'day' => 'Ngày', 'order_date' => 'Ngày đặt', 'source' => 'Nguồn list', 'suppliers' => 'Số NCC', 'value' => 'Tổng giá trị', 'by_supplier' => 'Phân bổ theo NCC', 'ingredients' => 'Tổng NL', 'ingredient_types' => ':selected/:total loại'],
    'empty' => ['no_menu' => 'Chưa lập thực đơn', 'no_menu_description' => 'Không tìm thấy thực đơn nào được lập cho ngày và ca đã chọn.', 'no_order_items' => 'Chưa có nguyên liệu để đặt', 'no_order_items_description' => 'Hãy lập & chốt thực đơn tuần trước, sau đó tạo đơn đặt hàng.', 'no_allocation' => 'Chưa có phân bổ.'],
    'notes' => ['title' => 'Lưu ý', 'separate_orders' => 'Mỗi NCC sẽ nhận đơn riêng.', 'optional_items' => 'Có thể bỏ trống nguyên liệu không đặt.', 'manual_priority' => 'SL đặt tay được ưu tiên khi tạo phiếu.'],
    'notifications' => ['invalid_order_date' => 'Ngày đặt hàng không hợp lệ', 'no_selected_items' => 'Không có nguyên liệu nào được chọn để tạo PO!', 'po_created' => 'Tạo PO thành công!'],
    'export' => [
        'sheet_title' => 'Danh sách hàng',
        'heading' => 'DANH SÁCH HÀNG NGÀY :date',
        'cols' => [
            'shift' => 'Ca',
            'dish' => 'Món ăn',
            'portions' => 'Số suất',
            'ing_code' => 'Mã NL',
            'ing_name' => 'Nguyên liệu',
            'dl' => 'ĐL (kg/suất)',
            'total_kg' => 'Tổng cần (kg)',
            'unit' => 'ĐVT',
        ],
        'empty' => 'Không có dữ liệu',
    ],
];
