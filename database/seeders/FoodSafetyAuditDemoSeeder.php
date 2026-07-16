<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Employee;
use App\Models\FoodSafetyAudit;
use App\Models\Ingredient;
use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Recipe;
use App\Models\Shift;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodSafetyAuditDemoSeeder extends Seeder
{
    private const DATE = '2026-05-18';

    public function run(): void
    {
        DB::transaction(function (): void {
            $area = Area::query()->firstOrCreate(
                ['code' => 'KV-DEMO-KT3B'],
                ['name' => 'Khu vực demo kiểm thực', 'status' => true],
            );

            $kitchen = Kitchen::query()->firstOrCreate(
                ['name' => 'Nhà ăn Test Hiệu Năng 5'],
                [
                    'area_id' => $area->id,
                    'type' => 'Nhà ăn phục vụ',
                    'capacity' => 1600,
                    'status' => 'active',
                ],
            );

            $shift = Shift::query()->firstOrCreate(
                ['name' => 'CA 1'],
                ['time_range' => '07:00 - 16:00'],
            );

            $suppliers = $this->suppliers();
            $ingredients = $this->ingredients($suppliers);
            $recipes = $this->recipes($ingredients);
            $inspector = $this->inspector($area, $kitchen);

            foreach ($recipes as $index => $recipe) {
                Menu::query()->updateOrCreate(
                    [
                        'kitchen_id' => $kitchen->id,
                        'date' => self::DATE,
                        'shift_id' => $shift->id,
                        'recipe_id' => $recipe->id,
                    ],
                    [
                        'estimated_portions' => $this->portions($recipe->name),
                        'status' => 'locked',
                    ],
                );

                $this->auditRows($recipe, $shift, $index, $inspector->name);
            }

            $this->purchaseOrders($kitchen, $ingredients);
        });
    }

    /**
     * @return array<string, Supplier>
     */
    private function suppliers(): array
    {
        $rows = [
            'fresh' => ['DEMO-FRESH', 'Công ty TNHH Feddy', 'Động vật', 'Lê Chiến Thắng', '0975438912'],
            'veg' => ['DEMO-VEG', 'TNHH TMDV Linh Thịnh', 'Rau củ quả', 'Nguyễn Duy Minh', '0967603339'],
            'dry' => ['DEMO-DRY', 'Hộ KD Vũ Đức Thọ', 'Thực phẩm khô', 'Vũ Đức Thọ', '0962156078'],
            'tofu' => ['DEMO-TOFU', 'Đậu Hủ Vũ Biên', 'Thực phẩm chế biến', 'Trần Thái Hoà', '0986155066'],
            'rice' => ['DEMO-RICE', 'Cơ sở Gạo An Phát', 'Lương thực', 'Phạm Minh An', '0908111222'],
        ];

        return collect($rows)->mapWithKeys(fn (array $row, string $key): array => [
            $key => Supplier::query()->updateOrCreate(
                ['code' => $row[0]],
                [
                    'name' => $row[1],
                    'type' => $row[2],
                    'contact_name' => $row[3],
                    'phone' => $row[4],
                    'status' => true,
                ],
            ),
        ])->all();
    }

    /**
     * @param  array<string, Supplier>  $suppliers
     * @return array<string, Ingredient>
     */
    private function ingredients(array $suppliers): array
    {
        $rows = [
            'chicken' => ['DEMO-ING-001', 'Gà bọng', 'Động vật', 'Kg', 90000, 'fresh'],
            'pork' => ['DEMO-ING-002', 'Thịt xay', 'Động vật', 'Kg', 115000, 'fresh'],
            'fish' => ['DEMO-ING-003', 'Cá sapa', 'Động vật', 'Kg', 85000, 'fresh'],
            'beef' => ['DEMO-ING-004', 'Bò', 'Động vật', 'Kg', 210000, 'fresh'],
            'bitter_melon' => ['DEMO-ING-005', 'Khổ qua', 'Thực vật', 'Kg', 18000, 'veg'],
            'bean_sprout' => ['DEMO-ING-006', 'Giá sống', 'Thực vật', 'Kg', 14000, 'veg'],
            'chives' => ['DEMO-ING-007', 'Hẹ', 'Thực vật', 'Kg', 35000, 'veg'],
            'mustard' => ['DEMO-ING-008', 'Cải xanh', 'Thực vật', 'Kg', 18000, 'veg'],
            'noodle' => ['DEMO-ING-009', 'Mỳ trần', 'Thực phẩm khô', 'Kg', 28000, 'dry'],
            'watermelon' => ['DEMO-ING-010', 'Dưa hấu', 'Trái cây', 'Kg', 12000, 'veg'],
            'bok_choy' => ['DEMO-ING-011', 'Cải thìa', 'Thực vật', 'Kg', 22000, 'veg'],
            'mushroom' => ['DEMO-ING-012', 'Nấm đông cô', 'Thực vật', 'Kg', 160000, 'veg'],
            'tofu' => ['DEMO-ING-013', 'Đậu hủ vàng', 'Thực phẩm chế biến', 'Kg', 28000, 'tofu'],
            'rice' => ['DEMO-ING-014', 'Gạo', 'Lương thực', 'Kg', 19000, 'rice'],
            'lemongrass' => ['DEMO-ING-015', 'Sả cây', 'Gia vị', 'Kg', 30000, 'veg'],
            'onion' => ['DEMO-ING-016', 'Hành tây', 'Thực vật', 'Kg', 25000, 'veg'],
            'carrot' => ['DEMO-ING-017', 'Cà rốt', 'Thực vật', 'Kg', 23000, 'veg'],
        ];

        return collect($rows)->mapWithKeys(function (array $row, string $key) use ($suppliers): array {
            $ingredient = Ingredient::query()->updateOrCreate(
                ['code' => $row[0]],
                [
                    'name' => $row[1],
                    'type' => $row[2],
                    'unit' => $row[3],
                    'reference_price' => $row[4],
                    'supplier_id' => $suppliers[$row[5]]->id,
                    'status' => true,
                ],
            );

            return [$key => $ingredient];
        })->all();
    }

    private function inspector(Area $area, Kitchen $kitchen): Employee
    {
        $employee = Employee::query()->updateOrCreate(
            ['code' => 'DEMO-KT3B-NV001'],
            [
                'name' => 'Nguyễn Thị Ánh Ngọc',
                'email' => 'anhngoc.kt3b@bluefire.test',
                'phone' => '0909000001',
                'department' => 'An toàn thực phẩm',
                'position' => 'Nhân viên kiểm thực',
                'area_id' => $area->id,
                'kitchen_id' => $kitchen->id,
                'start_date' => '2025-01-01',
                'status' => 'working',
            ],
        );

        User::query()
            ->whereNull('employee_id')
            ->orderBy('id')
            ->first()
            ?->update(['employee_id' => $employee->id]);

        return $employee;
    }

    /**
     * @param  array<string, Ingredient>  $ingredients
     * @return array<int, Recipe>
     */
    private function recipes(array $ingredients): array
    {
        $rows = [
            ['DEMO-KT3B-001', 'Gà kho sả ớt', 'Món mặn', [
                'chicken' => 0.10, 'lemongrass' => 0.003,
            ]],
            ['DEMO-KT3B-002', 'Khổ qua nhồi thịt', 'Món mặn', [
                'bitter_melon' => 0.10, 'pork' => 0.027,
            ]],
            ['DEMO-KT3B-003', 'Giá xào hẹ', 'Món xào', [
                'bean_sprout' => 0.06, 'chives' => 0.006,
            ]],
            ['DEMO-KT3B-004', 'Canh cải xanh thịt xay', 'Món canh', [
                'mustard' => 0.026, 'pork' => 0.002,
            ]],
            ['DEMO-KT3B-005', 'Mỳ xào bò', 'Món xào', [
                'noodle' => 0.104, 'beef' => 0.056, 'onion' => 0.012, 'carrot' => 0.016,
            ]],
            ['DEMO-KT3B-006', 'Dưa hấu', 'Trái cây', [
                'watermelon' => 0.102,
            ]],
            ['DEMO-KT3B-007', 'Cải thìa sốt nấm đông cô', 'Món chay', [
                'bok_choy' => 0.08, 'mushroom' => 0.006,
            ]],
            ['DEMO-KT3B-008', 'Đậu hủ nhồi chay', 'Món chay', [
                'tofu' => 0.12, 'mushroom' => 0.004,
            ]],
            ['DEMO-KT3B-009', 'Canh bí xanh nấu nấm', 'Món canh', [
                'mushroom' => 0.02, 'carrot' => 0.01,
            ]],
            ['DEMO-KT3B-010', 'Cơm', 'Món chính', [
                'rice' => 0.18,
            ]],
        ];

        return collect($rows)->map(function (array $row) use ($ingredients): Recipe {
            $recipe = Recipe::query()->updateOrCreate(
                ['code' => $row[0]],
                [
                    'name' => $row[1],
                    'type' => $row[2],
                    'status' => 'active',
                ],
            );

            $sync = [];
            foreach ($row[3] as $key => $qty) {
                $sync[$ingredients[$key]->id] = ['quantity_per_portion' => $qty];
            }
            $recipe->ingredients()->sync($sync);

            return $recipe;
        })->all();
    }

    /**
     * @param  array<string, Ingredient>  $ingredients
     */
    private function purchaseOrders(Kitchen $kitchen, array $ingredients): void
    {
        $groups = collect($ingredients)->groupBy(fn (Ingredient $ingredient): int => (int) $ingredient->supplier_id);

        foreach ($groups as $supplierId => $supplierIngredients) {
            $po = PurchaseOrder::query()->updateOrCreate(
                ['code' => 'DEMO-KT3B-PO-'.$supplierId],
                [
                    'kitchen_id' => $kitchen->id,
                    'supplier_id' => $supplierId,
                    'status' => 'done',
                    'type' => 'Nguyên liệu',
                    'estimated_delivery_date' => self::DATE,
                    'stocked_at' => self::DATE.' 05:00:00',
                    'note' => 'Dữ liệu demo kiểm thực 3 bước',
                ],
            );

            foreach ($supplierIngredients as $ingredient) {
                PurchaseOrderItem::query()->updateOrCreate(
                    ['purchase_order_id' => $po->id, 'ingredient_id' => $ingredient->id],
                    [
                        'quantity_ordered' => 100,
                        'quantity_received' => 100,
                        'unit_price' => $ingredient->reference_price,
                    ],
                );
            }
        }
    }

    private function auditRows(Recipe $recipe, Shift $shift, int $index, string $inspector): void
    {
        $prepStart = '07:00:00';
        $cookEnd = '09:30:00';
        $sampleTime = '2026-05-18 10:30:00';

        foreach ([
            'Bước 2' => [
                'status' => 'passed',
                'inspected_by' => $inspector,
                'cook_start_at' => $prepStart,
                'cook_end_at' => $cookEnd,
                'temperature' => (string) (75 + ($index % 5)),
                'notes' => '',
            ],
            'Bước 3' => [
                'status' => 'passed',
                'sample_kept_by' => $inspector,
                'sample_kept_at' => $sampleTime,
                'temperature' => (string) (65 + ($index % 4)),
                'utensil' => $this->utensil($recipe->name),
                'notes' => '',
            ],
            'Lưu mẫu' => [
                'status' => 'passed',
                'sample_kept_by' => $inspector,
                'sample_kept_at' => $sampleTime,
                'sample_code' => 'LM-20260518-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'temperature' => '2-8°C',
                'utensil' => 'Hũ Inox',
                'notes' => 'Đ',
            ],
            'Hủy mẫu' => [
                'status' => 'passed',
                'sample_kept_by' => $inspector,
                'sample_kept_at' => $sampleTime,
                'sample_code' => 'LM-20260518-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'temperature' => '2-8°C',
                'utensil' => 'Hũ Inox',
                'notes' => 'Đ',
            ],
        ] as $stage => $data) {
            FoodSafetyAudit::query()->updateOrCreate(
                [
                    'date' => self::DATE,
                    'shift_id' => $shift->id,
                    'recipe_id' => $recipe->id,
                    'stage' => $stage,
                ],
                $data,
            );
        }
    }

    private function portions(string $recipeName): int
    {
        return match ($recipeName) {
            'Mỳ xào bò' => 250,
            'Cải thìa sốt nấm đông cô', 'Đậu hủ nhồi chay', 'Canh bí xanh nấu nấm' => 70,
            'Dưa hấu' => 1570,
            default => 1250,
        };
    }

    private function utensil(string $recipeName): string
    {
        if (str_contains($recipeName, 'Canh')) {
            return 'Vá, chén, nồi, công';
        }

        if ($recipeName === 'Cơm') {
            return 'Thùng, công';
        }

        if ($recipeName === 'Dưa hấu') {
            return 'Khay, công';
        }

        return 'Vá, khay, công, tủ hâm';
    }
}
