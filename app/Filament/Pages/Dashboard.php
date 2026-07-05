<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\FoodSafetyAudit;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use Carbon\Carbon;
use Filament\Pages\Page;
use Livewire\WithPagination;

class Dashboard extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string $view = 'filament.pages.dashboard';

    public static function getNavigationGroup(): ?string
    {
        return __('TỔNG QUAN');
    }

    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
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

    public string $greeting = '';

    public string $todayFormatted = '';

    public function mount(): void
    {
        $this->todayFormatted = Carbon::today()->locale(app()->getLocale())->isoFormat(
            app()->getLocale() === 'vi' ? 'dddd, [Ngày] DD/MM/Y' : 'dddd, MMMM DD, Y'
        );

        $hour = (int) date('H');
        if ($hour < 12) {
            $this->greeting = 'Chào buổi sáng';
        } elseif ($hour < 18) {
            $this->greeting = 'Chào buổi chiều';
        } else {
            $this->greeting = 'Chào buổi tối';
        }

        // Ngày hôm nay dạng chuỗi 'Y-m-d' để so sánh trực tiếp trên cột DATE.
        $today = Carbon::today()->toDateString();

        // Fetch Stats
        $this->totalPortionsToday = (int) Menu::where('date', $today)->sum('estimated_portions');
        $this->totalIngredients = (int) Ingredient::where('status', true)->count();
        $this->pendingOrders = (int) PurchaseOrder::whereIn('status', ['draft', 'sent', 'checking'])->count();
        $this->activeEmployees = (int) Employee::where('status', 'Đang làm việc')->count();

        // Fetch Low Stock Ingredients.
        // Chỉ cảnh báo khi có định mức tối thiểu (> 0) và nguyên liệu đang hoạt động.
        // Sắp xếp theo mức thiếu hụt nhiều nhất và giới hạn danh sách để an toàn với dữ liệu lớn;
        // tổng số cảnh báo thực tế được đếm riêng qua $lowStockCount.
        $lowStockQuery = Stock::query()
            ->where('min_quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->whereHas('ingredient', fn ($query) => $query->where('status', true));

        $this->lowStockCount = (int) $lowStockQuery->clone()->count();

        $this->lowStockIngredients = $lowStockQuery
            ->with('ingredient:id,name,unit')
            ->orderByRaw('(min_quantity - quantity) DESC')
            ->limit(50)
            ->get(['id', 'ingredient_id', 'quantity', 'min_quantity'])
            ->toArray();

        // Fetch Recent Purchase Orders
        $this->recentOrders = PurchaseOrder::with('supplier:id,name')
            ->latest()
            ->limit(5)
            ->get(['id', 'code', 'supplier_id', 'status', 'created_at'])
            ->toArray();
    }

    /**
     * Get view data for dynamic elements (e.g. paginated queries).
     */
    public function getViewData(): array
    {
        $today = Carbon::today()->toDateString();

        return [
            'todayMenus' => Menu::where('date', $today)
                ->with([
                    'shift:id,name',
                    'recipe:id,name,type',
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
