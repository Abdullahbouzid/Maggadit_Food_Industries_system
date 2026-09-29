<?php

return [
    'pdf' => [
        'enabled' => true,
        'binary' => '/usr/bin/wkhtmltopdf',
        'options' => [
            'encoding' => 'UTF-8',
            'enable-local-file-access' => true,
            'javascript-delay' => 0,
            'no-stop-slow-scripts' => true,
        ],
        'timeout' => 120,
    ],
    'image' => [
        'enabled' => false,
        'binary' => '/usr/bin/wkhtmltoimage',
        'options' => [],
        'timeout' => 60,
    ],
];