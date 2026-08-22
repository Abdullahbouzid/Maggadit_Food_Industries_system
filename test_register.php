<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/mobile/register', 'POST', [
    'name' => 'Test User 2',
    'username' => 'testuser2',
    'email' => 'test2@example.com',
    'password' => 'password123',
]);
$request->headers->set('Accept', 'application/json');

$response = app()->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
