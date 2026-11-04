<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

class InsufficientBalanceException extends DomainException
{
    public function __construct(
        public readonly float $required,
        public readonly float $available,
        public readonly string $currencyCode,
    ) {
        parent::__construct(
            sprintf(
                'Insufficient wallet balance: required %s %s, available %s %s',
                $required,
                $currencyCode,
                $available,
                $currencyCode,
            ),
            Response::HTTP_PAYMENT_REQUIRED,
        );
    }
}
