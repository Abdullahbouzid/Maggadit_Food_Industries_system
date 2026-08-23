<?php

use App\Models\Customer;
use App\Models\User;

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // guard خاص بالعملاء (لتطبيق الويب داخل APK)
        'customer' => [
            'driver' => 'session',
            'provider' => 'customers',
        ],

        // guard خاص بـ API (للموبايل عبر Sanctum)
        'api' => [
            'driver' => 'sanctum',
            'provider' => 'customers',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => User::class,
        ],

        // provider خاص بنموذج Customer
        'customers' => [
            'driver' => 'eloquent',
            'model' => Customer::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'customers' => [
            'provider' => 'customers',
            'table' => 'customer_password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],
];
