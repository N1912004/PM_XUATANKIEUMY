@props([
    'currentPageOptionProperty' => 'tableRecordsPerPage',
    'extremeLinks' => false,
    'paginator',
    'pageOptions' => [],
])

@php
    use Illuminate\Contracts\Pagination\CursorPaginator;

    $isRtl = __('filament-panels::layout.direction') === 'rtl';
    $isSimple = ! $paginator instanceof \Illuminate\Pagination\LengthAwarePaginator;
    $first = \Illuminate\Support\Number::format($paginator->firstItem() ?? 0);
    $last = \Illuminate\Support\Number::format($paginator->lastItem() ?? 0);
    $total = \Illuminate\Support\Number::format($paginator->total());

    $summaryText = __('common.pagination.summary', [
        'first' => $first,
        'last' => $last,
        'total' => $total,
    ]);
@endphp

<nav
    aria-label="{{ __('filament::components/pagination.label') }}"
    role="navigation"
    {{
        $attributes->class([
            'fi-pagination flex items-center justify-between gap-x-3 w-full',
            'fi-simple' => $isSimple,
        ])
    }}
>
    @if (! $isSimple)
        <span
            class="fi-pagination-overview text-xs font-medium text-gray-500 dark:text-gray-400"
        >
            {{ $summaryText }}
        </span>
    @endif

    <div class="flex items-center gap-3">
        @if (count($pageOptions) > 1)
            <label class="fi-pagination-records-per-page-select flex items-center gap-x-2">
                <span class="text-xs font-normal text-gray-500 dark:text-gray-400">{{ __('common.pagination.per_page_label') }}</span>
                <x-filament::input.select
                    :wire:model.live="$currentPageOptionProperty"
                    style="height:30px; padding:0 24px 0 9px; border-radius:7px; border:1px solid var(--bd); font-size:12px; color:var(--su); background:var(--wh);"
                >
                    @foreach ($pageOptions as $option)
                        <option value="{{ $option }}">
                            {{ $option === 'all' ? __('filament::components/pagination.fields.records_per_page.options.all') : $option }}
                        </option>
                    @endforeach
                </x-filament::input.select>
            </label>
        @endif

        @if ((! $isSimple) && $paginator->total() > 0)
            <ol class="fi-pagination-items flex items-center gap-1">
                @if ($paginator->onFirstPage())
                    <x-filament::pagination.item
                        disabled
                        :aria-label="__('filament::components/pagination.actions.previous.label')"
                        :icon="$isRtl ? 'heroicon-m-chevron-right' : 'heroicon-m-chevron-left'"
                    />
                @else
                    <x-filament::pagination.item
                        :aria-label="__('filament::components/pagination.actions.previous.label')"
                        :icon="$isRtl ? 'heroicon-m-chevron-right' : 'heroicon-m-chevron-left'"
                        rel="prev"
                        :wire:click="'previousPage(\'' . $paginator->getPageName() . '\')'"
                        :wire:key="$this->getId() . '.pagination.previous'"
                    />
                @endif

                @foreach ($paginator->render()->offsetGet('elements') as $element)
                    @if (is_string($element))
                        <x-filament::pagination.item disabled :label="$element" />
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <x-filament::pagination.item
                                :active="$page === $paginator->currentPage()"
                                :aria-label="trans_choice('filament::components/pagination.actions.go_to_page.label', $page, ['page' => $page])"
                                :label="$page"
                                :wire:click="'gotoPage(' . $page . ', \'' . $paginator->getPageName() . '\')'"
                                :wire:key="$this->getId() . '.pagination.' . $paginator->getPageName() . '.' . $page"
                            />
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <x-filament::pagination.item
                        :aria-label="__('filament::components/pagination.actions.next.label')"
                        :icon="$isRtl ? 'heroicon-m-chevron-left' : 'heroicon-m-chevron-right'"
                        rel="next"
                        :wire:click="'nextPage(\'' . $paginator->getPageName() . '\')'"
                        :wire:key="$this->getId() . '.pagination.next'"
                    />
                @else
                    <x-filament::pagination.item
                        disabled
                        :aria-label="__('filament::components/pagination.actions.next.label')"
                        :icon="$isRtl ? 'heroicon-m-chevron-left' : 'heroicon-m-chevron-right'"
                    />
                @endif
            </ol>
        @endif
    </div>
</nav>
