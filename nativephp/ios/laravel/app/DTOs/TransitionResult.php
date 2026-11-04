<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class TransitionResult
{
    public function __construct(
        public string $fromStatus,
        public string $toStatus,
        public array $sideEffects = [],
        public bool $success = true,
        public ?string $errorMessage = null,
    ) {}
}
