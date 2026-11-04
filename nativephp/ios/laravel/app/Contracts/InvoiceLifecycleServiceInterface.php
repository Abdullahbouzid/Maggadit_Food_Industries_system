<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Carbon;

interface InvoiceLifecycleServiceInterface
{
    public function approve(Invoice $invoice, ?User $user = null, ?string $notes = null): Invoice;

    public function applyDeadline(Invoice $invoice): void;

    public function extendDeadline(Invoice $invoice, int $additionalHours, User $user, ?string $notes = null): Carbon;

    public function markAsPaid(Invoice $invoice, ?User $user = null): Invoice;

    public function markAsPartiallyPaid(Invoice $invoice, float $amount, ?User $user = null): Invoice;

    public function reject(Invoice $invoice, ?User $user = null, ?string $reason = null): Invoice;

    public function cancel(Invoice $invoice, ?User $user = null, ?string $reason = null): Invoice;

    public function expire(Invoice $invoice): Invoice;

    public function canTransitionTo(Invoice $invoice, InvoiceStatus $status): bool;

    public function getAllowedTransitions(Invoice $invoice): array;
}
