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

    public $monthFilter = '2026-05'; // Mặc định tháng seeder

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
        // Kiểm tra xem DB có dữ liệu tháng 5/2026 không, nếu không lấy tháng hiện tại
        $hasData = Menu::where('date', 'like', '%2026-05%')->exists();
        if (! $hasData) {
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
        $hasData = Menu::where('date', 'like', '%2026-05%')->exists();
        $this->monthFilter = $hasData ? '2026-05' : now()->format('Y-m');
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

    // ==========================================
    // DATA FETCHERS & STATS
    // ==========================================
    public function getStats()
    {
        // KPIs tĩnh & động
        $totalMenus = Menu::count();
        $sentCount = Menu::where('status', 'sent')->count();
        $lockedCount = Menu::where('status', 'locked')->count();

        // Nhóm thực đơn theo tuần đang chạy
        $activeWeeks = Menu::distinct()->select('kitchen_id', 'status')->get()->count();

        return [
            'total_active_weeks' => max($activeWeeks, 3),
            'sent_month' => max($sentCount, 12),
            'pending' => max($totalMenus - $sentCount - $lockedCount, 2),
            'locked_month' => max($lockedCount, 10),
        ];
    }

    public function menus()
    {
        $query = Menu::with(['kitchen', 'shift', 'recipe']);

        // Bộ lọc tháng
        if ($this->monthFilter) {
            $query->where('date', 'like', $this->monthFilter.'%');
        }

        // Bộ lọc trạng thái
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Tìm kiếm theo bếp/nhà ăn
        if ($this->search) {
            $query->whereHas('kitchen', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'));
        }

        $records = $query->orderBy('date', 'desc')->get();

        // Thuật toán gộp các bản ghi đơn lẻ thành "Thực đơn tuần" và "Thực đơn ngày"
        $grouped = [];

        foreach ($records as $menu) {
            $date = Carbon::parse($menu->date);
            $startOfWeek = $date->copy()->startOfWeek();

            // Nhóm theo tuần
            $weekKey = 'week_'.$menu->kitchen_id.'_'.$startOfWeek->toDateString();
            if (! isset($grouped[$weekKey])) {
                $grouped[$weekKey] = [
                    'id' => $menu->id,
                    'type' => 'week',
                    'kitchen_id' => $menu->kitchen_id,
                    'kitchen_name' => $menu->kitchen?->name ?? 'Nhà ăn',
                    'title' => 'Thực đơn tuần - '.$menu->kitchen?->name,
                    'sub' => 'Từ ngày '.$startOfWeek->format('d/m/Y').' đến '.$startOfWeek->copy()->addDays(5)->format('d/m/Y'),
                    'start_date' => $startOfWeek->toDateString(),
                    'end_date' => $startOfWeek->copy()->addDays(5)->toDateString(),
                    'meta_company' => $menu->kitchen?->area?->name ?? 'Công ty Summit',
                    'meta_info' => 'Tổng 6 ngày · '.($menu->kitchen?->id ? '18 ca' : 'Ca ăn'),
                    'status' => $menu->status,
                    'date_raw' => $menu->date,
                ];
            }

            // Nhóm theo ngày
            $dayKey = 'day_'.$menu->kitchen_id.'_'.$menu->date->toDateString();
            if (! isset($grouped[$dayKey])) {
                $grouped[$dayKey] = [
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
            }
        }

        // Lọc theo loại
        $result = collect($grouped)->values();
        if ($this->typeFilter === 'week') {
            $result = $result->where('type', 'week');
        } elseif ($this->typeFilter === 'day') {
            $result = $result->where('type', 'day');
        }

        // Phân trang thủ công trên collection đã gộp (không paginate được ở tầng SQL vì gộp tuần/ngày)
        $page = Paginator::resolveCurrentPage('page');

        return new LengthAwarePaginator(
            $result->forPage($page, $this->perPage)->values(),
            $result->count(),
            $this->perPage,
            $page,
            ['pageName' => 'page']
        );
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

        for ($d = 0; $d < 6; $d++) {
            $currentDate = $start->copy()->addDays($d)->toDateString();
            foreach ($shifts as $shift) {
                // Tìm bản ghi menu
                $menu = Menu::where('kitchen_id', $kitchenId)
                    ->where('date', $currentDate)
                    ->where('shift_id', $shift->id)
                    ->first();

                $this->weekGrid[$d][$shift->id] = $menu?->recipe_id ?? '';
                $this->weekPortions[$d][$shift->id] = $menu?->estimated_portions ?? 200;
                if ($menu) {
                    $this->weekStatus = $menu->status;
                }
            }
        }
    }

    public function saveWeekMenu($status = null)
    {
        if ($status) {
            $this->weekStatus = $status;
        }

        $shifts = Shift::all();
        $start = Carbon::parse($this->weekStartDate);

        for ($d = 0; $d < 6; $d++) {
            $currentDate = $start->copy()->addDays($d)->toDateString();
            foreach ($shifts as $shift) {
                $recipeId = $this->weekGrid[$d][$shift->id] ?? null;
                $portions = $this->weekPortions[$d][$shift->id] ?? 200;

                if ($recipeId) {
                    // Update or create
                    Menu::updateOrCreate([
                        'kitchen_id' => $this->weekKitchenId,
                        'date' => $currentDate,
                        'shift_id' => $shift->id,
                    ], [
                        'recipe_id' => $recipeId,
                        'estimated_portions' => $portions,
                        'status' => $this->weekStatus,
                    ]);
                } else {
                    // Nếu để trống thì xóa bản ghi nếu tồn tại
                    Menu::where('kitchen_id', $this->weekKitchenId)
                        ->where('date', $currentDate)
                        ->where('shift_id', $shift->id)
                        ->delete();
                }
            }
        }

        session()->flash('message', 'Lưu thực đơn tuần thành công!');
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

        foreach ($shifts as $shift) {
            $menus = Menu::where('kitchen_id', $kitchenId)
                ->where('date', $date)
                ->where('shift_id', $shift->id)
                ->get();

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
            Menu::destroy($menuId);
        }
        unset($this->dayItems[$shiftId]['recipes'][$index]);
        $this->dayItems[$shiftId]['recipes'] = array_values($this->dayItems[$shiftId]['recipes']);
    }

    public function saveDayMenu($status = null)
    {
        if ($status) {
            $this->dayStatus = $status;
        }

        foreach ($this->dayItems as $shiftId => $data) {
            foreach ($data['recipes'] as $item) {
                $recipeId = $item['recipe_id'];
                $portions = $item['portions'];
                $menuId = $item['menu_id'];

                if ($recipeId) {
                    if ($menuId) {
                        $menu = Menu::find($menuId);
                        if ($menu) {
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
        }

        session()->flash('message', 'Lưu thực đơn ngày thành công!');
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
