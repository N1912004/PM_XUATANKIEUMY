<?php

namespace App\Filament\Pages;

use App\Events\MessageSent;
use App\Models\Message;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Livewire\Attributes\On;

class ChatNhom extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?int $navigationSort = 99;

    public static function getNavigationLabel(): string
    {
        return __('Chat nhóm');
    }

    public function getTitle(): string
    {
        return __('Chat nhóm');
    }

    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    protected static string $view = 'filament.pages.chat-nhom';

    /**
     * Danh sách tin nhắn hiển thị (dạng mảng để realtime append dễ dàng).
     *
     * @var array<int, array<string, mixed>>
     */
    public array $messages = [];

    public string $newMessage = '';

    public function mount(): void
    {
        $this->messages = Message::with('user')
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->map(fn (Message $m): array => $this->toArray($m))
            ->values()
            ->all();
    }

    public function sendMessage(): void
    {
        $data = $this->validate([
            'newMessage' => ['required', 'string', 'max:2000'],
        ]);

        $user = Filament::auth()->user();

        $message = Message::create([
            'user_id' => $user->getAuthIdentifier(),
            'kitchen_id' => $user->currentKitchenId(),
            'body' => $data['newMessage'],
        ]);

        $message->setRelation('user', $user);

        // Hiển thị ngay cho người gửi + phát realtime cho những người khác
        $this->messages[] = $this->toArray($message);
        $this->newMessage = '';

        // Không để lỗi broadcast (Reverb chưa chạy / socket chưa kết nối) làm hỏng việc gửi tin
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Nhận tin nhắn realtime từ Reverb (kênh private chat-nhom).
     *
     * @param  array<string, mixed>  $payload
     */
    #[On('echo-private:chat-nhom,MessageSent')]
    public function onMessageReceived(array $payload): void
    {
        $this->messages[] = [
            'id' => $payload['id'] ?? null,
            'user_id' => $payload['user_id'] ?? null,
            'user_name' => $payload['user_name'] ?? __('chat.anonymous'),
            'body' => $payload['body'] ?? '',
            'created_at' => $payload['created_at'] ?? now()->format('H:i d/m'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function toArray(Message $message): array
    {
        return [
            'id' => $message->id,
            'user_id' => $message->user_id,
            'user_name' => $message->user?->name ?? __('chat.anonymous'),
            'body' => $message->body,
            'created_at' => $message->created_at?->format('H:i d/m'),
        ];
    }
}
