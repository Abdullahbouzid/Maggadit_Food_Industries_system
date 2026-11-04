<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\Response;

class InsufficientStockException extends DomainException
{
    public function __construct(
        public readonly float $requested,
        public readonly float $available,
        public readonly ?int $itemId = null,
        public readonly ?int $itemPackId = null,
        public readonly ?int $warehouseId = null,
    ) {
        $message = sprintf(
            'Insufficient stock: requested %s, available %s',
            $requested,
            $available,
        );

        if ($itemId) {
            $message .= sprintf(' for item %d', $itemId);
        }

        if ($itemPackId) {
            $message .= sprintf(' pack %d', $itemPackId);
        }

        if ($warehouseId) {
            $message .= sprintf(' at warehouse %d', $warehouseId);
        }

        parent::__construct($message, Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
