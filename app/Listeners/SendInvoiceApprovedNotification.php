<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\InvoiceApproved;
use App\Models\Customer;
use Illuminate\Support\Facades\Notification;

class SendInvoiceApprovedNotification
{
    public function handle(InvoiceApproved $event): void
    {
        $invoice = $event->invoice;
        $customer = $invoice->customer;

        if (!$customer || !$customer->fcm_token) {
            return;
        }

        // Send push notification via FCM
        // The actual notification channel implementation is separate
        Notification::send([$customer], new \App\Notifications\InvoiceApproved($invoice));
    }
}
