<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * المسارات المستثناة من التحقق CSRF.
     * مسارات Proxy تُستدعى من WebView في الـ APK
     * حيث لا يمكن الاعتماد على CSRF بشكل موثوق.
     *
     * @var array<int, string>
     */
    protected $except = [
        'customer/proxy/*',
    ];
}
