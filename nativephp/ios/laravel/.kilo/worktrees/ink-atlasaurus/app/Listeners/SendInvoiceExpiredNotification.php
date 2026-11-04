<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\InvoiceExpired;
use App\Models\Customer;
use Illuminate\Support\Facades\Notification;

class SendInvoiceExpiredNotification
{
    public function handle(InvoiceExpired $event): void
    {
        $invoice = $event->invoice;
        $customer = $invoice->customer;

        if (!$customer || !$customer->fcm_token) {
            return;
        }

        Notification::send([$customer], new \App\Notifications\InvoiceExpired($invoice));
    }
}
