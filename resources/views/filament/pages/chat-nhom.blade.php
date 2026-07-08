<x-filament-panels::page>
    @php $currentUserId = filament()->auth()->id(); @endphp

    <div
        x-data="{
            scrollDown() {
                this.$nextTick(() => {
                    const box = this.$refs.messages;
                    if (box) { box.scrollTop = box.scrollHeight; }
                });
            }
        }"
        x-init="scrollDown()"
        @messages-updated.window="scrollDown()"
        class="flex flex-col bg-white dark:bg-gray-900 rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden"
        style="height: calc(100vh - 16rem);"
    >
        <div class="px-5 py-3 border-b border-gray-100 dark:border-white/10 flex items-center gap-2">
            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            <span class="font-semibold text-gray-900 dark:text-white">Kênh trao đổi nội bộ</span>
            <span class="text-xs text-gray-400">— thời gian thực (Reverb)</span>
        </div>

        <div x-ref="messages" class="flex-1 overflow-y-auto px-5 py-4 space-y-3">
            @forelse ($messages as $message)
                @php $isOwn = $message['user_id'] == $currentUserId; @endphp
                <div class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[70%] {{ $isOwn ? 'items-end' : 'items-start' }} flex flex-col">
                        @unless ($isOwn)
                            <span class="text-xs font-semibold text-primary-600 dark:text-primary-400 mb-0.5">
                                {{ $message['user_name'] }}
                            </span>
                        @endunless
                        <div class="px-3.5 py-2 rounded-2xl text-sm leading-relaxed break-words
                            {{ $isOwn
                                ? 'bg-primary-600 text-white rounded-br-sm'
                                : 'bg-gray-100 dark:bg-white/5 text-gray-900 dark:text-gray-100 rounded-bl-sm' }}">
                            {{ $message['body'] }}
                        </div>
                        <span class="text-[11px] text-gray-400 mt-0.5">{{ $message['created_at'] }}</span>
                    </div>
                </div>
            @empty
                <div class="h-full flex items-center justify-center text-gray-400 text-sm">
                    Chưa có tin nhắn nào. Hãy bắt đầu cuộc trò chuyện!
                </div>
            @endforelse
        </div>

        <form
            wire:submit="sendMessage"
            x-on:submit="scrollDown()"
            class="p-3 border-t border-gray-100 dark:border-white/10 flex items-center gap-2"
        >
            <input
                type="text"
                wire:model="newMessage"
                placeholder="Nhập tin nhắn..."
                autocomplete="off"
                class="flex-1 rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 text-sm focus:border-primary-500 focus:ring-primary-500"
            />
            <x-filament::button type="submit" icon="heroicon-m-paper-airplane">
                Gửi
            </x-filament::button>
        </form>
    </div>

    <script data-navigate-once>
        // SPA-safe: with wire:navigate, "livewire:initialized" only fires on the first
        // full page load, so register the hook immediately when Livewire is already up.
        (function registerChatMorphHook() {
            if (window.__chatMorphHookRegistered) {
                return;
            }

            const register = () => {
                window.__chatMorphHookRegistered = true;
                Livewire.hook('morph.updated', () => {
                    window.dispatchEvent(new CustomEvent('messages-updated'));
                });
            };

            if (window.Livewire?.hook) {
                register();
            } else {
                document.addEventListener('livewire:initialized', register, { once: true });
            }
        })();
    </script>
</x-filament-panels::page>
