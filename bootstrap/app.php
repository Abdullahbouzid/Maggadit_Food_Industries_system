<?php

use App\Http\Middleware\CheckCustomerSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;   // <-- Add this line

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'mobile/login',
            'mobile/register',
            'customer/proxy/*',
            'customer/favorite/toggle',
        ]);
        $middleware->alias([
            'check.customer.session' => CheckCustomerSession::class,
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\Localization::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
