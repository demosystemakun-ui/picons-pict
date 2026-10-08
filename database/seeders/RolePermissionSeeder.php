<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Master data
            'master.items.view', 'master.items.manage',
            'master.categories.manage', 'master.units.manage',
            'master.vendors.manage', 'master.departments.manage',

            // Inventory
            'inventory.view', 'inventory.receipt', 'inventory.issue', 'inventory.adjustment',

            // Request
            'request.create', 'request.view', 'request.view.own',

            // Approval
            'approval.action',

            // Procurement
            'procurement.view', 'procurement.manage',
            'po.manage', 'vendor.compare',
            'cash_advance.create', 'cash_advance.approve.manager', 'cash_advance.approve.finance',
            'reimbursement.manage',

            // Reports
            'reports.view', 'reports.export',

            // Settings
            'settings.users.manage', 'settings.roles.manage', 'settings.company.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = [
            'Super Admin' => $permissions, // all permissions
            'Warehouse Admin' => [
                'master.items.view', 'inventory.view', 'inventory.receipt',
                'inventory.issue', 'inventory.adjustment', 'reports.view', 'reports.export',
            ],
            'Procurement' => [
                'master.items.view', 'procurement.view', 'procurement.manage',
                'po.manage', 'vendor.compare', 'cash_advance.create',
                'reimbursement.manage', 'reports.view', 'reports.export',
            ],
            'Department User' => [
                'request.create', 'request.view.own', 'master.items.view',
            ],
            'Manager' => [
                'request.view', 'approval.action', 'cash_advance.approve.manager',
                'reports.view', 'master.items.view',
            ],
            'Finance' => [
                'cash_advance.approve.finance', 'reimbursement.manage',
                'reports.view', 'reports.export', 'po.manage',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }
    }
}
