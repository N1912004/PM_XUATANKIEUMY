<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bỏ hẳn bảng `catalogs`: cả 4 nhóm cũ (kitchen_type/department/position/leave_type)
 * đã chuyển sang bảng riêng (kitchen_types/departments/positions/leave_types) và không
 * còn mã nào đọc `catalogs`. Chạy sau migration 173100 để bước seed vẫn đọc được catalogs.
 * Idempotent: dùng dropIfExists nên an toàn kể cả prod đã lỡ mất bảng từ trước.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('catalogs');
    }

    public function down(): void
    {
        if (Schema::hasTable('catalogs')) {
            return;
        }

        Schema::create('catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('group')->index();
            $table->string('name');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['group', 'name']);
        });
    }
};
