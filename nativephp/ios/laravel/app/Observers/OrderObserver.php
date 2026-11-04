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

        $user = request()->user();
        $isUser = $user instanceof \App\Models\User;

        \App\Models\AuditLog::create([
            'auditable_type' => Order::class,
            'auditable_id' => $order->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => $order->toArray(),
            'user_id' => $isUser ? $user->id : null,
            'user_type' => $isUser ? 'user' : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function updated(Order $order): void
    {
        $original = $order->getOriginal();
        $changes = $order->getChanges();
        unset($changes['updated_at']);

        $user = request()->user();
        $isUser = $user instanceof \App\Models\User;

        \App\Models\AuditLog::create([
            'auditable_type' => Order::class,
            'auditable_id' => $order->id,
            'event' => 'updated',
            'old_values' => $original,
            'new_values' => $changes,
            'user_id' => $isUser ? $user->id : null,
            'user_type' => $isUser ? 'user' : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function deleted(Order $order): void
    {
        $user = request()->user();
        $isUser = $user instanceof \App\Models\User;

        \App\Models\AuditLog::create([
            'auditable_type' => Order::class,
            'auditable_id' => $order->id,
            'event' => 'deleted',
            'old_values' => $order->toArray(),
            'new_values' => null,
            'user_id' => $isUser ? $user->id : null,
            'user_type' => $isUser ? 'user' : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
