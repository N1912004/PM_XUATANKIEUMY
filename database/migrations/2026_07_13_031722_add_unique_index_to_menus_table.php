<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ràng buộc nghiệp vụ: 1 món chỉ xuất hiện 1 lần trong (bếp, ngày, ca).
     * Trước giờ chỉ chống trùng bằng updateOrCreate ở tầng ứng dụng — DB chưa enforce.
     */
    public function up(): void
    {
        // Dọn bản ghi trùng trước khi đánh unique. Giữ bản ghi CÓ GIÁ TRỊ NHẤT của mỗi bộ 4 cột:
        // trạng thái cao nhất trước (locked > confirmed > sent > draft — bản đã chốt là căn cứ
        // sinh PO/kho, tuyệt đối không được xóa nhầm), hòa thì giữ bản mới nhất (id lớn nhất).
        // Viết bằng query builder để chạy được trên cả MySQL lẫn SQLite khi test.
        $statusRank = ['draft' => 0, 'sent' => 1, 'confirmed' => 2, 'locked' => 3];

        $duplicateKeys = DB::table('menus')
            ->select('kitchen_id', 'date', 'shift_id', 'recipe_id')
            ->groupBy('kitchen_id', 'date', 'shift_id', 'recipe_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateKeys as $dup) {
            $rows = DB::table('menus')
                ->where('kitchen_id', $dup->kitchen_id)
                ->where('date', $dup->date)
                ->where('shift_id', $dup->shift_id)
                ->where('recipe_id', $dup->recipe_id)
                ->get(['id', 'status']);

            $keepId = $rows
                ->sortByDesc(fn ($row) => [($statusRank[$row->status] ?? 0), $row->id])
                ->first()
                ->id;

            DB::table('menus')
                ->where('kitchen_id', $dup->kitchen_id)
                ->where('date', $dup->date)
                ->where('shift_id', $dup->shift_id)
                ->where('recipe_id', $dup->recipe_id)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::table('menus', function (Blueprint $table) {
            $table->unique(['kitchen_id', 'date', 'shift_id', 'recipe_id'], 'menus_kitchen_date_shift_recipe_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropUnique('menus_kitchen_date_shift_recipe_unique');
        });
    }
};
