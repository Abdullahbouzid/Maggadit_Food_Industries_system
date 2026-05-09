<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register custom component namespace
        $this->app['view']->prependNamespace('filament-support', resource_path('views/components/filament-support'));
        
        // Set locale from session or use default
        $locale = Session::get('locale', config('app.locale'));
        App::setLocale($locale);
        
        // Set HTML direction based on locale
        if (app()->getLocale() === 'ar') {
            view()->share('direction', 'rtl');
            view()->share('align', 'right');
        } else {
            view()->share('direction', 'ltr');
            view()->share('align', 'left');
        }
    }
}
