<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\InvoiceApproved;
use App\Events\InvoiceExpired;
use App\Events\OrderStatusChanged;
use App\Events\WalletDebited;
use App\Events\WalletDeposited;
use App\Listeners\SendInvoiceApprovedNotification;
use App\Listeners\SendInvoiceExpiredNotification;
use App\Listeners\SendOrderStatusChangedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        InvoiceApproved::class => [
            SendInvoiceApprovedNotification::class,
        ],
        InvoiceExpired::class => [
            SendInvoiceExpiredNotification::class,
        ],
        OrderStatusChanged::class => [
            SendOrderStatusChangedNotification::class,
        ],
        WalletDeposited::class => [
            // Auto-trigger: could send email/SMS
        ],
        WalletDebited::class => [
            // Auto-trigger: could send email/SMS
        ],
    ];

    protected $observers = [
        \App\Models\Order::class => \App\Observers\OrderObserver::class,
        \App\Models\Invoice::class => \App\Observers\InvoiceObserver::class,
        \App\Models\Quotation::class => \App\Observers\QuotationObserver::class,
        \App\Models\PriceListItem::class => \App\Observers\PriceListItemObserver::class,
        \App\Models\Inventory::class => \App\Observers\InventoryObserver::class,
        \App\Models\CustomerWallet::class => \App\Observers\CustomerWalletObserver::class,
    ];

    public function boot(): void
    {
        parent::boot();
    }
};
