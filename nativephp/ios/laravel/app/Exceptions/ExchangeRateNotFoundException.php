<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

final class ExchangeRateNotFoundException extends DomainException
{
    public function __construct(
        public readonly int $fromCurrencyId,
        public readonly int $toCurrencyId,
        public readonly string $reason,
    ) {
        parent::__construct(
            sprintf(
                'Exchange rate not found for conversion from currency ID %d to %d: %s',
                $fromCurrencyId,
                $toCurrencyId,
                $reason,
            ),
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }
}
