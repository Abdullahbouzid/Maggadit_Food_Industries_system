<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$app->bootstrapWith([
    Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
    Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
    Illuminate\Foundation\Bootstrap\HandleExceptions::class,
    Illuminate\Foundation\Bootstrap\RegisterFacades::class,
    Illuminate\Foundation\Bootstrap\RegisterProviders::class,
    Illuminate\Foundation\Bootstrap\BootProviders::class,
]);

try {
    $tables = DB::select('SHOW TABLES');
    echo "Tables in database:\n";
    foreach ($tables as $table) {
        foreach ($table as $key => $value) {
            echo "- $value\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>