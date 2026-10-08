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
        'api/customer/*',
    ];
}
