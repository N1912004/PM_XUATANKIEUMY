<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex items-center justify-center gap-x-3">
            <x-filament::button type="submit" size="lg">
                <x-slot name="icon">
                    <x-heroicon-m-check class="h-5 w-5" />
                </x-slot>
                {{ __('settings.save_settings') }}
            </x-filament::button>

            <x-filament::button
                color="gray"
                tag="a"
                :href="filament()->getUrl()"
            >
                {{ __('settings.cancel') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
