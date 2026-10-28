<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\Notification;
use App\Models\User;

class SendOrderCreatedNotification
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $orderNumber = $order->order_number ?? $order->id;
        $branchName = $order->branch?->name ?? '—';

        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'subject_ar' => 'طلب جديد',
                'subject_en' => 'New Order Created',
                'body_ar' => "تم إنشاء طلبية جديدة رقم {$orderNumber} في فرع {$branchName}.",
                'body_en' => "A new order #{$orderNumber} has been created at branch {$branchName}.",
                'type' => 'order_created',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $orderNumber,
                    'branch_id' => $order->branch_id,
                    'branch_name' => $branchName,
                    'customer_id' => $order->customer_id,
                    'total' => $order->total,
                ],
                'customers' => [$order->customer_id],
                'channel' => 'dashboard',
                'is_sent' => true,
            ]);
        }
    }
}
