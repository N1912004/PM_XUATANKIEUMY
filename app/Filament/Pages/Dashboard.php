<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\FoodSafetyAudit;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Models\Supplier;
use Carbon\Carbon;
use Filament\Pages\Page;
use Livewire\WithPagination;

class Dashboard extends Page
{
    use WithPagination;

    protected static bool $shouldRegisterNavigation = false; // Ẩn menu 'Bảng điều khiển' (Dashboard) khỏi Sidebar

    protected static ?string $navigationIcon = 'fa-house';

    protected static ?int $navigationSort = -10;

    protected static string $view = 'filament.pages.dashboard';

    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public static function getNavigationLabel(): string
    {
        return __('Bảng điều khiển');
    }

    public function getTitle(): string
    {
        return __('Bảng điều khiển');
    }

    public int $totalPortionsToday = 0;

    public int $totalIngredients = 0;

    public int $pendingOrders = 0;

    public int $activeEmployees = 0;

    public array $lowStockIngredients = [];

    public int $lowStockCount = 0;

    public array $recentOrders = [];

    /** Hồ sơ NCC sắp hết hạn (≤30 ngày) hoặc đã quá hạn — cảnh báo cho bộ phận mua hàng */
    public array $expiringSupplierDocs = [];

    public string $greeting = '';

    public string $todayFormatted = '';

    public function mount(): void
    {
        $this->todayFormatted = Carbon::today()->locale(app()->getLocale())->isoFormat(__('dashboard.date_format'));

        $hour = (int) date('H');
        if ($hour < 12) {
            $this->greeting = 'dashboard.greeting.morning';
        } elseif ($hour < 18) {
            $this->greeting = 'dashboard.greeting.afternoon';
        } else {
            $this->greeting = 'dashboard.greeting.evening';
        }

        // Ngày hôm nay dạng chuỗi 'Y-m-d' để so sánh trực tiếp trên cột DATE.
        $today = Carbon::today()->toDateString();
        $kitchenId = $this->scopedKitchenId();

        // Fetch Stats (scope theo bếp của user nếu không phải quản trị)
        $this->totalPortionsToday = (int) Menu::where('date', $today)
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->sum('estimated_portions');
        $this->totalIngredients = (int) Ingredient::where('status', true)->count();
        $this->pendingOrders = (int) PurchaseOrder::whereIn('status', ['draft', 'sent', 'checking'])
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->count();
        $this->activeEmployees = (int) Employee::where('status', 'working')
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->count();

        // Fetch Low Stock Ingredients.
        // Chỉ cảnh báo khi có định mức tối thiểu (> 0) và nguyên liệu đang hoạt động.
        // Sắp xếp theo mức thiếu hụt nhiều nhất và giới hạn danh sách để an toàn với dữ liệu lớn;
        // tổng số cảnh báo thực tế được đếm riêng qua $lowStockCount.
        $lowStockQuery = Stock::query()
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->where('min_quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->whereHas('ingredient', fn ($query) => $query->where('status', true));

        $this->lowStockCount = (int) $lowStockQuery->clone()->count();

        $this->lowStockIngredients = $lowStockQuery
            ->with('ingredient:id,name,unit_id')
            ->orderByRaw('(min_quantity - quantity) DESC')
            ->limit(50)
            ->get(['id', 'ingredient_id', 'quantity', 'min_quantity'])
            ->toArray();

        // Hồ sơ NCC sắp hết hạn/quá hạn (cột JSON nên duyệt PHP — cùng pattern hồ sơ nhân viên)
        $threshold = Carbon::today()->addDays(30);
        $expiring = [];
        foreach (Supplier::whereNotNull('documents')->get(['id', 'name', 'documents']) as $supplier) {
            foreach ($supplier->documents ?? [] as $doc) {
                if (empty($doc['expires_at'])) {
                    continue;
                }
                $expiresAt = Carbon::parse($doc['expires_at']);
                if ($expiresAt->lte($threshold)) {
                    $expiring[] = [
                        'supplier' => $supplier->name,
                        'document' => $doc['name'] ?? __('dashboard.supplier_document'),
                        'expires_at' => $expiresAt->format('d/m/Y'),
                        // Khóa sort dạng Y-m-d — chuỗi d/m/Y so sánh lexicographic sẽ sai thứ tự thời gian
                        'sort_key' => $expiresAt->toDateString(),
                        'expired' => $expiresAt->isPast(),
                    ];
                }
            }
        }
        // Quá hạn lên đầu, trong mỗi nhóm ngày gần nhất trước
        usort($expiring, fn ($a, $b) => [$b['expired'], $a['sort_key']] <=> [$a['expired'], $b['sort_key']]);
        $this->expiringSupplierDocs = array_slice($expiring, 0, 20);

        // Fetch Recent Purchase Orders
        $this->recentOrders = PurchaseOrder::with('supplier:id,name')
            ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
            ->latest()
            ->limit(5)
            ->get(['id', 'code', 'supplier_id', 'status', 'created_at'])
            ->toArray();
    }

    /**
     * Bếp cần scope KPI: null với quản trị viên (xem toàn hệ thống),
     * ngược lại là bếp của user — đồng bộ convention BelongsToKitchen.
     */
    protected function scopedKitchenId(): ?int
    {
        $user = auth()->user();
        if (! $user || $user->hasRole(['super_admin', 'Quản trị viên'])) {
            return null;
        }

        return $user->currentKitchenId();
    }

    /**
     * Get view data for dynamic elements (e.g. paginated queries).
     */
    public function getViewData(): array
    {
        $today = Carbon::today()->toDateString();
        $kitchenId = $this->scopedKitchenId();

        return [
            'todayMenus' => Menu::where('date', $today)
                ->when($kitchenId, fn ($q) => $q->where('kitchen_id', $kitchenId))
                ->with([
                    'shift:id,name',
                    'recipe:id,name,recipe_type_id',
                ])
                ->orderBy('shift_id')
                ->paginate(5, ['*'], 'menusPage'),

            'todayAudits' => FoodSafetyAudit::where('date', $today)
                ->with('shift:id,name')
                ->latest()
                ->paginate(5, ['*'], 'auditsPage'),
        ];
    }
}
