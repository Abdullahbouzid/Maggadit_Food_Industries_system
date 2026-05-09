<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;  // ✅ هنا أيضاً

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403);
        }
        return $next($request);
    }
}