<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

class ReservationExpiredException extends DomainException
{
    public function __construct(
        public readonly int $reservationId,
    ) {
        parent::__construct(
            sprintf('Stock reservation #%d has expired and cannot be used', $reservationId),
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }
}
