<?php

namespace App\Filament\Pages;

use App\Models\Menu;
use App\Models\Shift;
use Carbon\Carbon;
use Filament\Pages\Page;

class BaoCao extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Báo cáo';

    protected static ?string $title = 'Báo cáo';

    protected static ?string $navigationGroup = 'XUẤT ĂN';

    protected static ?int $navigationSort = 9;

    protected static string $view = 'filament.pages.bao-cao';

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

    public function getGroupedData(): array
    {
        if (! $this->fromDate || ! $this->toDate || empty($this->selectedShifts)) {
            return [];
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

        while ($current->lte($end)) {
            $dateStr = $current->toDateString();

            // Fetch menus for this date and selected shifts
            $shiftsData = [];
            $allShifts = Shift::whereIn('id', $this->selectedShifts)->get();

            foreach ($allShifts as $shift) {
                $menusQuery = Menu::with(['recipe.ingredients'])
                    ->where('date', $dateStr)
                    ->where('shift_id', $shift->id);

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

                $menus = $menusQuery->get();

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
                    foreach ($recipe->ingredients as $ingredient) {
                        $qty = $menu->estimated_portions * $ingredient->pivot->quantity_per_portion;
                        // Let's check if the ingredient matches search to highlight or filter
                        $ingredients[] = [
                            'code' => $ingredient->code,
                            'name' => $ingredient->name,
                            'dl_g' => $ingredient->pivot->quantity_per_portion,
                            'suat' => $menu->estimated_portions,
                            'phan' => 1, // Mock portion count
                            'quantity' => $qty,
                            'unit' => $ingredient->unit,
                        ];
                    }

                    $dishes[] = [
                        'name' => $recipe->name,
                        'type' => $recipe->type,
                        'suat' => $menu->estimated_portions,
                        'phan' => 1,
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

        return $days;
    }

    public function getStats(): array
    {
        $grouped = $this->getGroupedData();

        $totalDays = count($grouped);
        $totalDish = 0;
        $totalSuat = 0;
        $ingCodes = [];

        foreach ($grouped as $day) {
            foreach ($day['shifts'] as $shift) {
                foreach ($shift['dishes'] as $dish) {
                    $totalDish++;
                    $totalSuat += $dish['suat'];
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
        ];
    }
}
