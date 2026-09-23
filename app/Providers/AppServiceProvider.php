<?php

namespace App\Providers;

use App\Console\Commands\ExpireUnpaidInvoices;
use App\Console\Commands\NotifyLowStock;
use App\Console\Commands\NotifyNearExpiryBatches;
use App\Console\Commands\ReleaseExpiredReservations;
use App\Models\City;
use App\Models\Country;
use App\Models\Customer;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

// ثم أخبر Laravel أن يستخدم Customer model للمصادقة

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\PricingServiceInterface::class,
            \App\Services\PricingService::class
        );
        $this->app->bind(
            \App\Contracts\InventoryServiceInterface::class,
            \App\Services\InventoryService::class
        );
        $this->app->bind(
            \App\Contracts\OrderServiceInterface::class,
            \App\Services\OrderService::class
        );
        $this->app->bind(
            \App\Contracts\OrderLifecycleServiceInterface::class,
            \App\Services\OrderLifecycleService::class
        );
        $this->app->bind(
            \App\Contracts\InvoiceLifecycleServiceInterface::class,
            \App\Services\InvoiceLifecycleService::class
        );
        $this->app->bind(
            \App\Contracts\DeliveryServiceInterface::class,
            \App\Services\DeliveryService::class
        );
        $this->app->bind(
            \App\Contracts\WalletServiceInterface::class,
            \App\Services\WalletService::class
        );
        $this->app->bind(
            \App\Contracts\QuotationServiceInterface::class,
            \App\Services\QuotationService::class
        );
        $this->app->bind(
            \App\Contracts\ReturnServiceInterface::class,
            \App\Services\ReturnService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // Sanctum::usePersonalAccessTokenModel(...);
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

        $this->configureSchedule();
    }

    /**
     * Configure the application's scheduled jobs.
     */
    protected function configureSchedule(): void
    {
        $schedule = $this->app->make(Schedule::class);

        $schedule->command('invoices:expire-unpaid')->hourly();
        $schedule->command('reservations:release-expired')->hourly();
        $schedule->command('inventory:notify-low-stock')->dailyAt('09:00');
        $schedule->command('batches:notify-near-expiry')->dailyAt('09:00');
    }
}
