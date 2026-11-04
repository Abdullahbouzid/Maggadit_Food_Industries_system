<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class SendOrderStatusChangedNotification
{
    public function handle(OrderStatusChanged $event): void
    {
        $order = \App\Models\Order::find($event->orderId);

        if (!$order) {
            return;
        }

        $fromStatus = $event->fromStatus ? $event->fromStatus->label() : null;
        $toStatus = $event->toStatus->label();
        $orderNumber = $order->order_number ?? $order->id;

        $customer = $order->customer;

        if ($customer && $customer->fcm_token) {
            NotificationFacade::send([$customer], new \App\Notifications\OrderStatusChanged($order, $event->fromStatus, $event->toStatus));
        }

        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $fromLabel = $fromStatus ?? '—';
            Notification::create([
                'subject_ar' => 'تغيير في حالة الطلبية',
                'subject_en' => 'Order Status Changed',
                'body_ar' => "تم تغيير حالة الطلبية {$orderNumber} من '{$fromLabel}' إلى '{$toStatus}'",
                'body_en' => "Order #{$orderNumber} status changed from '{$fromLabel}' to '{$toStatus}'",
                'type' => 'order_status_changed',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $orderNumber,
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                ],
                'customers' => [],
                'channel' => 'all',
                'is_sent' => true,
            ]);
        }

        Log::info('Order status change notification sent', [
            'order_id' => $order->id,
            'from_status' => $event->fromStatus?->value,
            'to_status' => $event->toStatus->value,
        ]);
    }
}
