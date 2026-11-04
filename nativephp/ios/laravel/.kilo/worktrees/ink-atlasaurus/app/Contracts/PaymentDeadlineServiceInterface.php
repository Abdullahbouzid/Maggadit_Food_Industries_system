<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Carbon;

interface PaymentDeadlineServiceInterface
{
    public function applyDeadline(Invoice $invoice): ?Carbon;

    public function extend(Invoice $invoice, int $hours, User $user, ?string $notes = null): void;

    public function expireUnpaid(): array;
}
