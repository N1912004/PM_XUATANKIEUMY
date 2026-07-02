<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ChatNhom extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Chat nhóm';

    protected static ?string $title = 'Chat nhóm';

    protected static ?string $navigationGroup = 'CHAT NHÓM';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.chat-nhom';
}
