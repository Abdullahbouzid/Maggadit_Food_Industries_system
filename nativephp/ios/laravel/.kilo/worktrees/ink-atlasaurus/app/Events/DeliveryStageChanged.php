<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryStageChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Delivery $delivery,
        public string $stage,
        public ?User $updatedBy = null,
    ) {}
}
