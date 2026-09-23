<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

interface OrderLifecycleServiceInterface
{
    public function transition(
        Order $order,
        OrderStatus $newStatus,
        ?User $user = null,
        ?string $notes = null,
        bool $fromSystem = false,
    ): Order;

    public function canTransitionTo(Order $order, OrderStatus $newStatus): bool;

    public function getAllowedTransitions(Order $order): array;

    public function markAsInvoiceGenerated(Order $order, ?User $user = null): Order;

    public function markAsInDelivery(Order $order, ?User $user = null): Order;
}
