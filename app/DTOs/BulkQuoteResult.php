<?php

declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Support\Collection;

/**
 * Result of a bulk quote operation.
 * Holds a collection of QuotedLine DTOs plus summary metadata.
 */
final readonly class BulkQuoteResult
{
    /**
     * @param Collection<int, QuotedLine> $lines
     * @param int $totalLines Number of lines processed
     * @param float $totalAmount Sum of all line totals
     * @param int $currencyId Currency of all quoted lines
     */
    public function __construct(
        public Collection $lines,
        public int $totalLines,
        public float $totalAmount,
        public int $currencyId,
    ) {}
}
