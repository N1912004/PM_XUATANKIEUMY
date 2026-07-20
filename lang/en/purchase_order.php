<?php

$vi = require __DIR__.'/../vi/purchase_order.php';

return array_replace_recursive($vi, [
    'navigation' => ['label' => 'Ordering', 'model' => 'Purchase order', 'plural' => 'Purchase orders', 'group' => 'SUPPLY & WAREHOUSE'],
    'list' => ['title' => 'Purchase orders', 'subtitle' => 'Manage ingredient purchase orders by supplier from finalized item lists'],
    'fields' => ['code' => 'Order code', 'supplier' => 'Supplier', 'supplier_abbr' => 'Supplier', 'status' => 'Status', 'estimated_delivery_date' => 'Estimated delivery date', 'note' => 'Notes', 'ingredient' => 'Ingredient', 'quantity_ordered' => 'Qty ordered', 'quantity_received' => 'Qty received', 'unit_price' => 'Unit price', 'kitchen' => 'Kitchen'],
    'form' => ['details' => 'Order details', 'ordered_items' => 'Ordered items'],
    'help' => ['delivery_date' => 'Select a date within the next 2 days'],
    'status' => ['draft' => 'Draft', 'sent' => 'Sent to supplier', 'sent_short' => 'Sent', 'checking' => 'Checking goods', 'done' => 'Completed', 'cancelled' => 'Cancelled', 'draft_with_code' => 'Draft', 'sent_with_code' => 'Sent', 'checking_with_code' => 'Checking', 'done_with_code' => 'Completed', 'cancelled_with_code' => 'Cancelled'],
    'types' => ['week' => 'Weekly order', 'day' => 'Daily order', 'week_short' => 'Week', 'day_short' => 'Day'],
    'table' => ['index' => 'NO.', 'code' => 'ORDER CODE', 'supplier' => 'SUPPLIER', 'estimated_delivery_date' => 'ESTIMATED DELIVERY', 'total_value' => 'TOTAL VALUE', 'status' => 'STATUS', 'ingredient_name' => 'Ingredient name', 'type' => 'Type', 'quantity' => 'Quantity', 'unit_price' => 'Unit price', 'line_total' => 'Amount'],
    'actions' => ['create' => 'Create purchase order', 'add_item' => 'Add item', 'reset_filters' => 'Reset filters', 'view_order' => 'View order :code', 'view_details' => 'View details', 'check_goods' => 'Check goods', 'export_excel' => 'Export Excel', 'delete' => 'Delete order', 'back' => 'Back', 'export_all_suppliers' => 'Export Excel (all suppliers)', 'export_current_supplier' => 'Export this supplier to Excel'],
    'filters' => ['all_types' => 'All types', 'all_statuses' => 'All statuses', 'month' => ':month'],
    'placeholders' => ['search' => 'Search orders or suppliers...'],
    'kpi' => ['month_total' => 'Orders this month', 'awaiting_check' => 'Awaiting goods check', 'completed' => 'Completed', 'month_value' => 'Monthly total value'],
    'labels' => ['unassigned' => 'Unassigned', 'ingredient_count' => ':count ingredients', 'created_at' => 'Created: :date'],
    'detail' => ['order_title' => 'Order :code', 'subtitle' => 'Ingredients grouped by supplier · Order date :date', 'supplier_total' => ':supplier order total:'],
    'ingredient_types' => ['meat_wet' => 'Meat/Wet goods', 'dry' => 'Dry goods', 'meat' => 'Meat', 'vegetable_wet' => 'Vegetables/Wet goods'],
    'empty' => ['title' => 'No purchase orders found', 'subtitle' => 'Try changing the filters or using another search term.'],
    'confirm' => ['delete' => 'Are you sure you want to delete this order?'],
    'notifications' => ['deleted' => 'Purchase order deleted'],
    'currency' => ['thousand' => ':value thousand VND', 'amount' => ':value VND'],
    'pagination' => ['summary' => 'Showing :from - :to of :total orders'],
    'validation' => ['completed_status_locked' => 'An order already received into stock cannot change status.', 'status_cannot_go_back' => 'The order status cannot move backwards (Draft → Sent → Checking → Completed).'],
]);
