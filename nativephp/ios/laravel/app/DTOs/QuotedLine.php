<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\PriceSource;

final readonly class QuotedLine
{
    public function __construct(
        public int $itemId,
        public ?int $itemPackId,
        public float $quantity,
        public float $unitPrice,
        public float $discount,
        public float $finalUnitPrice,
        public float $taxPercent,
        public float $taxAmount,
        public float $subtotal,
        public float $total,
        public ?int $priceListItemId,
        public PriceSource $source,
        public int $currencyId,
        public float $exchangeRate,
    ) {}
}
