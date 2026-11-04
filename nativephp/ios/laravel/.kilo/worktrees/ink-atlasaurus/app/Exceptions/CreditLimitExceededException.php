<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

class CreditLimitExceededException extends DomainException
{
    public function __construct(
        public readonly float $required,
        public readonly float $limit,
        public readonly float $currentBalance,
    ) {
        parent::__construct(
            sprintf(
                'Credit limit exceeded: required %s, limit %s, current balance %s',
                $required,
                $limit,
                $currentBalance,
            ),
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }
}
