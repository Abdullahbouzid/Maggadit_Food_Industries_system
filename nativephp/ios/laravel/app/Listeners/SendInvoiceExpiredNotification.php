<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\InvoiceExpired;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Log;

class SendInvoiceExpiredNotification
{
    public function handle(InvoiceExpired $event): void
    {
        $invoice = $event->invoice;
        $customer = $invoice->customer;
        $orderNumber = $invoice->order?->order_number ?? $invoice->order_id ?? '—';
        $branchName = $invoice->order?->branch?->name ?? '—';

        if ($customer && $customer->fcm_token) {
            NotificationFacade::send([$customer], new \App\Notifications\InvoiceExpired($invoice));
        }

        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'subject_ar' => 'انتهت صلاحية فاتورة',
                'subject_en' => 'Invoice Expired',
                'body_ar' => "انتهت صلاحية الفاتورة #{$invoice->id} للطلبية {$orderNumber} (فرع {$branchName}).",
                'body_en' => "Invoice #{$invoice->id} for order #{$orderNumber} (branch {$branchName}) has expired.",
                'type' => 'invoice_expired',
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

        Log::info('Invoice expired notification sent', [
            'invoice_id' => $invoice->id,
            'order_id' => $invoice->order_id,
        ]);
    }
}
