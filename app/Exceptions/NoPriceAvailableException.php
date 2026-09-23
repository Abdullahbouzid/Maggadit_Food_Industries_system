<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

class NoPriceAvailableException extends DomainException
{
    public function __construct(
        public readonly int $itemId,
        public readonly ?int $itemPackId = null,
    ) {
        parent::__construct(
            sprintf(
                'No price available for item %d%s',
                $itemId,
                $itemPackId ? ' pack '.$itemPackId : '',
            ),
            Response::HTTP_NOT_FOUND,
        );
    }
}
