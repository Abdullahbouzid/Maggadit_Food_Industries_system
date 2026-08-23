<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$request = Request::create('/mobile/register', 'POST', [
    'name' => 'Test User 2',
    'username' => 'testuser2',
    'email' => 'test2@example.com',
    'password' => 'password123',
]);
$request->headers->set('Accept', 'application/json');

$response = app()->handle($request);
echo 'Status: '.$response->getStatusCode()."\n";
echo 'Content: '.$response->getContent()."\n";
