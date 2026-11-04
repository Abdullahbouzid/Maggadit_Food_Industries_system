<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\WalletTransactionType;

final readonly class WalletResult
{
    public function __construct(
        public float $previousBalance,
        public float $newBalance,
        public float $amount,
        public WalletTransactionType $type,
        public ?int $walletId = null,
    ) {}
}
