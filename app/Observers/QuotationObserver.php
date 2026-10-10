<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Quotation;
use App\Models\AuditLog;

class QuotationObserver
{
    private function auditUser(): array
    {
        $user = request()->user();
        $isUser = $user instanceof \App\Models\User;
        return [
            'user_id' => $isUser ? $user->id : null,
            'user_type' => $isUser ? 'user' : null,
        ];
    }

    public function created(Quotation $quotation): void
    {
        $audit = $this->auditUser();

        AuditLog::create([
            'auditable_type' => Quotation::class,
            'auditable_id' => $quotation->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => $quotation->toArray(),
            'user_id' => $audit['user_id'],
            'user_type' => $audit['user_type'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function updated(Quotation $quotation): void
    {
        $audit = $this->auditUser();
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
            'user_id' => $audit['user_id'],
            'user_type' => $audit['user_type'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}