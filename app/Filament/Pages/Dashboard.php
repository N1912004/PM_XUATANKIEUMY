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

class Dashboard extends Page
{
    protected static ?string $navigationGroup = 'TỔNG QUAN';

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Bảng điều khiển';

    protected static string $view = 'filament.pages.dashboard';

    public int $totalPortionsToday = 0;

    public int $totalIngredients = 0;

    public int $pendingOrders = 0;

    public int $activeEmployees = 0;

    public array $todayMenus = [];

    public array $lowStockIngredients = [];

    public array $recentOrders = [];

    public array $todayAudits = [];

    public string $greeting = '';

    public string $todayFormatted = '';

    public function mount(): void
    {
        $this->todayFormatted = Carbon::today()->locale('vi')->isoFormat('dddd, [Ngày] DD/MM/Y');

        $hour = (int) date('H');
        if ($hour < 12) {
            $this->greeting = 'Chào buổi sáng';
        } elseif ($hour < 18) {
            $this->greeting = 'Chào buổi chiều';
        } else {
            $this->greeting = 'Chào buổi tối';
        }

        // Fetch Stats
        $this->totalPortionsToday = (int) Menu::whereDate('date', Carbon::today())->sum('estimated_portions');
        $this->totalIngredients = (int) Ingredient::where('status', true)->count();
        $this->pendingOrders = (int) PurchaseOrder::whereIn('status', ['draft', 'sent', 'checking'])->count();
        $this->activeEmployees = (int) Employee::where('status', 'Đang làm việc')->count();

        // Fetch Today's Menus
        $this->todayMenus = Menu::whereDate('date', Carbon::today())
            ->with(['shift', 'recipe'])
            ->get()
            ->toArray();

        // Fetch Low Stock Ingredients
        $this->lowStockIngredients = Stock::whereColumn('quantity', '<=', 'min_quantity')
            ->with('ingredient')
            ->get()
            ->toArray();

        // Fetch Recent Purchase Orders
        $this->recentOrders = PurchaseOrder::with('supplier')
            ->latest()
            ->limit(5)
            ->get()
            ->toArray();

        // Fetch Today's Food Safety Audits
        $this->todayAudits = FoodSafetyAudit::whereDate('date', Carbon::today())
            ->with('shift')
            ->get()
            ->toArray();
    }
}
