<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $orderId,
        public ?OrderStatus $fromStatus,
        public OrderStatus $toStatus,
        public ?User $user = null,
        public ?string $notes = null,
        public bool $fromSystem = false,
    ) {}
}
