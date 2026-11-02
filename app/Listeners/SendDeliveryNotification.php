<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DeliveryCancelled;
use App\Events\DeliveryStageChanged;
use App\Models\Notification;
use App\Models\User;

class SendDeliveryNotification
{
    public function handleDeliveryCreated(DeliveryCreated $event): void
    {
        $delivery = $event->delivery;
        $orderNumber = $delivery->order?->order_number ?? $delivery->order_id;
        $branchName = $delivery->order?->branch?->name ?? '—';

        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'subject_ar' => 'إنشاء توصيل جديد',
                'subject_en' => 'New Delivery Created',
                'body_ar' => "تم إنشاء توصيل جديد للطلبية {$orderNumber} (فرع {$branchName}).",
                'body_en' => "A new delivery has been created for order #{$orderNumber} (branch {$branchName}).",
                'type' => 'delivery_created',
                'data' => [
                    'delivery_id' => $delivery->id,
                    'order_id' => $delivery->order_id,
                    'order_number' => $orderNumber,
                    'branch_id' => $delivery->order?->branch_id,
                    'branch_name' => $branchName,
                ],
                'customers' => [$delivery->order?->customer_id],
                'channel' => 'dashboard',
                'is_sent' => true,
            ]);
        }
    }

    public function handleDeliveryStageChanged(DeliveryStageChanged $event): void
    {
        $delivery = $event->delivery;
        $orderNumber = $delivery->order?->order_number ?? $delivery->order_id;
        $branchName = $delivery->order?->branch?->name ?? '—';
        $stage = $event->stage;

        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'subject_ar' => 'تحديث في حالة التسليم',
                'subject_en' => 'Delivery Status Updated',
                'body_ar' => "تم تحديث حالة التوصيل للطلبية {$orderNumber} (فرع {$branchName}) إلى مرحلة: {$stage}.",
                'body_en' => "Delivery for order #{$orderNumber} (branch {$branchName}) updated to stage: {$stage}.",
                'type' => 'delivery_status_changed',
                'data' => [
                    'delivery_id' => $delivery->id,
                    'order_id' => $delivery->order_id,
                    'order_number' => $orderNumber,
                    'branch_id' => $delivery->order?->branch_id,
                    'branch_name' => $branchName,
                    'stage' => $stage,
                ],
                'customers' => [$delivery->order?->customer_id],
                'channel' => 'dashboard',
                'is_sent' => true,
            ]);
        }
    }

    public function handleDeliveryCancelled(DeliveryCancelled $event): void
    {
        $delivery = $event->delivery;
        $orderNumber = $delivery->order?->order_number ?? $delivery->order_id;
        $branchName = $delivery->order?->branch?->name ?? '—';

        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'subject_ar' => 'إلغاء توصيل',
                'subject_en' => 'Delivery Cancelled',
                'body_ar' => "تم إلغاء التوصيل للطلبية {$orderNumber} (فرع {$branchName}).",
                'body_en' => "Delivery for order #{$orderNumber} (branch {$branchName}) has been cancelled.",
                'type' => 'delivery_cancelled',
                'data' => [
                    'delivery_id' => $delivery->id,
                    'order_id' => $delivery->order_id,
                    'order_number' => $orderNumber,
                    'branch_id' => $delivery->order?->branch_id,
                    'branch_name' => $branchName,
                    'reason' => $event->reason,
                ],
                'customers' => [$delivery->order?->customer_id],
                'channel' => 'dashboard',
                'is_sent' => true,
            ]);
        }
    }
}
