<?php

namespace App\Domains\Inventory\Domain\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ProductNotAvailableException extends Exception
{
    public function render($request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status_code' => 422,
            'message' => $this->getMessage() ?: 'المنتج غير متوفر أو غير فعال.',
        ], 422);
    }
}