<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->decimal('reference_price', 15, 2)->default(0.00);
            $table->timestamps();
        });

        // Copy dữ liệu liên kết cũ từ ingredients sang ingredient_supplier
        $oldLinks = DB::table('ingredients')
            ->whereNotNull('supplier_id')
            ->select('id', 'supplier_id', 'reference_price', 'created_at', 'updated_at')
            ->get();

        foreach ($oldLinks as $link) {
            DB::table('ingredient_supplier')->insert([
                'ingredient_id' => $link->id,
                'supplier_id' => $link->supplier_id,
                'reference_price' => $link->reference_price,
                'created_at' => $link->created_at ?? now(),
                'updated_at' => $link->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_supplier');
    }
};
