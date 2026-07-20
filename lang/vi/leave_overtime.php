<?php

$translations = [
    'navigation' => 'Nghỉ phép & Tăng ca', 'type_navigation' => 'Loại nghỉ phép / Tăng ca', 'group' => 'NHÂN SỰ',
    'sections' => ['request' => 'Thông tin yêu cầu', 'approval' => 'Chi tiết & Duyệt'],
    'fields' => ['employee' => 'Nhân viên yêu cầu', 'type' => 'Loại yêu cầu', 'duration' => 'Số ngày / Số giờ', 'start_date' => 'Từ ngày / Ngày áp dụng', 'end_date' => 'Đến ngày', 'reason' => 'Lý do chi tiết', 'approver' => 'Người duyệt', 'status' => 'Trạng thái phê duyệt'],
    'status' => ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối', 'cancelled' => 'Đã hủy'],
    'actions' => ['export' => 'Xuất dữ liệu', 'create' => 'Tạo yêu cầu', 'cancel' => 'Hủy bỏ', 'view' => 'Xem chi tiết', 'edit' => 'Chỉnh sửa'],
    'tabs' => ['all' => 'Tất cả yêu cầu', 'history' => 'Lịch sử phê duyệt'],
    'messages' => ['created' => 'Tạo yêu cầu mới thành công!', 'updated' => 'Cập nhật yêu cầu thành công!', 'deleted' => 'Xóa yêu cầu thành công.'],
    'validation' => ['employee_required' => 'Nhân viên là bắt buộc.', 'start_required' => 'Ngày bắt đầu là bắt buộc.', 'type_required' => 'Loại yêu cầu là bắt buộc.', 'reason_required' => 'Lý do là bắt buộc.', 'reason_min' => 'Lý do phải có ít nhất 5 ký tự.'],
    'type' => ['name' => 'Tên loại yêu cầu', 'name_placeholder' => 'Nhập tên loại yêu cầu (VD: Nghỉ phép năm, Tăng ca ngày thường...)', 'is_overtime' => 'Là tăng ca (OT)', 'is_overtime_help' => 'Bật nếu đây là loại yêu cầu làm thêm giờ / tăng ca, tắt nếu là nghỉ phép.', 'classification' => 'Phân loại tăng ca / nghỉ phép', 'sort' => 'Thứ tự hiển thị', 'active' => 'Đang sử dụng', 'active_help' => 'Tắt thì không còn xuất hiện ở các form đơn từ, dữ liệu cũ giữ nguyên.', 'request_count' => 'SỐ YÊU CẦU', 'created_at' => 'NGÀY TẠO', 'cannot_delete' => 'Không thể xóa loại này vì đang có :count đơn từ sử dụng.', 'bulk_cannot_delete' => 'Không thể xóa hàng loạt. Các loại yêu cầu sau đang được sử dụng: :names'],
    'ui' => ['edit_title' => 'Chỉnh sửa yêu cầu', 'create_title' => 'Tạo yêu cầu mới', 'edit_description' => 'Cập nhật chi tiết yêu cầu nghỉ phép hoặc tăng ca của nhân viên', 'create_description' => 'Vui lòng nhập thông tin để gửi yêu cầu nghỉ phép hoặc tăng ca', 'back' => 'Quay lại', 'leave' => 'Nghỉ phép', 'overtime' => 'Tăng ca', 'form_error' => 'Có lỗi xảy ra, vui lòng kiểm tra lại:', 'leave_info' => '1. Thông tin nghỉ phép', 'overtime_info' => '1. Thông tin tăng ca', 'select_employee' => 'Chọn nhân viên', 'leave_type' => 'Loại nghỉ phép', 'overtime_type' => 'Loại tăng ca', 'leave_time' => 'Thời gian nghỉ', 'to' => 'đến', 'leave_days' => 'Số ngày nghỉ', 'overtime_hours' => 'Số giờ tăng ca', 'unpaid' => 'Nghỉ không tính phép', 'handover_time' => 'Thời gian bàn giao công việc', 'leave_reason' => 'Lý do nghỉ', 'contact_info' => '2. Thông tin liên hệ khi cần', 'contact' => 'Người liên hệ', 'phone' => 'Số điện thoại', 'notes' => 'Ghi chú thêm', 'overtime_date' => 'Ngày tăng ca', 'overtime_time' => 'Thời gian tăng ca', 'location' => 'Địa điểm / khu vực làm việc', 'approver' => 'Người quản lý duyệt', 'select_approver' => 'Chọn người duyệt', 'overtime_reason' => 'Lý do tăng ca', 'work' => 'Công việc thực hiện', 'attachment' => 'Đính kèm file', 'file_hint' => 'Kéo thả file vào đây hoặc nhấn để chọn file', 'file_types' => 'PDF, XLSX, JPG, PNG tối đa 5MB', 'confirmation' => '2. Xác nhận & bàn giao', 'coworker' => 'Người phối hợp', 'approval' => 'Phê duyệt & Trạng thái yêu cầu', 'summary' => 'Tóm tắt yêu cầu', 'applicable_time' => 'Thời gian áp dụng', 'overtime_period' => 'Giờ tăng ca', 'location_short' => 'Địa điểm', 'annual_balance' => 'Số dư phép năm', 'annual_total' => 'Tổng số ngày phép', 'used' => 'Đã sử dụng', 'remaining' => 'Số dư còn lại', 'notice' => 'Lưu ý', 'notice_approval' => 'Yêu cầu sẽ được gửi trực tiếp đến người quản lý được chỉ định để phê duyệt.', 'notice_handover' => 'Vui lòng hoàn thành bàn giao công việc cần thiết trước thời gian áp dụng yêu cầu.', 'submit' => 'Gửi yêu cầu', 'subtitle' => 'Quản lý yêu cầu nghỉ phép, làm thêm giờ và trạng thái phê duyệt của nhân viên', 'filters' => 'Bộ lọc', 'reset_filters' => 'Cài lại bộ lọc', 'actions' => 'Hành động', 'none' => 'Chưa có', 'confirm_delete' => 'Bạn có chắc chắn muốn xóa yêu cầu này?', 'delete' => 'Xóa yêu cầu', 'empty' => 'Không tìm thấy yêu cầu nghỉ phép hay tăng ca nào', 'empty_hint' => 'Hãy thử điều chỉnh bộ lọc hoặc từ khóa tìm kiếm khác.', 'pagination' => 'Hiển thị :from đến :to trong tổng số :total yêu cầu', 'rows_per_page' => ':count dòng/trang'],
];

