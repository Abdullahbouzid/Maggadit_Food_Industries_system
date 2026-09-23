<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Inventory;
use App\Models\AuditLog;

class InventoryObserver
{
    public function created(Inventory $inventory): void
    {
        AuditLog::create([
            'auditable_type' => Inventory::class,
            'auditable_id' => $inventory->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => $inventory->toArray(),
            'user_id' => request()->user()?->id,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function updated(Inventory $inventory): void
    {
        $original = $inventory->getOriginal();
        $changes = $inventory->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        AuditLog::create([
            'auditable_type' => Inventory::class,
            'auditable_id' => $inventory->id,
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
