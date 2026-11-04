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
        // 1. bearer token (Sanctum) - check first for API requests from APK
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $tokenInstance = \Laravel\Sanctum\PersonalAccessToken::findToken($bearerToken);
            if ($tokenInstance && $tokenInstance->tokenable) {
                Auth::guard('customer')->setUser($tokenInstance->tokenable);
                // Persist token/customer_id into session only if not already set,
                // to avoid regenerating the session ID on every polling request.
                if (! $request->session()->has('api_token')) {
                    $request->session()->put('api_token', $bearerToken);
                    $request->session()->put('customer_id', $tokenInstance->tokenable->id);
                }

                return $next($request);
            }
        }

        // 2. API token stored in session (set by mobile login) — APK users
        //    The token was already validated against the external API during login.
        //    The controllers handle remote API fallback themselves.
        $apiToken = session('api_token');
        if ($apiToken) {
            $customerId = session('customer_id');
            if ($customerId) {
                $customer = Customer::find($customerId);
                if ($customer) {
                    Auth::guard('customer')->setUser($customer);
                    return $next($request);
                }
            }

            return $next($request);
        }

        // 3. جلسة guards (customer / api)
        if (Auth::guard('customer')->check()) {
            return $next($request);
        }

        if (Auth::guard('api')->check()) {
            return $next($request);
        }

        // 4. customer_id المحفوظ في الجلسة (يضبطه مسار تسجيل الدخول)
        $customerId = $request->session()->get('customer_id');
        if ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                Auth::guard('customer')->setUser($customer);

                return $next($request);
            }
        }

        // 5. لا يوجد عميل مصادق -> إعادة التوجيه لصفحة الدخول
        //    لا يتم استخدام أي عميل احتياطي Secara عشوائي لتجنب تسريب بيانات العملاء.
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        return redirect()->route('mobile.login');
    }
}
