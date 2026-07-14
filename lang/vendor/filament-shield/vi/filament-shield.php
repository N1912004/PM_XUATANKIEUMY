<?php

/**
 * Ghi đè điều hướng của Filament Shield: gom menu "Vai trò" vào nhóm NHÂN SỰ
 * (thay nhóm mặc định "Filament Shield" bằng tiếng Anh), đặt icon theo hệ màu sidebar.
 * Laravel merge file này lên trên file gốc của package nên chỉ cần khai các khóa muốn đổi.
 */
return [
    'nav' => [
        'group' => 'NHÂN SỰ',
        'role' => [
            'label' => 'Vai trò & Phân quyền',
            'icon' => 'heroicon-o-shield-check',
        ],
    ],
];
