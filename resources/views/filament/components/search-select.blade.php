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

{{--
    Panel dùng position:fixed + teleport ra <body> (khung cha có overflow/transform sẽ cắt hoặc lệch nó),
    và bám dính nút bấm mỗi khung hình khi mở: khung nội dung Filament cuộn BÊN TRONG container nên
    sự kiện scroll không bao giờ tới window. Chiều cao panel phải đo THẬT — dùng hằng số sẽ khiến panel
    "lật lên" sai và bay khỏi ô chọn. Gọi window.requestAnimationFrame (không gọi trần) vì trong phạm vi
    Alpine, hàm global bị bind sai `this` → TypeError: Illegal invocation.
--}}
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
            this.close();
            this.search = '';
        },

        panel: { top: 0, left: 0, width: 0 },
        frame: null,
        reposition() {
            const trigger = this.$refs.trigger;

            if (! trigger) {
                return;
            }

            const rect = trigger.getBoundingClientRect();

            const height = this.$refs.panelBox ? this.$refs.panelBox.offsetHeight : 0;
            const spaceBelow = window.innerHeight - rect.bottom - 8;
            const flipUp = height > 0 && spaceBelow < height && rect.top > height + 8;

            this.panel = {
                top: flipUp ? Math.max(8, rect.top - height - 4) : rect.bottom + 4,
                left: rect.left,
                width: rect.width,
            };
        },
        track() {
            this.reposition();
            this.frame = window.requestAnimationFrame(() => this.open && this.track());
        },
        close() {
            this.open = false;

            if (this.frame) {
                window.cancelAnimationFrame(this.frame);
                this.frame = null;
            }
        },
        toggle() {
            if (this.open) {
                this.close();

                return;
            }

            this.open = true;
            this.track();
            this.$nextTick(() => { if (this.$refs.searchBox) this.$refs.searchBox.focus(); });
        },
    }"
    class="relative w-full"
    @keydown.escape.stop="close()"
    x-on:destroy="close()"
    {{-- Livewire vẽ lại DOM (chọn xong, đổi tab…) → đóng panel, nếu không nó treo lại
         ở TOẠ ĐỘ CŨ và trôi lên đè phần trên của trang.
         Dùng x-on: chứ KHÔNG dùng @livewire:… — Blade sẽ hiểu nhầm thành directive @livewire. --}}
    x-on:livewire:commit.window="close()"
    x-on:livewire:navigated.window="close()"
>
    <button
        type="button"
        x-ref="trigger"
        @click="toggle()"
        class="flex w-full items-center justify-between gap-2 rounded-md border border-gray-300 bg-white px-2 py-1.5 text-left text-xs font-semibold text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
        style="height:34px"
    >
        <span x-text="currentLabel || @js($placeholder)" :class="currentLabel ? '' : 'text-gray-400'" class="truncate"></span>
        <svg class="h-3 w-3 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/>
        </svg>
    </button>

    {{-- Panel treo thẳng vào <body>: khung cha có overflow hoặc transform sẽ cắt/lệch nó --}}
    <template x-teleport="body">
    <div
        x-ref="panelBox"
        x-show="open"
        x-cloak
        @click.outside="close()"
        :style="`position:fixed; top:${panel.top}px; left:${panel.left}px; width:${panel.width}px; z-index:9999;`"
        class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900"
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
    </template>
</div>
