<?php

declare(strict_types=1);

namespace App\Observers;

use App\Events\WarehouseStockAlert;
use App\Models\Inventory;
use App\Models\AuditLog;

class InventoryObserver
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

    public function created(Inventory $inventory): void
    {
        $audit = $this->auditUser();

        AuditLog::create([
            'auditable_type' => Inventory::class,
            'auditable_id' => $inventory->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => $inventory->toArray(),
            'user_id' => $audit['user_id'],
            'user_type' => $audit['user_type'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function updated(Inventory $inventory): void
    {
        $audit = $this->auditUser();
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
            'user_id' => $audit['user_id'],
            'user_type' => $audit['user_type'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $availableQty = (float) $inventory->available_quantity;
        $threshold = (float) $inventory->reorder_level;

        if ($availableQty <= $threshold) {
            WarehouseStockAlert::dispatch($inventory, $availableQty, $threshold);
        }
    }
}