<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;

interface OrderServiceInterface
{
    /**
     * Create an order from a customer with items.
     * Snapshots customer price list, validates stock, computes totals,
     * and generates a price quote via PricingService::bulkQuote.
     */
    public function createFromCustomer(
        Customer $customer,
        array $items,
        ?User $user = null,
        ?string $deliveryAddress = null,
        ?string $deliveryPhone = null,
        ?Carbon $scheduledDeliveryDate = null,
    ): Order;
}
