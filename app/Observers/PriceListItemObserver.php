<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\PriceListItem;
use App\Models\AuditLog;

class PriceListItemObserver
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

    public function created(PriceListItem $priceListItem): void
    {
        $audit = $this->auditUser();

        AuditLog::create([
            'auditable_type' => PriceListItem::class,
            'auditable_id' => $priceListItem->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => $priceListItem->toArray(),
            'user_id' => $audit['user_id'],
            'user_type' => $audit['user_type'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function updated(PriceListItem $priceListItem): void
    {
        $audit = $this->auditUser();
        $original = $priceListItem->getOriginal();
        $changes = $priceListItem->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        AuditLog::create([
            'auditable_type' => PriceListItem::class,
            'auditable_id' => $priceListItem->id,
            'event' => 'updated',
            'old_values' => $original,
            'new_values' => $changes,
            'user_id' => $audit['user_id'],
            'user_type' => $audit['user_type'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function deleted(PriceListItem $priceListItem): void
    {
        $audit = $this->auditUser();

        AuditLog::create([
            'auditable_type' => PriceListItem::class,
            'auditable_id' => $priceListItem->id,
            'event' => 'deleted',
            'old_values' => $priceListItem->toArray(),
            'new_values' => null,
            'user_id' => $audit['user_id'],
            'user_type' => $audit['user_type'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}