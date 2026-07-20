<?php

$vi = require __DIR__.'/../vi/food_safety.php';

return array_replace_recursive($vi, [
    'navigation' => ['label' => 'Three-step food safety audit', 'model' => 'Food safety audit log', 'plural' => 'Three-step food safety audits', 'group' => 'KITCHEN OPERATIONS'],
    'page' => ['title' => 'Three-step food safety audit', 'subtitle' => 'Display the five B1–B5 forms based on the official Decision 1246/2017-BYT Excel template'],
    'fields' => ['audit_date' => 'Audit date', 'shift' => 'Service shift', 'stage' => 'Audit stage', 'dish' => 'Dish', 'conclusion' => 'Conclusion', 'inspector' => 'Inspector', 'cook_start' => 'Cooking start time', 'cook_end' => 'Cooking completion time', 'temperature' => 'Temperature', 'sample_keeper' => 'Sample keeper', 'sample_kept_at' => 'Sample storage time', 'sample_code' => 'Sample code', 'utensils' => 'Storage / dining utensils', 'notes' => 'Detailed observations / criteria'],
    'stages' => ['step_1' => 'Step 1', 'step_2' => 'Step 2', 'step_3' => 'Step 3', 'sample_storage' => 'Sample storage', 'sample_disposal' => 'Sample disposal', 'step_1_long' => 'Step 1 – Pre-processing inspection', 'step_2_long' => 'Step 2 – Inspection during cooking', 'step_3_long' => 'Step 3 – Inspection before serving', 'sample_storage_long' => 'Monitor stored samples', 'sample_disposal_long' => 'Monitor sample disposal'],
    'status' => ['pending' => 'Pending review', 'passed' => 'Passed', 'failed' => 'Failed'],
    'help' => ['dish' => 'Applies to audits by dish (steps 2, 3, and sample storage).'],
    'placeholders' => ['notes' => 'Example: good sensory quality, storage temperature 4°C...', 'select_employee' => 'Select employee', 'search_employee' => 'Search employees...'],
    'table' => ['audit_date' => 'Audit date', 'stage' => 'Audit stage', 'conclusion' => 'Conclusion', 'inspector' => 'Inspector'],
    'filters' => ['date' => 'Audit date', 'shift' => 'Service shift', 'all_shifts' => 'All shifts', 'location' => 'Facility / location', 'inspector' => 'Inspector'],
    'actions' => ['create_data' => 'Create data', 'export_excel' => 'Export Excel', 'clear_selection' => 'Clear selection', 'print_labels' => 'Print labels'],
    'labels' => ['default_kitchen' => 'Kitchen', 'filter_summary' => ':date · :dishes dishes · :ingredients ingredients'],
    'kpi' => ['ingredients_b1' => 'B1 ingredients', 'from_daily_dishes' => 'From today’s dishes', 'dishes' => 'Dishes', 'by_shift' => 'By shift', 'total_portions' => 'Total portions', 'by_dish' => 'By dish', 'forms' => 'Forms', 'b1_to_b5' => 'B1 to B5'],
    'empty' => ['no_employee_results' => 'No results found', 'no_audit_data' => 'No matching food safety audit data found.'],
    'errors' => ['template_not_found' => 'The food safety Excel template could not be found'],
    'accessibility' => ['export_excel' => 'Export the food safety audit report to Excel', 'inspector_picker' => 'Select an inspector', 'print_labels' => 'Print stored-sample labels'],
]);
