<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Models\Customer;
use Illuminate\Support\Facades\Notification;

class SendOrderStatusChangedNotification
{
    public function handle(OrderStatusChanged $event): void
    {
        $order = \App\Models\Order::find($event->orderId);

        if (!$order) {
            return;
        }

        $customer = $order->customer;

        if (!$customer || !$customer->fcm_token) {
            return;
        }

        Notification::send([$customer], new \App\Notifications\OrderStatusChanged($order, $event->fromStatus, $event->toStatus));
    }
}
