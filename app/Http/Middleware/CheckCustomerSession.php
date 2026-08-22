<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckCustomerSession
{
    public function handle(Request $request, Closure $next)
    {
        // التحقق من جلسة PHP العادية
        if (Auth::guard('customer')->check()) {
            return $next($request);
        }

        // التحقق من التوكن في الجلسة (للـ APK الذي قد لا يرسل الـ cookies بشكل صحيح)
        if ($request->session()->has('api_token') && ! empty($request->session()->get('api_token'))) {
            return $next($request);
        }

        return redirect()->route('mobile.login');
    }
}
