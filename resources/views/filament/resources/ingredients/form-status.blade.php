@php
    $isActive = (bool) ($active ?? false);
@endphp

<span @class([
    'text-sm font-semibold whitespace-nowrap',
    'text-success-600 dark:text-success-400' => $isActive,
    'text-danger-600 dark:text-danger-400' => ! $isActive,
])>
    {{ $isActive ? __('ingredient.status.active') : __('ingredient.status.inactive') }}
</span>
