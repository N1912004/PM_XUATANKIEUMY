<?php

namespace App\Filament\Pages;

use App\Exports\FinancialReportExport;
use App\Models\Menu;
use App\Models\Shift;
use Carbon\Carbon;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BaoCao extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 9;

    protected static string $view = 'filament.pages.bao-cao';

    public static function getNavigationLabel(): string
    {
        return __('Báo cáo');
    }

    public function getTitle(): string
    {
        return __('Báo cáo');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('VẬN HÀNH BẾP');
    }

    public ?string $fromDate = '2026-05-18';

    public ?string $toDate = '2026-05-23';

    public array $selectedShifts = [1, 2, 3];

    public string $search = '';

    public function mount(): void
    {
        // Try to sync shifts
        $shifts = Shift::pluck('id')->toArray();
        if (! empty($shifts)) {
            $this->selectedShifts = array_slice($shifts, 0, 3);
        }
    }

    public function toggleShift(int $id): void
    {
        if (in_array($id, $this->selectedShifts)) {
            $this->selectedShifts = array_values(array_diff($this->selectedShifts, [$id]));
        } else {
            $this->selectedShifts[] = $id;
        }
    }

    public function setThisWeek(): void
    {
        $this->fromDate = '2026-05-18';
        $this->toDate = '2026-05-23';
    }

    /**
     * Request-scope memo: getGroupedData() được gọi từ cả getStats() lẫn blade,
     * chỉ tính 1 lần mỗi render. (Protected nên không bị Livewire serialize.)
     *
     * @var array<int, array<string, mixed>>|null
     */
    protected ?array $groupedDataMemo = null;

    public function getGroupedData(): array
    {
        if ($this->groupedDataMemo !== null) {
            return $this->groupedDataMemo;
        }

        if (! $this->fromDate || ! $this->toDate || empty($this->selectedShifts)) {
            return $this->groupedDataMemo = [];
        }

        $start = Carbon::parse($this->fromDate);
        $end = Carbon::parse($this->toDate);

        $days = [];
        $current = $start->copy();

        // Map day of week to Vietnamese
        $dowVN = [
            0 => 'CHỦ NHẬT',
            1 => 'THỨ 2',
            2 => 'THỨ 3',
            3 => 'THỨ 4',
            4 => 'THỨ 5',
            5 => 'THỨ 6',
            6 => 'THỨ 7',
        ];

        // Nạp shifts 1 lần và toàn bộ menus của cả khoảng ngày bằng 1 query
        // (thay vì 1 query cho mỗi ngày × ca), rồi group trong PHP.
        $allShifts = Shift::whereIn('id', $this->selectedShifts)->get();

        // Khoảng nửa mở [start, end+1) — sargable trên MySQL (dùng index) và đúng cả trên
        // SQLite (nơi cột date lưu kèm giờ '00:00:00' khi test)
        $menusQuery = Menu::with(['recipe.ingredients'])
            ->where('date', '>=', $start->toDateString())
            ->where('date', '<', $end->copy()->addDay()->toDateString())
            ->whereIn('shift_id', $this->selectedShifts);

        if (! empty($this->search)) {
            $searchLower = '%'.strtolower($this->search).'%';
            $menusQuery->whereHas('recipe', function ($query) use ($searchLower) {
                $query->whereRaw('LOWER(name) LIKE ?', [$searchLower])
                    ->orWhereHas('ingredients', function ($q) use ($searchLower) {
                        $q->whereRaw('LOWER(name) LIKE ?', [$searchLower])
                            ->orWhereRaw('LOWER(code) LIKE ?', [$searchLower]);
                    });
            });
        }

        $menusByDateShift = $menusQuery->get()
            ->groupBy([fn (Menu $menu) => $menu->date->toDateString(), 'shift_id']);

        while ($current->lte($end)) {
            $dateStr = $current->toDateString();

            $shiftsData = [];

            foreach ($allShifts as $shift) {
                $menus = $menusByDateShift[$dateStr][$shift->id] ?? collect();

                if ($menus->isEmpty()) {
                    continue;
                }

                $dishes = [];
                foreach ($menus as $menu) {
                    $recipe = $menu->recipe;
                    if (! $recipe) {
                        continue;
                    }

                    $ingredients = [];
                    $costPerPortion = 0.0;
                    foreach ($recipe->ingredients as $ingredient) {
                        $qty = $menu->estimated_portions * $ingredient->pivot->quantity_per_portion;
                        $lineCost = $ingredient->pivot->quantity_per_portion * (float) $ingredient->reference_price;
                        $costPerPortion += $lineCost;
                        $ingredients[] = [
                            'code' => $ingredient->code,
                            'name' => $ingredient->name,
                            'dl_g' => $ingredient->pivot->quantity_per_portion,
                            'suat' => $menu->estimated_portions,
                            'phan' => 1,
                            'quantity' => $qty,
                            'unit' => $ingredient->unit,
                            'unit_cost' => (float) $ingredient->reference_price,
                            'line_cost' => $lineCost * $menu->estimated_portions,
                        ];
                    }

                    // Tổng giá vốn món = giá vốn/suất × số suất
                    $dishCost = $costPerPortion * $menu->estimated_portions;

                    $dishes[] = [
                        'name' => $recipe->name,
                        'type' => $recipe->type,
                        'suat' => $menu->estimated_portions,
                        'phan' => 1,
                        'cost_per_portion' => $costPerPortion,
                        'dish_cost' => $dishCost,
                        'ingredients' => $ingredients,
                    ];
                }

                if (! empty($dishes)) {
                    $shiftsData[] = [
                        'id' => $shift->id,
                        'name' => $shift->name,
                        'dishes' => $dishes,
                    ];
                }
            }

            if (! empty($shiftsData)) {
                $days[] = [
                    'date_str' => $dateStr,
                    'date_formatted' => $current->format('d/m/Y'),
                    'day_of_week' => $dowVN[$current->dayOfWeek],
                    'shifts' => $shiftsData,
                ];
            }

            $current->addDay();
        }

        return $this->groupedDataMemo = $days;
    }

    public function getStats(): array
    {
        $grouped = $this->getGroupedData();

        $totalDays = count($grouped);
        $totalDish = 0;
        $totalSuat = 0;
        $totalCost = 0.0;
        $ingCodes = [];

        foreach ($grouped as $day) {
            foreach ($day['shifts'] as $shift) {
                foreach ($shift['dishes'] as $dish) {
                    $totalDish++;
                    $totalSuat += $dish['suat'];
                    $totalCost += $dish['dish_cost'] ?? 0;
                    foreach ($dish['ingredients'] as $ing) {
                        $ingCodes[$ing['code']] = true;
                    }
                }
            }
        }

        return [
            'days' => $totalDays,
            'dishes' => $totalDish,
            'ingredients' => count($ingCodes),
            'suat' => $totalSuat,
            'cost' => $totalCost,
        ];
    }

    /**
     * Xuất báo cáo tài chính chi phí bếp ăn (tổng giá vốn theo Ngày → Ca → Món) ra Excel.
     */
    public function exportExcel(): BinaryFileResponse
    {
        abort_unless(auth()->user()?->can('page_BaoCao') ?? false, 403);

        $fileName = 'BaoCao_TaiChinh_'.str_replace('-', '', (string) $this->fromDate)
            .'_'.str_replace('-', '', (string) $this->toDate).'.xlsx';

        return Excel::download(
            new FinancialReportExport($this->getGroupedData(), $this->getStats()),
            $fileName,
        );
    }
}
