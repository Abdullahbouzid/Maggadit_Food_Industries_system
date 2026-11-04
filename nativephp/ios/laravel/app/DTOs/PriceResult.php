<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\PriceSource;

final readonly class PriceResult
{
    public function __construct(
        public float $unitPrice,
        public float $discount,
        public float $finalUnitPrice,
        public float $taxPercent,
        public float $taxAmount,
        public PriceSource $source,
        public ?int $priceListItemId,
        public int $currencyId,
        public float $exchangeRate,
    ) {}
}
