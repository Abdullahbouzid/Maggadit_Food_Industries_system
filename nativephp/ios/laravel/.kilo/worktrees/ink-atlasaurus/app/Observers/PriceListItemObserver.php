<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\PriceListItem;
use App\Models\AuditLog;

class PriceListItemObserver
{
    public function created(PriceListItem $priceListItem): void
    {
        AuditLog::create([
            'auditable_type' => PriceListItem::class,
            'auditable_id' => $priceListItem->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => $priceListItem->toArray(),
            'user_id' => request()->user()?->id,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function updated(PriceListItem $priceListItem): void
    {
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
            'user_id' => request()->user()?->id,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function deleted(PriceListItem $priceListItem): void
    {
        AuditLog::create([
            'auditable_type' => PriceListItem::class,
            'auditable_id' => $priceListItem->id,
            'event' => 'deleted',
            'old_values' => $priceListItem->toArray(),
            'new_values' => null,
            'user_id' => request()->user()?->id,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
