<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $models = [
            'users', 'customers', 'orders', 'invoices', 'quotations',
            'deliveries', 'returns', 'inventory', 'price_lists', 'wallets',
            'payment_deadlines', 'items', 'categories', 'brands', 'uoms',
            'warehouses', 'payments', 'offers', 'item_packs',
        ];

        $approveModels = ['invoices', 'offers', 'returns'];

        $permissionNames = [];
        foreach ($models as $model) {
            $permissionNames[] = "view_{$model}";
            $permissionNames[] = "create_{$model}";
            $permissionNames[] = "update_{$model}";
            $permissionNames[] = "delete_{$model}";

            if (in_array($model, $approveModels)) {
                $permissionNames[] = "approve_{$model}";
            }
        }

        foreach ($permissionNames as $permName) {
            Permission::updateOrCreate(
                ['name' => $permName, 'guard_name' => 'web'],
                ['name' => $permName, 'guard_name' => 'web']
            );
        }

        $roles = [
            'admin'             => 'مدير النظام',
            'manager'           => 'مدير المبيعات',
            'accountant'        => 'محاسب',
            'driver'            => 'سائق توصيل',
            'warehouse_keeper'  => 'أمين مستودع',
        ];

        foreach ($roles as $roleName => $roleLabel) {
            Role::updateOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['name' => $roleName, 'guard_name' => 'web']
            );
        }

        $allPermissions = Permission::all();

        // Admin: all permissions
        $admin = Role::findByName('admin');
        $admin->syncPermissions($allPermissions);
        $this->command->info('✅ Admin role assigned all ' . $allPermissions->count() . ' permissions');

        // Manager: all except delete_users and delete_settings
        $managerExcludes = ['delete_users', 'delete_settings', 'delete_item_packs', 'delete_payments', 'delete_offers', 'delete_payment_deadlines'];
        $managerPermissions = $allPermissions->filter(function ($perm) use ($managerExcludes) {
            return !in_array($perm->name, $managerExcludes);
        });
        $manager = Role::findByName('manager');
        $manager->syncPermissions($managerPermissions);
        $this->command->info('✅ Manager role assigned ' . $managerPermissions->count() . ' permissions (excluded delete_users)');

        // Accountant: view/approve on invoices, payments, wallets, returns + view customers
        $accountantPermissions = Permission::whereIn('name', [
            'view_invoices', 'approve_invoices',
            'view_payments', 'approve_payments',
            'view_wallets', 'approve_wallets',
            'view_returns', 'approve_returns',
            'view_customers',
        ])->get();
        $accountant = Role::findByName('accountant');
        $accountant->syncPermissions($accountantPermissions);
        $this->command->info('✅ Accountant role assigned ' . $accountantPermissions->count() . ' permissions');

        // Driver: view orders, view deliveries, update deliveries
        $driverPermissions = Permission::whereIn('name', [
            'view_orders',
            'view_deliveries', 'update_deliveries',
        ])->get();
        $driver = Role::findByName('driver');
        $driver->syncPermissions($driverPermissions);
        $this->command->info('✅ Driver role assigned ' . $driverPermissions->count() . ' permissions');

        // Warehouse keeper: view/create/update inventory, items, item_packs, warehouses
        $keeperPermissions = Permission::whereIn('name', [
            'view_inventory', 'create_inventory', 'update_inventory',
            'view_items', 'create_items', 'update_items',
            'view_item_packs', 'create_item_packs', 'update_item_packs',
            'view_warehouses', 'create_warehouses', 'update_warehouses',
        ])->get();
        $keeper = Role::findByName('warehouse_keeper');
        $keeper->syncPermissions($keeperPermissions);
        $this->command->info('✅ Warehouse keeper role assigned ' . $keeperPermissions->count() . ' permissions');

        $this->command->info('✅ Roles: ' . Role::count());
        $this->command->info('✅ Permissions: ' . Permission::count());
    }
}
