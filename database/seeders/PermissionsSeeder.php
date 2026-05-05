<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // ==================== تعريف الصلاحيات ====================
        
        // صلاحيات المستخدمين
        $userPermissions = [
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_any_users',
            'delete_any_users',
        ];
        
        // صلاحيات الأدوار
        $rolePermissions = [
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
            'view_any_roles',
            'delete_any_roles',
        ];
        
        // صلاحيات الصلاحيات
        $permissionPermissions = [
            'view_permissions',
            'edit_permissions',
            'view_any_permissions',
        ];
        
        // صلاحيات المنتجات
        $productPermissions = [
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
            'view_any_products',
            'delete_any_products',
        ];
        
        // صلاحيات الفئات
        $categoryPermissions = [
            'view_categories',
            'create_categories',
            'edit_categories',
            'delete_categories',
            'view_any_categories',
        ];
        
        // صلاحيات البراندات
        $brandPermissions = [
            'view_brands',
            'create_brands',
            'edit_brands',
            'delete_brands',
            'view_any_brands',
        ];
        
        // صلاحيات وحدات القياس
        $uomPermissions = [
            'view_uoms',
            'create_uoms',
            'edit_uoms',
            'delete_uoms',
            'view_any_uoms',
        ];
        
        // صلاحيات المخازن
        $warehousePermissions = [
            'view_warehouses',
            'create_warehouses',
            'edit_warehouses',
            'delete_warehouses',
            'view_any_warehouses',
        ];
        
        // صلاحيات المخزون
        $inventoryPermissions = [
            'view_inventories',
            'create_inventories',
            'edit_inventories',
            'delete_inventories',
            'view_any_inventories',
            'manage_inventory',
            'adjust_inventory',
        ];
        
        // صلاحيات قوائم الأسعار
        $priceListPermissions = [
            'view_price_lists',
            'create_price_lists',
            'edit_price_lists',
            'delete_price_lists',
            'view_any_price_lists',
        ];
        
        // صلاحيات الأسعار
        $pricePermissions = [
            'view_prices',
            'create_prices',
            'edit_prices',
            'delete_prices',
            'view_any_prices',
        ];
        
        // صلاحيات التقارير
        $reportPermissions = [
            'view_reports',
            'export_reports',
            'view_any_reports',
        ];
        
        // دمج جميع الصلاحيات
        $allPermissions = array_merge(
            $userPermissions,
            $rolePermissions,
            $permissionPermissions,
            $productPermissions,
            $categoryPermissions,
            $brandPermissions,
            $uomPermissions,
            $warehousePermissions,
            $inventoryPermissions,
            $priceListPermissions,
            $pricePermissions,
            $reportPermissions
        );
        
        // إنشاء الصلاحيات
        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }
        
        // ==================== إنشاء الأدوار ====================
        
        // 1. دور Super Admin (مدير كامل الصلاحيات)
        $superAdminRole = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdminRole->givePermissionTo(Permission::all());
        
        // 2. دور Admin (مدير رئيسي)
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo([
            'view_users', 'create_users', 'edit_users', 'delete_users', 'view_any_users',
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles', 'view_any_roles',
            'view_permissions', 'edit_permissions', 'view_any_permissions',
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
            'view_brands', 'create_brands', 'edit_brands', 'delete_brands',
            'view_uoms', 'create_uoms', 'edit_uoms', 'delete_uoms',
            'view_warehouses', 'create_warehouses', 'edit_warehouses',
            'view_inventories', 'manage_inventory', 'adjust_inventory',
            'view_price_lists', 'create_price_lists', 'edit_price_lists',
            'view_prices', 'create_prices', 'edit_prices',
            'view_reports', 'export_reports',
        ]);
        
        // 3. دور User Manager (مدير مستخدمين)
        $userManagerRole = Role::create(['name' => 'user_manager', 'guard_name' => 'web']);
        $userManagerRole->givePermissionTo([
            'view_users', 'create_users', 'edit_users', 'delete_users', 'view_any_users',
            'view_roles', 'view_permissions',
        ]);
        
        // 4. دور Product Manager (مدير منتجات)
        $productManagerRole = Role::create(['name' => 'product_manager', 'guard_name' => 'web']);
        $productManagerRole->givePermissionTo([
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_categories', 'create_categories', 'edit_categories',
            'view_brands', 'create_brands', 'edit_brands',
            'view_uoms', 'create_uoms', 'edit_uoms',
        ]);
        
        // 5. دور Warehouse Manager (مدير مخازن)
        $warehouseManagerRole = Role::create(['name' => 'warehouse_manager', 'guard_name' => 'web']);
        $warehouseManagerRole->givePermissionTo([
            'view_warehouses', 'view_inventories', 'manage_inventory', 'adjust_inventory',
            'view_products', 'view_reports',
        ]);
        
        // 6. دور Sales Manager (مدير مبيعات)
        $salesManagerRole = Role::create(['name' => 'sales_manager', 'guard_name' => 'web']);
        $salesManagerRole->givePermissionTo([
            'view_products', 'view_price_lists', 'view_prices',
            'view_inventories', 'view_reports', 'export_reports',
        ]);
        
        // 7. دور Viewer (مستخدم عادي - مشاهدة فقط)
        $viewerRole = Role::create(['name' => 'viewer', 'guard_name' => 'web']);
        $viewerRole->givePermissionTo([
            'view_products', 'view_categories', 'view_brands', 'view_uoms',
            'view_warehouses', 'view_inventories', 'view_price_lists', 'view_prices',
        ]);
        
        // ==================== إنشاء المستخدمين ====================
        
        // 1. Super Admin User
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@maggadit.com',
            'password' => Hash::make('12345678'),
            'phone' => '0500000001',
            'address' => 'الرياض، المملكة العربية السعودية',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super_admin');
        
        // 2. Admin User (مدير مع صلاحيات تعديل المستخدمين)
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@maggadit.com',
            'password' => Hash::make('12345678'),
            'phone' => '0500000002',
            'address' => 'جدة، المملكة العربية السعودية',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');
        
        // 3. User Manager (مدير مستخدمين - يمكنه تعديل المستخدمين والأدوار)
        $userManager = User::create([
            'name' => 'User Manager',
            'email' => 'usermanager@maggadit.com',
            'password' => Hash::make('12345678'),
            'phone' => '0500000003',
            'address' => 'الدمام، المملكة العربية السعودية',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $userManager->assignRole('user_manager');
        
        // 4. Product Manager
        $productManager = User::create([
            'name' => 'Product Manager',
            'email' => 'product@maggadit.com',
            'password' => Hash::make('12345678'),
            'phone' => '0500000004',
            'address' => 'مكة المكرمة، المملكة العربية السعودية',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $productManager->assignRole('product_manager');
        
        // 5. Warehouse Manager
        $warehouseManager = User::create([
            'name' => 'Warehouse Manager',
            'email' => 'warehouse@maggadit.com',
            'password' => Hash::make('12345678'),
            'phone' => '0500000005',
            'address' => 'المدينة المنورة، المملكة العربية السعودية',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $warehouseManager->assignRole('warehouse_manager');
        
        // 6. Sales Manager
        $salesManager = User::create([
            'name' => 'Sales Manager',
            'email' => 'sales@maggadit.com',
            'password' => Hash::make('12345678'),
            'phone' => '0500000006',
            'address' => 'الخبر، المملكة العربية السعودية',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $salesManager->assignRole('sales_manager');
        
        // 7. Viewer User (مشاهدة فقط)
        $viewer = User::create([
            'name' => 'Guest Viewer',
            'email' => 'viewer@maggadit.com',
            'password' => Hash::make('12345678'),
            'phone' => '0500000007',
            'address' => 'الرياض، المملكة العربية السعودية',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $viewer->assignRole('viewer');
        
        // 8. Normal User (بدون صلاحيات)
        $normalUser = User::create([
            'name' => 'Normal User',
            'email' => 'user@maggadit.com',
            'password' => Hash::make('12345678'),
            'phone' => '0500000008',
            'address' => 'الرياض، المملكة العربية السعودية',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        $this->command->info('تم إنشاء المستخدمين والأدوار والصلاحيات بنجاح!');
        $this->command->info('==================================================');
        $this->command->info('بيانات تسجيل الدخول:');
        $this->command->info('1. superadmin@maggadit.com / 12345678 (صلاحيات كاملة)');
        $this->command->info('2. admin@maggadit.com / 12345678 (مدير مع صلاحيات تعديل مستخدمين)');
        $this->command->info('3. usermanager@maggadit.com / 12345678 (مدير مستخدمين)');
        $this->command->info('4. product@maggadit.com / 12345678 (مدير منتجات)');
        $this->command->info('5. warehouse@maggadit.com / 12345678 (مدير مخازن)');
        $this->command->info('6. sales@maggadit.com / 12345678 (مدير مبيعات)');
        $this->command->info('7. viewer@maggadit.com / 12345678 (مشاهدة فقط)');
        $this->command->info('8. user@maggadit.com / 12345678 (مستخدم عادي)');
    }
}