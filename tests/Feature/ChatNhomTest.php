<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Filament\Pages\ChatNhom;
use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChatNhomTest extends TestCase
{
    use RefreshDatabase;

    public function test_sending_a_message_persists_and_appends_to_thread(): void
    {
        $user = $this->createSuperAdmin();
        $this->actingAs($user);

        Livewire::test(ChatNhom::class)
            ->set('newMessage', 'Xin chào cả nhà bếp!')
            ->call('sendMessage')
            ->assertHasNoErrors()
            ->assertSet('newMessage', '')
            ->assertCount('messages', 1);

        $this->assertDatabaseHas('messages', [
            'user_id' => $user->id,
            'body' => 'Xin chào cả nhà bếp!',
        ]);
    }

    public function test_empty_message_is_rejected(): void
    {
        $this->actingAs($this->createSuperAdmin());

        Livewire::test(ChatNhom::class)
            ->set('newMessage', '')
            ->call('sendMessage')
            ->assertHasErrors('newMessage');

        $this->assertSame(0, Message::count());
    }

    public function test_message_sent_event_broadcasts_on_private_chat_channel(): void
    {
        $user = $this->createSuperAdmin();
        $message = Message::create([
            'user_id' => $user->id,
            'body' => 'Test realtime',
        ]);

        $event = new MessageSent($message);
        $channels = $event->broadcastOn();

        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-chat-nhom', $channels[0]->name);
        $this->assertSame('Test realtime', $event->broadcastWith()['body']);
    }
}
