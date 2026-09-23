<?php

namespace App\Domains\Inventory\Domain\Exceptions;

use Exception; 
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class InsufficientStockException extends Exception
{
    public function __construct(string $message = 'لا يمكن إخراج كمية أكبر من المخزون المتاح.')
    {
        // Standard Exception constructor: ($message, $code)
        parent::__construct($message, Response::HTTP_CONFLICT);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status_code' => Response::HTTP_CONFLICT,
            'message' => $this->getMessage(),
        ], Response::HTTP_CONFLICT);
    }
}