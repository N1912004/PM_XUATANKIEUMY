<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Employee;
use App\Models\Ingredient;
use App\Models\Kitchen;
use App\Models\LeaveOvertime;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\Shift;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Timekeeping;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create default admin if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        // 1b. Phân quyền chạy NGAY sau khi tạo admin — hạ tầng vai trò không được phụ thuộc vào
        // dữ liệu demo (nhân viên/chấm công/nghỉ phép) chạy trót lọt. Nếu để cuối, một lỗi seed
        // demo bất kỳ sẽ khiến ShieldRoleSeeder không chạy → admin không có vai trò → 403 khi
        // đăng nhập (canAccessPanel yêu cầu có ít nhất 1 vai trò).
        $this->call(ShieldRoleSeeder::class);

        // 2. Suppliers (Ncc.png)
        $sup1 = Supplier::firstOrCreate(['code' => 'NCC001'], [
            'name' => 'Công ty TNHH Thực phẩm Hưng Thịnh',
            'type' => 'Tổng hợp',
            'contact_name' => 'Lê Hoàng Cường',
            'phone' => '0987.654.321',
            'email' => 'contact@hungthinhfood.vn',
            'status' => true,
        ]);

        $sup2 = Supplier::firstOrCreate(['code' => 'NCC002'], [
            'name' => 'Cơ sở Rau sạch Xanh Việt',
            'type' => 'Rau củ',
            'contact_name' => 'Phạm Thị Dung',
            'phone' => '0912.345.678',
            'email' => 'xanhviet@rauqua.vn',
            'status' => true,
        ]);

        $sup3 = Supplier::firstOrCreate(['code' => 'NCC003'], [
            'name' => 'Đại lý Thực phẩm khô Nam Phát',
            'type' => 'Thực phẩm khô',
            'contact_name' => 'Trần Thị Bình',
            'phone' => '0909.090.909',
            'email' => 'namphat@dryfood.vn',
            'status' => true,
        ]);

        // 3. Ingredients (ListNguyenLieu.png)
        $ing1 = Ingredient::firstOrCreate(['code' => 'NL001'], [
            'name' => 'Vịt bọng',
            'type' => 'Động vật',
            'unit' => 'Kg',
            'supplier_id' => $sup1->id,
            'reference_price' => 125000.00,
            'status' => true,
        ]);

        $ing2 = Ingredient::firstOrCreate(['code' => 'NL002'], [
            'name' => 'Trứng gà tươi',
            'type' => 'Động vật',
            'unit' => 'Quả',
            'supplier_id' => $sup1->id,
            'reference_price' => 3000.00,
            'status' => true,
        ]);

        $ing3 = Ingredient::firstOrCreate(['code' => 'NL003'], [
            'name' => 'Su su',
            'type' => 'Thực vật',
            'unit' => 'Kg',
            'supplier_id' => $sup2->id,
            'reference_price' => 12000.00,
            'status' => true,
        ]);

        $ing4 = Ingredient::firstOrCreate(['code' => 'NL004'], [
            'name' => 'Canh cải xanh',
            'type' => 'Thực vật',
            'unit' => 'Kg',
            'supplier_id' => $sup2->id,
            'reference_price' => 15000.00,
            'status' => true,
        ]);

        $ing5 = Ingredient::firstOrCreate(['code' => 'NL005'], [
            'name' => 'Cơm trắng (Gạo)',
            'type' => 'Thực phẩm khô',
            'unit' => 'Kg',
            'supplier_id' => $sup3->id,
            'reference_price' => 18000.00,
            'status' => true,
        ]);

        $ing6 = Ingredient::firstOrCreate(['code' => 'NL006'], [
            'name' => 'Bò viên chay',
            'type' => 'Thực vật',
            'unit' => 'Kg',
            'supplier_id' => $sup1->id,
            'reference_price' => 85000.00,
            'status' => true,
        ]);

        $ing7 = Ingredient::firstOrCreate(['code' => 'NL007'], [
            'name' => 'Riềng củ',
            'type' => 'Gia vị',
            'unit' => 'Kg',
            'supplier_id' => $sup1->id,
            'reference_price' => 25000.00,
            'status' => true,
        ]);

        $ing8 = Ingredient::firstOrCreate(['code' => 'NL008'], [
            'name' => 'Sả cây',
            'type' => 'Gia vị',
            'unit' => 'Kg',
            'supplier_id' => $sup1->id,
            'reference_price' => 20000.00,
            'status' => true,
        ]);

        // 4. Recipes & Recipe Ingredients
        $recipe1 = Recipe::firstOrCreate(['code' => 'MON001'], [
            'name' => 'Vịt kho riềng sả',
            'type' => 'Món 1',
            'selling_price_per_portion' => 35000.00,
            'cost_per_portion' => 32000.00,
            'status' => 'active',
        ]);
        $recipe1->ingredients()->syncWithoutDetaching([
            $ing1->id => ['quantity_per_portion' => 0.125], // 125g
            $ing7->id => ['quantity_per_portion' => 0.003], // 3g
            $ing8->id => ['quantity_per_portion' => 0.003], // 3g
        ]);

        $recipe2 = Recipe::firstOrCreate(['code' => 'MON002'], [
            'name' => 'Trứng luộc sốt me',
            'type' => 'Món 2',
            'selling_price_per_portion' => 15000.00,
            'cost_per_portion' => 12000.00,
            'status' => 'active',
        ]);
        $recipe2->ingredients()->syncWithoutDetaching([
            $ing2->id => ['quantity_per_portion' => 2.0], // 2 quả
        ]);

        $recipe3 = Recipe::firstOrCreate(['code' => 'MON003'], [
            'name' => 'Su su xào',
            'type' => 'Rau xào/Luộc',
            'selling_price_per_portion' => 10000.00,
            'cost_per_portion' => 8000.00,
            'status' => 'active',
        ]);
        $recipe3->ingredients()->syncWithoutDetaching([
            $ing3->id => ['quantity_per_portion' => 0.150],
        ]);

        // 5. Shifts (Ca)
        $shift1 = Shift::firstOrCreate(['name' => 'CA 1'], [
            'time_range' => '07:00 - 16:00',
        ]);
        $shift2 = Shift::firstOrCreate(['name' => 'CA 2'], [
            'time_range' => '16:00 - 00:00',
        ]);
        $shift3 = Shift::firstOrCreate(['name' => 'CA 3'], [
            'time_range' => '00:00 - 07:00',
        ]);
        // 6. Menus (18/05/2026 - T2)
        $dateStr = '2026-05-18';
        Menu::firstOrCreate([
            'date' => $dateStr,
            'shift_id' => $shift1->id,
            'recipe_id' => $recipe1->id,
        ], [
            'estimated_portions' => 240,
            'status' => 'locked',
        ]);

        Menu::firstOrCreate([
            'date' => $dateStr,
            'shift_id' => $shift1->id,
            'recipe_id' => $recipe2->id,
        ], [
            'estimated_portions' => 240,
            'status' => 'locked',
        ]);

        Menu::firstOrCreate([
            'date' => $dateStr,
            'shift_id' => $shift1->id,
            'recipe_id' => $recipe3->id,
        ], [
            'estimated_portions' => 240,
            'status' => 'locked',
        ]);

        // 7. Stocks
        foreach ([$ing1, $ing2, $ing3, $ing4, $ing5, $ing6, $ing7, $ing8] as $ing) {
            Stock::firstOrCreate(['ingredient_id' => $ing->id], [
                'quantity' => 150.000,
                'min_quantity' => 30.000,
                'unit_price' => $ing->reference_price,
            ]);
        }

        // Areas & Kitchens (defined early to resolve circular dependencies)
        $area1 = Area::firstOrCreate(['code' => 'KV-DN'], [
            'name' => 'Đồng Nai',
            'status' => true,
            'notes' => 'Khu vực vận hành Nhơn Trạch',
        ]);
        $area2 = Area::firstOrCreate(['code' => 'KV-HCM'], [
            'name' => 'Hồ Chí Minh',
            'status' => true,
            'notes' => 'Các điểm phục vụ nội thành',
        ]);
        $area3 = Area::firstOrCreate(['code' => 'KV-BD'], [
            'name' => 'Bình Dương',
            'status' => false,
            'notes' => 'Đang rà soát lại công suất',
        ]);

        $kitchen1 = Kitchen::firstOrCreate(['name' => 'Nhà ăn Nhơn Trạch 1'], [
            'area_id' => $area1->id,
            'type' => 'Bếp sản xuất',
            'capacity' => 1800,
            'status' => true,
        ]);
        $kitchen2 = Kitchen::firstOrCreate(['name' => 'Nhà ăn Summit'], [
            'area_id' => $area1->id,
            'type' => 'Nhà ăn phục vụ',
            'capacity' => 900,
            'status' => true,
        ]);

        // 8. Employees (Nhanvien.png)
        $emp1 = Employee::firstOrCreate(['code' => 'NV001'], [
            'name' => 'Nguyễn Văn An',
            'email' => 'vanan@bluefire.vn',
            'phone' => '0987.654.001',
            'department' => 'Nhân sự',
            'position' => 'Quản lý nhân sự',
            'area_id' => $area1->id,
            'kitchen_id' => null,
            'start_date' => '2022-03-15',
            'status' => 'working',
            'avatar_url' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=100&q=80',
        ]);

        $emp2 = Employee::firstOrCreate(['code' => 'NV002'], [
            'name' => 'Trần Thị Bình',
            'email' => 'thibinh@bluefire.vn',
            'phone' => '0987.654.002',
            'department' => 'Kế toán',
            'position' => 'Kế toán trưởng',
            'area_id' => $area1->id,
            'kitchen_id' => $kitchen2->id,
            'start_date' => '2021-01-10',
            'status' => 'working',
            'avatar_url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=100&q=80',
        ]);

        $emp3 = Employee::firstOrCreate(['code' => 'NV003'], [
            'name' => 'Lê Hoàng Cường',
            'email' => 'hoangcuong@bluefire.vn',
            'phone' => '0987.654.003',
            'department' => 'Kho',
            'position' => 'Thủ kho',
            'area_id' => $area1->id,
            'kitchen_id' => $kitchen1->id,
            'start_date' => '2022-06-05',
            'status' => 'working',
            'avatar_url' => 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?auto=format&fit=crop&w=100&q=80',
        ]);

        $emp4 = Employee::firstOrCreate(['code' => 'NV004'], [
            'name' => 'Phạm Thị Dung',
            'email' => 'thidung@bluefire.vn',
            'phone' => '0987.654.004',
            'department' => 'Sản xuất',
            'position' => 'Tổ trưởng bếp',
            'area_id' => $area2->id,
            'kitchen_id' => null,
            'start_date' => '2022-08-20',
            'status' => 'on_leave',
            'avatar_url' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=100&q=80',
        ]);

        $emp5 = Employee::firstOrCreate(['code' => 'NV005'], [
            'name' => 'Hoàng Minh Đức',
            'email' => 'minhduc@bluefire.vn',
            'phone' => '0987.654.005',
            'department' => 'Kinh doanh',
            'position' => 'Nhân viên kinh doanh',
            'area_id' => $area1->id,
            'kitchen_id' => null,
            'start_date' => '2022-11-12',
            'status' => 'working',
            'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80',
        ]);

        $emp6 = Employee::firstOrCreate(['code' => 'NV006'], [
            'name' => 'Vũ Thị Mai',
            'email' => 'thimai@bluefire.vn',
            'phone' => '0987.654.006',
            'department' => 'Chăm sóc KH',
            'position' => 'Nhân viên CSKH',
            'area_id' => $area1->id,
            'kitchen_id' => null,
            'start_date' => '2023-02-01',
            'status' => 'working',
            'avatar_url' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100&q=80',
        ]);

        $emp7 = Employee::firstOrCreate(['code' => 'NV007'], [
            'name' => 'Đặng Quốc Bảo',
            'email' => 'quocbao@bluefire.vn',
            'phone' => '0987.654.007',
            'department' => 'IT',
            'position' => 'Lập trình viên',
            'area_id' => $area1->id,
            'kitchen_id' => null,
            'start_date' => '2023-04-18',
            'status' => 'resigned',
            'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=100&q=80',
        ]);

        $emp8 = Employee::firstOrCreate(['code' => 'NV008'], [
            'name' => 'Ngô Thị Lan',
            'email' => 'thilan@bluefire.vn',
            'phone' => '0987.654.008',
            'department' => 'Sản xuất',
            'position' => 'Nhân viên sơ chế',
            'area_id' => $area2->id,
            'kitchen_id' => null,
            'start_date' => '2023-05-22',
            'status' => 'working',
            'avatar_url' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=100&q=80',
        ]);

        // 9. Timekeepings (Chamcong.png for 15/05/2026)
        $tcDate = '2026-05-15';
        Timekeeping::firstOrCreate(['employee_id' => $emp1->id, 'date' => $tcDate], [
            'shift_id' => $shift1->id,
            'check_in' => '07:01',
            'check_out' => '16:05',
            'overtime_hours' => '0h',
            'status' => 'on_time',
        ]);
        Timekeeping::firstOrCreate(['employee_id' => $emp2->id, 'date' => $tcDate], [
            'shift_id' => $shift1->id,
            'check_in' => '07:18',
            'check_out' => '16:12',
            'overtime_hours' => '0h',
            'status' => 'late',
        ]);
        Timekeeping::firstOrCreate(['employee_id' => $emp3->id, 'date' => $tcDate], [
            'shift_id' => $shift1->id,
            'check_in' => '06:58',
            'check_out' => '18:10',
            'overtime_hours' => '2h10',
            'status' => 'on_time',
        ]);
        Timekeeping::firstOrCreate(['employee_id' => $emp4->id, 'date' => $tcDate], [
            'shift_id' => $shift1->id,
            'check_in' => null,
            'check_out' => null,
            'overtime_hours' => '0h',
            'status' => 'absent',
        ]);
        Timekeeping::firstOrCreate(['employee_id' => $emp5->id, 'date' => $tcDate], [
            'shift_id' => $shift1->id,
            'check_in' => '07:05',
            'check_out' => '17:35',
            'overtime_hours' => '1h30',
            'status' => 'on_time',
        ]);
        Timekeeping::firstOrCreate(['employee_id' => $emp6->id, 'date' => $tcDate], [
            'shift_id' => $shift1->id,
            'check_in' => '07:00',
            'check_out' => '16:00',
            'overtime_hours' => '0h',
            'status' => 'on_time',
        ]);
        Timekeeping::firstOrCreate(['employee_id' => $emp7->id, 'date' => $tcDate], [
            'shift_id' => $shift1->id,
            'check_in' => '07:25',
            'check_out' => '16:08',
            'overtime_hours' => '0h',
            'status' => 'late',
        ]);
        Timekeeping::firstOrCreate(['employee_id' => $emp8->id, 'date' => $tcDate], [
            'shift_id' => $shift1->id,
            'check_in' => null,
            'check_out' => null,
            'overtime_hours' => '0h',
            'status' => 'absent',
        ]);

        // 10. Leave & Overtimes (NghiphepvaTangca.png)
        LeaveOvertime::firstOrCreate(['employee_id' => $emp1->id, 'start_date' => '2026-05-15'], [
            'type' => 'Nghỉ phép năm',
            'end_date' => '2026-05-15',
            'duration_text' => '1 ngày',
            'reason' => 'Về quê',
            'approver_id' => $emp2->id,
            'status' => 'pending',
        ]);
        LeaveOvertime::firstOrCreate(['employee_id' => $emp8->id, 'start_date' => '2026-05-16'], [
            'type' => 'Nghỉ phép bệnh',
            'end_date' => '2026-05-16',
            'duration_text' => '1 ngày',
            'reason' => 'Khám bệnh',
            'approver_id' => $emp3->id,
            'status' => 'pending',
        ]);
        LeaveOvertime::firstOrCreate(['employee_id' => $emp5->id, 'start_date' => '2026-05-14'], [
            'type' => 'Tăng ca ngày thường',
            'end_date' => '2026-05-14',
            'duration_text' => '2.5 giờ',
            'reason' => 'Hoàn thành đơn hàng',
            'approver_id' => $emp1->id,
            'status' => 'approved',
        ]);

        // 11. Update managers for Areas & Kitchens (Khuvuc.png)
        $area1->update(['manager_id' => $emp1->id]);
        $area2->update(['manager_id' => $emp4->id]);
        $area3->update(['manager_id' => $emp2->id]);

        $kitchen1->update(['manager_id' => $emp3->id]);
        $kitchen2->update(['manager_id' => $emp2->id]);

        // Dữ liệu demo đầy đủ cho trang /admin/food-safety-audits:
        // locked menu + PO đã nhập kho + B2/B3/Lưu mẫu/Hủy mẫu.
        $this->call(FoodSafetyAuditDemoSeeder::class);

        // Chạy lại ShieldRoleSeeder ở cuối để gán vai trò cho các user tạo THÊM trong đợt seed này
        // (seeder idempotent — chạy 2 lần không sao); lần chạy ở đầu đã đảm bảo admin có quyền.
        $this->call(ShieldRoleSeeder::class);
    }
}
