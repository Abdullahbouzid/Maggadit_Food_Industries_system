<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CustomRolesSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء دور مدير الصلاحيات
        $permissionManager = Role::create(['name' => 'permission_manager', 'guard_name' => 'web']);
        $permissionManager->givePermissionTo([
            'view_roles',
            'create_roles', 
            'edit_roles',
            'delete_roles',
            'view_permissions',
            'edit_permissions',
        ]);
        
        // إنشاء دور مدير التقارير
        $reportManager = Role::create(['name' => 'report_manager', 'guard_name' => 'web']);
        $reportManager->givePermissionTo([
            'view_reports',
            'export_reports',
            'view_any_reports',
        ]);
        
        $this->command->info('تم إنشاء الأدوار الإضافية بنجاح!');
    }
}