<?php

$translations = [
    'navigation' => 'Leave & Overtime', 'type_navigation' => 'Leave / Overtime types', 'group' => 'HUMAN RESOURCES', 'sections' => ['request' => 'Request information', 'approval' => 'Details & approval'],
    'fields' => ['employee' => 'Requesting employee', 'type' => 'Request type', 'duration' => 'Days / Hours', 'start_date' => 'Start / Applicable date', 'end_date' => 'End date', 'reason' => 'Detailed reason', 'approver' => 'Approver', 'status' => 'Approval status'],
    'status' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'],
    'actions' => ['export' => 'Export data', 'create' => 'Create request', 'cancel' => 'Cancel', 'view' => 'View details', 'edit' => 'Edit'], 'tabs' => ['all' => 'All requests', 'history' => 'Approval history'],
    'messages' => ['created' => 'Request created successfully!', 'updated' => 'Request updated successfully!', 'deleted' => 'Request deleted successfully.'],
    'validation' => ['employee_required' => 'Employee is required.', 'start_required' => 'Start date is required.', 'type_required' => 'Request type is required.', 'reason_required' => 'Reason is required.', 'reason_min' => 'Reason must contain at least 5 characters.'],
    'type' => ['name' => 'Request type name', 'name_placeholder' => 'Enter a request type (e.g. Annual leave, Weekday overtime...)', 'is_overtime' => 'Overtime (OT)', 'is_overtime_help' => 'Enable for overtime requests; disable for leave requests.', 'classification' => 'Overtime / leave classification', 'sort' => 'Display order', 'active' => 'Active', 'active_help' => 'When disabled, this type no longer appears in forms; existing data remains unchanged.', 'request_count' => 'REQUESTS', 'created_at' => 'CREATED AT', 'cannot_delete' => 'This type cannot be deleted because it is used by :count requests.', 'bulk_cannot_delete' => 'Bulk deletion is unavailable. These request types are in use: :names'],
    'ui' => ['edit_title' => 'Edit request', 'create_title' => 'Create request', 'edit_description' => 'Update the employee leave or overtime request', 'create_description' => 'Enter information to submit a leave or overtime request', 'back' => 'Back', 'leave' => 'Leave', 'overtime' => 'Overtime', 'form_error' => 'There were errors; please check the form:', 'leave_info' => '1. Leave information', 'overtime_info' => '1. Overtime information', 'select_employee' => 'Select employee', 'leave_type' => 'Leave type', 'overtime_type' => 'Overtime type', 'leave_time' => 'Leave period', 'to' => 'to', 'leave_days' => 'Leave days', 'overtime_hours' => 'Overtime hours', 'unpaid' => 'Unpaid leave', 'handover_time' => 'Work handover time', 'leave_reason' => 'Leave reason', 'contact_info' => '2. Emergency contact', 'contact' => 'Contact person', 'phone' => 'Phone number', 'notes' => 'Additional notes', 'overtime_date' => 'Overtime date', 'overtime_time' => 'Overtime period', 'location' => 'Work location / area', 'approver' => 'Approving manager', 'select_approver' => 'Select approver', 'overtime_reason' => 'Overtime reason', 'work' => 'Work to perform', 'attachment' => 'Attachment', 'file_hint' => 'Drag a file here or click to choose one', 'file_types' => 'PDF, XLSX, JPG, PNG up to 5MB', 'confirmation' => '2. Confirmation & handover', 'coworker' => 'Coworker', 'approval' => 'Approval & request status', 'summary' => 'Request summary', 'applicable_time' => 'Applicable period', 'overtime_period' => 'Overtime hours', 'location_short' => 'Location', 'annual_balance' => 'Annual leave balance', 'annual_total' => 'Total annual leave', 'used' => 'Used', 'remaining' => 'Remaining', 'notice' => 'Note', 'notice_approval' => 'The request will be sent directly to the selected manager for approval.', 'notice_handover' => 'Complete the necessary handover before the request takes effect.', 'submit' => 'Submit request', 'subtitle' => 'Manage employee leave, overtime and approval statuses', 'filters' => 'Filters', 'reset_filters' => 'Reset filters', 'actions' => 'Actions', 'none' => 'None', 'confirm_delete' => 'Are you sure you want to delete this request?', 'delete' => 'Delete request', 'empty' => 'No leave or overtime requests found', 'empty_hint' => 'Try adjusting the filters or search terms.', 'pagination' => 'Showing :from to :to of :total requests', 'rows_per_page' => ':count rows/page'],
];

$translations['ui'] += [
    'employee' => 'Employee',
    'duration_placeholder' => 'E.g. 1 day or 4 hours',
    'leave_duration_placeholder' => 'E.g. 3 days',
    'overtime_duration_placeholder' => 'E.g. 3 hours',
    'leave_reason_placeholder' => 'Enter the leave reason',
    'contact_placeholder' => 'Enter the contact name',
    'phone_placeholder' => 'Enter the phone number',
    'optional_notes' => 'Additional notes (optional)',
    'notes_placeholder' => 'Enter additional notes',
    'overtime_reason_placeholder' => 'Enter the overtime reason...',
    'work_placeholder' => 'Describe the work to be performed...',
    'file_drag' => 'Drag a file here or',
    'file_choose' => 'click to choose one',
    'coworker_placeholder' => 'Enter the coworker name',
    'handover_time_short' => 'Handover time',
    'reason' => 'Reason',
    'days_count' => ':count days',
    'search_placeholder' => 'Search employees...',
    'department' => 'Department',
    'status' => 'Status',
    'employee_code' => 'Employee ID',
    'full_name' => 'Full name',
    'applied_date' => 'Period / Applicable date',
    'weekday_prefix' => '',
    'pagination_navigation' => 'Pagination navigation',
    'select_all' => 'Select all requests',
    'select_request' => 'Select request for employee :code',
];
$translations['weekdays'] = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$translations['locations'] = ['central_kitchen_a' => 'Central kitchen - Area A', 'ingredient_warehouse' => 'Ingredient warehouse', 'head_office' => 'Head office'];

return $translations;
