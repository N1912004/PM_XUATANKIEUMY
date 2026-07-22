<?php

return [
    'navigation' => 'Quản lý tài khoản',
    'model' => 'tài khoản',
    'group' => 'NHÂN SỰ',
    'profile' => [
        'heading' => 'Chỉnh sửa hồ sơ cá nhân',
        'avatar' => 'Ảnh đại diện',
        'avatar_help' => 'Tải lên ảnh PNG, JPG hoặc WEBP (Tối đa 2MB)',
        'name' => 'Họ và tên',
        'email' => 'Email đăng nhập',
        'new_password' => 'Mật khẩu mới',
        'password_confirmation' => 'Xác nhận mật khẩu mới',
        'save' => 'Lưu thay đổi',
    ],
    'sections' => [
        'login' => 'Thông tin đăng nhập',
        'employee' => 'Liên kết hồ sơ nhân sự',
        'roles' => 'Vai trò phân quyền',
        'avatar' => 'Ảnh đại diện',
        'account' => 'Thông tin tài khoản',
    ],
    'fields' => [
        'name' => 'Họ tên tài khoản',
        'email' => 'Email đăng nhập',
        'password' => 'Mật khẩu',
        'avatar_url' => 'Ảnh đại diện (URL, tùy chọn)',
        'employee' => 'Nhân viên',
        'kitchen' => 'Bếp trực thuộc',
        'roles' => 'Vai trò',
    ],
    'table' => [
        'name' => 'HỌ VÀ TÊN',
        'email' => 'EMAIL ĐĂNG NHẬP',
        'employee' => 'NHÂN VIÊN LIÊN KẾT',
        'kitchen' => 'BẾP TRỰC THUỘC',
        'roles' => 'VAI TRÒ',
        'created_at' => 'NGÀY TẠO',
    ],
    'filters' => [
        'roles' => 'Vai trò',
        'employee_linked' => 'Đã liên kết nhân viên',
        'linked' => 'Đã liên kết',
        'not_linked' => 'Chưa liên kết',
    ],
    'placeholders' => [
        'no_access' => '— Không vào được hệ thống —',
    ],
    'actions' => [
        'create' => 'Thêm tài khoản',
    ],
    'messages' => [
        'profile_updated' => 'Đã cập nhật hồ sơ thành công!',
    ],
    'validation' => [
        'last_super_admin' => 'Đây là tài khoản toàn quyền cuối cùng — không thể gỡ vai trò :role.',
        'cannot_grant_super_admin' => 'Bạn không có quyền gán vai trò :role.',
    ],
    'help' => [
        'password' => 'Để trống khi sửa nếu không muốn đổi mật khẩu.',
        'employee' => 'Chỉ hiện nhân viên chưa có tài khoản (mỗi nhân viên tối đa 1 tài khoản).',
        'employee_section' => 'Bếp trực thuộc của tài khoản được xác định qua nhân viên. Tài khoản vận hành chưa gán nhân viên sẽ không thấy dữ liệu nghiệp vụ.',
    ],
];
