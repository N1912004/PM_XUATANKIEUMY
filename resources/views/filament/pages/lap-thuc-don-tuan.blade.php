<x-filament-panels::page>
    <form wire:submit="saveDraft" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-3">
            <x-filament::button type="submit" color="gray" icon="heroicon-m-document">
                Lưu nháp
            </x-filament::button>
            <x-filament::button wire:click="sendToClient" color="info" icon="heroicon-m-paper-airplane">
                Gửi khách hàng
            </x-filament::button>
            <x-filament::button wire:click="confirmByClient" color="warning" icon="heroicon-m-check-badge">
                Khách đã xác nhận
            </x-filament::button>
            <x-filament::button wire:click="lockWeek" color="success" icon="heroicon-m-lock-closed">
                Chốt thực đơn
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
