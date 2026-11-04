<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Result of an availability check for a specific item+pack+warehouse combination.
 *
 * @param int $itemId The item ID
 * @param int $itemPackId The item pack ID (0 if no pack)
 * @param int $warehouseId The warehouse ID
 * @param float $onHand Total quantity on hand
 * @param float $reserved Quantity currently reserved
 * @param float $available Available quantity (onHand - reserved)
 * @param float $damaged Quantity damaged/not sellable
 * @param float $reorderLevel Minimum stock level before reorder
 * @param bool $needsReorder Whether quantity_on_hand <= reorder_level
 */
final readonly class AvailabilityResult
{
    public function __construct(
        public int $itemId,
        public int $itemPackId,
        public int $warehouseId,
        public float $onHand,
        public float $reserved,
        public float $available,
        public float $damaged,
        public float $reorderLevel,
        public bool $needsReorder,
    ) {}
}
