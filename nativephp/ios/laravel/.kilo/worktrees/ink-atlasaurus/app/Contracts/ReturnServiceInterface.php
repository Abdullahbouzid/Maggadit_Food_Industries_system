<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\ReturnRequest;

interface ReturnServiceInterface
{
    public function createRequest(
        Invoice $invoice,
        \App\Models\Customer $customer,
        string $type,
        string $reason,
        array $items,
        ?\App\Models\User $user = null,
    ): ReturnRequest;

    public function approve(ReturnRequest $request, ?\App\Models\User $reviewer = null, ?string $notes = null): ReturnRequest;

    public function reject(ReturnRequest $request, ?\App\Models\User $reviewer = null, ?string $reason = null): ReturnRequest;

    public function process(ReturnRequest $request): CreditNote;

    public function applyToInvoice(CreditNote $creditNote, ?Invoice $invoice = null): Invoice;
}
