<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Exports\MenuExport;
use App\Filament\Resources\MenuResource;
use App\Filament\Resources\ShiftResource;
use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\RecipeType;
use App\Models\Shift;
use App\Models\WeekMenu;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
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
    public $kitchenFilter = '';

    public $typeFilter = ''; // 'week' hoặc 'day' hoặc ''

    public $statusFilter = ''; // 'draft', 'sent', 'locked'

    public $weekFilter = '';

    public $dayFilter = '';

    public $monthFilter = '';

    public $searchFilter = '';

    public int $perPage = 10;

    // FORM WEEK STATES
    public $weekKitchenId;

    public $weekDateFrom;

    public $weekDateTo;

    public $weekStatus = 'draft';

    // Ma trận grid tuần: mỗi ô [day_index][shift_id] là DANH SÁCH món
    // (mỗi phần tử ['recipe_id' => , 'portions' => ]) — cho phép nhiều món/ô qua nút (+).
    public array $weekCells = [];

    public array $selectedShifts = []; // Danh sách shift_id được tích chọn hiển thị trên ma trận tuần

    public string $weekEditReason = ''; // Lý do sửa — bắt buộc khi ghi đè thực đơn ĐÃ CHỐT

    public bool $weekHasExistingMenus = false;

    public bool $weekHasEditableLockedMenus = false;

    public bool $weekHasPastLockedMenus = false;

    public bool $isEditingWeek = false;

    public string $mode = ''; // 'create' hoặc 'edit'

    public $weekMenuId = null; // ID của WeekMenu khi chỉnh sửa

    // FORM DAY STATES
    public $dayKitchenId;

    public $dayDate;

    public $dayStatus = 'draft';

    public string $dayEditReason = ''; // Lý do sửa — bắt buộc khi ghi đè thực đơn ĐÃ CHỐT

    public bool $dayHasExistingMenus = false;

    public bool $dayHasEditableLockedMenus = false;

    public bool $dayHasPastLockedMenus = false;

    public bool $isEditingDay = false;

    public $dayItems = []; // Array of shifts, each containing recipes selected

    protected $queryString = [
        'activeView' => ['except' => 'list'],
        'mode' => ['except' => ''],
        'weekMenuId' => ['except' => null],
        'kitchenFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'weekFilter' => ['except' => ''],
        'dayFilter' => ['except' => ''],
        'monthFilter' => ['except' => ''],
        'searchFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->normalizeListFilters();

        $firstKitchenId = $this->getKitchens()->first()?->id;
        $this->weekKitchenId = $firstKitchenId;
        $this->weekDateFrom = now()->startOfWeek()->toDateString();
        $this->weekDateTo = now()->endOfWeek()->toDateString();
        $this->dayKitchenId = $firstKitchenId;
        $this->dayDate = now()->toDateString();

        $reqMode = request()->query('mode');
        $reqWeekMenuId = request()->query('weekMenuId');

        if ($reqWeekMenuId && ($wm = WeekMenu::find($reqWeekMenuId))) {
            $this->activeView = 'week';
            $this->weekMenuId = $wm->id;
            $this->mode = 'edit';
            $this->weekKitchenId = $wm->kitchen_id;
            $this->weekDateFrom = $wm->date_from;
            $this->weekDateTo = $wm->date_to;
            $this->loadWeekMenu($wm->kitchen_id, $wm->date_from, $wm->date_to, true);
        } elseif ($this->activeView === 'week' && $firstKitchenId) {
            $isEditing = $reqMode === 'edit';
            $this->loadWeekMenu($firstKitchenId, $this->weekDateFrom, $this->weekDateTo, $isEditing);
        } elseif ($this->activeView === 'day' && $firstKitchenId) {
            $isEditing = $reqMode === 'edit';
            $this->loadDayMenu($firstKitchenId, $this->dayDate, $isEditing);
        }
    }

    public function switchView($view)
    {
        $this->activeView = $view;
        if ($view === 'list') {
            $this->mode = '';
            $this->weekMenuId = null;
            $this->resetWeekForm();
            $this->resetDayForm();
        } elseif ($view === 'week') {
            $this->mode = 'create';
            $this->weekMenuId = null;
            $this->loadWeekMenu(
                $this->weekKitchenId ?? $this->getKitchens()->first()?->id,
                $this->weekDateFrom ?? now()->startOfWeek()->toDateString(),
                $this->weekDateTo ?? now()->endOfWeek()->toDateString(),
                false
            );
        } elseif ($view === 'day') {
            $this->mode = 'create';
            $this->weekMenuId = null;
            $this->loadDayMenu($this->dayKitchenId ?? $this->getKitchens()->first()?->id, $this->dayDate ?? now()->toDateString(), false);
        }
    }

    public function resetFilters()
    {
        $this->kitchenFilter = $this->canChooseKitchen()
            ? ''
            : (string) (auth()->user()?->currentKitchenId() ?? '');
        $this->typeFilter = '';
        $this->statusFilter = '';
        $this->weekFilter = '';
        $this->dayFilter = '';
        $this->monthFilter = '';
        $this->searchFilter = '';
        $this->resetPage();
    }

    public function updatedKitchenFilter(): void
    {
        if ($this->kitchenFilter !== '' && ! $this->getKitchens()->contains('id', (int) $this->kitchenFilter)) {
            $this->kitchenFilter = '';
        }

        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        if (! in_array($this->typeFilter, ['', 'week', 'day'], true)) {
            $this->typeFilter = '';
        }

        if ($this->typeFilter !== 'week') {
            $this->weekFilter = '';
        }
        if ($this->typeFilter !== 'day') {
            $this->dayFilter = '';
        }

        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        if (! in_array($this->statusFilter, ['', ...array_keys(Menu::STATUS_ORDER)], true)) {
            $this->statusFilter = '';
        }

        $this->resetPage();
    }

    public function updatedWeekFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDayFilter(): void
    {
        $this->resetPage();
    }

    public function updatedMonthFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearchFilter(): void
    {
        $this->resetPage();
    }

    public function getKitchenOptions(): array
    {
        return $this->getKitchens()->map(fn (Kitchen $kitchen): array => [
            'value' => (string) $kitchen->id,
            'label' => $kitchen->name,
            'sub' => $kitchen->area?->name ?? '',
        ])->all();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected function normalizeListFilters(): void
    {
        if (! in_array($this->typeFilter, ['', 'week', 'day'], true)) {
            $this->typeFilter = '';
        }
        if (! in_array($this->statusFilter, ['', ...array_keys(Menu::STATUS_ORDER)], true)) {
            $this->statusFilter = '';
        }
        if ($this->weekFilter !== '' && $this->selectedWeekRange() === null) {
            $this->weekFilter = '';
        }
        if ($this->dayFilter !== '' && ! $this->isValidDate($this->dayFilter)) {
            $this->dayFilter = '';
        }
        if ($this->monthFilter !== '' && ! preg_match('/^\d{4}-\d{2}$/', $this->monthFilter)) {
            $this->monthFilter = '';
        }

        if (! $this->canChooseKitchen()) {
            $this->kitchenFilter = (string) (auth()->user()?->currentKitchenId() ?? '');

            return;
        }

        if ($this->kitchenFilter !== '' && ! $this->getKitchens()->contains('id', (int) $this->kitchenFilter)) {
            $this->kitchenFilter = '';
        }
    }

    /**
     * @return array{0: string, 1: string}|null [Thứ Hai, Thứ Hai tuần kế tiếp)
     */
    protected function selectedWeekRange(): ?array
    {
        if (! preg_match('/^(\d{4})-W(\d{2})$/', (string) $this->weekFilter, $matches)) {
            return null;
        }

        $year = (int) $matches[1];
        $week = (int) $matches[2];
        if ($week < 1 || $week > 53) {
            return null;
        }

        $start = Carbon::now()->setISODate($year, $week)->startOfDay();
        if ($start->isoWeekYear !== $year || $start->isoWeek !== $week) {
            return null;
        }

        return [$start->toDateString(), $start->copy()->addWeek()->toDateString()];
    }

    protected function isValidDate(string $date): bool
    {
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
            return false;
        }

        return checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1]);
    }

    public function canChooseKitchen(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->hasRole(['super_admin', 'Quản trị viên', 'Quản lý bếp']) || $this->getKitchens()->count() > 1;
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

        $query = $this->scopedMenuQuery()
            ->with(['kitchen', 'shift', 'recipe'])
            ->orderBy('date')
            ->orderBy('shift_id');

        if ($kitchenId && $from) {
            $query->where('kitchen_id', $kitchenId)
                ->where('date', '>=', $from)
                ->where('date', '<', Carbon::parse($to ?: $from)->addDay()->toDateString());
            $subtitle = 'Từ '.Carbon::parse($from)->format('d/m/Y').' đến '.Carbon::parse($to ?: $from)->format('d/m/Y');
            $isSingleDay = ($to ?: $from) === $from;
        } else {
            $query = $this->filteredMenuQuery()
                ->with(['kitchen', 'shift', 'recipe'])
                ->orderBy('date')
                ->orderBy('shift_id');
            $subtitle = __('menu.export.filtered_list');
            $isSingleDay = false;
        }

        // Xuất .xlsx thật qua Laravel Excel — phân định rõ thực đơn ngày / thực đơn tuần và đa ngôn ngữ VI/EN.
        $isEn = app()->getLocale() === 'en';
        if ($isSingleDay) {
            $prefix = $isEn ? 'daily-menu' : 'thuc-don-ngay';
        } else {
            $prefix = $isEn ? 'weekly-menu' : 'thuc-don-tuan';
        }

        $fileName = $prefix.'-'.($from ?: now()->format('Y-m-d')).'.xlsx';

        return Excel::download(new MenuExport($query->get(), $subtitle, $isSingleDay), $fileName);
    }

    /** Xuất tuần đang soạn trên form (T2 → CN, khớp đủ 7 ngày của grid). */
    public function exportWeekForm()
    {
        $start = Carbon::parse($this->weekDateFrom);
        $end = Carbon::parse($this->weekDateTo);

        if (! WeekMenu::query()
            ->where('kitchen_id', $this->weekKitchenId)
            ->where('date_from', $start->toDateString())
            ->where('date_to', $end->toDateString())
            ->exists()) {
            $this->addError('weekDateFrom', __('menu.errors.export_requires_saved'));

            return null;
        }

        return $this->exportMenus((int) $this->weekKitchenId, $start->toDateString(), $end->toDateString());
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
        $monthPrefix = $this->monthFilter ?: now()->format('Y-m');
        $from = $monthPrefix.'-01';
        $to = Carbon::parse($from)->addMonth()->toDateString();

        $kitchenIdFilter = $this->canChooseKitchen() && $this->kitchenFilter !== ''
            ? (int) $this->kitchenFilter
            : (! $this->canChooseKitchen() ? auth()->user()?->currentKitchenId() : null);

        $counts = WeekMenu::query()
            ->when($kitchenIdFilter, fn ($b) => $b->where('kitchen_id', $kitchenIdFilter))
            ->where('date_from', '>=', $from)
            ->where('date_from', '<', $to)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft")
            ->selectRaw("SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) AS sent")
            ->selectRaw("SUM(CASE WHEN status = 'locked' THEN 1 ELSE 0 END) AS locked")
            ->first();

        $activeWeeks = WeekMenu::query()
            ->when($kitchenIdFilter, fn ($b) => $b->where('kitchen_id', $kitchenIdFilter))
            ->where('date_from', '>=', $from)
            ->where('date_from', '<', $to)
            ->count();

        return [
            'total_active_weeks' => $activeWeeks,
            'sent_month' => (int) ($counts->sent ?? 0),
            'pending' => (int) ($counts->draft ?? 0),
            'locked_month' => (int) ($counts->locked ?? 0),
        ];
    }

    /**
     * Danh sách các ngày trong đợt thực đơn tuần đang soạn.
     */
    public function getWeekDaysProperty(): array
    {
        $start = Carbon::parse($this->weekDateFrom ?: now()->startOfWeek());
        $end = Carbon::parse($this->weekDateTo ?: $start->copy()->addDays(6));
        if ($end->isBefore($start)) {
            $end = $start->copy();
        }

        $dayKeys = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        $days = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $dayKey = $dayKeys[$current->dayOfWeek];
            $days[] = [
                'date' => $current->toDateString(),
                'formatted' => $current->format('d/m'),
                'day_name' => __('menu.days.'.$dayKey),
            ];
            $current->addDay();
        }

        return $days;
    }

    /**
     * Query fail-closed theo bếp đăng nhập. Chỉ quản trị được phép chọn bếp khác.
     */
    protected function scopedMenuQuery(): Builder
    {
        $query = Menu::query();
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($this->canChooseKitchen()) {
            return $query->when(
                $this->kitchenFilter !== '',
                fn (Builder $builder) => $builder->where('kitchen_id', (int) $this->kitchenFilter)
            );
        }

        $kitchenId = $user->currentKitchenId();

        return $kitchenId
            ? $query->where('kitchen_id', $kitchenId)
            : $query->whereRaw('1 = 0');
    }

    /**
     * Query dùng chung cho key card và chi tiết card, bảo đảm filter/badge không lệch nhau.
     */
    protected function filteredMenuQuery(): Builder
    {
        $query = $this->scopedMenuQuery()
            ->when($this->statusFilter !== '', fn (Builder $builder) => $builder->where('status', $this->statusFilter));

        if ($this->typeFilter === 'week' && ($range = $this->selectedWeekRange()) !== null) {
            $query->where('date', '>=', $range[0])->where('date', '<', $range[1]);
        }

        if ($this->typeFilter === 'day' && $this->isValidDate($this->dayFilter)) {
            $query->where('date', '>=', $this->dayFilter)
                ->where('date', '<', Carbon::parse($this->dayFilter)->addDay()->toDateString());
        }

        if ($this->typeFilter === '' && $this->monthFilter !== '' && preg_match('/^\d{4}-\d{2}$/', $this->monthFilter)) {
            $start = $this->monthFilter.'-01';
            $end = Carbon::parse($start)->addMonth()->toDateString();
            $query->where('date', '>=', $start)->where('date', '<', $end);
        }

        if ($this->searchFilter !== '') {
            $term = '%'.$this->searchFilter.'%';
            $query->where(function (Builder $b) use ($term) {
                $b->whereHas('kitchen', fn ($k) => $k->where('name', 'like', $term)->orWhereHas('area', fn ($a) => $a->where('name', 'like', $term)))
                    ->orWhereHas('recipe', fn ($r) => $r->where('name', 'like', $term));
            });
        }

        return $query;
    }

    public function menus()
    {
        $kitchenIdFilter = $this->canChooseKitchen() && $this->kitchenFilter !== ''
            ? (int) $this->kitchenFilter
            : (! $this->canChooseKitchen() ? auth()->user()?->currentKitchenId() : null);

        $weekKeys = DB::table('week_menus')
            ->selectRaw("'week' AS card_type, id AS record_id, kitchen_id, date_from AS group_date, status")
            ->when($kitchenIdFilter, fn ($b) => $b->where('kitchen_id', $kitchenIdFilter))
            ->when($this->statusFilter !== '', fn ($b) => $b->where('status', $this->statusFilter))
            ->when($this->typeFilter === 'week' && ($range = $this->selectedWeekRange()) !== null, fn ($b) => $b->where('date_from', '>=', $range[0])->where('date_from', '<=', $range[1]))
            ->when($this->typeFilter === '' && $this->monthFilter !== '' && preg_match('/^\d{4}-\d{2}$/', $this->monthFilter), function ($b) {
                $start = $this->monthFilter.'-01';
                $end = Carbon::parse($start)->addMonth()->toDateString();
                $b->where('date_from', '>=', $start)->where('date_from', '<', $end);
            });

        $dayKeys = DB::table('menus')
            ->whereNull('week_menu_id')
            ->selectRaw("'day' AS card_type, MIN(id) AS record_id, kitchen_id, date AS group_date, MIN(status) AS status")
            ->when($kitchenIdFilter, fn ($b) => $b->where('kitchen_id', $kitchenIdFilter))
            ->when($this->statusFilter !== '', fn ($b) => $b->where('status', $this->statusFilter))
            ->when($this->typeFilter === 'day' && $this->isValidDate($this->dayFilter), fn ($b) => $b->where('date', '>=', $this->dayFilter)->where('date', '<', Carbon::parse($this->dayFilter)->addDay()->toDateString()))
            ->when($this->typeFilter === '' && $this->monthFilter !== '' && preg_match('/^\d{4}-\d{2}$/', $this->monthFilter), function ($b) {
                $start = $this->monthFilter.'-01';
                $end = Carbon::parse($start)->addMonth()->toDateString();
                $b->where('date', '>=', $start)->where('date', '<', $end);
            })
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

        $weekIds = $pageKeys->where('card_type', 'week')->pluck('record_id')->filter()->all();
        $dayIds = $pageKeys->where('card_type', 'day')->pluck('record_id')->filter()->all();

        $weekMenus = $weekIds !== [] ? WeekMenu::with('kitchen.area')->whereIn('id', $weekIds)->get()->keyBy('id') : collect();
        $dayMenus = $dayIds !== [] ? Menu::with(['kitchen.area', 'shift'])->whereNull('week_menu_id')->whereIn('id', $dayIds)->get()->keyBy('id') : collect();

        $cards = $pageKeys->map(function ($key) use ($weekMenus, $dayMenus) {
            if ($key->card_type === 'week') {
                $wm = $weekMenus->get($key->record_id);
                if (! $wm) {
                    return null;
                }
                $start = Carbon::parse($wm->date_from);
                $end = Carbon::parse($wm->date_to);
                $days = (int) $start->diffInDays($end) + 1;

                return [
                    'id' => $wm->id,
                    'type' => 'week',
                    'kitchen_id' => $wm->kitchen_id,
                    'kitchen_name' => $wm->kitchen?->name ?? 'Nhà ăn',
                    'title' => 'Thực đơn đợt/tuần - '.$wm->kitchen?->name,
                    'sub' => 'Từ ngày '.$start->format('d/m/Y').' đến '.$end->format('d/m/Y'),
                    'start_date' => $wm->date_from,
                    'end_date' => $wm->date_to,
                    'date_from' => $wm->date_from,
                    'date_to' => $wm->date_to,
                    'meta_company' => $wm->kitchen?->area?->name ?? 'Công ty Summit',
                    'meta_info' => "Tổng {$days} ngày · Ca ăn",
                    'status' => $wm->status,
                    'date_raw' => $wm->date_from,
                ];
            }

            $dm = $dayMenus->get($key->record_id);
            if (! $dm) {
                return null;
            }

            return [
                'id' => $dm->id,
                'type' => 'day',
                'kitchen_id' => $dm->kitchen_id,
                'kitchen_name' => $dm->kitchen?->name ?? 'Nhà ăn',
                'title' => 'Thực đơn ngày – '.$dm->date->format('d/m/Y').' (Thứ '.['Chủ Nhật', 'Hai', 'Ba', 'Tư', 'Năm', 'Sáu', 'Bảy'][$dm->date->dayOfWeek].')',
                'sub' => $dm->kitchen?->name.' · '.$dm->shift?->name.' · '.$dm->estimated_portions.' suất',
                'start_date' => $dm->date->toDateString(),
                'end_date' => $dm->date->toDateString(),
                'date_from' => $dm->date->toDateString(),
                'date_to' => $dm->date->toDateString(),
                'meta_company' => $dm->kitchen?->area?->name ?? 'Công ty Summit',
                'meta_info' => $dm->estimated_portions.' suất '.$dm->shift?->name,
                'status' => $dm->status,
                'date_raw' => $dm->date,
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

        return $this->filteredMenuQuery()
            ->with(['kitchen.area', 'shift'])
            ->whereIn('kitchen_id', $pageKeys->pluck('kitchen_id')->unique()->all())
            ->where('date', '>=', $rangeStart)
            ->where('date', '<', Carbon::parse($rangeEnd)->addDay()->toDateString())
            ->orderByDesc('date')
            ->get()
            ->groupBy('kitchen_id')
            ->all();
    }

    /**
     * Backward-compat: compiled Blade views may still reference \$this->weekStartDate.
     * Map it transparently to the renamed weekDateFrom property.
     */
    public array $customDishCategories = [];

    public function getDishCategoriesProperty(): array
    {
        if (empty($this->customDishCategories)) {
            $this->customDishCategories = ['Món 1', 'Món 2', 'Rau xào / luộc', 'Canh', 'Cơm', 'Món chay 1', 'Món chay 2', 'Canh chay', 'Tráng miệng'];
        }

        return $this->customDishCategories;
    }

    public function addCategoryRow(?string $name = null): void
    {
        $categories = $this->dishCategories;
        $newLabel = trim($name ?: '') ?: ('Món '.(count($categories) + 1));
        $this->customDishCategories[] = $newLabel;
        $newIdx = count($this->customDishCategories) - 1;

        foreach ($this->weekCells as $d => $dShifts) {
            foreach ($dShifts as $sId => $items) {
                if (! isset($this->weekCells[$d][$sId][$newIdx])) {
                    $this->weekCells[$d][$sId][$newIdx] = ['recipe_id' => '', 'portions' => 1];
                }
            }
        }
    }

    public function removeCategoryRow(int $index): void
    {
        $categories = $this->dishCategories;
        if (count($categories) <= 1) {
            return;
        }

        array_splice($this->customDishCategories, $index, 1);

        foreach ($this->weekCells as $d => $dShifts) {
            foreach ($dShifts as $sId => $items) {
                if (isset($this->weekCells[$d][$sId][$index])) {
                    array_splice($this->weekCells[$d][$sId], $index, 1);
                }
            }
        }
    }

    // ==========================================
    // WEEK MENU LOGIC
    // ==========================================
    public function loadWeekMenu($kitchenId, $dateFrom, $dateTo = null, ?bool $isEditing = null)
    {
        if (is_bool($dateTo)) {
            $isEditing = $dateTo;
            $dateTo = null;
        }

        $kitchenId = (int) $kitchenId;
        $this->assertKitchenAccess($kitchenId);
        $this->weekKitchenId = $kitchenId;

        $start = Carbon::parse($dateFrom);
        $end = $dateTo ? Carbon::parse($dateTo) : $start->copy()->addDays(6);
        if ($end->isBefore($start)) {
            $end = $start->copy();
        }

        $this->weekDateFrom = $start->toDateString();
        $this->weekDateTo = $end->toDateString();
        $this->activeView = 'week';
        $this->weekEditReason = '';

        $this->weekCells = [];
        $shifts = $this->getShifts();
        if (empty($this->selectedShifts)) {
            $this->selectedShifts = $shifts->pluck('id')->map(fn ($id) => (string) $id)->all();
        }

        $weekMenu = WeekMenu::where('kitchen_id', $kitchenId)
            ->where('date_from', $this->weekDateFrom)
            ->where('date_to', $this->weekDateTo)
            ->first();

        // Also check for loose (orphaned) Menu rows in the date range
        // that are NOT linked to any WeekMenu — covers legacy data and day menus.
        $looseMenus = collect();
        if (! $weekMenu) {
            $looseMenus = Menu::where('kitchen_id', $kitchenId)
                ->whereNull('week_menu_id')
                ->where('date', '>=', $this->weekDateFrom)
                ->where('date', '<=', $this->weekDateTo)
                ->get();
        }

        $hasData = $weekMenu !== null || $looseMenus->isNotEmpty();

        // Auto-detect editing mode: if caller didn't specify, infer from existing data.
        if ($isEditing === null) {
            $isEditing = $hasData;
        }
        $this->isEditingWeek = (bool) $isEditing;
        $this->mode = $this->isEditingWeek ? 'edit' : 'create';
        $this->weekMenuId = $this->isEditingWeek ? $weekMenu?->id : null;

        $this->weekHasExistingMenus = $hasData;
        $this->weekHasEditableLockedMenus = $weekMenu?->status === 'locked' && ! $weekMenu->isPastLocked();
        $this->weekHasPastLockedMenus = (bool) $weekMenu?->isPastLocked();
        $this->weekStatus = $weekMenu?->status ?? 'draft';

        $weekMenus = $weekMenu ? $weekMenu->menus : $looseMenus;
        $menusByDayShift = $weekMenus->groupBy(fn (Menu $m) => $m->date->toDateString().'|'.$m->shift_id);

        $daysCount = (int) $start->diffInDays($end) + 1;
        $catCount = count($this->dishCategories);

        for ($d = 0; $d < $daysCount; $d++) {
            $currentDate = $start->copy()->addDays($d)->toDateString();
            foreach ($shifts as $shift) {
                $menus = $menusByDayShift->get($currentDate.'|'.$shift->id, collect());

                $items = [];
                foreach ($menus as $menu) {
                    $items[] = ['recipe_id' => (string) $menu->recipe_id, 'portions' => $menu->estimated_portions];
                }

                while (count($items) < $catCount) {
                    $items[] = ['recipe_id' => '', 'portions' => 1];
                }

                $this->weekCells[$d][$shift->id] = $items;
            }
        }
    }

    public function updatedWeekKitchenId($kitchenId): void
    {
        if ($kitchenId && $this->weekDateFrom) {
            $this->loadWeekMenu($kitchenId, $this->weekDateFrom, $this->weekDateTo, $this->isEditingWeek);
        }
    }

    public function updatedWeekDateFrom($dateFrom): void
    {
        if ($this->weekKitchenId && $dateFrom) {
            if (! $this->weekDateTo || Carbon::parse($this->weekDateTo)->isBefore($dateFrom)) {
                $this->weekDateTo = Carbon::parse($dateFrom)->addDays(6)->toDateString();
            }
            $this->loadWeekMenu($this->weekKitchenId, $dateFrom, $this->weekDateTo, $this->isEditingWeek);
        }
    }

    public function updatedWeekDateTo($dateTo): void
    {
        if ($this->weekKitchenId && $this->weekDateFrom && $dateTo) {
            if (Carbon::parse($dateTo)->isBefore($this->weekDateFrom)) {
                $this->weekDateTo = $this->weekDateFrom;
            }
            $this->loadWeekMenu($this->weekKitchenId, $this->weekDateFrom, $this->weekDateTo, $this->isEditingWeek);
        }
    }

    /** Thêm 1 dòng món trống vào ô (ngày, ca) của grid tuần. */
    public function addWeekDish(int $day, int $shiftId): void
    {
        $this->weekCells[$day][$shiftId][] = ['recipe_id' => '', 'portions' => 1];
    }

    /** Bỏ 1 dòng món khỏi ô; luôn chừa lại tối thiểu 1 dòng trống. */
    public function removeWeekDish(int $day, int $shiftId, int $index): void
    {
        unset($this->weekCells[$day][$shiftId][$index]);
        $this->weekCells[$day][$shiftId] = array_values($this->weekCells[$day][$shiftId]);

        if ($this->weekCells[$day][$shiftId] === []) {
            $this->weekCells[$day][$shiftId] = [['recipe_id' => '', 'portions' => 1]];
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
        abort_unless($ownKitchenId && (int) $kitchenId === (int) $ownKitchenId, 403, __('menu.errors.own_kitchen_only'));
    }

    /**
     * Cảnh báo LẶP MÓN so với 3 TUẦN (21 ngày) trước đợt đang lập (BA R33) — tính trên
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

        $weekStart = Carbon::parse($this->weekDateFrom ?: now()->startOfWeek());

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

        // Sanitize empty recipe_id values to null so validation doesn't reject unselected options
        foreach ($this->weekCells as $d => $shifts) {
            foreach ($shifts as $shiftId => $items) {
                foreach ($items as $idx => $item) {
                    $rid = $item['recipe_id'] ?? null;
                    if ($rid === '' || $rid === 0 || $rid === '0' || $rid === false) {
                        $this->weekCells[$d][$shiftId][$idx]['recipe_id'] = null;
                    }
                }
            }
        }

        $this->validate([
            'weekKitchenId' => ['required', 'integer', 'exists:kitchens,id'],
            'weekDateFrom' => ['required', 'date'],
            'weekDateTo' => ['required', 'date', 'after_or_equal:weekDateFrom'],
            'weekStatus' => ['required', Rule::in(array_keys(WeekMenu::STATUS_ORDER))],
            'weekCells.*.*.*.recipe_id' => ['nullable', 'integer', 'exists:recipes,id'],
            'weekCells.*.*.*.portions' => ['required', 'integer', 'min:1'],
        ], [
            'weekStatus.in' => __('menu.errors.invalid_status'),
            'weekDateTo.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.',
        ]);

        $hasAnyRecipe = false;
        foreach ($this->weekCells as $byShift) {
            foreach ($byShift as $cell) {
                foreach ($cell as $item) {
                    if ((int) ($item['recipe_id'] ?? 0) > 0) {
                        $hasAnyRecipe = true;
                        break 3;
                    }
                }
            }
        }

        if (! $hasAnyRecipe) {
            Notification::make()
                ->title(__('menu.notifications.no_items_title'))
                ->body(__('menu.notifications.no_items'))
                ->warning()
                ->send();

            return;
        }

        $start = Carbon::parse($this->weekDateFrom);
        $end = Carbon::parse($this->weekDateTo);
        $dateFrom = $start->toDateString();
        $dateTo = $end->toDateString();

        $weekMenu = WeekMenu::where('kitchen_id', $this->weekKitchenId)
            ->where('date_from', $dateFrom)
            ->where('date_to', $dateTo)
            ->first();

        if ($weekMenu && ($blocked = $weekMenu->editBlockReason($this->weekStatus, $this->weekEditReason))) {
            $this->addError('weekEditReason', match ($blocked) {
                'past' => __('menu.errors.past_locked_edit'),
                'downgrade' => __('menu.errors.status_downgrade'),
                'need_reason' => __('menu.errors.audit_reason_required'),
                default => __('menu.errors.invalid_status'),
            });

            return;
        }

        $shifts = $this->getShifts();
        $daysCount = (int) $start->diffInDays($end) + 1;

        DB::transaction(function () use ($shifts, $start, $dateFrom, $dateTo, $daysCount, &$weekMenu): void {
            $weekMenu = WeekMenu::updateOrCreate(
                [
                    'kitchen_id' => $this->weekKitchenId,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                ],
                [
                    'status' => $this->weekStatus,
                    'audit_reason' => trim($this->weekEditReason) ?: null,
                ]
            );

            // Sync custom dish categories into recipe_types table
            foreach ($this->customDishCategories as $catLabel) {
                $trimmed = trim($catLabel);
                if ($trimmed !== '') {
                    RecipeType::firstOrCreate(['name' => $trimmed]);
                }
            }

            // Adopt orphaned Menu rows that fall within this week range
            // (covers legacy data created before the WeekMenu system).
            Menu::where('kitchen_id', $this->weekKitchenId)
                ->whereNull('week_menu_id')
                ->where('date', '>=', $dateFrom)
                ->where('date', '<=', $dateTo)
                ->update(['week_menu_id' => $weekMenu->id]);

            for ($d = 0; $d < $daysCount; $d++) {
                $currentDate = $start->copy()->addDays($d)->toDateString();
                foreach ($shifts as $shift) {
                    $cell = $this->weekCells[$d][$shift->id] ?? [];

                    $desired = [];
                    foreach ($cell as $item) {
                        $rid = (int) ($item['recipe_id'] ?? 0);
                        if ($rid > 0) {
                            $desired[$rid] = (int) ($item['portions'] ?? 1);
                        }
                    }

                    $existingByRecipe = Menu::where('week_menu_id', $weekMenu->id)
                        ->whereDate('date', $currentDate)
                        ->where('shift_id', $shift->id)
                        ->get()
                        ->keyBy('recipe_id');

                    // 1) Thêm mới / Cập nhật
                    foreach ($desired as $recipeId => $portions) {
                        $existing = $existingByRecipe->get($recipeId);

                        if ($existing) {
                            $existing->auditReason = trim($this->weekEditReason) ?: null;
                            $existing->update([
                                'estimated_portions' => $portions,
                                'status' => $this->weekStatus,
                            ]);
                        } else {
                            abort_unless(MenuResource::canCreate(), 403);
                            Menu::create([
                                'kitchen_id' => $this->weekKitchenId,
                                'week_menu_id' => $weekMenu->id,
                                'date' => $currentDate,
                                'shift_id' => $shift->id,
                                'recipe_id' => $recipeId,
                                'estimated_portions' => $portions,
                                'status' => $this->weekStatus,
                            ]);
                        }
                    }

                    // 2) Xóa các món đã bị gỡ khỏi ô
                    foreach ($existingByRecipe as $recipeId => $menu) {
                        if (! isset($desired[$recipeId])) {
                            $menu->auditReason = trim($this->weekEditReason) ?: null;
                            $menu->delete();
                        }
                    }
                }
            }
        });

        Notification::make()
            ->title($this->weekStatus === 'locked' ? 'Đã chốt thực đơn đợt thành công!' : 'Đã lưu thực đơn đợt thành công!')
            ->success()
            ->send();

        session()->flash('message', __('menu.notifications.week_saved'));
        $this->switchView('list');
    }

    /**
     * Guard vòng đời cho form TUẦN — ủy quyền về Menu::editBlockReason.
     */
    protected function weekGuardSkips(Menu $menu): bool
    {
        return $this->isMenuBlocked($menu, $this->weekStatus, $this->weekEditReason, $this->isEditingWeek);
    }

    protected function isMenuBlocked(Menu $menu, string $targetStatus, string $reason, bool $isEditing): bool
    {
        $blocked = $menu->editBlockReason($targetStatus, $reason);

        // The Menu model's saving() hook always enforces 'need_reason' — if we
        // let the update through, Eloquent throws ValidationException inside the
        // DB transaction.  Return true so the caller silently skips this row.
        return $blocked !== null;
    }

    public function resetWeekForm()
    {
        $this->customDishCategories = [];
        $this->weekStatus = 'draft';
        $this->weekCells = [];
        $this->weekEditReason = '';
        $this->weekHasExistingMenus = false;
        $this->weekHasEditableLockedMenus = false;
        $this->weekHasPastLockedMenus = false;
        $this->isEditingWeek = false;
    }

    // ==========================================
    // DAY MENU LOGIC
    // ==========================================
    public function loadDayMenu($kitchenId, $date, ?bool $isEditing = null)
    {
        $kitchenId = (int) $kitchenId;
        $this->assertKitchenAccess($kitchenId);
        $this->dayKitchenId = $kitchenId;
        $this->dayDate = $date;
        $this->activeView = 'day';
        $this->dayEditReason = '';

        $this->dayItems = [];
        $shifts = $this->getShifts();

        // 1 query cho cả ngày (chỉ lấy thực đơn ngày lẻ — week_menu_id IS NULL)
        $dayMenus = Menu::where('kitchen_id', $kitchenId)
            ->whereNull('week_menu_id')
            ->where('date', '>=', $date)
            ->where('date', '<', Carbon::parse($date)->addDay()->toDateString())
            ->get();

        // Auto-detect editing mode: if caller didn't specify, infer from existing data.
        if ($isEditing === null) {
            $isEditing = $dayMenus->isNotEmpty();
        }
        $this->isEditingDay = (bool) $isEditing;
        $this->mode = $this->isEditingDay ? 'edit' : 'create';

        $this->dayHasExistingMenus = $dayMenus->isNotEmpty();
        $this->dayHasEditableLockedMenus = $dayMenus->contains(
            fn (Menu $menu): bool => $menu->status === 'locked' && ! $menu->isPastLocked()
        );
        $this->dayHasPastLockedMenus = $dayMenus->contains(fn (Menu $menu): bool => $menu->isPastLocked());
        $this->dayStatus = $dayMenus
            ->sortByDesc(fn (Menu $menu): int => Menu::STATUS_ORDER[$menu->status] ?? -1)
            ->first()?->status ?? 'draft';

        $menusByShift = $dayMenus->groupBy('shift_id');
        $defaultLabels = __('menu.default_dish_labels');
        if (! is_array($defaultLabels)) {
            $defaultLabels = ['Món 1', 'Món 2', 'Rau xào / luộc', 'Canh', 'Cơm', 'Tráng miệng', 'Món chay 1', 'Món chay 2', 'Canh chay'];
        }

        foreach ($shifts as $shift) {
            $menus = $menusByShift->get($shift->id, collect());
            $shiftPortions = $menus->first()?->estimated_portions ?? 1;

            $recipes = [];
            foreach ($menus as $idx => $m) {
                $recipes[] = [
                    'menu_id' => $m->id,
                    'label' => $defaultLabels[$idx] ?? __('menu.labels.dish_index', ['index' => $idx + 1]),
                    'recipe_id' => $m->recipe_id,
                    'portions' => $m->estimated_portions,
                ];
            }

            if (empty($recipes)) {
                foreach (array_slice($defaultLabels, 0, 6) as $lbl) {
                    $recipes[] = [
                        'menu_id' => null,
                        'label' => $lbl,
                        'recipe_id' => '',
                        'portions' => $shiftPortions,
                    ];
                }
            }

            $this->dayItems[$shift->id] = [
                'shift_name' => $shift->name,
                'shift_portions' => $shiftPortions,
                'recipes' => $recipes,
            ];
        }

        $this->sortDayItems();
    }

    public function sortDayItems(bool $notify = false): void
    {
        $shifts = $this->getShifts();
        $shiftIndexes = [];
        foreach ($shifts->values() as $idx => $shift) {
            $shiftIndexes[$shift->id] = $idx;
        }

        uksort($this->dayItems, function ($a, $b) use ($shiftIndexes) {
            $orderA = $shiftIndexes[$a] ?? 999999;
            $orderB = $shiftIndexes[$b] ?? 999999;

            return $orderA <=> $orderB;
        });

        if ($notify) {
            Notification::make()
                ->title(__('menu.actions.sort_shifts'))
                ->success()
                ->send();
        }
    }

    public function sortWeekItems(): void
    {
        $shiftOrders = $this->getShifts()->pluck('sort_order', 'id')->all();

        foreach ($this->weekCells as $d => $shiftsData) {
            uksort($this->weekCells[$d], function ($a, $b) use ($shiftOrders) {
                $orderA = (int) ($shiftOrders[$a] ?? 999999);
                $orderB = (int) ($shiftOrders[$b] ?? 999999);

                return $orderA <=> $orderB;
            });
        }

        Notification::make()
            ->title(__('menu.actions.sort_shifts'))
            ->success()
            ->send();
    }

    public function updatedDayKitchenId($kitchenId): void
    {
        if ($kitchenId && $this->dayDate) {
            $this->loadDayMenu($kitchenId, $this->dayDate, $this->isEditingDay);
        }
    }

    public function updatedDayDate($date): void
    {
        if ($this->dayKitchenId && $date) {
            // Pass null to auto-detect editing mode based on existing data.
            $this->loadDayMenu($this->dayKitchenId, $date);
        }
    }

    public function updatedDayItems($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'shift_portions') {
            $shiftId = $parts[0];
            $portions = (int) $value;
            if (isset($this->dayItems[$shiftId]['recipes'])) {
                foreach ($this->dayItems[$shiftId]['recipes'] as $idx => $r) {
                    $this->dayItems[$shiftId]['recipes'][$idx]['portions'] = $portions;
                }
            }
        }
    }

    public function addRecipeToShift($shiftId)
    {
        $nextIdx = count($this->dayItems[$shiftId]['recipes'] ?? []) + 1;
        $shiftPortions = (int) ($this->dayItems[$shiftId]['shift_portions'] ?? 1);
        $this->dayItems[$shiftId]['recipes'][] = [
            'menu_id' => null,
            'label' => __('menu.labels.dish_index', ['index' => $nextIdx]),
            'recipe_id' => '',
            'portions' => $shiftPortions,
        ];
    }

    public function addShiftToDay(): void
    {
        $allShifts = $this->getShifts();
        $existingShiftIds = array_keys($this->dayItems);
        $unusedShift = $allShifts->first(fn ($s) => ! in_array($s->id, $existingShiftIds));

        $defaultLabels = __('menu.default_dish_labels');
        if (! is_array($defaultLabels)) {
            $defaultLabels = ['Món 1', 'Món 2', 'Rau xào / luộc', 'Canh', 'Cơm', 'Tráng miệng'];
        }
        $recipes = [];
        foreach (array_slice($defaultLabels, 0, 6) as $lbl) {
            $recipes[] = [
                'menu_id' => null,
                'label' => $lbl,
                'recipe_id' => '',
                'portions' => 1,
            ];
        }

        if (! $unusedShift) {
            Notification::make()
                ->title(__('menu.notifications.all_shifts_added_title'))
                ->body(__('menu.notifications.all_shifts_added_body'))
                ->info()
                ->send();

            $this->redirect(ShiftResource::getUrl('create', array_filter([
                'from' => 'day_menu',
                'date' => $this->dayDate,
                'kitchen' => $this->dayKitchenId,
            ])));

            return;
        }

        $this->dayItems[$unusedShift->id] = [
            'shift_name' => $unusedShift->name,
            'shift_portions' => 1,
            'recipes' => $recipes,
        ];

        $this->sortDayItems();
    }

    public function removeShiftFromDay($shiftId): void
    {
        if (count($this->dayItems) <= 1) {
            Notification::make()
                ->title(__('menu.notifications.at_least_one_shift'))
                ->warning()
                ->send();

            return;
        }

        // Nếu ca có bản ghi menu đã lưu thì xóa các bản ghi menu
        if (isset($this->dayItems[$shiftId]['recipes'])) {
            foreach ($this->dayItems[$shiftId]['recipes'] as $item) {
                if (! empty($item['menu_id'])) {
                    $menu = Menu::find($item['menu_id']);
                    if ($menu && MenuResource::canDelete($menu)) {
                        $menu->auditReason = trim($this->dayEditReason) ?: null;
                        $menu->delete();
                    }
                }
            }
        }

        unset($this->dayItems[$shiftId]);
    }

    public function removeRecipeFromShift($shiftId, $index)
    {
        $menuId = $this->dayItems[$shiftId]['recipes'][$index]['menu_id'] ?? null;
        if ($menuId) {
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

        // Sanitize empty recipe_id values to null so validation doesn't reject unselected options
        foreach ($this->dayItems as $shiftId => $data) {
            if (isset($data['recipes']) && is_array($data['recipes'])) {
                foreach ($data['recipes'] as $idx => $item) {
                    $rid = $item['recipe_id'] ?? null;
                    if ($rid === '' || $rid === 0 || $rid === '0' || $rid === false) {
                        $this->dayItems[$shiftId]['recipes'][$idx]['recipe_id'] = null;
                    }
                }
            }
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

        $hasAnyRecipe = false;
        foreach ($this->dayItems as $data) {
            foreach ($data['recipes'] as $item) {
                if ((int) ($item['recipe_id'] ?? 0) > 0) {
                    $hasAnyRecipe = true;
                    break 2;
                }
            }
        }

        if (! $hasAnyRecipe) {
            Notification::make()
                ->title(__('menu.notifications.no_items_title'))
                ->body(__('menu.notifications.no_items'))
                ->warning()
                ->send();

            return;
        }

        $existingDayMenus = Menu::query()
            ->where('kitchen_id', $this->dayKitchenId)
            ->whereNull('week_menu_id')
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
                            ->whereNull('week_menu_id')
                            ->where('date', '>=', $this->dayDate)
                            ->where('date', '<', Carbon::parse($this->dayDate)->addDay()->toDateString())
                            ->where('shift_id', $shiftId)
                            ->first();

                        if ($menu) {
                            // Guard vòng đời dùng chung (khóa quá khứ / không hạ cấp / lý do khi sửa đã chốt)
                            if ($this->isMenuBlocked($menu, $this->dayStatus, $this->dayEditReason, $this->isEditingDay)) {
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
                        // update dòng cũ thay vì INSERT để không vỡ unique
                        $duplicate = Menu::where('kitchen_id', $this->dayKitchenId)
                            ->where('date', '>=', $this->dayDate)
                            ->where('date', '<', Carbon::parse($this->dayDate)->addDay()->toDateString())
                            ->where('shift_id', $shiftId)
                            ->where('recipe_id', $recipeId)
                            ->first();

                        if ($duplicate) {
                            if ($this->isMenuBlocked($duplicate, $this->dayStatus, $this->dayEditReason, $this->isEditingDay)) {
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
                                'week_menu_id' => null,
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

        Notification::make()
            ->title($this->dayStatus === 'locked' ? 'Đã chốt thực đơn ngày thành công!' : 'Đã lưu thực đơn ngày thành công!')
            ->success()
            ->send();

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
        $isEditing = ($errorKey === 'weekEditReason') ? $this->isEditingWeek : $this->isEditingDay;

        foreach ($menus as $menu) {
            abort_unless(MenuResource::canEdit($menu), 403);

            $blocked = $menu->editBlockReason($targetStatus, $reason);
            if ($blocked === null) {
                continue;
            }

            if (! $isEditing && $blocked === 'need_reason') {
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
        $this->isEditingDay = false;
    }

    // ==========================================
    // HELPERS & LIST DATA
    // ==========================================
    public function getKitchens()
    {
        $query = Kitchen::query()->orderBy('name');
        $user = auth()->user();

        if (! $user || $this->canChooseKitchen()) {
            return $query->get();
        }

        $kitchenId = $user->currentKitchenId();

        return $kitchenId
            ? $query->whereKey($kitchenId)->get()
            : collect();
    }

    public function getShifts()
    {
        return Shift::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function getRecipes()
    {
        return Recipe::orderBy('name')->get();
    }
}
