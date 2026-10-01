<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Invoice;
use App\Models\InvoiceStatusHistory;
use App\Models\AuditLog;

class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        // Stage-entry time, used to measure stage-to-stage cancellation
        // deadlines. Written before the audit log so the first stage is timed.
        InvoiceStatusHistory::create([
            'invoice_id' => $invoice->id,
            'from_status' => null,
            'to_status' => $invoice->status,
            'changed_by' => request()->user()?->id,
        ]);

        AuditLog::create([
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => $invoice->toArray(),
            'user_id' => request()->user()?->id,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function updated(Invoice $invoice): void
    {
        $original = $invoice->getOriginal();
        $changes = $invoice->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        // Every stage change is timed so cancellation deadlines can be measured.
        if (array_key_exists('status', $changes) && $changes['status'] !== null) {
            InvoiceStatusHistory::create([
                'invoice_id' => $invoice->id,
                'from_status' => $original['status'] ?? null,
                'to_status' => $changes['status'],
                'changed_by' => request()->user()?->id,
            ]);
        }

        AuditLog::create([
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
            'event' => 'updated',
            'old_values' => $original,
            'new_values' => $changes,
            'user_id' => request()->user()?->id,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function deleted(Invoice $invoice): void
    {
        AuditLog::create([
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
            'event' => 'deleted',
            'old_values' => $invoice->toArray(),
            'new_values' => null,
            'user_id' => request()->user()?->id,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
