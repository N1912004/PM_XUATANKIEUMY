@php
    use App\Imports\IngredientsImport;

    $badge = [
        IngredientsImport::CREATED => 'text-success-700 bg-success-50 dark:text-success-400 dark:bg-success-400/10',
        IngredientsImport::UPDATED => 'text-info-700 bg-info-50 dark:text-info-400 dark:bg-info-400/10',
        IngredientsImport::SKIPPED => 'text-danger-700 bg-danger-50 dark:text-danger-400 dark:bg-danger-400/10',
    ];

    // File lớn: badge tổng LUÔN đúng; chỉ render tối đa $cap dòng mỗi nhóm —
    // dòng BỎ QUA hiện thẳng (cần soát lý do), Thêm mới/Cập nhật gập trong <details>.
    $cap = 200;
    $grouped = collect($import->results)->groupBy('status');
    $skippedRows = $grouped->get(IngredientsImport::SKIPPED, collect());
    $createdRows = $grouped->get(IngredientsImport::CREATED, collect());
    $updatedRows = $grouped->get(IngredientsImport::UPDATED, collect());
@endphp

<div class="space-y-3">
    <div class="flex flex-wrap gap-2 text-sm">
        <span class="rounded-md px-2 py-1 {{ $badge[IngredientsImport::CREATED] }}">
            {{ __('ingredient.import.preview_created', ['count' => number_format($createdRows->count())]) }}
        </span>
        <span class="rounded-md px-2 py-1 {{ $badge[IngredientsImport::UPDATED] }}">
            {{ __('ingredient.import.preview_updated', ['count' => number_format($updatedRows->count())]) }}
        </span>
        <span class="rounded-md px-2 py-1 {{ $badge[IngredientsImport::SKIPPED] }}">
            {{ __('ingredient.import.preview_skipped', ['count' => number_format($skippedRows->count())]) }}
        </span>
    </div>

    @if ($import->results === [])
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('ingredient.import.empty') }}</p>
    @else
        @if ($skippedRows->isNotEmpty())
            <div class="overflow-x-auto rounded-lg ring-1 ring-danger-600/20 max-h-80 overflow-y-auto">
                <table class="w-full text-start text-sm">
                    <thead class="bg-danger-50 dark:bg-danger-400/10 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-start font-medium w-16">{{ __('ingredient.import.col_row') }}</th>
                            <th class="px-3 py-2 text-start font-medium">{{ __('ingredient.table.code') }}</th>
                            <th class="px-3 py-2 text-start font-medium">{{ __('ingredient.table.name') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($skippedRows->take($cap) as $result)
                            <tr>
                                <td class="px-3 py-2 tabular-nums">{{ $result['row'] }}</td>
                                <td class="px-3 py-2 font-mono">{{ $result['code'] ?: '—' }}</td>
                                <td class="px-3 py-2">
                                    <span class="font-medium">{{ $result['name'] ?: '—' }}</span>
                                    @if ($result['message'])
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $result['message'] }}</p>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($skippedRows->count() > $cap)
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('ingredient.import.preview_more', ['count' => number_format($skippedRows->count() - $cap)]) }}
                </p>
            @endif
        @endif

        @foreach ([IngredientsImport::CREATED => $createdRows, IngredientsImport::UPDATED => $updatedRows] as $status => $rows)
            @if ($rows->isNotEmpty())
                <details class="rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10">
                    <summary class="cursor-pointer select-none px-3 py-2 text-sm font-medium">
                        {{ __('ingredient.import.status_'.$status) }}: {{ number_format($rows->count()) }}
                        <span class="text-xs font-normal text-gray-500 dark:text-gray-400">
                            ({{ __('ingredient.import.preview_toggle', ['count' => min($rows->count(), $cap)]) }})
                        </span>
                    </summary>
                    <div class="max-h-80 overflow-y-auto border-t border-gray-200 dark:border-white/10">
                        <table class="w-full text-start text-sm">
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                @foreach ($rows->take($cap) as $result)
                                    <tr>
                                        <td class="px-3 py-1.5 tabular-nums w-16">{{ $result['row'] }}</td>
                                        <td class="px-3 py-1.5 font-mono">{{ $result['code'] ?: '—' }}</td>
                                        <td class="px-3 py-1.5">{{ $result['name'] ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if ($rows->count() > $cap)
                            <p class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('ingredient.import.preview_more', ['count' => number_format($rows->count() - $cap)]) }}
                            </p>
                        @endif
                    </div>
                </details>
            @endif
        @endforeach
    @endif
</div>
