<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Country;
use App\Models\City;

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
        //
        // ربط متغير $user مع view 'dashboard.partials.navbar'
        View::composer('dashboard.partials.navbar', function ($view) {
            $view->with('user', Auth::user());
        });
 View::composer('dashboard.partials.script', function ($view) {
        $countries = Country::with('cities')->get();
        $cities = City::with('country')->get();
        $markers = [];
        foreach ($countries as $country) {
            if ($country->latitude && $country->longitude) {
                $markers[] = ['lat' => $country->latitude, 'lng' => $country->longitude, 'name' => $country->name, 'type' => 'Country'];
            }
        }
        foreach ($cities as $city) {
            if ($city->latitude && $city->longitude) {
                $markers[] = ['lat' => $city->latitude, 'lng' => $city->longitude, 'name' => $city->name, 'type' => 'City'];
            }
        }
        $view->with('markers', $markers);
    });
        
    }
}
