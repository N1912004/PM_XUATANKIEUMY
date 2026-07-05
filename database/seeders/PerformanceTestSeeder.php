<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Ingredient;
use App\Models\Kitchen;
use App\Models\Recipe;
use App\Models\Shift;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PerformanceTestSeeder extends Seeder
{
    /**
     * Seed the database with high volumes of data for performance and scale testing.
     */
    public function run(): void
    {
        // 1. Create default admin if not exists
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        // Disable foreign key checks for clean truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate large tables to avoid overlapping data
        DB::table('timekeepings')->truncate();
        DB::table('menus')->truncate();
        DB::table('purchase_order_items')->truncate();
        DB::table('purchase_orders')->truncate();
        DB::table('stock_transactions')->truncate();
        DB::table('food_safety_audits')->truncate();
        DB::table('employees')->truncate();
        DB::table('stocks')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $faker = Factory::create('vi_VN');

        $this->command->info('Seeding Areas & Kitchens...');
        // 1. Areas & Kitchens
        $areas = [];
        $areaIds = [];
        $areaNames = ['Đồng Nai', 'Hồ Chí Minh', 'Bình Dương', 'Hà Nội', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ', 'Bắc Ninh', 'Quảng Ninh', 'Khánh Hòa'];
        for ($i = 0; $i < 10; $i++) {
            $code = 'KV-'.Str::upper(Str::random(3));
            $area = Area::firstOrCreate(['code' => $code], [
                'name' => $areaNames[$i],
                'status' => 'Đang hoạt động',
                'notes' => 'Khu vực tự động tạo để test hiệu năng '.$areaNames[$i],
            ]);
            $areaIds[] = $area->id;
            $areas[] = $area;
        }

        $kitchens = [];
        $kitchenIds = [];
        for ($i = 1; $i <= 20; $i++) {
            $areaId = $areaIds[array_rand($areaIds)];
            $kitchen = Kitchen::firstOrCreate(['name' => 'Nhà ăn Test Hiệu Năng '.$i], [
                'area_id' => $areaId,
                'type' => $i % 2 == 0 ? 'Bếp sản xuất' : 'Nhà ăn phục vụ',
                'capacity' => rand(500, 2000),
                'status' => 'Đang hoạt động',
            ]);
            $kitchenIds[] = $kitchen->id;
            $kitchens[] = $kitchen;
        }

        $this->command->info('Seeding Suppliers, Ingredients & Recipes...');
        // 2. Suppliers
        $supplierIds = DB::table('suppliers')->pluck('id')->toArray();
        if (empty($supplierIds)) {
            for ($i = 1; $i <= 15; $i++) {
                $sup = Supplier::create([
                    'code' => 'NCC'.str_pad($i, 3, '0', STR_PAD_LEFT),
                    'name' => 'Công ty Cung ứng Thực phẩm '.$faker->company,
                    'type' => 'Tổng hợp',
                    'contact_name' => $faker->name,
                    'phone' => $faker->phoneNumber,
                    'email' => $faker->companyEmail,
                    'status' => true,
                ]);
                $supplierIds[] = $sup->id;
            }
        }

        // 3. Ingredients
        $ingredientIds = DB::table('ingredients')->pluck('id')->toArray();
        if (empty($ingredientIds)) {
            $ingTypes = ['Động vật', 'Thực vật', 'Gia vị', 'Thực phẩm khô'];
            for ($i = 1; $i <= 50; $i++) {
                $ing = Ingredient::create([
                    'code' => 'NL'.str_pad($i, 3, '0', STR_PAD_LEFT),
                    'name' => 'Nguyên liệu test '.$i,
                    'type' => $ingTypes[array_rand($ingTypes)],
                    'unit' => 'Kg',
                    'supplier_id' => $supplierIds[array_rand($supplierIds)],
                    'reference_price' => rand(10, 200) * 1000,
                    'status' => true,
                ]);
                $ingredientIds[] = $ing->id;
            }
        }

        // 4. Recipes
        $recipeIds = DB::table('recipes')->pluck('id')->toArray();
        if (empty($recipeIds)) {
            $types = ['Món 1', 'Món 2', 'Rau xào/Luộc', 'Canh'];
            for ($i = 1; $i <= 30; $i++) {
                $recipe = Recipe::create([
                    'code' => 'MON'.str_pad($i, 3, '0', STR_PAD_LEFT),
                    'name' => 'Món ăn Test '.$i,
                    'type' => $types[array_rand($types)],
                    'price_level' => rand(10, 50) * 1000,
                    'actual_price' => rand(10, 50) * 1000,
                    'status' => 'active',
                ]);
                $recipeIds[] = $recipe->id;

                // Sync 2-4 ingredients
                $syncData = [];
                $selectedIngs = array_rand(array_flip($ingredientIds), rand(2, 4));
                foreach ((array) $selectedIngs as $ingId) {
                    $syncData[$ingId] = ['quantity_per_portion' => rand(5, 150) / 1000];
                }
                $recipe->ingredients()->sync($syncData);
            }
        }

        // 5. Stocks (one record per ingredient per kitchen)
        $stocks = [];
        foreach ($kitchenIds as $kId) {
            foreach ($ingredientIds as $ingId) {
                $stocks[] = [
                    'kitchen_id' => $kId,
                    'ingredient_id' => $ingId,
                    'quantity' => rand(100, 1000),
                    'min_quantity' => rand(10, 50),
                    'unit_price' => rand(10, 150) * 1000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        foreach (array_chunk($stocks, 1000) as $chunk) {
            DB::table('stocks')->insert($chunk);
        }

        $this->command->info('Seeding 1,000 Employees...');
        // 6. Employees
        $employeeIds = [];
        $employeeData = [];
        $departments = ['Nhân sự', 'Kế toán', 'Kho', 'Sản xuất', 'Kinh doanh', 'IT'];
        $positions = ['Quản lý', 'Nhân viên', 'Tổ trưởng', 'Kỹ thuật viên'];
        for ($i = 1; $i <= 1000; $i++) {
            $code = 'NV'.str_pad($i, 4, '0', STR_PAD_LEFT);
            $kitchen = $kitchens[array_rand($kitchens)];

            $employeeData[] = [
                'code' => $code,
                'name' => $faker->name,
                'email' => strtolower(Str::random(8)).'@bluefire.vn',
                'phone' => '0987.654.'.str_pad($i, 3, '0', STR_PAD_LEFT),
                'department' => $departments[array_rand($departments)],
                'position' => $positions[array_rand($positions)],
                'area_id' => $kitchen->area_id,
                'kitchen_id' => $kitchen->id,
                'start_date' => Carbon::now()->subMonths(rand(1, 36))->format('Y-m-d'),
                'status' => 'Đang làm việc',
                'avatar_url' => 'https://images.unsplash.com/photo-'.(1500000000000 + rand(1000000, 9000000)).'?auto=format&fit=crop&w=100&q=80',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        foreach (array_chunk($employeeData, 200) as $chunk) {
            DB::table('employees')->insert($chunk);
        }
        $employeeIds = DB::table('employees')->pluck('id')->toArray();

        // Update Area & Kitchen managers with some employees
        foreach ($areas as $area) {
            $area->update(['manager_id' => $employeeIds[array_rand($employeeIds)]]);
        }
        foreach ($kitchens as $kitchen) {
            $kitchen->update(['manager_id' => $employeeIds[array_rand($employeeIds)]]);
        }

        $this->command->info('Seeding 100,000 Timekeepings...');
        // 7. Timekeepings (100 days of clock-ins)
        $shiftIds = DB::table('shifts')->pluck('id')->toArray();
        if (empty($shiftIds)) {
            // Seed default shifts if missing
            $shift1 = Shift::firstOrCreate(['name' => 'CA 1'], ['time_range' => '07:00 - 16:00']);
            $shift2 = Shift::firstOrCreate(['name' => 'CA 2'], ['time_range' => '16:00 - 00:00']);
            $shift3 = Shift::firstOrCreate(['name' => 'CA 3'], ['time_range' => '00:00 - 07:00']);
            $shift4 = Shift::firstOrCreate(['name' => 'CA 4'], ['time_range' => 'Ca đặc biệt']);
            $shiftIds = [$shift1->id, $shift2->id, $shift3->id, $shift4->id];
        }

        $timekeepings = [];
        $startDate = Carbon::now()->subDays(100);
        $statuses = ['Đúng giờ', 'Đi trễ', 'Tăng ca', 'Nghỉ phép', 'Vắng mặt'];

        for ($d = 0; $d < 100; $d++) {
            $date = (clone $startDate)->addDays($d)->format('Y-m-d');
            foreach ($employeeIds as $empId) {
                $status = $statuses[array_rand($statuses)];
                $timekeepings[] = [
                    'employee_id' => $empId,
                    'date' => $date,
                    'shift_id' => $shiftIds[array_rand($shiftIds)],
                    'check_in' => $status === 'Vắng mặt' ? null : '07:'.str_pad(rand(0, 30), 2, '0', STR_PAD_LEFT),
                    'check_out' => $status === 'Vắng mặt' ? null : '16:'.str_pad(rand(0, 45), 2, '0', STR_PAD_LEFT),
                    'overtime_hours' => $status === 'Tăng ca' ? rand(1, 3).'h' : '0h',
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Bulk insert in chunks of 1,000 to optimize performance
            if (count($timekeepings) >= 1000) {
                DB::table('timekeepings')->insert($timekeepings);
                $timekeepings = [];
            }
        }
        if (count($timekeepings) > 0) {
            DB::table('timekeepings')->insert($timekeepings);
        }

        $this->command->info('Seeding 5,000 Menus...');
        // 8. Menus (Planning menus for the 20 kitchens over 100 days and next 30 days)
        $menus = [];
        $menuStatuses = ['draft', 'sent', 'locked'];
        for ($d = -100; $d <= 30; $d++) {
            $date = Carbon::now()->addDays($d)->format('Y-m-d');
            foreach ($kitchenIds as $kId) {
                // Seed 2 menus per kitchen per day
                for ($m = 1; $m <= 2; $m++) {
                    $menus[] = [
                        'kitchen_id' => $kId,
                        'date' => $date,
                        'shift_id' => $shiftIds[array_rand($shiftIds)],
                        'recipe_id' => $recipeIds[array_rand($recipeIds)],
                        'estimated_portions' => rand(100, 500),
                        'status' => $date < Carbon::now()->format('Y-m-d') ? 'locked' : $menuStatuses[array_rand($menuStatuses)],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (count($menus) >= 1000) {
                    DB::table('menus')->insert($menus);
                    $menus = [];
                }
            }
        }
        if (count($menus) > 0) {
            DB::table('menus')->insert($menus);
        }

        $this->command->info('Seeding 10,000 Purchase Orders & 30,000 Items...');
        // 9. Purchase Orders
        $poStatuses = ['draft', 'sent', 'checking', 'done'];
        $poData = [];
        for ($i = 1; $i <= 10000; $i++) {
            $poData[] = [
                'kitchen_id' => $kitchenIds[array_rand($kitchenIds)],
                'code' => 'PO-'.Carbon::now()->format('Ymd').'-'.str_pad($i, 5, '0', STR_PAD_LEFT),
                'supplier_id' => $supplierIds[array_rand($supplierIds)],
                'status' => $poStatuses[array_rand($poStatuses)],
                'stocked_at' => rand(0, 1) ? Carbon::now()->subDays(rand(0, 60)) : null,
                'estimated_delivery_date' => Carbon::now()->addDays(rand(-30, 30))->format('Y-m-d'),
                'note' => 'Đơn mua hàng tự động tạo để test hiệu năng '.$i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        foreach (array_chunk($poData, 1000) as $chunk) {
            DB::table('purchase_orders')->insert($chunk);
        }

        $poIds = DB::table('purchase_orders')->pluck('id')->toArray();
        $poItems = [];
        foreach ($poIds as $poId) {
            // 3 items per PO
            for ($j = 0; $j < 3; $j++) {
                $poItems[] = [
                    'purchase_order_id' => $poId,
                    'ingredient_id' => $ingredientIds[array_rand($ingredientIds)],
                    'quantity_ordered' => rand(10, 500),
                    'quantity_received' => rand(10, 500),
                    'unit_price' => rand(10, 150) * 1000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (count($poItems) >= 1000) {
                DB::table('purchase_order_items')->insert($poItems);
                $poItems = [];
            }
        }
        if (count($poItems) > 0) {
            DB::table('purchase_order_items')->insert($poItems);
        }

        $this->command->info('Seeding 50,000 Stock Transactions...');
        // 10. Stock Transactions
        $txTypes = ['in', 'out'];
        $transactions = [];
        for ($i = 1; $i <= 50000; $i++) {
            $transactions[] = [
                'kitchen_id' => $kitchenIds[array_rand($kitchenIds)],
                'type' => $txTypes[array_rand($txTypes)],
                'voucher_code' => 'VOUCHER-'.str_pad($i, 5, '0', STR_PAD_LEFT),
                'ingredient_id' => $ingredientIds[array_rand($ingredientIds)],
                'quantity' => rand(10, 200),
                'after_quantity' => rand(200, 1000),
                'note' => 'Giao dịch kho '.$i,
                'attachment_url' => 'https://example.com/receipt-'.$i.'.pdf',
                'created_at' => Carbon::now()->subDays(rand(0, 100)),
                'updated_at' => now(),
            ];
            if (count($transactions) >= 1000) {
                DB::table('stock_transactions')->insert($transactions);
                $transactions = [];
            }
        }
        if (count($transactions) > 0) {
            DB::table('stock_transactions')->insert($transactions);
        }

        $this->command->info('Seeding 5,000 Food Safety Audits...');
        // 11. Food Safety Audits
        $auditStages = ['Bước 1', 'Bước 2', 'Bước 3', 'Lưu mẫu', 'Hủy mẫu'];
        $auditStatuses = ['Đạt', 'Không đạt'];
        $audits = [];
        for ($i = 1; $i <= 5000; $i++) {
            $auditDate = Carbon::now()->subDays(rand(0, 100))->format('Y-m-d');
            $audits[] = [
                'date' => $auditDate,
                'shift_id' => $shiftIds[array_rand($shiftIds)],
                'recipe_id' => $recipeIds[array_rand($recipeIds)],
                'stage' => $auditStages[array_rand($auditStages)],
                'status' => $auditStatuses[array_rand($auditStatuses)],
                'inspected_by' => $employeeIds[array_rand($employeeIds)],
                'cook_start_at' => '08:00',
                'cook_end_at' => '10:30',
                'temperature' => rand(70, 85) + (rand(0, 9) / 10),
                'sample_kept_by' => $employeeIds[array_rand($employeeIds)],
                'sample_kept_at' => $auditDate.' 11:00:00',
                'sample_code' => 'SAMPLE-'.str_pad($i, 5, '0', STR_PAD_LEFT),
                'utensil' => 'Đạt tiêu chuẩn kiểm định',
                'notes' => 'Mẫu kiểm định tự động '.$i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (count($audits) >= 1000) {
                DB::table('food_safety_audits')->insert($audits);
                $audits = [];
            }
        }
        if (count($audits) > 0) {
            DB::table('food_safety_audits')->insert($audits);
        }

        $this->command->info('Seeding Shield Roles...');
        $this->call(ShieldRoleSeeder::class);

        $this->command->info('Successfully seeded database for performance testing!');
    }
}
