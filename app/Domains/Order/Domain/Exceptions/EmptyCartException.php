<?php

namespace App\Domains\Order\Domain\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class EmptyCartException extends Exception
{
    public function __construct(string $message = 'لا يمكن إتمام الطلب، السلة فارغة.')
    {
        parent::__construct($message, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => $this->getMessage(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}