<?php

return [
    'navigation' => ['label' => 'Kiểm thực 3 bước', 'model' => 'Nhật ký kiểm thực', 'plural' => 'Kiểm thực 3 bước', 'group' => 'VẬN HÀNH BẾP'],
    'page' => ['title' => 'Kiểm thực 3 bước', 'subtitle' => 'Hiển thị 5 biểu mẫu B1-B5 theo file Excel chuẩn QĐ 1246/2017-BYT'],
    'fields' => ['audit_date' => 'Ngày kiểm tra', 'shift' => 'Ca phục vụ', 'stage' => 'Bước kiểm thực', 'dish' => 'Món ăn', 'conclusion' => 'Kết luận', 'inspector' => 'Người thực hiện', 'cook_start' => 'Giờ bắt đầu chế biến', 'cook_end' => 'Giờ hoàn thành chế biến', 'temperature' => 'Nhiệt độ', 'sample_keeper' => 'Người lưu mẫu', 'sample_kept_at' => 'Thời điểm lưu mẫu', 'sample_code' => 'Mã số mẫu lưu', 'utensils' => 'Dụng cụ chứa đựng / ăn uống', 'notes' => 'Ghi nhận chi tiết/chỉ tiêu'],
    'stages' => ['step_1' => 'Bước 1', 'step_2' => 'Bước 2', 'step_3' => 'Bước 3', 'sample_storage' => 'Lưu mẫu', 'sample_disposal' => 'Hủy mẫu', 'step_1_long' => 'Bước 1 – Kiểm tra trước chế biến', 'step_2_long' => 'Bước 2 – Kiểm tra khi chế biến', 'step_3_long' => 'Bước 3 – Kiểm tra trước khi ăn', 'sample_storage_long' => 'Theo dõi lưu mẫu', 'sample_disposal_long' => 'Theo dõi hủy mẫu'],
    'status' => ['pending' => 'Chờ đánh giá', 'passed' => 'Đạt', 'failed' => 'Không đạt'],
    'help' => ['dish' => 'Áp dụng cho kiểm thực theo từng món (Bước 2, 3, lưu mẫu).'],
    'placeholders' => ['notes' => 'Ví dụ: cảm quan tốt, nhiệt độ tủ lưu 4°C...', 'select_employee' => 'Chọn nhân viên', 'search_employee' => 'Tìm kiếm nhân viên...'],
    'table' => ['audit_date' => 'Ngày kiểm', 'stage' => 'Bước kiểm thực', 'conclusion' => 'Kết luận', 'inspector' => 'Người thực hiện'],
    'filters' => ['date' => 'Ngày kiểm thực', 'shift' => 'Ca phục vụ', 'all_shifts' => 'Tất cả ca', 'location' => 'Cơ sở / địa điểm', 'inspector' => 'Người kiểm tra'],
    'actions' => ['create_data' => 'Tạo dữ liệu', 'export_excel' => 'Xuất Excel', 'clear_selection' => 'Bỏ chọn', 'print_labels' => 'In tem nhãn'],
    'labels' => ['default_kitchen' => 'Bếp ăn', 'filter_summary' => ':date · :dishes món · :ingredients nguyên liệu'],
    'kpi' => ['ingredients_b1' => 'Nguyên liệu B1', 'from_daily_dishes' => 'Từ món trong ngày', 'dishes' => 'Món ăn', 'by_shift' => 'Phân theo ca', 'total_portions' => 'Tổng suất', 'by_dish' => 'Theo từng món', 'forms' => 'Biểu mẫu', 'b1_to_b5' => 'B1 đến B5'],
    'empty' => ['no_employee_results' => 'Không tìm thấy kết quả', 'no_audit_data' => 'Không tìm thấy dữ liệu kiểm thực phù hợp.'],
    'errors' => ['template_not_found' => 'Không tìm thấy file Excel mẫu kiểm thực'],
    'accessibility' => ['export_excel' => 'Xuất báo cáo kiểm thực ra Excel', 'inspector_picker' => 'Chọn người kiểm tra', 'print_labels' => 'In tem nhãn lưu mẫu'],
];
