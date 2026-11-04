<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;

class OrderObserver
{
    public function created(Order $order): void
    {
        \App\Events\OrderCreated::dispatch($order, null);

        \App\Models\AuditLog::create([
            'auditable_type' => Order::class,
            'auditable_id' => $order->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => $order->toArray(),
            'user_id' => request()->user()?->id,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function updated(Order $order): void
    {
        $original = $order->getOriginal();
        $changes = $order->getChanges();
        unset($changes['updated_at']);

        \App\Models\AuditLog::create([
            'auditable_type' => Order::class,
            'auditable_id' => $order->id,
            'event' => 'updated',
            'old_values' => $original,
            'new_values' => $changes,
            'user_id' => request()->user()?->id,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function deleted(Order $order): void
    {
        \App\Models\AuditLog::create([
            'auditable_type' => Order::class,
            'auditable_id' => $order->id,
            'event' => 'deleted',
            'old_values' => $order->toArray(),
            'new_values' => null,
            'user_id' => request()->user()?->id,
            'user_type' => 'user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
