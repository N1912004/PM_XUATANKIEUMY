@php
    $getFn = is_callable($get ?? null) ? $get : fn ($key) => null;
    $code = $getFn('code') ?: '--';
    $supplierId = $getFn('supplier_id');
    $supplierName = $supplierId ? \App\Models\Supplier::find($supplierId)?->name : '--';
    $unitId = $getFn('unit_id');
    $unitName = $unitId ? \App\Models\Unit::find($unitId)?->name : '--';
    $typeId = $getFn('ingredient_type_id');
    $typeName = $typeId ? \App\Models\IngredientType::find($typeId)?->name : '--';
@endphp

<div class="flex flex-col divide-y divide-gray-100 dark:divide-gray-800 text-xs">
    <div class="flex items-center justify-between py-2.5">
        <span class="flex items-center text-gray-600 dark:text-gray-400 font-medium">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400 mr-2 flex-shrink-0">
                <i class="fa-solid fa-tag text-[11px]"></i>
            </span>
            Mã nguyên liệu
        </span>
        <span class="font-semibold text-gray-900 dark:text-white">{{ $code }}</span>
    </div>

    <div class="flex items-center justify-between py-2.5">
        <span class="flex items-center text-gray-600 dark:text-gray-400 font-medium">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400 mr-2 flex-shrink-0">
                <i class="fa-solid fa-truck text-[11px]"></i>
            </span>
            Nhà cung cấp
        </span>
        <span class="font-semibold text-gray-900 dark:text-white truncate max-w-[150px]" title="{{ $supplierName }}">{{ $supplierName }}</span>
    </div>

    <div class="flex items-center justify-between py-2.5">
        <span class="flex items-center text-gray-600 dark:text-gray-400 font-medium">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400 mr-2 flex-shrink-0">
                <i class="fa-solid fa-ruler-combined text-[11px]"></i>
            </span>
            Đơn vị
        </span>
        <span class="font-semibold text-gray-900 dark:text-white">{{ $unitName }}</span>
    </div>

    <div class="flex items-center justify-between py-2.5">
        <span class="flex items-center text-gray-600 dark:text-gray-400 font-medium">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400 mr-2 flex-shrink-0">
                <i class="fa-solid fa-tags text-[11px]"></i>
            </span>
            Loại NL
        </span>
        <span class="font-semibold text-gray-900 dark:text-white">{{ $typeName }}</span>
    </div>
</div>
