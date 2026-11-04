<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    // عرض قائمة الأدوار
    public function index(Request $request) {
        $search = $request->get('search', '');
        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') $perPage = 999999;

        $query = Role::with('permissions');
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhereHas('permissions', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        $roles = $query->paginate((int) $perPage);

        return view('dashboard.roles.index', compact('roles'));
    }

    // عرض نموذج إضافة دور جديد
    public function create()
    {
        $permissions = Permission::all();

        return view('dashboard.roles.create', compact('permissions'));
    }

    // حفظ دور جديد
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles|string|max:255',
        ]);

        $role = Role::create(['name' => $request->name]);
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.index')
            ->with('success', 'تم إنشاء الدور بنجاح ✅');
    }

    // عرض نموذج تعديل دور
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('dashboard.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    // تحديث دور
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.$role->id,
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')
            ->with('success', 'تم تحديث الدور بنجاح ✏️');
    }

    // حذف دور
    public function destroy(Role $role)
    {
        // منع حذف دور المشرف الرئيسي
        if ($role->name === 'Admin') {
            return redirect()->route('roles.index')
                ->with('error', 'لا يمكن حذف دور المشرف الرئيسي ⚠️');
        }
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'تم حذف الدور بنجاح 🗑️');
    }

    // عرض قائمة الصلاحيات (للمشاهدة فقط)
    public function permissionsIndex(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') $perPage = 999999;

        $query = Permission::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('guard_name', 'like', "%{$search}%");
        }

        $permissions = $query->paginate((int) $perPage);

        return view('dashboard.permissions.index', compact('permissions'));
    }

    // داخل app/Http/Controllers/Admin/RolePermissionController.php

    // عرض نموذج إنشاء صلاحية
    public function createPermission()
    {
        return view('dashboard.permissions.create');
    }

    // تخزين صلاحية جديدة
    public function storePermission(Request $request)
    {
        $request->validate(['name' => 'required|unique:permissions,name']);
        Permission::create(['name' => $request->name, 'guard_name' => 'web']);
        Log::info('Permission created: '.$request->name);

        return redirect()->route('permissions.index')->with('success', 'Permission created.');
    }

    // عرض نموذج تعديل صلاحية
    public function editPermission(Permission $permission)
    {
        return view('dashboard.permissions.edit', compact('permission'));
    }

    // تحديث صلاحية
    public function updatePermission(Request $request, Permission $permission)
    {
        $request->validate(['name' => 'required|unique:permissions,name,'.$permission->id]);
        $permission->update(['name' => $request->name]);

        return redirect()->route('permissions.index')->with('success', 'Permission updated.');
    }

    // حذف صلاحية
    public function destroyPermission(Permission $permission)
    {
        // منع حذف صلاحيات حرجة إذا أردت
        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permission deleted.');
    }
}
