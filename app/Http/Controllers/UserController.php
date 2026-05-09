<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Permission;  // ✅ أضف هذا
use Spatie\Permission\Models\Role;         // ✅ للرولات
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function assignPermission(User $user, $permissionName)
    {
        $permission = Permission::findByName($permissionName);
        $user->givePermissionTo($permission);
    }
}