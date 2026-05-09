<?php

return [
    'paths' => [
        resource_path('views'),
    ],

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

    'namespaces' => [
        'filament-support' => resource_path('views/components/filament-support'),
    ],
];
