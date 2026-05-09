<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory as ViewFactory;

class FilamentFixProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register a custom blade component directive for grid
        if ($this->app->bound('view')) {
            $this->app['view']->componentNamespace('filament-support::components.grid', 'filament-support::grid');
        }
    }
}
