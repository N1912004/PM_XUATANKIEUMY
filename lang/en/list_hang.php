<?php

return [
    'currency' => 'VND',
    'title' => 'Ingredient List', 'heading' => 'Ingredient list — :date', 'subtitle' => 'Ingredients to prepare by date and shift', 'navigation' => ['group' => 'SUPPLY & WAREHOUSE'],
    'actions' => ['export' => 'Export Excel', 'print' => 'Print list', 'create_po' => 'Create purchase orders', 'today' => 'Today', 'back' => 'Back', 'create_send' => 'Create & send orders'],
    'filters' => ['week' => 'Week', 'in_period' => 'In period', 'out_of_period' => 'Out of period'], 'stats' => ['shifts' => 'Serving shifts', 'portions' => 'Total portions', 'dishes' => 'Dishes to cook', 'ingredients' => 'Ingredient types'],
    'table' => ['ingredient' => 'Ingredient', 'ingredient_name' => 'Ingredient name', 'portions' => 'Portions', 'quantity_g' => 'Qty (g)', 'quantity_kg' => 'Qty (kg)', 'order' => 'Order', 'dishes' => 'Used by dishes', 'demand' => 'Demand', 'stock' => 'Stock', 'manual_quantity' => 'Manual qty', 'unit_price' => 'Unit price', 'total' => 'Total', 'supplier' => 'Supplier', 'dish_total' => ':dish total'],
    'counts' => ['ingredients' => ':count ingredients', 'portions' => ':count portions', 'dishes' => ':count dishes', 'items' => ':count items'],
    'categories' => ['meat' => 'Meat & seafood', 'produce' => 'Vegetables & produce', 'dry' => 'Dry goods & spices'],
    'po' => ['title' => 'Create purchase orders', 'subtitle' => 'Aggregate ingredients → assign suppliers → create orders', 'steps' => ['select_list' => 'Select ingredient list', 'assign_supplier' => 'Assign & confirm suppliers', 'assign_supplier_description' => 'Assign suppliers', 'create' => 'Create orders', 'create_description' => 'Export & send to suppliers'], 'fields' => ['order_date' => 'Order date', 'source_from' => 'Source from', 'source_to' => 'Source to', 'shift' => 'Ingredient shift'], 'quick_supplier' => 'Quick supplier assignment', 'select_supplier' => '-- Select supplier --', 'ordered_code' => 'Ordered · :code', 'group_total' => ':group subtotal', 'grand_total' => 'PURCHASE ORDER TOTAL – :date', 'ordered_title' => ':count orders have been placed for this ingredient today', 'other_orders' => '+:count more orders', 'selection_summary' => ':suppliers suppliers · :selected/:total ingredients'],
    'summary' => ['title' => 'Order summary', 'scope' => 'Scope', 'day' => 'Day', 'order_date' => 'Order date', 'source' => 'List source', 'suppliers' => 'Suppliers', 'value' => 'Total value', 'by_supplier' => 'Allocation by supplier', 'ingredients' => 'Total ingredients', 'ingredient_types' => ':selected/:total types'],
    'empty' => ['no_menu' => 'No menu planned', 'no_menu_description' => 'No menu was found for the selected date and shift.', 'no_order_items' => 'No ingredients to order', 'no_order_items_description' => 'Plan and lock the weekly menu before creating purchase orders.', 'no_allocation' => 'No allocation yet.'],
    'notes' => ['title' => 'Notes', 'separate_orders' => 'Each supplier will receive a separate PO.', 'optional_items' => 'Items left empty will be skipped.', 'manual_priority' => 'Manual quantity overrides total calculations.'],
    'notifications' => ['invalid_order_date' => 'Invalid order date', 'no_selected_items' => 'No ingredients selected for PO creation!', 'po_created' => 'PO created successfully!'],
    'export' => [
        'sheet_title' => 'Ingredient List',
        'heading' => 'DAILY INGREDIENT LIST :date',
        'cols' => [
            'shift' => 'Shift',
            'dish' => 'Dish',
            'portions' => 'Portions',
            'ing_code' => 'Ingredient Code',
            'ing_name' => 'Ingredient Name',
            'dl' => 'Portion Qty (kg)',
            'total_kg' => 'Total Needed (kg)',
            'unit' => 'Unit',
        ],
        'empty' => 'No data available',
    ],
];
