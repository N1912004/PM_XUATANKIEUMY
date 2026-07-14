<?php

/**
 * Ghi đè điều hướng của Filament Shield: gom menu "Vai trò" vào nhóm NHÂN SỰ
 * (thay nhóm mặc định "Filament Shield" bằng tiếng Anh).
 * LƯU Ý: khóa của package là dạng PHẲNG ('nav.group'), không phải mảng lồng nhau —
 * viết lồng nhau sẽ không ghi đè được. Laravel merge file này lên trên file gốc của package.
 */
return [
    'nav.group' => 'NHÂN SỰ',
    'nav.role.label' => 'Vai trò & Phân quyền',
    'nav.role.icon' => 'heroicon-o-shield-check',
];
