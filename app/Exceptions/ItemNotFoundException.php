<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

class ItemNotFoundException extends DomainException
{
    public function __construct(
        public readonly int $itemId,
        public readonly ?int $itemPackId = null,
    ) {
        parent::__construct(
            sprintf(
                'Item%s not found%s',
                $itemPackId ? ' pack' : '',
                $itemPackId ? ' (item '.$itemId.', pack '.$itemPackId.')' : ' (item '.$itemId.')',
            ),
            Response::HTTP_NOT_FOUND,
        );
    }
}
