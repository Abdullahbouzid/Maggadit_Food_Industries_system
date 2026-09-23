<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Invoice;
use App\Models\AuditLog;

class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
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
