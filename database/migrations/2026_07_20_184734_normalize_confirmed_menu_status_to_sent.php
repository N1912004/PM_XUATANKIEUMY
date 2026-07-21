<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('menus')
            ->where('status', 'confirmed')
            ->update(['status' => 'sent']);
    }

    public function down(): void
    {
        // Không thể phân biệt bản ghi `sent` cũ với bản ghi được chuyển từ `confirmed`.
        // Việc chuẩn hóa trạng thái này chủ ý không đảo ngược để tránh làm sai dữ liệu.
    }
};
