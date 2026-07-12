<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_type_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_type_id')->constrained()->cascadeOnDelete();
            $table->unique(['supplier_id', 'ingredient_type_id']);
        });

        $this->backfillFromTypeStrings();
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_type_supplier');
    }

    /**
     * Di trú dữ liệu cũ: tách chuỗi `suppliers.type` (ghép dấu phẩy) thành các
     * dòng pivot. Loại chưa có trong danh mục `ingredient_types` sẽ được tạo mới
     * để không mất dữ liệu (quyết định "dùng chung ingredient_types").
     */
    protected function backfillFromTypeStrings(): void
    {
        $typeIdByName = DB::table('ingredient_types')->pluck('id', 'name')->all();

        DB::table('suppliers')
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->orderBy('id')
            ->each(function (object $supplier) use (&$typeIdByName): void {
                $names = array_filter(array_map('trim', explode(',', (string) $supplier->type)));

                foreach (array_unique($names) as $name) {
                    $typeId = $typeIdByName[$name] ??= DB::table('ingredient_types')->insertGetId(['name' => $name]);

                    DB::table('ingredient_type_supplier')->insertOrIgnore([
                        'supplier_id' => $supplier->id,
                        'ingredient_type_id' => $typeId,
                    ]);
                }
            });
    }
};
