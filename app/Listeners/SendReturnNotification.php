<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReturnProcessed;
use App\Events\ReturnRequested;
use App\Models\Notification;
use App\Models\User;

class SendReturnNotification
{
    public function handleReturnRequested(ReturnRequested $event): void
    {
        $returnRequest = $event->returnRequest;
        $orderNumber = $returnRequest->order?->order_number ?? $returnRequest->order_id;
        $branchName = $returnRequest->order?->branch?->name ?? '—';

        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'subject_ar' => 'طلب إرجاع جديد',
                'subject_en' => 'New Return Request',
                'body_ar' => "تم إرسال طلب إرجاع جديد للطلبية {$orderNumber} (فرع {$branchName}).",
                'body_en' => "A new return request has been submitted for order #{$orderNumber} (branch {$branchName}).",
                'type' => 'return_requested',
                'data' => [
                    'return_id' => $returnRequest->id,
                    'order_id' => $returnRequest->order_id,
                    'order_number' => $orderNumber,
                    'branch_id' => $returnRequest->order?->branch_id,
                    'branch_name' => $branchName,
                ],
                'customers' => [$returnRequest->customer_id],
                'channel' => 'dashboard',
                'is_sent' => true,
            ]);
        }
    }

    public function handleReturnProcessed(ReturnProcessed $event): void
    {
        $returnRequest = $event->returnRequest;
        $orderNumber = $returnRequest->order?->order_number ?? $returnRequest->order_id;
        $branchName = $returnRequest->order?->branch?->name ?? '—';

        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'subject_ar' => 'تم معالجة طلب الإرجاع',
                'subject_en' => 'Return Processed',
                'body_ar' => "تمت معالجة طلب الإرجاع للطلبية {$orderNumber} (فرع {$branchName}).",
                'body_en' => "Return request for order #{$orderNumber} (branch {$branchName}) has been processed.",
                'type' => 'return_processed',
                'data' => [
                    'return_id' => $returnRequest->id,
                    'order_id' => $returnRequest->order_id,
                    'order_number' => $orderNumber,
                    'branch_id' => $returnRequest->order?->branch_id,
                    'branch_name' => $branchName,
                    'credit_note_id' => $event->creditNote->id,
                ],
                'customers' => [$returnRequest->customer_id],
                'channel' => 'dashboard',
                'is_sent' => true,
            ]);
        }
    }
}
