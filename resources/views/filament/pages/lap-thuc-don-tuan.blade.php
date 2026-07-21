<x-filament-panels::page>
    <form wire:submit="saveDraft" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-3">
            <x-filament::button type="submit" color="gray" icon="heroicon-m-document">
                {{ __('menu.actions.save_draft') }}
            </x-filament::button>
            <x-filament::button wire:click="sendToClient" color="info" icon="heroicon-m-paper-airplane">
                {{ __('menu.actions.send_customer') }}
            </x-filament::button>
            <x-filament::button wire:click="lockWeek" color="success" icon="heroicon-m-lock-closed">
                {{ __('menu.actions.lock') }}
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
