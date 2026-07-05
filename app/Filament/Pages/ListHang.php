<?php

namespace App\Filament\Pages;

use App\Models\Menu;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Shift;
use App\Models\Supplier;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ListHang extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.list-hang';

    public static function getNavigationGroup(): ?string
    {
        return __('CUNG ỨNG & KHO');
    }

    public static function getNavigationLabel(): string
    {
        return __('Danh sách hàng');
    }

    public function getTitle(): string
    {
        return __('Danh sách hàng');
    }

    public ?string $date = null;

    public array $selectedShifts = [];

    public function mount(): void
    {
        $this->date = now()->toDateString();
        $this->selectedShifts = Shift::pluck('id')->toArray();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('Ngày phục vụ')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn () => $this->updatedDate()),
                Select::make('selectedShifts')
                    ->label('Chọn Ca phục vụ')
                    ->multiple()
                    ->options(Shift::pluck('name', 'id'))
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn () => $this->updatedShifts()),
            ])
            ->statePath('data');
    }

    public function updatedDate(): void
    {
        // Livewire updates it automatically
    }

    public function updatedShifts(): void
    {
        // Livewire updates it automatically
    }

    public function getGroupedData(): array
    {
        if (empty($this->selectedShifts) || ! $this->date) {
            return [];
        }

        $shifts = Shift::whereIn('id', $this->selectedShifts)->get();
        $data = [];

        foreach ($shifts as $shift) {
            $menus = Menu::with(['recipe.ingredients'])
                ->whereDate('date', $this->date)
                ->where('shift_id', $shift->id)
                ->get();

            if ($menus->isEmpty()) {
                continue;
            }

            $dishes = [];
            $shiftPortions = 0;

            foreach ($menus as $menu) {
                $recipe = $menu->recipe;
                if (! $recipe) {
                    continue;
                }

                $shiftPortions += $menu->estimated_portions;
                $ingredients = [];

                foreach ($recipe->ingredients as $ingredient) {
                    $qty = $menu->estimated_portions * $ingredient->pivot->quantity_per_portion;
                    $ingredients[] = [
                        'code' => $ingredient->code,
                        'name' => $ingredient->name,
                        'quantity' => $qty,
                        'unit' => $ingredient->unit,
                    ];
                }

                $dishes[] = [
                    'name' => $recipe->name,
                    'type' => $recipe->type,
                    'portions' => $menu->estimated_portions,
                    'ingredients' => $ingredients,
                ];
            }

            $data[] = [
                'id' => $shift->id,
                'name' => $shift->name,
                'time_range' => $shift->time_range,
                'total_portions' => $shiftPortions,
                'total_dishes' => count($dishes),
                'dishes' => $dishes,
            ];
        }

        return $data;
    }

    public function getStats(): array
    {
        if (empty($this->selectedShifts) || ! $this->date) {
            return [
                'shifts' => 0,
                'portions' => 0,
                'dishes' => 0,
                'ingredients' => 0,
            ];
        }

        $portions = Menu::whereDate('date', $this->date)
            ->whereIn('shift_id', $this->selectedShifts)
            ->sum('estimated_portions');

        $dishes = Menu::whereDate('date', $this->date)
            ->whereIn('shift_id', $this->selectedShifts)
            ->distinct('recipe_id')
            ->count('recipe_id');

        $grouped = $this->getGroupedData();
        $ingCount = 0;
        $ingCodes = [];
        foreach ($grouped as $s) {
            foreach ($s['dishes'] as $d) {
                foreach ($d['ingredients'] as $i) {
                    $ingCodes[$i['code']] = true;
                }
            }
        }

        return [
            'shifts' => count($grouped),
            'portions' => $portions,
            'dishes' => $dishes,
            'ingredients' => count($ingCodes),
        ];
    }

    public function generatePurchaseOrders(): void
    {
        if (empty($this->selectedShifts) || ! $this->date) {
            Notification::make()
                ->title('Vui lòng chọn ngày và ca phục vụ!')
                ->danger()
                ->send();

            return;
        }

        $menus = Menu::with(['recipe.ingredients.supplier'])
            ->whereDate('date', $this->date)
            ->whereIn('shift_id', $this->selectedShifts)
            ->get();

        if ($menus->isEmpty()) {
            Notification::make()
                ->title('Không có thực đơn nào được lập cho ngày và ca đã chọn để tạo PO!')
                ->warning()
                ->send();

            return;
        }

        // Calculate total demand per ingredient
        $demands = [];
        foreach ($menus as $menu) {
            $recipe = $menu->recipe;
            if (! $recipe) {
                continue;
            }

            foreach ($recipe->ingredients as $ingredient) {
                $qty = $menu->estimated_portions * $ingredient->pivot->quantity_per_portion;
                if ($qty <= 0) {
                    continue;
                }

                if (! isset($demands[$ingredient->id])) {
                    $demands[$ingredient->id] = [
                        'ingredient' => $ingredient,
                        'quantity' => 0,
                    ];
                }
                $demands[$ingredient->id]['quantity'] += $qty;
            }
        }

        if (empty($demands)) {
            Notification::make()
                ->title('Không có nguyên liệu nào cần chuẩn bị để tạo PO!')
                ->warning()
                ->send();

            return;
        }

        // Group by supplier
        $supplierGroups = [];
        $firstSupplier = Supplier::first();

        foreach ($demands as $id => $data) {
            $ingredient = $data['ingredient'];
            $supplierId = $ingredient->supplier_id ?? ($firstSupplier ? $firstSupplier->id : null);

            if (! $supplierId) {
                continue; // Skip if no supplier exists at all in system
            }

            if (! isset($supplierGroups[$supplierId])) {
                $supplierGroups[$supplierId] = [];
            }
            $supplierGroups[$supplierId][] = $data;
        }

        $poCount = 0;
        $poCodes = [];

        foreach ($supplierGroups as $supplierId => $itemsData) {
            $poCode = 'PO-LH-'.Carbon::parse($this->date)->format('Ymd').'-'.mt_rand(100, 999);

            // Check if PO code already exists, retry if needed
            while (PurchaseOrder::where('code', $poCode)->exists()) {
                $poCode = 'PO-LH-'.Carbon::parse($this->date)->format('Ymd').'-'.mt_rand(100, 999);
            }

            $po = PurchaseOrder::create([
                'code' => $poCode,
                'kitchen_id' => Filament::auth()->user()?->currentKitchenId(),
                'supplier_id' => $supplierId,
                'status' => 'draft',
                'estimated_delivery_date' => $this->date,
                'note' => 'Đơn đặt hàng tự động tạo từ List hàng ngày '.Carbon::parse($this->date)->format('d/m/Y'),
            ]);

            foreach ($itemsData as $data) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'ingredient_id' => $data['ingredient']->id,
                    'quantity_ordered' => $data['quantity'],
                    'quantity_received' => 0.0,
                    'unit_price' => $data['ingredient']->reference_price ?? 0.0,
                ]);
            }

            $poCount++;
            $poCodes[] = $poCode;
        }

        Notification::make()
            ->title('Tạo PO thành công!')
            ->body("Đã tạo tự động {$poCount} đơn đặt hàng nháp: ".implode(', ', $poCodes))
            ->success()
            ->persistent()
            ->send();
    }
}
