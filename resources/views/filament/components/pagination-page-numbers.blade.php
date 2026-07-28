@props([
    'paginator',
    'pageName' => 'page',
])

@if($paginator->total() > 0)
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $pageWindow = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
            ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
            ->unique()
            ->sort()
            ->values();
    @endphp

    <nav role="navigation" aria-label="{{ __('common.pagination.navigation') }}" class="flex items-center justify-end gap-1">
        @if($paginator->onFirstPage())
            <span aria-disabled="true" class="inline-flex h-7 min-w-7 items-center justify-center rounded-md border border-gray-200 px-1.5 text-gray-300 dark:border-gray-700 dark:text-gray-600">‹</span>
        @else
            <button type="button" wire:click="previousPage('{{ $pageName }}')" class="inline-flex h-7 min-w-7 items-center justify-center rounded-md border border-gray-300 px-1.5 text-gray-600 dark:border-gray-700 dark:text-gray-300">‹</button>
        @endif

        @foreach($pageWindow as $index => $page)
            @if($index > 0 && $page - $pageWindow[$index - 1] > 1)
                <span aria-hidden="true" class="px-1 text-gray-400">…</span>
            @endif

            @if($page === $currentPage)
                <span aria-current="page" class="inline-flex h-7 min-w-7 items-center justify-center rounded-md bg-primary-600 px-1.5 font-bold text-white">{{ $page }}</span>
            @else
                <button type="button" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" class="inline-flex h-7 min-w-7 items-center justify-center rounded-md border border-gray-300 px-1.5 text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $page }}</button>
            @endif
        @endforeach

        @if($paginator->hasMorePages())
            <button type="button" wire:click="nextPage('{{ $pageName }}')" class="inline-flex h-7 min-w-7 items-center justify-center rounded-md border border-gray-300 px-1.5 text-gray-600 dark:border-gray-700 dark:text-gray-300">›</button>
        @else
            <span aria-disabled="true" class="inline-flex h-7 min-w-7 items-center justify-center rounded-md border border-gray-200 px-1.5 text-gray-300 dark:border-gray-700 dark:text-gray-600">›</span>
        @endif
    </nav>
@endif
