<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Kênh chat nhóm nội bộ: mọi người dùng đã đăng nhập đều tham gia được.
Broadcast::channel('chat-nhom', function ($user) {
    return $user !== null;
});
