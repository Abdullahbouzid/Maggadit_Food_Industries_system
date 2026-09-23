<?php

declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Support\Collection;

final readonly class ReturnResult
{
    public function __construct(
        public bool $canReturn,
        public ?string $reason = null,
        public ?Collection $items = null,
        public ?int $orderId = null,
    ) {}
}
