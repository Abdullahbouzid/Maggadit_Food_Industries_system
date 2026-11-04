<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\DeliveryStage;
use App\Models\User;
use Carbon\Carbon;

final readonly class DeliveryResult
{
    public function __construct(
        public DeliveryStage $stage,
        public ?User $driver = null,
        public ?Carbon $estimatedArrival = null,
        public float $distanceKm = 0.0,
    ) {}
}
