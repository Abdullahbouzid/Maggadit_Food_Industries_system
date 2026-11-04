<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Single FEFO (First Expired First Out) batch allocation result.
 *
 * @param int $batchStockId ID of the batch_item_stock record
 * @param int $batchItemId ID of the batch_item record
 * @param float $quantity Quantity allocated from this batch
 * @param string|null $expiryDate Expiry date in Y-m-d format (null if no expiry)
 * @param float|null $unitCost Unit cost of the batch item (null if unknown)
 */
final readonly class FefoAllocation
{
    public function __construct(
        public int $batchStockId,
        public int $batchItemId,
        public float $quantity,
        public ?string $expiryDate,
        public ?float $unitCost,
    ) {}
}
