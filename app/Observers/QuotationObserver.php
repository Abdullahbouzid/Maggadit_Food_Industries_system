<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Quotation;
use App\Models\AuditLog;

class QuotationObserver
{
    public function created(Quotation $quotation): void
    {
        AuditLog::create([
            'auditable_type' => Quotation::class,
            'auditable_id' => $quotation->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => $quotation->toArray(),
            'user_id' => request()->user()?->id,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function updated(Quotation $quotation): void
    {
        $original = $quotation->getOriginal();
        $changes = $quotation->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        AuditLog::create([
            'auditable_type' => Quotation::class,
            'auditable_id' => $quotation->id,
            'event' => 'updated',
            'old_values' => $original,
            'new_values' => $changes,
            'user_id' => request()->user()?->id,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
