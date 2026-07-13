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
@endphp

<div class="space-y-3">
    <div class="flex flex-wrap gap-2 text-sm">
        <span class="rounded-md px-2 py-1 {{ $badge[RecipesImport::CREATED] }}">
            Thêm mới: {{ $import->countOf(RecipesImport::CREATED) }} món
        </span>
        <span class="rounded-md px-2 py-1 {{ $badge[RecipesImport::UPDATED] }}">
            Cập nhật: {{ $import->countOf(RecipesImport::UPDATED) }} món
        </span>
        <span class="rounded-md px-2 py-1 {{ $badge[RecipesImport::SKIPPED] }}">
            Bỏ qua: {{ $import->countOf(RecipesImport::SKIPPED) }} món
        </span>
    </div>

    @if ($import->results === [])
        <p class="text-sm text-gray-500 dark:text-gray-400">File không có món ăn nào để nhập.</p>
    @else
        <div class="overflow-x-auto rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10">
            <table class="w-full text-start text-sm">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-3 py-2 text-start font-medium">Dòng</th>
                        <th class="px-3 py-2 text-start font-medium">Tên món ăn</th>
                        <th class="px-3 py-2 text-start font-medium">Kết quả</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @foreach ($import->results as $result)
                        <tr>
                            <td class="px-3 py-2 tabular-nums">{{ $result['row'] }}</td>
                            <td class="px-3 py-2">{{ $result['name'] ?: '—' }}</td>
                            <td class="px-3 py-2">
                                <span class="rounded-md px-2 py-1 text-xs {{ $badge[$result['status']] }}">
                                    {{ $labels[$result['status']] }}
                                </span>
                                @if ($result['message'])
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $result['message'] }}</p>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
