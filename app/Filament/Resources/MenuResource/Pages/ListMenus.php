<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\Shift;
use Filament\Resources\Pages\Page;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

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

    public $weekGrid = []; // Grid matrix [day_index][shift_id] = recipe_id

    public $weekPortions = []; // [day_index][shift_id] = portions count

    // FORM DAY STATES
    public $dayKitchenId;

    public $dayDate;

    public $dayStatus = 'draft';

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
        $months = Menu::query()
            ->selectRaw("DISTINCT DATE_FORMAT(date, '%Y-%m') AS ym")
            ->orderByDesc('ym')
            ->pluck('ym')
            ->push(now()->format('Y-m'))
            ->unique()
            ->sortDesc();

        return $months->mapWithKeys(function (string $ym): array {
            [$y, $m] = explode('-', $ym);

            return [$ym => "Tháng {$m}/{$y}"];
        })->all();
    }

    /**
     * Xuất CSV thực đơn thật (thay nút alert() giả trước đây). Có thể giới hạn phạm vi
     * theo bếp + khoảng ngày (dùng cho nút xuất trên từng card tuần/ngày).
     */
    public function exportMenus(?int $kitchenId = null, ?string $from = null, ?string $to = null)
    {
        abort_unless(MenuResource::canViewAny(), 403);

        $query = Menu::with(['kitchen', 'shift', 'recipe'])->orderBy('date')->orderBy('shift_id');

        if ($kitchenId && $from) {
            $query->where('kitchen_id', $kitchenId)
                ->whereBetween('date', [$from, $to ?: $from]);
        } elseif ($this->monthFilter) {
            $query->where('date', 'like', $this->monthFilter.'%');
        }

        $records = $query->get();
        $filename = 'thuc_don_'.($from ?: $this->monthFilter ?: now()->format('Y-m')).'.csv';

        return response()->streamDownload(function () use ($records): void {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, ['STT', 'Bếp ăn', 'Ngày', 'Thứ', 'Ca', 'Món ăn', 'Số suất', 'Trạng thái']);

            $statusLabels = ['draft' => 'Nháp', 'sent' => 'Đã gửi khách', 'locked' => 'Đã chốt'];
            foreach ($records as $i => $menu) {
                fputcsv($output, [
                    $i + 1,
                    $menu->kitchen?->name,
                    $menu->date->format('d/m/Y'),
                    'Thứ '.['Chủ Nhật', 'Hai', 'Ba', 'Tư', 'Năm', 'Sáu', 'Bảy'][$menu->date->dayOfWeek],
                    $menu->shift?->name,
                    $menu->recipe?->name,
                    $menu->estimated_portions,
                    $statusLabels[$menu->status] ?? $menu->status,
                ]);
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Xuất tuần đang soạn trên form (T2 → T7). */
    public function exportWeekForm()
    {
        $start = Carbon::parse($this->weekStartDate);

        return $this->exportMenus((int) $this->weekKitchenId, $start->toDateString(), $start->copy()->addDays(5)->toDateString());
    }

    /** Xuất ngày đang soạn trên form. */
    public function exportDayForm()
    {
        return $this->exportMenus((int) $this->dayKitchenId, $this->dayDate, $this->dayDate);
    }

    // ==========================================
    // DATA FETCHERS & STATS
    // ==========================================
    public function getStats()
    {
        // KPI thật theo tháng đang lọc (1 query aggregate), không dùng hằng số demo
        $monthPrefix = $this->monthFilter ?: now()->format('Y-m');
        $counts = Menu::query()
            ->where('date', '>=', $monthPrefix.'-01')
            ->where('date', '<', Carbon::parse($monthPrefix.'-01')->addMonth()->toDateString())
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) AS sent")
            ->selectRaw("SUM(CASE WHEN status = 'locked' THEN 1 ELSE 0 END) AS locked")
            ->selectRaw('COUNT(DISTINCT CONCAT(kitchen_id, "-", YEARWEEK(date, 1))) AS active_weeks')
            ->first();

        return [
            'total_active_weeks' => (int) $counts->active_weeks,
            'sent_month' => (int) $counts->sent,
            'pending' => max(0, (int) $counts->total - (int) $counts->sent - (int) $counts->locked),
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
        // Tuần = thứ 2 đầu tuần (WEEKDAY: 0 = thứ 2); ngày = chính date.
        $weekKeys = $this->filteredMenuQuery()
            ->selectRaw("'week' AS card_type, kitchen_id, DATE_SUB(date, INTERVAL WEEKDAY(date) DAY) AS group_date")
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

        // Bước 2: chỉ nạp chi tiết menu cho ~perPage nhóm của trang này để dựng card
        $cards = $pageKeys->map(function ($key) {
            $isWeek = $key->card_type === 'week';
            $start = Carbon::parse($key->group_date);
            $end = $isWeek ? $start->copy()->addDays(5) : $start->copy();

            $menu = Menu::with(['kitchen.area', 'shift'])
                ->where('kitchen_id', $key->kitchen_id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->orderByDesc('date')
                ->first();

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
                    'meta_info' => 'Tổng 6 ngày · '.($menu->kitchen?->id ? '18 ca' : 'Ca ăn'),
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

    // ==========================================
    // WEEK MENU LOGIC
    // ==========================================
    public function loadWeekMenu($kitchenId, $startDate)
    {
        $this->weekKitchenId = $kitchenId;
        $this->weekStartDate = $startDate;
        $this->activeView = 'week';

        // Khởi tạo ma trận rỗng cho 6 ngày (T2 -> T7) và các ca ăn
        $this->weekGrid = [];
        $this->weekPortions = [];
        $shifts = Shift::all();
        $start = Carbon::parse($startDate);

        // Nạp menu CẢ TUẦN bằng 1 query rồi tra theo (ngày, ca) — trước đây 6 ngày × số ca query lẻ
        $menusByDayShift = Menu::where('kitchen_id', $kitchenId)
            ->whereBetween('date', [$start->toDateString(), $start->copy()->addDays(5)->toDateString()])
            ->get()
            ->keyBy(fn (Menu $m) => $m->date->toDateString().'|'.$m->shift_id);

        for ($d = 0; $d < 6; $d++) {
            $currentDate = $start->copy()->addDays($d)->toDateString();
            foreach ($shifts as $shift) {
                $menu = $menusByDayShift->get($currentDate.'|'.$shift->id);

                $this->weekGrid[$d][$shift->id] = $menu?->recipe_id ?? '';
                $this->weekPortions[$d][$shift->id] = $menu?->estimated_portions ?? 200;
                if ($menu) {
                    $this->weekStatus = $menu->status;
                }
            }
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
        abort_if($ownKitchenId && (int) $kitchenId !== (int) $ownKitchenId, 403, 'Bạn chỉ có thể thao tác trên thực đơn của bếp mình.');
    }

    public function saveWeekMenu($status = null)
    {
        abort_unless(MenuResource::canCreate(), 403);
        $this->assertKitchenAccess($this->weekKitchenId);

        if ($status) {
            $this->weekStatus = $status;
        }

        $shifts = Shift::all();
        $start = Carbon::parse($this->weekStartDate);
        $skippedLocked = 0;

        DB::transaction(function () use ($shifts, $start, &$skippedLocked): void {
            for ($d = 0; $d < 6; $d++) {
                $currentDate = $start->copy()->addDays($d)->toDateString();
                foreach ($shifts as $shift) {
                    $recipeId = $this->weekGrid[$d][$shift->id] ?? null;
                    $portions = $this->weekPortions[$d][$shift->id] ?? 200;

                    $existing = Menu::where('kitchen_id', $this->weekKitchenId)
                        ->where('date', $currentDate)
                        ->where('shift_id', $shift->id)
                        ->first();

                    // Menu ĐÃ CHỐT không bị ghi đè/xóa ngầm khi lưu với trạng thái thấp hơn
                    if ($existing && $existing->status === 'locked' && $this->weekStatus !== 'locked') {
                        $skippedLocked++;

                        continue;
                    }

                    if ($recipeId) {
                        Menu::updateOrCreate([
                            'kitchen_id' => $this->weekKitchenId,
                            'date' => $currentDate,
                            'shift_id' => $shift->id,
                        ], [
                            'recipe_id' => $recipeId,
                            'estimated_portions' => $portions,
                            'status' => $this->weekStatus,
                        ]);
                    } elseif ($existing) {
                        // Xóa qua model instance để hook audit (MenuAuditLog) vẫn chạy
                        $existing->delete();
                    }
                }
            }
        });

        session()->flash('message', 'Lưu thực đơn tuần thành công!'.($skippedLocked > 0 ? " ({$skippedLocked} ca đã chốt được giữ nguyên)" : ''));
        $this->switchView('list');
    }

    public function resetWeekForm()
    {
        $this->weekStatus = 'draft';
        $this->weekGrid = [];
        $this->weekPortions = [];
    }

    // ==========================================
    // DAY MENU LOGIC
    // ==========================================
    public function loadDayMenu($kitchenId, $date)
    {
        $this->dayKitchenId = $kitchenId;
        $this->dayDate = $date;
        $this->activeView = 'day';

        $this->dayItems = [];
        $shifts = Shift::all();

        // 1 query cho cả ngày, nhóm theo ca — thay vì mỗi ca một query
        $menusByShift = Menu::where('kitchen_id', $kitchenId)
            ->where('date', $date)
            ->get()
            ->groupBy('shift_id');

        foreach ($shifts as $shift) {
            $menus = $menusByShift->get($shift->id, collect());

            $recipes = [];
            foreach ($menus as $m) {
                $recipes[] = [
                    'menu_id' => $m->id,
                    'recipe_id' => $m->recipe_id,
                    'portions' => $m->estimated_portions,
                ];
                $this->dayStatus = $m->status;
            }

            // Nếu chưa có món nào, tạo một dòng trống để chọn
            if (empty($recipes)) {
                $recipes[] = [
                    'menu_id' => null,
                    'recipe_id' => '',
                    'portions' => 150,
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
            'portions' => 150,
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

                if ($menu->status === 'locked') {
                    session()->flash('error', 'Không thể xóa món thuộc thực đơn đã chốt!');

                    return;
                }

                $menu->delete();
            }
        }
        unset($this->dayItems[$shiftId]['recipes'][$index]);
        $this->dayItems[$shiftId]['recipes'] = array_values($this->dayItems[$shiftId]['recipes']);
    }

    public function saveDayMenu($status = null)
    {
        abort_unless(MenuResource::canCreate(), 403);
        $this->assertKitchenAccess($this->dayKitchenId);

        if ($status) {
            $this->dayStatus = $status;
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
                            ->first();

                        if ($menu) {
                            // Không hạ cấp menu đã chốt về trạng thái thấp hơn
                            if ($menu->status === 'locked' && $this->dayStatus !== 'locked') {
                                $skippedLocked++;

                                continue;
                            }

                            $menu->update([
                                'recipe_id' => $recipeId,
                                'estimated_portions' => $portions,
                                'status' => $this->dayStatus,
                            ]);
                        }
                    } else {
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
        });

        session()->flash('message', 'Lưu thực đơn ngày thành công!'.($skippedLocked > 0 ? " ({$skippedLocked} món đã chốt được giữ nguyên)" : ''));
        $this->switchView('list');
    }

    public function resetDayForm()
    {
        $this->dayStatus = 'draft';
        $this->dayItems = [];
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
