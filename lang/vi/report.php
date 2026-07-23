<?php

return [
    'currency' => 'đ',
    'title' => 'Báo cáo', 'heading' => 'Báo cáo – Xuất ăn', 'subtitle' => 'Tổng hợp món ăn & nguyên liệu theo khoảng ngày và ca phục vụ',
    'navigation' => ['group' => 'VẬN HÀNH BẾP'], 'actions' => ['export' => 'Xuất Excel', 'this_week' => 'Theo tuần thực đơn'],
    'filters' => ['from_date' => 'Từ ngày', 'to_date' => 'Đến ngày', 'kitchen' => 'Bếp', 'all_kitchens' => 'Tất cả bếp', 'search' => 'Tìm món / nguyên liệu...'],
    'stats' => ['menu_days' => 'Ngày có thực đơn', 'dishes' => 'Lượt món', 'ingredient_rows' => 'Dòng nguyên liệu', 'portions' => 'Tổng suất', 'cost' => 'Tổng chi phí giá vốn'],
    'ingredients' => ['title' => 'TỔNG NGUYÊN LIỆU TIÊU THỤ CẢ KỲ'],
    'counts' => ['ingredients' => ':count nguyên liệu', 'dishes' => ':count món', 'portions' => ':count suất', 'servings' => ':count phần', 'shifts' => ':count ca'],
    'table' => ['code' => 'MÃ', 'ingredient' => 'TÊN NGUYÊN LIỆU', 'total_consumption' => 'TỔNG TIÊU THỤ', 'value' => 'GIÁ TRỊ', 'ingredient_code' => 'Mã NL', 'ingredient_name' => 'Tên nguyên liệu', 'quantity_per_portion' => 'ĐL (g/suất)', 'portions' => 'Số suất', 'servings' => 'Số phần', 'total_kg' => 'Tổng KG'],
    'empty' => ['title' => 'Không có dữ liệu trong khoảng đã chọn', 'description' => 'Hãy chọn lại khoảng ngày, ca, hoặc thử tìm kiếm cụm từ khác.', 'manual_dish_ingredients' => 'Món tự nhập – chưa khai báo nguyên liệu trong ngân hàng'],
];
