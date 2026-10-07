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

        if (is_string($token) && is_string($request->session()->token())
            && hash_equals($request->session()->token(), $token)) {
            return true;
        }

        if ($request->session()->get('customer_id') || $request->session()->get('api_token')) {
            return true;
        }

        return false;
    }

    protected $except = [
        'api/customer/*',
        'customer/proxy/checkout',
        'customer/proxy/contact-us',
        'mobile/*',
    ];
}
