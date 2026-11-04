<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected function tokensMatch($request)
    {
        $token = $this->getTokenFromRequest($request);

        if ($request->bearerToken()) {
            $personalToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            if ($personalToken && $personalToken->tokenable) {
                return true;
            }
        }

        return is_string($request->session()->token()) && is_string($token) &&
            hash_equals($request->session()->token(), $token);
    }

    protected $except = [
        // مسارات الـ API الخاصة بالعميل (بدون تحقق CSRF)
        'api/customer/*',
        // مسارات الطلبات وعروض الأسعار (مستمدة من تطبيق NativePHP)
        'customer/order/*/quotation/respond',
        // مسارات الإشعارات (مستمدة من تطبيق NativePHP)
        'customer/notifications/*',
        // مسارات المفضلة (مستمدة من تطبيق NativePHP)
        'customer/favorite/*',
        // تحديث معلومات العميل (مستمدة من تطبيق NativePHP)
        'customer/my-info',
        // إتمام الطلب (مستمدة من تطبيق NativePHP)
        'customer/checkout',
    ];
}
