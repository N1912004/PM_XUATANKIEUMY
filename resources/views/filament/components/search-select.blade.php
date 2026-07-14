{{--
    Ô chọn dạng VỪA TÌM VỪA CHỌN (combobox) dùng chung cho các trang hand-rolled.

    Dựng bằng Alpine thuần + @entangle theo đúng convention dự án — KHÔNG dùng Choices.js
    (thư viện đó không có trong package.json và Filament không expose window.Choices).

    Tham số:
      $name        : tên thuộc tính Livewire (VD: 'selectedPOId', 'directItemsData.0.ingredient_id')
      $options     : mảng [['value' => ..., 'label' => ..., 'sub' => ...(tùy chọn)], ...]
      $placeholder : chữ hiện khi chưa chọn
      $live        : true = cập nhật server ngay khi chọn (wire:model.live), false = giữ ở client
      $nullable    : cho phép bỏ chọn (dòng "— Bỏ chọn —")
      $emptyLabel  : nhãn dòng bỏ chọn
--}}
@php
    $placeholder ??= '— Chọn —';
    $live ??= false;
    $nullable ??= true;
    $emptyLabel ??= $placeholder;
    $searchPlaceholder ??= 'Gõ để tìm...';

    $normalized = collect($options)->map(fn ($option): array => [
        'value' => (string) ($option['value'] ?? ''),
        'label' => (string) ($option['label'] ?? ''),
        'sub' => (string) ($option['sub'] ?? ''),
    ])->values();
@endphp

<div
    x-data="{
        open: false,
        search: '',
        selected: @if($live) @entangle($name).live @else @entangle($name) @endif,
        options: {{ Illuminate\Support\Js::from($normalized) }},
        get filtered() {
            if (! this.search) return this.options;
            const needle = this.search.toLowerCase();
            return this.options.filter(o => (o.label + ' ' + o.sub).toLowerCase().includes(needle));
        },
        get currentLabel() {
            const hit = this.options.find(o => o.value === String(this.selected ?? ''));
            return hit ? hit.label : '';
        },
        pick(value) {
            this.selected = value;
            this.open = false;
            this.search = '';
        },
    }"
    class="relative w-full"
    @keydown.escape.stop="open = false"
>
    <button
        type="button"
        @click="open = ! open; $nextTick(() => open && $refs.searchBox?.focus())"
        class="flex w-full items-center justify-between gap-2 rounded-md border border-gray-300 bg-white px-2 py-1.5 text-left text-xs font-semibold text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
        style="height:34px"
    >
        <span x-text="currentLabel || @js($placeholder)" :class="currentLabel ? '' : 'text-gray-400'" class="truncate"></span>
        <svg class="h-3 w-3 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/>
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        @click.away="open = false"
        class="absolute inset-x-0 top-full z-50 mt-1 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900"
    >
        <div class="p-2">
            <input
                x-ref="searchBox"
                x-model="search"
                type="text"
                placeholder="{{ $searchPlaceholder }}"
                class="w-full rounded-md border border-gray-200 px-2 py-1 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
            >
        </div>

        <div class="max-h-56 overflow-y-auto px-2 pb-2">
            @if($nullable)
                <div
                    @click="pick('')"
                    class="cursor-pointer rounded px-2 py-1.5 text-xs font-semibold italic text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    {{ $emptyLabel }}
                </div>
            @endif

            <template x-for="option in filtered" :key="option.value">
                <div
                    @click="pick(option.value)"
                    class="cursor-pointer rounded px-2 py-1.5 text-xs font-semibold hover:bg-gray-100 dark:hover:bg-gray-800"
                    :class="String(selected ?? '') === option.value ? 'bg-primary-50 text-primary-700 dark:bg-gray-800' : 'text-gray-800 dark:text-gray-100'"
                >
                    <span x-text="option.label"></span>
                    <span x-show="option.sub" x-text="option.sub" class="ml-1 text-[11px] font-normal text-gray-400"></span>
                </div>
            </template>

            <div x-show="filtered.length === 0" class="px-2 py-3 text-center text-xs font-semibold text-gray-400">
                Không tìm thấy kết quả
            </div>
        </div>
    </div>
</div>
