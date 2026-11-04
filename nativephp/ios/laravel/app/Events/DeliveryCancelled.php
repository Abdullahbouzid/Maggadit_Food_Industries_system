<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Delivery;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Delivery $delivery,
        public ?string $reason = null,
    ) {}
}