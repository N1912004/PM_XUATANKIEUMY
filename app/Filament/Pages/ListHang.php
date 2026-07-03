<?php

namespace App\Filament\Pages;

use App\Models\Menu;
use App\Models\Shift;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Page;

class ListHang extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'List hàng';

    protected static ?string $title = 'List hàng';

    protected static ?string $navigationGroup = 'CUNG ỨNG & KHO';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.list-hang';

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
                ->where('date', $this->date)
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

        $portions = Menu::where('date', $this->date)
            ->whereIn('shift_id', $this->selectedShifts)
            ->sum('estimated_portions');

        $dishes = Menu::where('date', $this->date)
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
}
