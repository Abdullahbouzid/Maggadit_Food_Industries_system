<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Order;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Support\Carbon;

interface QuotationServiceInterface
{
    /**
     * Create a quotation from an order.
     * Increments version if a previous quotation exists.
     */
    public function createFromOrder(Order $order, ?User $user = null): Quotation;

    /**
     * Send a quotation to the customer.
     */
    public function send(Quotation $quotation, ?User $user = null): void;

    /**
     * Record customer response to a quotation.
     * @param string $response 'approved' | 'rejected' | 'changes_requested'
     */
    public function recordCustomerResponse(
        Quotation $quotation,
        string $response,
        ?string $notes = null,
    ): Quotation;
}
