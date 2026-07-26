<?php

return [
    'breadcrumb' => ['home' => 'Catering', 'list' => 'Accounts', 'create' => 'Add account', 'edit' => 'Edit account'],
    'navigation' => 'User accounts',
    'model' => 'account',
    'group' => 'HUMAN RESOURCES',
    'profile' => [
        'heading' => 'Edit Profile',
        'avatar' => 'Avatar',
        'avatar_help' => 'Upload PNG, JPG or WEBP image (Max 2MB)',
        'name' => 'Full name',
        'email' => 'Login email',
        'new_password' => 'New password',
        'password_confirmation' => 'Confirm new password',
        'save' => 'Save changes',
    ],
    'sections' => [
        'login' => 'Login information',
        'employee' => 'Linked employee profile',
        'roles' => 'Roles and permissions',
        'avatar' => 'Profile image',
        'account' => 'Account information',
    ],
    'fields' => [
        'name' => 'Account name',
        'email' => 'Login email',
        'password' => 'Password',
        'avatar_url' => 'Profile image URL (optional)',
        'employee' => 'Employee',
        'kitchen' => 'Assigned kitchen',
        'roles' => 'Roles',
    ],
    'table' => [
        'name' => 'FULL NAME',
        'email' => 'LOGIN EMAIL',
        'employee' => 'LINKED EMPLOYEE',
        'kitchen' => 'ASSIGNED KITCHEN',
        'roles' => 'ROLES',
        'created_at' => 'CREATED AT',
    ],
    'filters' => [
        'roles' => 'Roles',
        'employee_linked' => 'Employee linked',
        'linked' => 'Linked',
        'not_linked' => 'Not linked',
    ],
    'placeholders' => [
        'no_access' => '— No system access —',
    ],
    'actions' => [
        'create' => 'Add account',
    ],
    'messages' => [
        'profile_updated' => 'Profile updated successfully!',
    ],
    'validation' => [
        'last_super_admin' => 'This is the last full-access account; the :role role cannot be removed.',
        'cannot_grant_super_admin' => 'You are not allowed to assign the :role role.',
    ],
    'help' => [
        'password' => 'Leave blank when editing to keep the current password.',
        'employee' => 'Only employees without an account are shown (one account per employee).',
        'employee_section' => 'The account kitchen is determined through its linked employee. Operational accounts without an employee cannot access business data.',
    ],
];
