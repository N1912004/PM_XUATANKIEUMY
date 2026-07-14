@php
    use App\Imports\RecipesImport;

    $badge = [
        RecipesImport::CREATED => 'text-success-700 bg-success-50 dark:text-success-400 dark:bg-success-400/10',
        RecipesImport::UPDATED => 'text-info-700 bg-info-50 dark:text-info-400 dark:bg-info-400/10',
        RecipesImport::SKIPPED => 'text-danger-700 bg-danger-50 dark:text-danger-400 dark:bg-danger-400/10',
    ];
    $labels = [
        RecipesImport::CREATED => 'Thêm mới',
        RecipesImport::UPDATED => 'Cập nhật',
        RecipesImport::SKIPPED => 'Bỏ qua',
    ];

    // File có thể rất lớn — modal không render nổi toàn bộ. Badge tổng LUÔN đúng 100%;
    // bảng chi tiết ưu tiên dòng BỎ QUA (thứ cần đọc trước khi xác nhận), phần
    // Thêm mới/Cập nhật gập lại và mỗi nhóm chỉ hiện tối đa $cap dòng.
    $cap = 200;
    $grouped = collect($import->results)->groupBy('status');
    $skippedRows = $grouped->get(RecipesImport::SKIPPED, collect());
    $createdRows = $grouped->get(RecipesImport::CREATED, collect());
    $updatedRows = $grouped->get(RecipesImport::UPDATED, collect());
@endphp

<div class="space-y-3">
    <div class="flex flex-wrap gap-2 text-sm">
        <span class="rounded-md px-2 py-1 {{ $badge[RecipesImport::CREATED] }}">
            Thêm mới: {{ number_format($createdRows->count()) }} món
        </span>
        <span class="rounded-md px-2 py-1 {{ $badge[RecipesImport::UPDATED] }}">
            Cập nhật: {{ number_format($updatedRows->count()) }} món
        </span>
        <span class="rounded-md px-2 py-1 {{ $badge[RecipesImport::SKIPPED] }}">
            Bỏ qua: {{ number_format($skippedRows->count()) }} món
        </span>
    </div>

    @if ($import->results === [])
        <p class="text-sm text-gray-500 dark:text-gray-400">File không có món ăn nào để nhập.</p>
    @else
        {{-- Dòng BỎ QUA hiện thẳng (kèm lý do) — đây là thứ cần soát trước khi bấm xác nhận --}}
        @if ($skippedRows->isNotEmpty())
            <div class="overflow-x-auto rounded-lg ring-1 ring-danger-600/20 max-h-80 overflow-y-auto">
                <table class="w-full text-start text-sm">
                    <thead class="bg-danger-50 dark:bg-danger-400/10 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-start font-medium w-16">Dòng</th>
                            <th class="px-3 py-2 text-start font-medium">Món bị bỏ qua — lý do</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($skippedRows->take($cap) as $result)
                            <tr>
                                <td class="px-3 py-2 tabular-nums">{{ $result['row'] }}</td>
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
                    … và {{ number_format($skippedRows->count() - $cap) }} dòng bỏ qua khác (tổng số ở badge phía trên là chính xác).
                </p>
            @endif
        @endif

        {{-- Thêm mới / Cập nhật: gập lại, bấm mới mở — tránh render hàng nghìn dòng --}}
        @foreach ([RecipesImport::CREATED => $createdRows, RecipesImport::UPDATED => $updatedRows] as $status => $rows)
            @if ($rows->isNotEmpty())
                <details class="rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10">
                    <summary class="cursor-pointer select-none px-3 py-2 text-sm font-medium">
                        {{ $labels[$status] }}: {{ number_format($rows->count()) }} món
                        <span class="text-xs font-normal text-gray-500 dark:text-gray-400">(bấm để xem {{ $rows->count() > $cap ? $cap.' dòng đầu' : 'danh sách' }})</span>
                    </summary>
                    <div class="max-h-80 overflow-y-auto border-t border-gray-200 dark:border-white/10">
                        <table class="w-full text-start text-sm">
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                @foreach ($rows->take($cap) as $result)
                                    <tr>
                                        <td class="px-3 py-1.5 tabular-nums w-16">{{ $result['row'] }}</td>
                                        <td class="px-3 py-1.5">{{ $result['name'] ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if ($rows->count() > $cap)
                            <p class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                                … và {{ number_format($rows->count() - $cap) }} món khác (tổng số ở badge phía trên là chính xác).
                            </p>
                        @endif
                    </div>
                </details>
            @endif
        @endforeach
    @endif
</div>
