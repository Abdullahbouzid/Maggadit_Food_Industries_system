<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/mobile/register', 'POST', [
    'name' => 'Test User 3',
    'username' => 'testuser3_'.rand(1000,9999),
    'email' => 'test3_'.rand(1000,9999).'@example.com',
    'password' => 'password123',
]);
$request->headers->set('Accept', 'application/json');

$response = app()->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
