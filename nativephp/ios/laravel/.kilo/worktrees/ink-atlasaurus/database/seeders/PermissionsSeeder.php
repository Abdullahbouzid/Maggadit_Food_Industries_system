<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // قائمة الصلاحيات (يمكنك إضافة كل الصلاحيات التي تحتاجها)
        $permissions = [
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
            'view_permissions', 'create_permissions', 'edit_permissions', 'delete_permissions',
            'view_organizations', 'create_organizations', 'edit_organizations', 'delete_organizations', 'import_organizations', 'export_organizations',
            'view_vendors', 'create_vendors', 'edit_vendors', 'delete_vendors',
            'view_competitors', 'create_competitors', 'edit_competitors', 'delete_competitors',
            'view_employees', 'create_employees', 'edit_employees', 'delete_employees',
            'view_dashboard', 'view_profile', 'edit_profile',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // إنشاء دور Admin (إذا لم يكن موجوداً)
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        // منح جميع الصلاحيات لـ Admin
        $adminRole->syncPermissions(Permission::all());

        // تعيين أول مستخدم (مثلاً ID=1) كـ Admin
        $user = User::first(); // أو استخدم User::find(1)
        if ($user) {
            $user->assignRole('Admin');
        }

        $this->command->info('✅ Roles and permissions seeded successfully!');
    }
}
