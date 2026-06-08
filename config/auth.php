<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

  'guards' => [
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'customers',
    ],
],
'providers' => [
    'customers' => [
        'driver' => 'eloquent',
        'model' => App\Models\Customer::class,
    ],
],
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        // إذا أردت إعادة تعيين كلمة المرور للعملاء
        'customers' => [
            'provider' => 'customers',
            'table' => 'customer_password_resets', // أنشئ هذا الجدول إذا احتجت
            'expire' => 60,
            'throttle' => 60,
        ],
    ],
];