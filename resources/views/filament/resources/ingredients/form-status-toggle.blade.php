@php
    $statePath = $getStatePath();
@endphp

<div class="flex items-center gap-3 py-1" x-data="{ state: $wire.$entangle('{{ $statePath }}') }">
    <span class="text-sm font-medium text-gray-950 dark:text-white whitespace-nowrap">
        {{ __('ingredient.form.status') }}
    </span>

    <button
        type="button"
        x-on:click="state = ! state"
        :class="state ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-700'"
        class="fi-fo-toggle relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2"
        role="switch"
        :aria-checked="state"
    >
        <span
            :class="state ? 'translate-x-5' : 'translate-x-0'"
            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
        ></span>
    </button>

    <span
        :class="state ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400'"
        class="text-sm font-semibold whitespace-nowrap"
        x-text="state ? '{{ __('ingredient.status.active') }}' : '{{ __('ingredient.status.inactive') }}'"
    ></span>
</div>
