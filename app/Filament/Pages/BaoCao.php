<?php

namespace App\Filament\Pages;

use App\Exports\FinancialReportExport;
use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\Shift;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BaoCao extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 9;

    protected static string $view = 'filament.pages.bao-cao';

    public static function getNavigationLabel(): string
    {
        return __('report.title');
    }

    public function getTitle(): string
    {
        return __('report.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('catalog.groups.kitchen_operations');
    }

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public array $selectedShifts = [1, 2, 3];

    public string $search = '';

    /** Lọc theo bếp; null = tất cả các bếp (chỉ quản lý cấp trên mới cần gộp). */
    public ?int $kitchenId = null;

    public function mount(): void
    {
        // Mặc định tuần hiện tại (T2 → T7) thay vì tuần seeder demo
        $this->fromDate ??= now()->startOfWeek()->toDateString();
        $this->toDate ??= now()->startOfWeek()->addDays(5)->toDateString();

        $shifts = Shift::pluck('id')->toArray();
        if (! empty($shifts)) {
            $this->selectedShifts = array_slice($shifts, 0, 3);
        }
    }

    /** @var Collection|null Memo trong 1 render — blade gọi trong vòng lặp */
    protected $allShiftsCache = null;

    public function getAllShifts()
    {
        return $this->allShiftsCache ??= Shift::all();
    }

    /** Cấp quản lý xem được số liệu toàn hệ thống; còn lại bị khóa vào bếp của mình. */
    protected function seesAllKitchens(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'Quản trị viên']) ?? false;
    }

    /**
     * Bếp bị ÉP cho người dùng thường (null = được xem toàn hệ thống).
     *
     * Đây là scope BẮT BUỘC, không phải bộ lọc: `$this->kitchenId` là lựa chọn trên UI nên
     * client sửa được — không thể dựa vào nó để phân quyền dữ liệu.
     */
    protected function enforcedKitchenId(): ?int
    {
        return $this->seesAllKitchens() ? null : auth()->user()?->currentKitchenId();
    }

    /**
     * Danh sách bếp để lọc — người dùng đã gắn bếp thì chỉ thấy bếp của mình.
     *
     * @return array<int, string>
     */
    public function getKitchenOptions(): array
    {
        $enforced = $this->enforcedKitchenId();

        return Kitchen::query()
            ->when($enforced, fn ($q, int $kitchenId) => $q->whereKey($kitchenId))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
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
        $this->fromDate = now()->startOfWeek()->toDateString();
        $this->toDate = now()->startOfWeek()->addDays(5)->toDateString();
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
        // Báo cáo tài chính chỉ tính thực đơn ĐÃ CHỐT — nháp/đang gửi chưa phải chi phí thực
        $menusQuery = Menu::with(['recipe.ingredients'])
            ->where('status', 'locked')
            ->where('date', '>=', $start->toDateString())
            ->where('date', '<', $end->copy()->addDay()->toDateString())
            ->whereIn('shift_id', $this->selectedShifts)
            // Người dùng thường: KHÓA CỨNG vào bếp của mình (fail-closed — chưa gắn bếp thì
            // không thấy dữ liệu nào, giống trait BelongsToKitchen). Cấp quản lý mới được gộp
            // toàn hệ thống và dùng $this->kitchenId như một bộ lọc tùy chọn.
            ->when($this->enforcedKitchenId(), fn ($q, int $kitchenId) => $q->where('kitchen_id', $kitchenId))
            ->when(
                ! $this->seesAllKitchens() && ! auth()->user()?->currentKitchenId(),
                fn ($q) => $q->whereRaw('1 = 0')
            )
            ->when(
                $this->seesAllKitchens() && $this->kitchenId,
                fn ($q) => $q->where('kitchen_id', $this->kitchenId)
            );

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

                    // Cost override (nếu có) thay cho cost tự tính — theo quy định BA về giá vốn món
                    if ($recipe->cost_override !== null) {
                        $costPerPortion = (float) $recipe->cost_override;
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

    /**
     * Tổng khối lượng TỪNG nguyên liệu tiêu thụ trong cả khoảng báo cáo (BA: R22).
     * Gộp xuyên suốt Ngày → Ca → Món, sắp xếp theo giá trị tiêu thụ giảm dần.
     *
     * @return array<int, array{code: string, name: string, unit: string, quantity: float, cost: float}>
     */
    public function getIngredientTotals(): array
    {
        $totals = [];

        foreach ($this->getGroupedData() as $day) {
            foreach ($day['shifts'] as $shift) {
                foreach ($shift['dishes'] as $dish) {
                    foreach ($dish['ingredients'] as $ing) {
                        $code = $ing['code'];

                        $totals[$code] ??= [
                            'code' => $code,
                            'name' => $ing['name'],
                            'unit' => $ing['unit'],
                            'quantity' => 0.0,
                            'cost' => 0.0,
                        ];

                        $totals[$code]['quantity'] += (float) $ing['quantity'];
                        $totals[$code]['cost'] += (float) $ing['line_cost'];
                    }
                }
            }
        }

        usort($totals, fn (array $a, array $b): int => $b['cost'] <=> $a['cost']);

        return $totals;
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
