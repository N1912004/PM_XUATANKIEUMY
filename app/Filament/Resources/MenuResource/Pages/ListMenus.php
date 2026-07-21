<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Exports\MenuExport;
use App\Filament\Resources\MenuResource;
use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\Shift;
use Filament\Resources\Pages\Page;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListMenus extends Page
{
    use WithPagination;

    protected static string $resource = MenuResource::class;

    protected static string $view = 'filament.resources.menus.pages.list-menus';

    // Views: 'list' (danh sách chính), 'week' (form tuần), 'day' (form ngày)
    public $activeView = 'list';

    // LIST FILTERS
    public $search = '';

    public $typeFilter = ''; // 'week' hoặc 'day' hoặc ''

    public $statusFilter = ''; // 'draft', 'sent', 'locked'

    public $monthFilter = '';

    public int $perPage = 10;

    // FORM WEEK STATES
    public $weekKitchenId;

    public $weekStartDate;

    public $weekStatus = 'draft';

    // Ma trận grid tuần: mỗi ô [day_index][shift_id] là DANH SÁCH món
    // (mỗi phần tử ['recipe_id' => , 'portions' => ]) — cho phép nhiều món/ô qua nút (+).
    public array $weekCells = [];

    public string $weekEditReason = ''; // Lý do sửa — bắt buộc khi ghi đè thực đơn ĐÃ CHỐT

    public bool $weekHasExistingMenus = false;

    public bool $weekHasEditableLockedMenus = false;

    public bool $weekHasPastLockedMenus = false;

    // FORM DAY STATES
    public $dayKitchenId;

    public $dayDate;

    public $dayStatus = 'draft';

    public string $dayEditReason = ''; // Lý do sửa — bắt buộc khi ghi đè thực đơn ĐÃ CHỐT

    public bool $dayHasExistingMenus = false;

    public bool $dayHasEditableLockedMenus = false;

    public bool $dayHasPastLockedMenus = false;

    public $dayItems = []; // Array of shifts, each containing recipes selected

    protected $queryString = [
        'activeView' => ['except' => 'list'],
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'monthFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        if ($this->monthFilter === '') {
            $this->monthFilter = now()->format('Y-m');
        }

        $this->weekKitchenId = Kitchen::first()?->id;
        $this->weekStartDate = now()->startOfWeek()->toDateString();
        $this->dayKitchenId = Kitchen::first()?->id;
        $this->dayDate = now()->toDateString();
    }

    public function switchView($view)
    {
        $this->activeView = $view;
        if ($view === 'list') {
            $this->resetWeekForm();
            $this->resetDayForm();
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->statusFilter = '';
        $this->monthFilter = now()->format('Y-m');
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedMonthFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    /**
     * Danh sách tháng cho bộ lọc: sinh từ dữ liệu Menu thực tế + tháng hiện tại (không hardcode).
     *
     * @return array<string, string> ['Y-m' => 'Tháng m/Y']
     */
    public function getMonthOptions(): array
    {
        // substr(date,1,7) = 'YYYY-MM' — portable trên cả MySQL lẫn SQLite (thay DATE_FORMAT)
        $months = Menu::query()
            ->selectRaw('DISTINCT substr(date, 1, 7) AS ym')
            ->orderByDesc('ym')
            ->pluck('ym')
            ->push(now()->format('Y-m'))
            ->unique()
            ->sortDesc();

        return $months->mapWithKeys(function (string $ym): array {
            [$y, $m] = explode('-', $ym);

            return [$ym => __('menu.month_label', ['month' => $m, 'year' => $y])];
        })->all();
    }

    /**
     * Xuất CSV thực đơn thật (thay nút alert() giả trước đây). Có thể giới hạn phạm vi
     * theo bếp + khoảng ngày (dùng cho nút xuất trên từng card tuần/ngày).
     */
    public function exportMenus(?int $kitchenId = null, ?string $from = null, ?string $to = null): BinaryFileResponse
    {
        abort_unless(MenuResource::canViewAny(), 403);

        if ($kitchenId !== null) {
            $this->assertKitchenAccess($kitchenId);
        }

        $query = Menu::with(['kitchen', 'shift', 'recipe'])->orderBy('date')->orderBy('shift_id');

        if ($kitchenId && $from) {
            $query->where('kitchen_id', $kitchenId)
                ->where('date', '>=', $from)
                ->where('date', '<', Carbon::parse($to ?: $from)->addDay()->toDateString());
            $subtitle = 'Từ '.Carbon::parse($from)->format('d/m/Y').' đến '.Carbon::parse($to ?: $from)->format('d/m/Y');
        } else {
            $period = $this->monthFilter ?: now()->format('Y-m');
            $query->where('date', 'like', $period.'%');
            $subtitle = 'Tháng '.Carbon::parse($period.'-01')->format('m/Y');
        }

        // Xuất .xlsx thật qua Laravel Excel (trước đây là CSV) — file này còn dùng để gửi khách duyệt.
        $fileName = 'thuc-don-'.($from ?: $this->monthFilter ?: now()->format('Y-m')).'.xlsx';

        return Excel::download(new MenuExport($query->get(), $subtitle), $fileName);
    }

    /** Xuất tuần đang soạn trên form (T2 → CN, khớp đủ 7 ngày của grid). */
    public function exportWeekForm()
    {
        $start = Carbon::parse($this->weekStartDate);

        if (! Menu::query()
            ->where('kitchen_id', $this->weekKitchenId)
            ->where('date', '>=', $start->toDateString())
            ->where('date', '<', $start->copy()->addDays(7)->toDateString())
            ->exists()) {
            $this->addError('weekStartDate', __('menu.errors.export_requires_saved'));

            return null;
        }

        return $this->exportMenus((int) $this->weekKitchenId, $start->toDateString(), $start->copy()->addDays(6)->toDateString());
    }

    /** Xuất ngày đang soạn trên form. */
    public function exportDayForm()
    {
        if (! Menu::query()
            ->where('kitchen_id', $this->dayKitchenId)
            ->where('date', '>=', $this->dayDate)
            ->where('date', '<', Carbon::parse($this->dayDate)->addDay()->toDateString())
            ->exists()) {
            $this->addError('dayDate', __('menu.errors.export_requires_saved'));

            return null;
        }

        return $this->exportMenus((int) $this->dayKitchenId, $this->dayDate, $this->dayDate);
    }

    // ==========================================
    // DATA FETCHERS & STATS
    // ==========================================
    public function getStats()
    {
        // KPI thật theo tháng đang lọc. Đếm draft/sent/locked bằng 1 aggregate (portable);
        // "số tuần hoạt động" tính trong PHP bằng ISO year-week để CHẠY ĐƯỢC CẢ SQLite lẫn MySQL
        // (YEARWEEK là hàm riêng của MySQL, sẽ vỡ suite chạy trên SQLite).
        $monthPrefix = $this->monthFilter ?: now()->format('Y-m');
        $from = $monthPrefix.'-01';
        $to = Carbon::parse($from)->addMonth()->toDateString();

        $counts = Menu::query()
            ->where('date', '>=', $from)
            ->where('date', '<', $to)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft")
            ->selectRaw("SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) AS sent")
            ->selectRaw("SUM(CASE WHEN status = 'locked' THEN 1 ELSE 0 END) AS locked")
            ->first();

        $activeWeeks = Menu::query()
            ->where('date', '>=', $from)
            ->where('date', '<', $to)
            ->get(['kitchen_id', 'date'])
            ->map(fn (Menu $m) => $m->kitchen_id.'-'.$m->date->format('o-W'))
            ->unique()
            ->count();

        return [
            'total_active_weeks' => $activeWeeks,
            'sent_month' => (int) $counts->sent,
            'pending' => (int) $counts->draft,
            'locked_month' => (int) $counts->locked,
        ];
    }

    /**
     * Query menu đã áp bộ lọc tháng / trạng thái / tìm kiếm bếp — dùng chung cho 2 bước bên dưới.
     */
    protected function filteredMenuQuery()
    {
        return Menu::query()
            ->when($this->monthFilter, fn ($q) => $q
                ->where('date', '>=', $this->monthFilter.'-01')
                ->where('date', '<', Carbon::parse($this->monthFilter.'-01')->addMonth()->toDateString()))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, fn ($q) => $q->whereHas('kitchen', fn ($k) => $k->where('name', 'like', '%'.$this->search.'%')));
    }

    public function menus()
    {
        // Bước 1: gộp nhóm + phân trang Ở TẦNG SQL (chỉ lấy các khóa nhóm của trang hiện tại)
        // thay vì nạp toàn bộ menu của tháng vào PHP rồi gộp — trước đây 720+ bản ghi/request.
        // Tuần = thứ 2 đầu tuần. Biểu thức tính theo driver để chạy được cả MySQL lẫn SQLite (test):
        //   MySQL:  DATE_SUB(date, INTERVAL WEEKDAY(date) DAY)  (WEEKDAY: 0 = thứ 2)
        //   SQLite: date(date, '-6 days', 'weekday 1')          (lùi 6 ngày rồi tiến tới thứ 2)
        $weekStartExpr = DB::getDriverName() === 'sqlite'
            ? "date(date, '-6 days', 'weekday 1')"
            : 'DATE_SUB(date, INTERVAL WEEKDAY(date) DAY)';

        $weekKeys = $this->filteredMenuQuery()
            ->selectRaw("'week' AS card_type, kitchen_id, {$weekStartExpr} AS group_date")
            ->groupBy('kitchen_id', 'group_date');

        $dayKeys = $this->filteredMenuQuery()
            ->selectRaw("'day' AS card_type, kitchen_id, date AS group_date")
            ->groupBy('kitchen_id', 'group_date');

        $keysQuery = match ($this->typeFilter) {
            'week' => $weekKeys,
            'day' => $dayKeys,
            default => $weekKeys->unionAll($dayKeys),
        };

        $page = Paginator::resolveCurrentPage('page');
        $allKeys = DB::query()->fromSub($keysQuery, 'groups')
            ->orderByDesc('group_date')
            ->orderBy('card_type')
            ->orderBy('kitchen_id');

        $total = (clone $allKeys)->count();
        $pageKeys = $allKeys->forPage($page, $this->perPage)->get();

        // Bước 2: nạp chi tiết menu cho CẢ TRANG bằng 1 query (thay vì 1 query/card = N+1),
        // rồi chọn menu đại diện (mới nhất trong khoảng của từng nhóm) trong PHP.
        $representativeMenus = $this->loadRepresentativeMenus($pageKeys);

        $cards = $pageKeys->map(function ($key) use ($representativeMenus) {
            $isWeek = $key->card_type === 'week';
            $start = Carbon::parse($key->group_date);
            // Tuần thực đơn là 7 ngày T2 → CN (trước đây addDays(5) làm rơi mất Chủ nhật)
            $end = $isWeek ? $start->copy()->addDays(6) : $start->copy();

            // Đại diện = menu có date lớn nhất trong [start, end] của bếp này (đã sort date desc).
            $menu = ($representativeMenus[$key->kitchen_id] ?? collect())
                ->first(fn (Menu $m) => $m->date->betweenIncluded($start, $end));

            if (! $menu) {
                return null;
            }

            if ($isWeek) {
                return [
                    'id' => $menu->id,
                    'type' => 'week',
                    'kitchen_id' => $menu->kitchen_id,
                    'kitchen_name' => $menu->kitchen?->name ?? 'Nhà ăn',
                    'title' => 'Thực đơn tuần - '.$menu->kitchen?->name,
                    'sub' => 'Từ ngày '.$start->format('d/m/Y').' đến '.$end->format('d/m/Y'),
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'meta_company' => $menu->kitchen?->area?->name ?? 'Công ty Summit',
                    'meta_info' => 'Tổng 7 ngày · '.($menu->kitchen?->id ? '18 ca' : 'Ca ăn'),
                    'status' => $menu->status,
                    'date_raw' => $menu->date,
                ];
            }

            return [
                'id' => $menu->id,
                'type' => 'day',
                'kitchen_id' => $menu->kitchen_id,
                'kitchen_name' => $menu->kitchen?->name ?? 'Nhà ăn',
                'title' => 'Thực đơn ngày – '.$menu->date->format('d/m/Y').' (Thứ '.['Chủ Nhật', 'Hai', 'Ba', 'Tư', 'Năm', 'Sáu', 'Bảy'][$menu->date->dayOfWeek].')',
                'sub' => $menu->kitchen?->name.' · '.$menu->shift?->name.' · '.$menu->estimated_portions.' suất',
                'start_date' => $menu->date->toDateString(),
                'end_date' => $menu->date->toDateString(),
                'meta_company' => $menu->kitchen?->area?->name ?? 'Công ty Summit',
                'meta_info' => $menu->estimated_portions.' suất '.$menu->shift?->name,
                'status' => $menu->status,
                'date_raw' => $menu->date,
            ];
        })->filter()->values();

        return new LengthAwarePaginator($cards, $total, $this->perPage, $page, ['pageName' => 'page']);
    }

    /**
     * Nạp menu chi tiết cho toàn bộ nhóm của trang bằng 1 query rồi gom theo kitchen_id
     * (mỗi nhóm đã sort date desc) — tránh chạy 1 query/card như trước.
     *
     * @param  Collection<int, object>  $pageKeys
     * @return array<int, Collection<int, Menu>>
     */
    protected function loadRepresentativeMenus($pageKeys): array
    {
        if ($pageKeys->isEmpty()) {
            return [];
        }

        // Khoảng ngày bao trùm mọi nhóm của trang: từ ngày nhỏ nhất tới (ngày lớn nhất + 6)
        // để phủ trọn tuần của các card tuần.
        $dates = $pageKeys->map(fn ($key) => Carbon::parse($key->group_date));
        $rangeStart = $dates->min()->toDateString();
        $rangeEnd = $dates->max()->copy()->addDays(6)->toDateString();

        return Menu::with(['kitchen.area', 'shift'])
            ->whereIn('kitchen_id', $pageKeys->pluck('kitchen_id')->unique()->all())
            ->whereBetween('date', [$rangeStart, $rangeEnd])
            ->orderByDesc('date')
            ->get()
            ->groupBy('kitchen_id')
            ->all();
    }

    // ==========================================
    // WEEK MENU LOGIC
    // ==========================================
    public function loadWeekMenu($kitchenId, $startDate)
    {
        $this->weekKitchenId = $kitchenId;
        $this->weekStartDate = $startDate;
        $this->activeView = 'week';
        $this->weekEditReason = '';

        // Khởi tạo ma trận rỗng cho cả tuần (T2 -> CN) và các ca ăn
        $this->weekCells = [];
        $shifts = Shift::all();
        $start = Carbon::parse($startDate);

        // Nạp menu CẢ TUẦN (T2→CN) bằng 1 query rồi gom NHIỀU món theo (ngày, ca)
        $weekMenus = Menu::where('kitchen_id', $kitchenId)
            ->where('date', '>=', $start->toDateString())
            ->where('date', '<', $start->copy()->addDays(7)->toDateString())
            ->orderBy('id')
            ->get();

        $this->weekHasExistingMenus = $weekMenus->isNotEmpty();
        $this->weekHasEditableLockedMenus = $weekMenus->contains(
            fn (Menu $menu): bool => $menu->status === 'locked' && ! $menu->isPastLocked()
        );
        $this->weekHasPastLockedMenus = $weekMenus->contains(fn (Menu $menu): bool => $menu->isPastLocked());
        $this->weekStatus = $weekMenus
            ->sortByDesc(fn (Menu $menu): int => Menu::STATUS_ORDER[$menu->status] ?? -1)
            ->first()?->status ?? 'draft';

        $menusByDayShift = $weekMenus->groupBy(fn (Menu $m) => $m->date->toDateString().'|'.$m->shift_id);

        for ($d = 0; $d < 7; $d++) {
            $currentDate = $start->copy()->addDays($d)->toDateString();
            foreach ($shifts as $shift) {
                $menus = $menusByDayShift->get($currentDate.'|'.$shift->id, collect());

                $items = [];
                foreach ($menus as $menu) {
                    $items[] = ['recipe_id' => (string) $menu->recipe_id, 'portions' => $menu->estimated_portions];
                }

                // Ô rỗng vẫn giữ 1 dòng trống để user nhập nhanh (không bắt bấm + trước)
                $this->weekCells[$d][$shift->id] = $items ?: [['recipe_id' => '', 'portions' => 200]];
            }
        }
    }

    /** Thêm 1 dòng món trống vào ô (ngày, ca) của grid tuần. */
    public function addWeekDish(int $day, int $shiftId): void
    {
        $this->weekCells[$day][$shiftId][] = ['recipe_id' => '', 'portions' => 200];
    }

    /** Bỏ 1 dòng món khỏi ô; luôn chừa lại tối thiểu 1 dòng trống. */
    public function removeWeekDish(int $day, int $shiftId, int $index): void
    {
        unset($this->weekCells[$day][$shiftId][$index]);
        $this->weekCells[$day][$shiftId] = array_values($this->weekCells[$day][$shiftId]);

        if ($this->weekCells[$day][$shiftId] === []) {
            $this->weekCells[$day][$shiftId] = [['recipe_id' => '', 'portions' => 200]];
        }
    }

    /**
     * User không phải quản trị chỉ được thao tác trên bếp của mình — kitchenId từ Livewire là dữ liệu client.
     */
    protected function assertKitchenAccess($kitchenId): void
    {
        $user = auth()->user();
        if (! $user || $user->hasRole(['super_admin', 'Quản trị viên'])) {
            return;
        }

        $ownKitchenId = $user->currentKitchenId();
        abort_if($ownKitchenId && (int) $kitchenId !== (int) $ownKitchenId, 403, __('menu.errors.own_kitchen_only'));
    }

    /**
     * Cảnh báo LẶP MÓN so với 3 TUẦN (21 ngày) trước tuần đang lập (BA R33) — tính trên
     * chính grid tuần, để bếp thấy ngay khi chọn món trùng thực đơn vừa chạy.
     *
     * @return array<int, string> tên các món bị lặp
     */
    public function getWeekDuplicateWarnings(): array
    {
        $recipeIds = [];
        foreach ($this->weekCells as $byShift) {
            foreach ($byShift as $items) {
                foreach ($items as $item) {
                    $rid = (int) ($item['recipe_id'] ?? 0);
                    if ($rid > 0) {
                        $recipeIds[$rid] = true;
                    }
                }
            }
        }

        if ($recipeIds === []) {
            return [];
        }

        $weekStart = Carbon::parse($this->weekStartDate);

        return Menu::query()
            ->when($this->weekKitchenId, fn ($q) => $q->where('kitchen_id', $this->weekKitchenId))
            ->where('date', '>=', $weekStart->copy()->subDays(21)->toDateString())
            ->where('date', '<', $weekStart->toDateString())
            ->whereIn('recipe_id', array_keys($recipeIds))
            ->with('recipe')
            ->get()
            ->pluck('recipe.name')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function saveWeekMenu($status = null)
    {
        $this->assertKitchenAccess($this->weekKitchenId);

        if ($status) {
            $this->weekStatus = $status;
        }

        $this->validate([
            'weekKitchenId' => ['required', 'integer', 'exists:kitchens,id'],
            'weekStartDate' => ['required', 'date'],
            'weekStatus' => ['required', Rule::in(array_keys(Menu::STATUS_ORDER))],
            'weekCells.*.*.*.recipe_id' => ['nullable', 'integer', 'exists:recipes,id'],
            'weekCells.*.*.*.portions' => ['required', 'integer', 'min:1'],
        ], [
            'weekStatus.in' => __('menu.errors.invalid_status'),
        ]);

        $shifts = Shift::all();
        $start = Carbon::parse($this->weekStartDate);
        $skippedLocked = 0;

        $existingWeekMenus = Menu::query()
            ->where('kitchen_id', $this->weekKitchenId)
            ->where('date', '>=', $start->toDateString())
            ->where('date', '<', $start->copy()->addDays(7)->toDateString())
            ->get();

        if (! $this->authorizeAndGuardExistingMenus($existingWeekMenus, $this->weekStatus, $this->weekEditReason, 'weekEditReason')) {
            return;
        }

        DB::transaction(function () use ($shifts, $start, &$skippedLocked): void {
            for ($d = 0; $d < 7; $d++) {
                $currentDate = $start->copy()->addDays($d)->toDateString();
                foreach ($shifts as $shift) {
                    $cell = $this->weekCells[$d][$shift->id] ?? [];

                    // Danh sách món MUỐN có trong ô (dedupe recipe_id để không vi phạm
                    // unique (kitchen, date, shift, recipe)); giữ số suất theo dòng cuối cùng nhập.
                    $desired = [];
                    foreach ($cell as $item) {
                        $rid = (int) ($item['recipe_id'] ?? 0);
                        if ($rid > 0) {
                            $desired[$rid] = (int) ($item['portions'] ?? 200);
                        }
                    }

                    // Menu hiện có của ô, keyed theo recipe_id. Khoảng nửa mở [ngày, ngày+1)
                    // để khớp cả khi SQLite lưu date kèm '00:00:00' và vẫn dùng index trên MySQL.
                    $nextDate = Carbon::parse($currentDate)->addDay()->toDateString();
                    $existingByRecipe = Menu::where('kitchen_id', $this->weekKitchenId)
                        ->where('date', '>=', $currentDate)
                        ->where('date', '<', $nextDate)
                        ->where('shift_id', $shift->id)
                        ->get()
                        ->keyBy('recipe_id');

                    // 1) Thêm mới / cập nhật các món mong muốn
                    foreach ($desired as $recipeId => $portions) {
                        $existing = $existingByRecipe->get($recipeId);

                        if ($existing) {
                            if ($this->weekGuardSkips($existing)) {
                                $skippedLocked++;

                                continue;
                            }
                            $existing->auditReason = trim($this->weekEditReason) ?: null;
                            $existing->update([
                                'estimated_portions' => $portions,
                                'status' => $this->weekStatus,
                            ]);
                        } else {
                            abort_unless(MenuResource::canCreate(), 403);
                            Menu::create([
                                'kitchen_id' => $this->weekKitchenId,
                                'date' => $currentDate,
                                'shift_id' => $shift->id,
                                'recipe_id' => $recipeId,
                                'estimated_portions' => $portions,
                                'status' => $this->weekStatus,
                            ]);
                        }
                    }

                    // 2) Xóa các món đã bị gỡ khỏi ô (có trong DB nhưng không còn trong desired)
                    foreach ($existingByRecipe as $recipeId => $menu) {
                        if (isset($desired[$recipeId])) {
                            continue;
                        }
                        if ($this->weekGuardSkips($menu)) {
                            $skippedLocked++;

                            continue;
                        }
                        // Xóa qua model instance để hook audit (MenuAuditLog) vẫn chạy
                        $menu->auditReason = trim($this->weekEditReason) ?: null;
                        $menu->delete();
                    }
                }
            }
        });

        session()->flash('message', $skippedLocked > 0
            ? __('menu.notifications.week_saved_with_skipped', ['count' => $skippedLocked])
            : __('menu.notifications.week_saved'));
        $this->switchView('list');
    }

    /**
     * Guard vòng đời cho form TUẦN — ủy quyền về Menu::editBlockReason.
     */
    protected function weekGuardSkips(Menu $menu): bool
    {
        return $menu->editBlockReason($this->weekStatus, $this->weekEditReason) !== null;
    }

    public function resetWeekForm()
    {
        $this->weekStatus = 'draft';
        $this->weekCells = [];
        $this->weekEditReason = '';
        $this->weekHasExistingMenus = false;
        $this->weekHasEditableLockedMenus = false;
        $this->weekHasPastLockedMenus = false;
    }

    // ==========================================
    // DAY MENU LOGIC
    // ==========================================
    public function loadDayMenu($kitchenId, $date)
    {
        $this->dayKitchenId = $kitchenId;
        $this->dayDate = $date;
        $this->activeView = 'day';
        $this->dayEditReason = '';

        $this->dayItems = [];
        $shifts = Shift::all();

        // 1 query cho cả ngày, nhóm theo ca — thay vì mỗi ca một query
        $dayMenus = Menu::where('kitchen_id', $kitchenId)
            ->where('date', '>=', $date)
            ->where('date', '<', Carbon::parse($date)->addDay()->toDateString())
            ->get();

        $this->dayHasExistingMenus = $dayMenus->isNotEmpty();
        $this->dayHasEditableLockedMenus = $dayMenus->contains(
            fn (Menu $menu): bool => $menu->status === 'locked' && ! $menu->isPastLocked()
        );
        $this->dayHasPastLockedMenus = $dayMenus->contains(fn (Menu $menu): bool => $menu->isPastLocked());
        $this->dayStatus = $dayMenus
            ->sortByDesc(fn (Menu $menu): int => Menu::STATUS_ORDER[$menu->status] ?? -1)
            ->first()?->status ?? 'draft';

        $menusByShift = $dayMenus->groupBy('shift_id');

        foreach ($shifts as $shift) {
            $menus = $menusByShift->get($shift->id, collect());

            $recipes = [];
            foreach ($menus as $m) {
                $recipes[] = [
                    'menu_id' => $m->id,
                    'recipe_id' => $m->recipe_id,
                    'portions' => $m->estimated_portions,
                ];
            }

            // Nếu chưa có món nào, tạo một dòng trống để chọn
            if (empty($recipes)) {
                $recipes[] = [
                    'menu_id' => null,
                    'recipe_id' => '',
                    'portions' => 1,
                ];
            }

            $this->dayItems[$shift->id] = [
                'shift_name' => $shift->name,
                'recipes' => $recipes,
            ];
        }
    }

    public function addRecipeToShift($shiftId)
    {
        $this->dayItems[$shiftId]['recipes'][] = [
            'menu_id' => null,
            'recipe_id' => '',
            'portions' => 1,
        ];
    }

    public function removeRecipeFromShift($shiftId, $index)
    {
        $menuId = $this->dayItems[$shiftId]['recipes'][$index]['menu_id'] ?? null;
        if ($menuId) {
            // menu_id là dữ liệu client — phải kiểm tra quyền xóa + quyền bếp + trạng thái trước khi destroy
            $menu = Menu::find($menuId);
            if ($menu) {
                abort_unless(MenuResource::canDelete($menu), 403);
                $this->assertKitchenAccess($menu->kitchen_id);

                $blocked = $menu->editBlockReason($menu->status, $this->dayEditReason);
                if ($blocked !== null) {
                    $this->addError('dayEditReason', match ($blocked) {
                        'past' => __('menu.errors.past_locked_delete'),
                        'need_reason' => __('menu.errors.audit_reason_required'),
                        default => __('menu.errors.invalid_status'),
                    });

                    return;
                }

                $menu->auditReason = trim($this->dayEditReason) ?: null;
                $menu->delete();
            }
        }
        unset($this->dayItems[$shiftId]['recipes'][$index]);
        $this->dayItems[$shiftId]['recipes'] = array_values($this->dayItems[$shiftId]['recipes']);
    }

    public function saveDayMenu($status = null)
    {
        $this->assertKitchenAccess($this->dayKitchenId);

        if ($status) {
            $this->dayStatus = $status;
        }

        $this->validate([
            'dayKitchenId' => ['required', 'integer', 'exists:kitchens,id'],
            'dayDate' => ['required', 'date'],
            'dayStatus' => ['required', Rule::in(array_keys(Menu::STATUS_ORDER))],
            'dayItems.*.recipes.*.recipe_id' => ['nullable', 'integer', 'exists:recipes,id'],
            'dayItems.*.recipes.*.portions' => ['required', 'integer', 'min:1'],
        ], [
            'dayStatus.in' => __('menu.errors.invalid_status'),
        ]);

        $existingDayMenus = Menu::query()
            ->where('kitchen_id', $this->dayKitchenId)
            ->where('date', '>=', $this->dayDate)
            ->where('date', '<', Carbon::parse($this->dayDate)->addDay()->toDateString())
            ->get();

        if (! $this->authorizeAndGuardExistingMenus($existingDayMenus, $this->dayStatus, $this->dayEditReason, 'dayEditReason')) {
            return;
        }

        $skippedLocked = 0;

        DB::transaction(function () use (&$skippedLocked): void {
            foreach ($this->dayItems as $shiftId => $data) {
                foreach ($data['recipes'] as $item) {
                    $recipeId = $item['recipe_id'];
                    $portions = $item['portions'];
                    $menuId = $item['menu_id'];

                    if (! $recipeId) {
                        continue;
                    }

                    if ($menuId) {
                        // Chỉ nhận menu thuộc đúng bếp/ngày đang thao tác (menu_id là dữ liệu client)
                        $menu = Menu::whereKey($menuId)
                            ->where('kitchen_id', $this->dayKitchenId)
                            ->where('date', '>=', $this->dayDate)
                            ->where('date', '<', Carbon::parse($this->dayDate)->addDay()->toDateString())
                            ->where('shift_id', $shiftId)
                            ->first();

                        if ($menu) {
                            // Guard vòng đời dùng chung (khóa quá khứ / không hạ cấp / lý do khi sửa đã chốt)
                            if ($menu->editBlockReason($this->dayStatus, $this->dayEditReason) !== null) {
                                $skippedLocked++;

                                continue;
                            }

                            $menu->auditReason = trim($this->dayEditReason) ?: null;
                            $menu->update([
                                'recipe_id' => $recipeId,
                                'estimated_portions' => $portions,
                                'status' => $this->dayStatus,
                            ]);
                        }
                    } else {
                        // Món này có thể đã tồn tại trong ca (thêm trùng trên form, hoặc tạo từ màn khác):
                        // update dòng cũ thay vì INSERT để không vỡ unique (kitchen, date, shift, recipe)
                        $duplicate = Menu::where('kitchen_id', $this->dayKitchenId)
                            ->where('date', '>=', $this->dayDate)
                            ->where('date', '<', Carbon::parse($this->dayDate)->addDay()->toDateString())
                            ->where('shift_id', $shiftId)
                            ->where('recipe_id', $recipeId)
                            ->first();

                        if ($duplicate) {
                            if ($duplicate->editBlockReason($this->dayStatus, $this->dayEditReason) !== null) {
                                $skippedLocked++;

                                continue;
                            }

                            $duplicate->auditReason = trim($this->dayEditReason) ?: null;
                            $duplicate->update([
                                'estimated_portions' => $portions,
                                'status' => $this->dayStatus,
                            ]);
                        } else {
                            abort_unless(MenuResource::canCreate(), 403);
                            Menu::create([
                                'kitchen_id' => $this->dayKitchenId,
                                'date' => $this->dayDate,
                                'shift_id' => $shiftId,
                                'recipe_id' => $recipeId,
                                'estimated_portions' => $portions,
                                'status' => $this->dayStatus,
                            ]);
                        }
                    }
                }
            }
        });

        session()->flash('message', $skippedLocked > 0
            ? __('menu.notifications.day_saved_with_skipped', ['count' => $skippedLocked])
            : __('menu.notifications.day_saved'));
        $this->switchView('list');
    }

    /**
     * Kiểm tra quyền và state machine trước transaction để không lưu dở một phần rồi báo thành công.
     *
     * @param  Collection<int, Menu>  $menus
     */
    protected function authorizeAndGuardExistingMenus(Collection $menus, string $targetStatus, string $reason, string $errorKey): bool
    {
        foreach ($menus as $menu) {
            abort_unless(MenuResource::canEdit($menu), 403);

            $blocked = $menu->editBlockReason($targetStatus, $reason);
            if ($blocked === null) {
                continue;
            }

            $this->addError($blocked === 'need_reason' ? $errorKey : str_replace('EditReason', 'Status', $errorKey), match ($blocked) {
                'past' => __('menu.errors.past_locked_edit'),
                'downgrade' => __('menu.errors.status_downgrade'),
                'need_reason' => __('menu.errors.audit_reason_required'),
                default => __('menu.errors.invalid_status'),
            });

            return false;
        }

        return true;
    }

    public function resetDayForm()
    {
        $this->dayStatus = 'draft';
        $this->dayItems = [];
        $this->dayEditReason = '';
        $this->dayHasExistingMenus = false;
        $this->dayHasEditableLockedMenus = false;
        $this->dayHasPastLockedMenus = false;
    }

    // ==========================================
    // HELPERS & LIST DATA
    // ==========================================
    public function getKitchens()
    {
        return Kitchen::orderBy('name')->get();
    }

    public function getShifts()
    {
        return Shift::all();
    }

    public function getRecipes()
    {
        return Recipe::orderBy('name')->get();
    }

    public function getLockedMenus()
    {
        return Menu::with('kitchen')
            ->where('status', 'locked')
            ->select('kitchen_id', 'date')
            ->distinct()
            ->get();
    }
}
