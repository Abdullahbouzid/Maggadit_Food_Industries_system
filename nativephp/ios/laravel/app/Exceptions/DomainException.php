<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DomainException extends Exception
{
    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        $status = property_exists($this, 'statusCode') && $this->statusCode !== null
            ? $this->statusCode
            : Response::HTTP_UNPROCESSABLE_ENTITY;

        return response()->json([
            'message' => $this->getMessage(),
            'code' => $this->code ?: $status,
            'errors' => property_exists($this, 'context') && $this->context !== null
                ? $this->context
                : null,
        ], $status);
    }
}