$translations['ui'] += [
    'employee' => 'Nhân viên',
    'duration_placeholder' => 'VD: 1 ngày hoặc 4 giờ',
    'leave_duration_placeholder' => 'VD: 3 ngày',
    'overtime_duration_placeholder' => 'VD: 3 giờ',
    'leave_reason_placeholder' => 'Nhập lý do nghỉ phép',
    'contact_placeholder' => 'Nhập tên người liên hệ',
    'phone_placeholder' => 'Nhập số điện thoại',
    'optional_notes' => 'Ghi chú thêm (nếu có)',
    'notes_placeholder' => 'Nhập ghi chú thêm',
    'overtime_reason_placeholder' => 'Lý do tăng ca...',
    'work_placeholder' => 'Mô tả công việc sẽ thực hiện...',
    'file_drag' => 'Kéo thả file vào đây hoặc',
    'file_choose' => 'nhấn để chọn file',
    'coworker_placeholder' => 'Nhập tên người phối hợp',
    'handover_time_short' => 'Thời gian bàn giao',
    'reason' => 'Lý do',
    'days_count' => ':count ngày',
    'search_placeholder' => 'Tìm kiếm nhân viên...',
    'department' => 'Phòng ban',
    'status' => 'Trạng thái',
    'employee_code' => 'Mã NV',
    'full_name' => 'Họ và tên',
    'applied_date' => 'Thời gian / Ngày áp dụng',
    'weekday_prefix' => 'Thứ',
    'pagination_navigation' => 'Điều hướng phân trang',
    'select_all' => 'Chọn tất cả yêu cầu',
    'select_request' => 'Chọn yêu cầu của nhân viên :code',
];
$translations['weekdays'] = ['Chủ Nhật', 'Hai', 'Ba', 'Tư', 'Năm', 'Sáu', 'Bảy'];
$translations['locations'] = ['central_kitchen_a' => 'Bếp trung tâm - Khu A', 'ingredient_warehouse' => 'Kho nguyên liệu', 'head_office' => 'Văn phòng HQ'];

return $translations;
