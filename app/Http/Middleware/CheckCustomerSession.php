<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class CheckCustomerSession
{
    public function handle(Request $request, Closure $next)
    {
        // 1. جلسة guards (customer / api)
        if (Auth::guard('customer')->check()) {
            return $next($request);
        }

        if (Auth::guard('api')->check()) {
            return $next($request);
        }

        // 2. Bearer token (Sanctum) في ترويسة الطلب
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $tokenInstance = PersonalAccessToken::findToken($bearerToken);
            if ($tokenInstance && $tokenInstance->tokenable) {
                Auth::guard('customer')->setUser($tokenInstance->tokenable);

                return $next($request);
            }
        }

        // 3. customer_id المحفوظ في الجلسة (يضبطه مسار تسجيل الدخول)
        $customerId = $request->session()->get('customer_id');
        if ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                Auth::guard('customer')->setUser($customer);

                return $next($request);
            }
        }

        // 4. لا يوجد عميل مصادق -> إعادة التوجيه لصفحة الدخول
        //    لا يتم استخدام أي عميل احتياطي Secara عشوائي لتجنب تسريب بيانات العملاء.
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        return redirect()->route('mobile.login');
    }
}
