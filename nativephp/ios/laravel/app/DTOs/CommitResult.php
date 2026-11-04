<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Result of committing stock for an invoice via FEFO allocation.
 *
 * @param array<int, FefoAllocation> $allocations All batch allocations made during commit
 * @param float $totalQuantity Total quantity committed across all batches
 * @param int $invoiceId The invoice that triggered the commit
 */
final readonly class CommitResult
{
    public function __construct(
        public array $allocations,
        public float $totalQuantity,
        public int $invoiceId,
    ) {}
}
