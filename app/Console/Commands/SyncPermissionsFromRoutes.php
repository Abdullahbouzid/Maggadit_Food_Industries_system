<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class SyncPermissionsFromRoutes extends Command
{
    protected $signature = 'permissions:sync-from-routes';

    protected $description = 'Parse all registered routes and generate permissions (view, create, edit, delete, import, export) based on resource names.';

    public function handle()
    {
        $routes = Route::getRoutes();
        $permissions = [];

        foreach ($routes as $route) {
            $action = $route->getActionName();
            // استهداف فقط الـ Controllers داخل App\Http\Controllers
            if (str_contains($action, 'App\Http\Controllers') && ! str_contains($action, 'Auth\\') && ! str_contains($action, 'Admin\\RolePermission')) {
                $controllerMethod = class_basename($action);
                // استخراج اسم الـ Controller الأساسي
                if (preg_match('/(\w+)Controller@(\w+)/', $controllerMethod, $matches)) {
                    $controller = $matches[1];
                    $method = $matches[2];

                    // تحديد الصلاحية بناءً على الـ method
                    $permission = $this->methodToPermission($controller, $method);
                    if ($permission) {
                        $permissions[] = $permission;
                    }
                }
            }
        }

        // إضافة بعض الصلاحيات الثابتة إن لم تظهر
        $extra = ['view_dashboard', 'view_profile', 'edit_profile', 'view_roles', 'create_roles', 'edit_roles', 'delete_roles', 'assign_permissions'];
        $permissions = array_unique(array_merge($permissions, $extra));

        $count = 0;
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            $count++;
        }

        $this->info("✅ Synced {$count} permissions successfully.");
    }

    private function methodToPermission($controller, $method)
    {
        $controller = Str::snake($controller);
        $map = [
            'index' => "view_{$controller}",
            'show' => "view_{$controller}",
            'create' => "create_{$controller}",
            'store' => "create_{$controller}",
            'edit' => "edit_{$controller}",
            'update' => "edit_{$controller}",
            'destroy' => "delete_{$controller}",
            'export' => "export_{$controller}",
            'import' => "import_{$controller}",
            'downloadTemplate' => "import_{$controller}",
        ];

        return $map[$method] ?? null;
    }
}
