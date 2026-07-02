<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use App\Models\Ingredient;
use App\Models\Stock;
use App\Models\StockTransaction;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListStocks extends ListRecords
{
    protected static string $resource = StockResource::class;

    protected static string $view = 'filament.pages.warehouse';

    public string $warehouseTab = 'stock';

    public string $search = '';

    public string $selectedType = '';

    // End day check properties
    public ?string $checkDate = null;

    public array $actualQuantities = [];

    public array $checkNotes = [];

    // Movement properties
    public ?int $inIngredientId = null;

    public ?string $inDate = null;

    public float $inQuantity = 0;

    public float $inPrice = 0;

    public string $inRef = '';

    public ?int $outIngredientId = null;

    public ?string $outDate = null;

    public float $outQuantity = 0;

    public string $outReason = 'Sản xuất theo list hàng';

    public string $outRef = '';

    public function mount(): void
    {
        parent::mount();
        $this->checkDate = now()->toDateString();
        $this->inDate = now()->toDateString();
        $this->outDate = now()->toDateString();

        // Initialize actual quantities for end day checks
        $stocks = Stock::all();
        foreach ($stocks as $stock) {
            $this->actualQuantities[$stock->id] = $stock->quantity;
            $this->checkNotes[$stock->id] = '';
        }

        // Set default ingredients
        $firstIng = Ingredient::first();
        if ($firstIng) {
            $this->inIngredientId = $firstIng->id;
            $this->outIngredientId = $firstIng->id;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tạo tồn kho'),
        ];
    }

    public function setTab(string $tab): void
    {
        $this->warehouseTab = $tab;
    }

    public function saveEndDay(): void
    {
        // Mock save actual quantities and generate stock transactions for discrepancies
        foreach ($this->actualQuantities as $stockId => $actualQty) {
            $stock = Stock::find($stockId);
            if (! $stock) {
                continue;
            }

            $diff = $actualQty - $stock->quantity;
            if ($diff != 0) {
                // Record transaction
                StockTransaction::create([
                    'stock_id' => $stock->id,
                    'type' => $diff > 0 ? 'Nhập kho' : 'Xuất kho',
                    'quantity' => abs($diff),
                    'balance_after' => $actualQty,
                    'reference' => 'Kiểm kê cuối ngày '.($this->checkNotes[$stockId] ?: ''),
                ]);

                $stock->update(['quantity' => $actualQty]);
            }
        }

        Notification::make()
            ->title('Đã lưu tồn cuối ngày thành công!')
            ->success()
            ->send();
    }

    public function saveMovement(string $type): void
    {
        if ($type === 'in') {
            if (! $this->inIngredientId || $this->inQuantity <= 0) {
                Notification::make()->title('Vui lòng chọn nguyên liệu và số lượng hợp lệ!')->danger()->send();

                return;
            }

            $stock = Stock::where('ingredient_id', $this->inIngredientId)->first();
            if (! $stock) {
                // Create a stock entry
                $ingredient = Ingredient::find($this->inIngredientId);
                $stock = Stock::create([
                    'ingredient_id' => $this->inIngredientId,
                    'quantity' => 0,
                    'min_quantity' => 10,
                    'unit_price' => $this->inPrice,
                    'status' => 'Đủ hàng',
                ]);
            }

            $newQty = $stock->quantity + $this->inQuantity;
            StockTransaction::create([
                'stock_id' => $stock->id,
                'type' => 'Nhập kho',
                'quantity' => $this->inQuantity,
                'balance_after' => $newQty,
                'reference' => $this->inRef ?: 'Nhập kho trực tiếp',
            ]);

            $stock->update([
                'quantity' => $newQty,
                'unit_price' => $this->inPrice > 0 ? $this->inPrice : $stock->unit_price,
            ]);

            Notification::make()->title('Đã lưu nhập kho thành công!')->success()->send();

            // Reset inputs
            $this->inQuantity = 0;
            $this->inRef = '';
        } else {
            if (! $this->outIngredientId || $this->outQuantity <= 0) {
                Notification::make()->title('Vui lòng chọn nguyên liệu và số lượng hợp lệ!')->danger()->send();

                return;
            }

            $stock = Stock::where('ingredient_id', $this->outIngredientId)->first();
            if (! $stock || $stock->quantity < $this->outQuantity) {
                Notification::make()->title('Số lượng tồn kho không đủ để xuất!')->danger()->send();

                return;
            }

            $newQty = $stock->quantity - $this->outQuantity;
            StockTransaction::create([
                'stock_id' => $stock->id,
                'type' => 'Xuất kho',
                'quantity' => $this->outQuantity,
                'balance_after' => $newQty,
                'reference' => $this->outReason.($this->outRef ? ' ('.$this->outRef.')' : ''),
            ]);

            $stock->update(['quantity' => $newQty]);

            Notification::make()->title('Đã lưu xuất kho thành công!')->success()->send();

            // Reset inputs
            $this->outQuantity = 0;
            $this->outRef = '';
        }
    }

    public function getWarehouseData(): array
    {
        $query = Stock::with(['ingredient.supplier']);

        if (! empty($this->search)) {
            $searchLower = '%'.strtolower($this->search).'%';
            $query->whereHas('ingredient', function ($q) use ($searchLower) {
                $q->whereRaw('LOWER(name) LIKE ?', [$searchLower])
                    ->orWhereRaw('LOWER(code) LIKE ?', [$searchLower]);
            });
        }

        if (! empty($this->selectedType)) {
            $query->whereHas('ingredient', function ($q) {
                $q->where('type', $this->selectedType);
            });
        }

        return $query->get()->toArray();
    }

    public function getLogData(): array
    {
        return StockTransaction::with(['stock.ingredient'])
            ->latest()
            ->take(50)
            ->get()
            ->toArray();
    }

    public function getStats(): array
    {
        $allStocks = Stock::all();
        $totalItems = $allStocks->count();
        $totalValue = $allStocks->sum(fn ($s) => $s->quantity * $s->unit_price);
        $lowStock = $allStocks->filter(fn ($s) => $s->quantity <= $s->min_quantity)->count();

        return [
            'items' => $totalItems,
            'value' => $totalValue,
            'low' => $lowStock,
            'check' => 5, // Mock value
        ];
    }
}
