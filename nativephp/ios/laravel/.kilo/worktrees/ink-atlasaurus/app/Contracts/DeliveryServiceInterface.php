<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

interface DeliveryServiceInterface
{
    public function assignDriver(
        Invoice $invoice,
        ?Vehicle $vehicle = null,
        ?User $driver = null,
    ): Delivery;

    public function advanceStage(
        Delivery $delivery,
        string $stage,
        ?User $user = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $notes = null,
        ?string $photoPath = null,
    ): Delivery;

    public function getTimeline(Invoice|Order $model): Collection;

    public function cancel(Invoice $invoice, ?string $reason = null, ?User $user = null): ?Delivery;
}
