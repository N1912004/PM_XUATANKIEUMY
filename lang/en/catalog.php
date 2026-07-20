<?php

$translations = [
    'area_section' => 'Area information',
    'kitchen_section' => 'Kitchen information',
    'kitchen_status' => ['active' => 'Active', 'paused' => 'Paused', 'maintenance' => 'Maintenance'],
    'groups' => ['area_kitchen' => 'AREAS & KITCHENS', 'hr' => 'HUMAN RESOURCES', 'kitchen_operations' => 'KITCHEN OPERATIONS'],
    'common' => ['index' => '#', 'active' => 'Active', 'status' => 'STATUS', 'active_status' => 'Active status', 'notes' => 'Notes', 'sort_order' => 'Display order', 'sort_order_upper' => 'ORDER', 'in_use' => 'In use', 'in_use_upper' => 'IN USE', 'created_at' => 'CREATED'],
    'area' => ['label' => 'Areas', 'fields' => ['name' => 'Area name', 'code' => 'Area code', 'manager' => 'Manager'], 'placeholders' => ['name' => 'E.g. Ho Chi Minh City', 'code' => 'E.g. AREA-HCM', 'notes' => 'Operations scope, production shifts, main customers...'], 'table' => ['code' => 'CODE', 'name' => 'AREA', 'manager' => 'MANAGER', 'kitchens_count' => 'KITCHENS'], 'errors' => ['in_use' => 'This area cannot be deleted while it still has kitchens.'], 'notifications' => ['deleted' => 'Area deleted successfully.']],
    'kitchen' => ['label' => 'Kitchens', 'fields' => ['name' => 'Kitchen name', 'area' => 'Area', 'type' => 'Type', 'capacity' => 'Serving capacity (portions/day)', 'manager' => 'Kitchen manager'], 'placeholders' => ['name' => 'E.g. Main kitchen'], 'table' => ['name' => 'KITCHEN', 'area' => 'AREA', 'type' => 'TYPE', 'capacity' => 'CAPACITY (PORTIONS/DAY)', 'manager' => 'MANAGER'], 'notifications' => ['deleted' => 'Kitchen deleted successfully.']],
    'kitchen_type' => ['label' => 'Kitchen types', 'fields' => ['name' => 'Type name'], 'placeholders' => ['name' => 'Enter a kitchen type name'], 'helpers' => ['active' => 'Disable to hide this value from selection forms while preserving existing data.'], 'table' => ['name' => 'TYPE NAME', 'count' => 'KITCHENS'], 'errors' => ['in_use' => 'This type cannot be deleted because :count kitchens use it.', 'bulk_in_use' => 'Bulk deletion is unavailable. These types are in use: :names']],
    'department' => ['label' => 'Departments', 'fields' => ['name' => 'Department name'], 'placeholders' => ['name' => 'Enter a department name'], 'helpers' => ['active' => 'Disable to hide this department from selection forms while preserving existing data.'], 'table' => ['name' => 'DEPARTMENT', 'count' => 'EMPLOYEES'], 'errors' => ['in_use' => 'This department cannot be deleted because it has :count employees.', 'bulk_in_use' => 'Bulk deletion is unavailable. These departments have employees: :names']],
    'position' => ['label' => 'Positions', 'fields' => ['name' => 'Position name'], 'placeholders' => ['name' => 'Enter a position name'], 'helpers' => ['active' => 'Disable to hide this position from selection forms while preserving existing data.'], 'table' => ['name' => 'POSITION', 'count' => 'EMPLOYEES'], 'errors' => ['in_use' => 'This position cannot be deleted because it has :count employees.', 'bulk_in_use' => 'Bulk deletion is unavailable. These positions have employees: :names']],
    'shift' => ['navigation' => 'Shift configuration', 'label' => 'Shifts', 'fields' => ['name' => 'Shift name', 'start_time' => 'Start time', 'end_time' => 'End time'], 'placeholders' => ['name' => 'E.g. Shift 1'], 'table' => ['time_range' => 'Time range']],
];

$translations['common'] += ['actions' => 'Actions', 'edit' => 'Edit', 'delete' => 'Delete', 'reset_filters' => 'Clear filters'];
$translations['area']['list'] = [
    'title' => 'Areas',
    'subtitle' => 'Manage operating areas and their associated cafeterias and production kitchens',
    'create' => 'Add area',
    'search_placeholder' => 'Search areas...',
    'no_notes' => 'No notes',
    'confirm_delete' => 'Are you sure you want to delete this area?',
    'empty' => 'No areas found.',
    'kpi' => [
        'total_label' => 'Areas', 'total_note' => 'Managed',
        'active_label' => 'Active', 'active_note' => 'Available areas',
        'kitchens_label' => 'Cafeterias / kitchens', 'kitchens_note' => 'Total associated facilities',
        'managers_label' => 'Managers', 'managers_note' => 'By area',
    ],
    'columns' => ['code' => 'Code', 'area' => 'Area', 'manager' => 'Manager', 'kitchens' => 'Kitchens'],
];
$translations['kitchen']['list'] = [
    'title' => 'Cafeterias / kitchens',
    'subtitle' => 'Manage cafeterias and production kitchens associated with each operating area',
    'create' => 'Add cafeteria / kitchen',
    'search_placeholder' => 'Search cafeterias / kitchens...',
    'confirm_delete' => 'Are you sure you want to delete this cafeteria/kitchen?',
    'empty' => 'No cafeterias or production kitchens found.',
    'capacity_value' => ':count portions/day',
    'kpi' => [
        'total_label' => 'Cafeterias / kitchens', 'total_note' => 'Total production facilities',
        'active_label' => 'Active', 'active_note' => 'Available facilities',
        'areas_label' => 'Areas', 'areas_note' => 'Managed',
        'managers_label' => 'Kitchen managers', 'managers_note' => 'By facility',
    ],
    'filters' => ['all_areas' => 'All areas', 'all_types' => 'All types', 'all_statuses' => 'All statuses'],
    'columns' => ['kitchen' => 'Cafeteria / kitchen', 'area' => 'Area', 'type' => 'Type', 'capacity' => 'Capacity', 'manager' => 'Manager'],
];

return $translations;
