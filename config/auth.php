<?php

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
        // ✅ أضف هذا الـ guard الخاص بالعملاء لتطبيق الويب
        'customer' => [
            'driver' => 'session',
            'provider' => 'customers',
        ],
        // ✅ هذا الـ guard الخاص بـ API (لـ Sanctum)
        'api' => [
            'driver' => 'sanctum',
            'provider' => 'customers',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        // ✅ أضف هذا الـ provider الخاص بنموذج Customer
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