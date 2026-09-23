<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

class InvalidTransitionException extends DomainException
{
    public function __construct(
        public readonly string $entityType,
        public readonly string $fromStatus,
        public readonly string $toStatus,
        public readonly ?int $entityId = null,
    ) {
        parent::__construct(
            sprintf(
                "Invalid transition for %s%s: '%s' → '%s'",
                $entityType,
                $entityId ? " #{$entityId}" : '',
                $fromStatus,
                $toStatus,
            ),
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }
}
