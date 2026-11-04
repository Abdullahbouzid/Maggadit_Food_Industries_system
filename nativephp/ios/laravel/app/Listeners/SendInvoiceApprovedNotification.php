<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\InvoiceApproved;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Log;

class SendInvoiceApprovedNotification
{
    public function handle(InvoiceApproved $event): void
    {
        $invoice = $event->invoice;
        $customer = $invoice->customer;
        $orderNumber = $invoice->order?->order_number ?? $invoice->order_id ?? '—';
        $branchName = $invoice->order?->branch?->name ?? '—';

        if ($customer && $customer->fcm_token) {
            NotificationFacade::send([$customer], new \App\Notifications\InvoiceApproved($invoice));
        }

        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'subject_ar' => 'تم قبول فاتورة',
                'subject_en' => 'Invoice Approved',
                'body_ar' => "تمت الموافقة على الفاتورة #{$invoice->id} للطلبية {$orderNumber} (فرع {$branchName}).",
                'body_en' => "Invoice #{$invoice->id} for order #{$orderNumber} (branch {$branchName}) has been approved.",
                'type' => 'invoice_approved',
                'data' => [
                    'invoice_id' => $invoice->id,
                    'order_id' => $invoice->order_id,
                    'order_number' => $orderNumber,
                    'branch_id' => $invoice->order?->branch_id,
                    'branch_name' => $branchName,
                ],
                'customers' => [$invoice->customer_id],
                'channel' => 'dashboard',
                'is_sent' => true,
            ]);
        }

        Log::info('Invoice approved notification sent', [
            'invoice_id' => $invoice->id,
            'order_id' => $invoice->order_id,
        ]);
    }
}
