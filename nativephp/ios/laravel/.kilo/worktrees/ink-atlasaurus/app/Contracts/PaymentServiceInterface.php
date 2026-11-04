<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Payment;

interface PaymentServiceInterface
{
    /**
     * Record a payment for an invoice.
     */
    public function record(
        Invoice $invoice,
        float $amount,
        string $method,
        ?string $reference = null,
        ?int $receivedBy = null,
        ?string $notes = null,
    ): Payment;

    /**
     * Void/refund a credit note's financial impact.
     */
    public function void(CreditNote $creditNote, ?string $reason = null): void;
}
