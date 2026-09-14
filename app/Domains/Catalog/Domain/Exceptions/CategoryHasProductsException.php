<?php

namespace App\Domains\Catalog\Domain\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CategoryHasProductsException extends Exception
{
    public function __construct(string $message = 'لا يمكن حذف التصنيف لوجود منتجات مرتبطة به.')
    {
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