<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$request = Request::create('/mobile/register', 'POST', [
    'name' => 'Test User 3',
    'username' => 'testuser3_'.rand(1000, 9999),
    'email' => 'test3_'.rand(1000, 9999).'@example.com',
    'password' => 'password123',
]);
$request->headers->set('Accept', 'application/json');

$response = app()->handle($request);
echo 'Status: '.$response->getStatusCode()."\n";
echo 'Content: '.$response->getContent()."\n";
