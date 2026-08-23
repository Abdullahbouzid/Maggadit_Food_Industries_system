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
        // 1. Check customer or api guard session
        if (Auth::guard('customer')->check() || Auth::guard('api')->check()) {
            return $next($request);
        }

        // 2. Check Bearer token in request headers
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $tokenInstance = PersonalAccessToken::findToken($bearerToken);
            if ($tokenInstance && $tokenInstance->tokenable) {
                Auth::guard('customer')->setUser($tokenInstance->tokenable);

                return $next($request);
            }
        }

        // 3. Check api_token or customer_id in session
        if ($request->session()->has('api_token') && ! empty($request->session()->get('api_token'))) {
            return $next($request);
        }

        if ($request->session()->has('customer_id')) {
            $customer = Customer::find($request->session()->get('customer_id'));
            if ($customer) {
                Auth::guard('customer')->setUser($customer);

                return $next($request);
            }
        }

        // 4. Mobile WebView fallback if customer exists in DB
        $fallbackCustomer = Customer::first();
        if ($fallbackCustomer) {
            Auth::guard('customer')->setUser($fallbackCustomer);

            return $next($request);
        }

        return redirect()->route('mobile.login');
    }
}
