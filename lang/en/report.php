<?php

return [
    'currency' => 'VND',
    'title' => 'Reports', 'heading' => 'Meal Production Report', 'subtitle' => 'Dish and ingredient totals by date range and serving shift',
    'navigation' => ['group' => 'KITCHEN OPERATIONS'], 'actions' => ['export' => 'Export Excel', 'this_week' => 'Current menu week'],
    'filters' => ['from_date' => 'From', 'to_date' => 'To', 'kitchen' => 'Kitchen', 'all_kitchens' => 'All kitchens', 'search' => 'Search dishes / ingredients...'],
    'stats' => ['menu_days' => 'Menu days', 'dishes' => 'Dish entries', 'ingredient_rows' => 'Ingredient rows', 'portions' => 'Total portions', 'cost' => 'Total cost'],
    'ingredients' => ['title' => 'TOTAL INGREDIENT CONSUMPTION'],
    'counts' => ['ingredients' => ':count ingredients', 'dishes' => ':count dishes', 'portions' => ':count portions', 'servings' => ':count servings', 'shifts' => ':count shifts'],
    'table' => ['code' => 'CODE', 'ingredient' => 'INGREDIENT', 'total_consumption' => 'TOTAL CONSUMPTION', 'value' => 'VALUE', 'ingredient_code' => 'Ingredient code', 'ingredient_name' => 'Ingredient name', 'quantity_per_portion' => 'Qty (g/portion)', 'portions' => 'Portions', 'servings' => 'Servings', 'total_kg' => 'Total kg'],
    'empty' => ['title' => 'No data for the selected range', 'description' => 'Change the date range or shift, or try another search term.', 'manual_dish_ingredients' => 'Manually entered dish – ingredients have not been defined in the menu bank'],
    'export_title' => 'Financial_Report',
    'export_filename_prefix' => 'Financial_Report',
    'export_heading' => 'MEAL PRODUCTION & INGREDIENT CONSUMPTION REPORT',
    'export_cols' => [
        'date' => 'Date',
        'day' => 'Day',
        'shift' => 'Shift',
        'dish' => 'Dish',
        'dish_type' => 'Category',
        'ing_code' => 'Ingredient Code',
        'ing_name' => 'Ingredient Name',
        'dl' => 'Portion Qty (g)',
        'portions' => 'Portions',
        'cost' => 'Amount (VND)',
        'total_kg' => 'Total KG',
    ],
    'export_summary' => [
        'total' => 'TOTAL',
    ],
];
